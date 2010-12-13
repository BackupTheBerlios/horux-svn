/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License.     *
 *                                       *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/
#include "clcddisplay.h"
#include <QtCore>
#include <QDateTime>

CLCDDisplay::CLCDDisplay(QObject *parent) : QObject(parent)
{
  deviceParent = NULL;
  _isConnected = false;
  status = FREE;
  socket = NULL;
  timeDateTimer = NULL;
  messageTimer = new QTimer(this);

  connect(messageTimer, SIGNAL(timeout()), SLOT(displayDefaulfMessage()));

  addFunction("displayMessage", CLCDDisplay::s_displayMessage);
}

CDeviceInterface *CLCDDisplay::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
  CDeviceInterface *p = new CLCDDisplay ( parent );

  p->setParameter("name",config["name"]);
  p->setParameter("_isLog",config["isLog"]);
  p->setParameter("accessPlugin",config["accessPlugin"]);
  p->setParameter("id",config["id_device"]);  
  p->setParameter("ip",config["ip"]);
  p->setParameter("port",config["port"]);
  p->setParameter("messageTimerDisplay",config["messageTimerDisplay"]);
  p->setParameter("defaultMessage",config["defaultMessage"]);

  return p;
}


void CLCDDisplay::deviceAction(QString xml)
{
  int parent_id = 0;
  if(deviceParent)
      parent_id = deviceParent->getParameter("id").toInt();

  QMap<QString, MapParam> func = CXmlFactory::deviceAction(xml, id,parent_id);
  QMapIterator<QString, MapParam> i(func);
  while (i.hasNext())
  {
     i.next();
     if(interfaces[i.key()])
     {
          void (*func)(QObject *, QMap<QString, QVariant>) = interfaces[i.key()];
          func(getMetaObject(), i.value());
      }
  }
}


void CLCDDisplay::connectChild(CDeviceInterface *)
{

}

QVariant CLCDDisplay::getParameter(QString paramName)
{
  if(paramName == "name")
    return name;
  if(paramName == "id")
    return id;
  if(paramName == "_isLog")
    return _isLog;
  if(paramName == "accessPlugin")
    return accessPlugin;


  if(paramName == "ip")
    return ip;
  if(paramName == "port")
    return port;
  if(paramName == "messageTimerDisplay")
    return messageTimerDisplay;
  if(paramName == "defaultMessage")
    return defaultMessage;

  return "undefined";
}

void CLCDDisplay::setParameter(QString paramName, QVariant value)
{
  if(paramName == "name")
    name = value.toString();
  if(paramName == "id")
    id = value.toInt();
  if(paramName == "_isLog")
    _isLog = value.toBool();
  if(paramName == "accessPlugin")
    accessPlugin = value.toString();

  if(paramName == "ip")
    ip = value.toString();
  if(paramName == "port")
    port = value.toInt();
  if(paramName == "messageTimerDisplay")
    messageTimerDisplay = value.toInt();
  if(paramName == "defaultMessage")
    defaultMessage = value.toString();
}


bool CLCDDisplay::open()
{

  if(socket)
  {
     if(socket->isOpen())
        return true;
  }
  socket = new QTcpSocket(this);

  connect(socket, SIGNAL(readyRead ()), this, SLOT(readyRead()));
  connect(socket, SIGNAL(connected ()), this, SLOT(deviceConnected()));
  connect(socket, SIGNAL(disconnected ()), this, SLOT(deviceDiconnected()));
  connect(socket, SIGNAL(error ( QAbstractSocket::SocketError )), this, SLOT(deviceError( QAbstractSocket::SocketError )));

  socket->connectToHost(ip, port);

  return true;
}

void CLCDDisplay::reopen()
{
    open();
}

void CLCDDisplay::deviceConnected()
{

  _isConnected = true;
  
  emit deviceConnection(id, true);

  QChar reset(0x0c);

  QChar esc(0x1b);
  QChar hiddenCursor('C');

  QString clear = "";
  clear += reset;
  clear += esc;
  clear += hiddenCursor;

  uchar *m = new uchar[clear.toLatin1().length()];

  for(int i=0; i<clear.toLatin1().length(); i++)
      m[i] = clear.at(i).toLatin1();

  appendMessage(m, clear.length());

  displayDefaulfMessage();
}

void CLCDDisplay::displayDefaulfMessage()
{
    QDateTime dateTime = QDateTime::currentDateTime();

    QString mTmp = defaultMessage;
    QString mTmp2 = mTmp;


    if(timeDateTimer == NULL && ( mTmp.contains("{date}") || mTmp.contains("{time}") ) )
    {
        timeDateTimer = new QTimer(this);
        connect(timeDateTimer, SIGNAL(timeout()), this, SLOT(displayDefaulfMessage()));

        timeDateTimer->start(1000 * 60);
    }


    if( mTmp.contains("{date}") || mTmp.contains("{time}"))
    {

        //insert the time
        mTmp.replace(QString("{time}"), QString(dateTime.toString("hh:mm")));
        //insert the date
        mTmp.replace(QString("{date}"), QString(dateTime.toString("dd-MM-yyyy")));

        // when we display a other message thant the default message, the timer was stopped
        if(!timeDateTimer->isActive())
            timeDateTimer->start(1000 * 60);
    }

    displayMessage(mTmp);
}

void CLCDDisplay::displayMessage(QString message)
{
    QString lineFeed = "";
    lineFeed += QChar(0x0A) ;
    lineFeed += QChar(0x0D) ;
    //replace the carriage return and at the next line
    message.replace(QString("{nl}"), lineFeed);

    //replace the cursor position
    message.replace(QRegExp("\\{([0-9]*),([0-9]*)\\}"), " ESC O \\1 \\2 ");

    //add the reset screen char
    QChar reset(0x0c);

    message = reset + message;

    int length = 0;

    bool isUM = false;
    uchar rep = 0;

    for(int i=0; i<message.toLatin1().length(); i++)
    {
        switch((unsigned char)message.at(i).toLatin1())
        {
            case 0xC3:
                //ignore this char
                break;
            case 0xA4:    // ä
                length++;
                break;
            case 0xBC:    // ü
                length++;
                break;
            case 0xB6:    // ö
                length++;
                break;
            case 0x41:    // Ä
                if(isUM)
                    rep = 0x8E;
                else
                    length++;
                break;
            case 0x55:    // Ü
                if(isUM)
                    rep = 0x9A;
                else
                    length++;
                break;
            case 0x4F:    // Ö
                if(isUM)
                    rep = 0x99;
                else
                    length++;
                break;
            case 0x7B:
                isUM = true;
                break;
            case 0x7D:
                if(isUM)
                    length++;
                isUM = false;
                break;
            default:
                length++;
                break;
        }
    }

    uchar *m = new uchar[length];


    for(int i=0, j=0; i<message.toLatin1().length(); i++)
    {
        switch((unsigned char)message.at(i).toLatin1())
        {
            case 0xC3:
                //ignore this char
                break;
            case 0xA4:    // ä
                m[j++] = 0x84;
                break;
            case 0xBC:    // ü
                m[j++] = 0x81;
                break;
            case 0xB6:    // ö
                m[j++] = 0x94;
                break;
            case 0x41:    // Ä
                if(isUM)
                    rep = 0x8E;
                else
                    m[j++] = message.at(i).toAscii();
                break;
            case 0x55:    // Ü
                if(isUM)
                    rep = 0x9A;
                else
                    m[j++] = message.at(i).toAscii();
                break;
            case 0x4F:    // Ö
                if(isUM)
                    rep = 0x99;
                else
                    m[j++] = message.at(i).toAscii();
                break;
            case 0x7B:
                isUM = true;
                break;
            case 0x7D:
                if(isUM)
                    m[j++] = rep;
                isUM = false;
                break;
            default:
                m[j++] = message.at(i).toAscii();
                break;
        }


    }


    appendMessage(m, length);

}

void CLCDDisplay::deviceDiconnected()
{
  close();

  QTimer::singleShot(5000, this, SLOT(reopen()));

}

void CLCDDisplay::deviceError( QAbstractSocket::SocketError socketError )
{
  //qDebug() << "Socket error " << socketError;

  close();

  QTimer::singleShot(5000, this, SLOT(reopen()));
}

void CLCDDisplay::readyRead ()
{
  if(socket->bytesAvailable () > 0)
  {
    msg += socket->readAll();
    hasMsg();
  }
}

void CLCDDisplay::close()
{


  if(socket)
  {
    socket->close();
    socket->deleteLater();    
  }

  if(timeDateTimer && timeDateTimer->isActive())
  {
      timeDateTimer->stop();
  }

  if(messageTimer && messageTimer->isActive())
  {
      timeDateTimer->stop();
  }

  pendingMessage.clear();


  status = FREE;

  if(_isConnected)
    emit deviceConnection(id, false);

  _isConnected = false;


}

bool CLCDDisplay::isOpened()
{
  return _isConnected;
}

void CLCDDisplay::appendMessage(uchar *msg, int len)
{
  if(status == FREE)
  {
    if(isOpened())
    {
      //status = BUSY;

      currentMessage.clear();
      
      for(int i=0; i<len; i++)      
        currentMessage.append(msg[i]);

      socket->write((const char*)msg,len);

      socket->flush();
      logComm(msg, false, len);

      delete[] msg;
    }
  }
  else
  {
    if(isOpened())
    {
      QByteArray *ba = new QByteArray((const char*)msg, len);
      pendingMessage.append(ba);
    }
  }
}

void CLCDDisplay::hasMsg()
{
  //! do we read any byte
  if( msg.length() == 0) return;


  uchar len = (uchar)msg[0];

  //! check if we have enough data for at least one message
  if( msg.length() < len) return;

  dispatchMessage(msg.left(len));

}


void CLCDDisplay::dispatchMessage(QByteArray ba)
{
  logComm((uchar*)ba.data(), true, ba.size());

  msg.remove(0,ba.size());

  //! receive the reader response, send the next message if has one
  status = FREE;


  if(pendingMessage.size() > 0)
  {
    QByteArray *baNext = pendingMessage.takeFirst();
    if(baNext)
    {
      appendMessage((uchar*)baNext->constData(), baNext->size());
    }
  }
}

/*!
    \fn CLCDDisplay::logComm(QByteArray ba)
 */
void CLCDDisplay::logComm(uchar *ba, bool isReceive, int len)
{
  if(!_isLog)
    return;

  QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

	checkPermision(logPath + "log_" + name + ".html");

  QFile file(logPath + "log_" + name + ".html");
  if (!file.open(QIODevice::Append | QIODevice::Text))
   return;

  QString s = "",s1;
  
  for(int i=0;i<len; i++)
    s += s1.sprintf("%02X ",ba[i]);

  QTextStream out(&file);

  if(isReceive)
    out << "<span class=\"date\">" << date << "</span>" << "<span  style=\"color:blue\" class=\"receive\">" << s << "</span>" << "<br/>\n";
  else
    out << "<span class=\"date\">" << date << "</span>" << "<span style=\"color:green\" class=\"send\">" << s << "</span>" << "<br/>\n";

  file.close();
}

QDomElement CLCDDisplay::getDeviceInfo(QDomDocument xml_info )
{
  QDomElement device = xml_info.createElement( "device");
  device.setAttribute("id", QString::number(id));

  QDomElement newElement = xml_info.createElement( "name");
  QDomText text =  xml_info.createTextNode(name);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "isConnected");
  text =  xml_info.createTextNode(QString::number(_isConnected));
  newElement.appendChild(text);
  device.appendChild(newElement);

  return device;

}

void CLCDDisplay::s_displayMessage(QObject *p, QMap<QString, QVariant>params)
{

     CLCDDisplay *pThis = qobject_cast<CLCDDisplay *>(p);

     if(!pThis->isOpened())
         return;

     //stop the defaul message containing the date or the time
     if(pThis->timeDateTimer->isActive())
         pThis->timeDateTimer->stop();

     // stop the timer for an old message
     if(pThis->messageTimer->isActive())
        pThis->messageTimer->stop();

     //display the message
     pThis->displayMessage(params["message"].toString());

     //start a single shot timer to redisplay the default message after X seconds
     pThis->messageTimer->start(pThis->messageTimerDisplay * 1000);
}

void CLCDDisplay::connection(int deviceId, bool isConnected) {
    if(deviceId == deviceParent->getParameter("id")) {
        if(!isConnected)
            close();
        else
            open();
    }
}

Q_EXPORT_PLUGIN2(clcddisplay, CLCDDisplay);
