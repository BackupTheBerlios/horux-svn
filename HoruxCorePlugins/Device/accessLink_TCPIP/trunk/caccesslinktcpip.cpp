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
#include "caccesslinktcpip.h"
#include <QtCore>

CAccessLinkTCPIP::CAccessLinkTCPIP(QObject *parent) : QObject(parent)
{
  _isConnected = false;
  input = 0;
  output = 0;
  status = FREE;
  msgTimeoutTimer = 0;
  doorLockMode = NONE;
  openModeTimeout = 0;
  openModeTimeoutTimer = 0;
  openModeInput = 0;
  socket = NULL;
  isFreeAccess = false;
  
  addFunction("openDoor", CAccessLinkTCPIP::s_openDoorLock);

}

CDeviceInterface *CAccessLinkTCPIP::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
  CDeviceInterface *p = new CAccessLinkTCPIP ( parent );

  p->setParameter("name",config["name"]);
  p->setParameter("_isLog",config["isLog"]);
  p->setParameter("accessPlugin",config["accessPlugin"]);
  p->setParameter("id",config["id_device"]);  
  p->setParameter("ip",config["ip"]);
  p->setParameter("port",config["port"]);
  p->setParameter("oTime1",config["outputTime1"]);
  p->setParameter("oTime2",config["outputTime2"]);
  p->setParameter("oTime3",config["outputTime3"]);
  p->setParameter("oTime4",config["outputTime4"]);
  p->setParameter("antipassback",config["antipassback"]);
  p->setParameter("doorLockMode",config["open_mode"]);
  p->setParameter("openModeTimeout",config["open_mode_timeout"]);
  p->setParameter("openModeInput",config["open_mode_input"]);


  return p;
}


void CAccessLinkTCPIP::deviceAction(QString xml)
{

  QDomDocument doc;
  doc.setContent(xml);

  QDomElement root = doc.documentElement();

  QDomNode node = root.firstChild();

  if(root.tagName() != "deviceAction")
  {
    return;
  }

  if(root.attribute("id").toInt() != id)
    return;

  QDomNode actionNode = root.firstChild();

  while(!actionNode.isNull())
  {
    QDomElement action = actionNode.toElement();

    if(action.tagName() == "action") 
    {
      QString funcName;
      QMap<QString, QVariant>funcParam;

      QDomNode functionNode = action.firstChild();
      while(!functionNode.isNull())
      {
        QDomElement function = functionNode.toElement();

        if(function.tagName() == "function")
          funcName = function.text();

        if(function.tagName() == "params")
        {
          QDomNode paramsNode = function.firstChild();
          while(!paramsNode.isNull())
          {
            QDomElement params = paramsNode.toElement();

            if(params.tagName() == "param")
            {
              QString pName;
              QVariant pValue;
              QDomNode p = params.firstChild();
              if(p.toElement().tagName() == "name")
              {  
                pName = p.toElement().text();
                p = p.nextSibling();
                if(p.toElement().tagName() == "value")
                {
                  pValue = p.toElement().text();
                  funcParam[pName] = pValue;
                }
              } 
            }
            
            paramsNode = paramsNode.nextSibling(); 
          }

        }

        functionNode = functionNode.nextSibling(); 

      }
      
      if(interfaces[funcName])
      {
          void (*func)(QObject *, QMap<QString, QVariant>) = interfaces[funcName];
          func(getMetaObject(), funcParam);
      }
      else
        qDebug("The function %s is not define in the device %s", funcName.toLatin1() .constData(), name.toLatin1().constData());
    }

    actionNode = actionNode.nextSibling(); 
  }

}


void CAccessLinkTCPIP::connectChild(CDeviceInterface *)
{

}

QVariant CAccessLinkTCPIP::getParameter(QString paramName)
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
  if(paramName == "oTime1")
    return oTime1;
  if(paramName == "oTime2")
    return oTime2;
  if(paramName == "oTime3")
    return oTime3;
  if(paramName == "oTime4")
    return oTime4;
  if(paramName == "antipassback")
    return antipassback;
  if(paramName == "doorLockMode")
    return doorLockMode;
  if(paramName == "openModeTimeout")
    return openModeTimeout;
  if(paramName == "openModeInput")
    return openModeInput;



  return "undefined";
}

void CAccessLinkTCPIP::setParameter(QString paramName, QVariant value) 
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
  if(paramName == "oTime1")
    oTime1 = value.toInt();
  if(paramName == "oTime2")
    oTime2 = value.toInt();
  if(paramName == "oTime3")
    oTime3 = value.toInt();
  if(paramName == "oTime4")
    oTime4 = value.toInt();
  if(paramName == "antipassback")
    antipassback = value.toInt();
  if(paramName == "doorLockMode")
  {
    doorLockMode = (DOOR_LOCK_MODE)value.toInt();

    if(value.toString().toLatin1() == "NONE") doorLockMode = NONE;
    if(value.toString().toLatin1() == "NO_TIMEOUT") doorLockMode = NO_TIMEOUT;
    if(value.toString().toLatin1() == "TIMEOUT") doorLockMode = TIMEOUT;
    if(value.toString().toLatin1() == "TIMEOUT_IN") doorLockMode = TIMEOUT_IN;
  }

  if(paramName == "openModeTimeout")
    openModeTimeout = value.toInt();

  if(paramName == "openModeInput")
    openModeInput = value.toInt();

}


bool CAccessLinkTCPIP::open()
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

void CAccessLinkTCPIP::deviceConnected()
{

  _isConnected = true;
  
  emit deviceConnection(id, true);

  //checkConnectionTimer = startTimer(10000);

  //! ask the reader firmware
  getFirmware();

  //! ask the reader serial number
  getSerialNumber();

  //! set antenna on
  setAntenna(true);

  //! reset the output
  resetOutput();  
  
  //! read output status
  getOutputStatus();

}

void CAccessLinkTCPIP::deviceDiconnected()
{
  close();
}

void CAccessLinkTCPIP::deviceError( QAbstractSocket::SocketError socketError )
{
  qDebug() << "Socket error " << socketError;
}

void CAccessLinkTCPIP::readyRead ()
{
  if(socket->bytesAvailable () > 0)
  {
    msg += socket->readAll();
    hasMsg();
  }
}

void CAccessLinkTCPIP::close()
{
  _isConnected = false;
  input = 0;

  /*if(checkConnectionTimer)
  {
    killTimer(checkConnectionTimer);
    checkConnectionTimer = 0;
  }*/

  if(socket)
  {
    socket->close();
    socket->deleteLater();
    
  }

  if(msgTimeoutTimer)
  {
    killTimer(msgTimeoutTimer);
    msgTimeoutTimer = 0;
  }

  if(openModeTimeoutTimer)
  {
    killTimer(openModeTimeoutTimer);
    openModeTimeoutTimer = 0;
  }

  QMapIterator<QString, int> i(passBackTimer);
  while (i.hasNext()) 
  {
      i.next();
      killTimer(i.value());
  }

  passBackTimer.clear();

  for(int i=0; i<pendingMessage.size(); i++)
  {
    QByteArray *ba = pendingMessage.at(i);
    delete ba;
  }

  pendingMessage.clear();


  status = FREE;

  emit deviceConnection(id, false);
}

bool CAccessLinkTCPIP::isOpened()
{
  return _isConnected;
}

void CAccessLinkTCPIP::appendMessage(uchar *msg, int len)
{
  if(status == FREE)
  {
    if(isOpened())
    {
      status = BUSY;
      msgTimeoutTimer = startTimer(500);
      currentMessage.clear();
      
      for(int i=0; i<len; i++)      
        currentMessage.append(msg[i]);

      socket->write((const char*)msg,len);

      socket->flush();
      logComm(msg, false, len);
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

void CAccessLinkTCPIP::hasMsg()
{
  //! do we read any byte
  if( msg.length() == 0) return;


  uchar len = (uchar)msg[0];

  //! check if we have enough data for at least one message
  if( msg.length() < len) return;

  uchar cmd = (uchar)msg[1];

  //! be sure that the message is tag detection or tag standart read
  if(cmd == 0x90 || cmd == 0x92)
  {
      uchar tagStatus = (uchar)msg[2] & 0x07;

      //! wait, no information, timeout
      if(tagStatus == 0 || tagStatus == 0x04 || tagStatus == 0x05)
      {
        logComm((uchar*)msg.left(len).data(), true, len);
        msg.remove(0,len);
        hasMsg();
        return;
      }

      //! more data will comming
      if(tagStatus == 0x01 || tagStatus == 0x02)
      {
        if(cmd == 0x90)
          keyDetection << msg.left(len);
        logComm((uchar*)msg.left(len).data(), true, len);
        msg.remove(0,len);
        hasMsg();
        return;
      }

      //! all data are received
      if(tagStatus == 0x03 || tagStatus == 0x07)
      {
        if(cmd == 0x90)
          keyDetection << msg.left(len);
        logComm((uchar*)msg.left(len).data(), true, len);
        msg.remove(0,len);

        QByteArray key;

        //! read only tag or read/write tag without tag identification
        if(keyDetection.count() == 1)
        {
          QByteArray sn = keyDetection.at(0);

          //! this is not possible, the sn is missing
          if(sn[3] == (char)0x21)
          {
            keyDetection.clear();
            hasMsg();
            return;
          }

          //! read only tag
          if(sn[0] == (char)10)
          {
            key = sn.mid(4,5);
          }

          //! read write tag without tag identification
          if(sn[0] == (char)9)
          {
            key = sn.mid(4,4);
          }
        }

        //! read/write tag with tag identification
        if(keyDetection.count() == 2)
        {
          QByteArray sn = keyDetection.at(0).mid(4,4);
          QByteArray tid = keyDetection.at(1).mid(4,4);

          key = tid + sn;
        }

        keyDetection.clear();

        if(key.length() > 0)
        {
          QString sn = "", sn1;
          for(int i=0; i<key.length(); i++)
            sn += sn1.sprintf("%02X", (uchar)key.at(i));

          sn = sn.rightJustified(16, '0');

          handleSn(sn);
        }

      }
   }
   else
   {
        dispatchMessage(msg.left(len));
   }

   hasMsg();

}

void CAccessLinkTCPIP::timerEvent(QTimerEvent *e)
{
  /*if(checkConnectionTimer == e->timerId())
  {
      //! read output status
      getOutputStatus();

      return;
  }*/



  if(msgTimeoutTimer == e->timerId())
  {
      //! one message was maybe not well sended
      QString cmd;
      cmd = cmd.sprintf("%02X",currentMessage.at(1));
      QString xml = CXmlFactory::deviceEvent(QString::number(id),"1017", "Do not receive the response from the reader (" + cmd + ")");
        
      emit deviceEvent(xml);

      killTimer(msgTimeoutTimer);
      msgTimeoutTimer = 0;

      status = FREE;
      //! send next message
      if(pendingMessage.size() > 0)
      {
        QByteArray *baNext = pendingMessage.takeFirst();
        if(baNext)
        {
          appendMessage((uchar*)baNext->constData(), baNext->size());
          delete baNext;
        }
      }

      return;
  } 

  if(openModeTimeoutTimer == e->timerId())
  {
    //! we can reaccpet new tag
    killTimer(openModeTimeoutTimer);
    openModeTimeoutTimer = 0;
    return;
  }

  //! check the antipassback timer
  QMapIterator<QString, int> i(passBackTimer);
  while (i.hasNext()) 
  {
      i.next();

      if(e->timerId() == i.value())
      {
        killTimer(i.value());
        passBackTimer.remove(i.key());
        return;
      }
  }  
}

void CAccessLinkTCPIP::dispatchMessage(QByteArray ba)
{
  logComm((uchar*)ba.data(), true, ba.size());

  switch((unsigned char)ba.at(1))
  {
    case 0x01: // software version
      {
        QString osVersion, appVersion;
        osVersion = QString::number((uchar)ba.at(3),16) + 
                    QString::number(((uchar)ba.at(4) & 0xF0)>>4,16) + "." + 
                    QString::number(((uchar)ba.at(4) & 0x0F),16);

        appVersion = QString::number((uchar)ba.at(5),16) + 
                    QString::number(((uchar)ba.at(6) & 0xF0)>>4,16) + "." +
                    QString::number(((uchar)ba.at(6) & 0x0F),16);

        firmwareVersion = osVersion + "/" + appVersion;

        checkReaderStatus((uchar)ba.at(2));
      }
      break;
    case 0x02: // serial number 
      {
        unsigned long serialNumber = ((uchar)ba.at(3)<<24);
        serialNumber |= (uchar)ba.at(4)<<16;
        serialNumber |= (uchar)ba.at(5)<<8;
        serialNumber |= (uchar)ba.at(6);
        this->serialNumber = QString::number(serialNumber);

        checkReaderStatus((uchar)ba.at(2));

      }
      break;
    case 0x11:  //input status read

      checkReaderStatus((uchar)ba.at(2));
      break;
    case 0x12:  //output status read
      {
        if(output != (uchar)ba.at(2))
        {
          uchar mask = 1;
      
          for(int i = 0; i<8; i++)
          {
            uchar b_new = (uchar)ba.at(2) & mask;
            uchar b_old = input & mask;
      
            if(b_new != b_old)
            {
              switch(mask)
              {
                case 1: //Output 1
                  break;
                case 2: //Output 2
                  break;
                case 4: //Output 3
                  break;
                case 8: //Output 4
                  break;
                case 16: //modulation
                  if(b_new == 1)
                  {

                    QString xml = CXmlFactory::deviceEvent(QString::number(id),"1015", "The modulation is off");
                    
                    emit deviceEvent(xml); 
                  }
                  break;
                case 32: //not used
                  break;
                case 64: //antenna power
                  if(b_new == 1)
                  {
                    QString xml = CXmlFactory::deviceEvent(QString::number(id),"1015", "The antenna power is off");
                    
                    emit deviceEvent(xml); 
                  }
                  break;
                case 128://not used
                  break;
              }
            }
      
            mask <<= 1;
          }
      
          output = (uchar)ba.at(2);
        }
      }
      break;
    case 0x18:  //output status write (in memory )
      checkReaderStatus((uchar)ba.at(2));
      break;
    case 0x19:  //output status write (not in memory )
      checkReaderStatus((uchar)ba.at(2));
      break;
    case 0x1A:  //output blink frequency
      checkReaderStatus((uchar)ba.at(2));
      break;
    case 0x1B:  //output control write
      checkReaderStatus((uchar)ba.at(2));
     break;
  }

  msg.remove(0,ba.size());

  //! receive the reader response, send the next message if has one
  status = FREE;
  killTimer(msgTimeoutTimer);
  msgTimeoutTimer = 0;


  if(pendingMessage.size() > 0)
  {
    QByteArray *baNext = pendingMessage.takeFirst();
    if(baNext)
    {
      appendMessage((uchar*)baNext->constData(), baNext->size());
      delete baNext;
    }
  }
}

void CAccessLinkTCPIP::getFirmware()
{
  uchar cmd[3];

  cmd[0] = 0x03;
  cmd[1] = 0x01;
  cmd[2] = 0x02;

  appendMessage(cmd, 3);
}

void CAccessLinkTCPIP::getSerialNumber()
{
  uchar cmd[3];

  cmd[0] = 0x03;
  cmd[1] = 0x02;
  cmd[2] = 0x01;

  appendMessage(cmd, 3);
}

void CAccessLinkTCPIP::setAntenna(bool flag)
{
  uchar cmd[4];

  if(flag)
    output &= 0xBF;
  else
    output |= 0x40;

  cmd[0] = 0x04;
  cmd[1] = 0x19;
  cmd[2] = output;
  cmd[3] = cmd[0] ^ cmd[1] ^ cmd[2];

  appendMessage(cmd, 4);

}

void CAccessLinkTCPIP::resetOutput()
{
  uchar cmd[4];

  output &= 0xF0;   //! 1111 0000

  cmd[0] = 0x04;
  cmd[1] = 0x19;
  cmd[2] = output;
  cmd[3] = cmd[0] ^ cmd[1] ^ cmd[2];

  appendMessage(cmd, 4);
}

void CAccessLinkTCPIP::getRdStatus()
{
  uchar cmd[3];

  cmd[0] = 0x03;
  cmd[1] = 0x11;
  cmd[2] = 0x12;

  appendMessage(cmd, 3);
}

void CAccessLinkTCPIP::getOutputStatus()
{
  uchar cmd[3];

  cmd[0] = 0x03;
  cmd[1] = 0x12;
  cmd[2] = 0x11;

  appendMessage(cmd, 3);
}



Q_EXPORT_PLUGIN2(accesslinktcpip, CAccessLinkTCPIP);




/*!
    \fn CAccessLinkTCPIP::checkReaderStatus(uchar status)
 */
void CAccessLinkTCPIP::checkReaderStatus(uchar status)
{
  if(input != status)
  {
    uchar mask = 1;


    for(int i = 0; i<INPUT_NUMBER; i++)
    {
      uchar b_new = status & mask;
      uchar b_old = input & mask;

      if(b_new != b_old)
      {

        if(TIMEOUT_IN == doorLockMode)
        {

            if(openModeInput == mask)
            {
              //! we can reaccpet new tag
              if(openModeTimeoutTimer)
              {
                killTimer(openModeTimeoutTimer);
                openModeTimeoutTimer = 0;
              }
            }
        }

        emit deviceInputChange(id, mask, (bool)b_new);
      }

      mask <<= 1;
    }

    //! if the door lock mode is waiting on the 2 input activation we need to check the to input
    if(TIMEOUT_IN == doorLockMode)
    {
        if(openModeInput == 3)
        {
          if((status & 0x03) == 0)
          {
            //! we can reaccpet new tag
            if(openModeTimeoutTimer)
            {

              killTimer(openModeTimeoutTimer); 
              openModeTimeoutTimer = 0;
            }
          }
        }
    }

    //!Tag control
    uchar tc_new = status & 0x40;
    uchar tc_old = input & 0x40;

    if(tc_new != tc_old)
    {
        if(tc_new > 0)
        {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1015", "The tag control is off");

            emit deviceEvent(xml); 
        }
        else
        {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1014", "The tag control is on");
            emit deviceEvent(xml); 
        }
    }

    input = status;
  }
}


/*!
    \fn CAccessLinkTCPIP::handleSn(QString sn)
 */
void CAccessLinkTCPIP::handleSn(QString sn)
{

  switch(doorLockMode)
  {
    //! we only track the sn like for a timestamp reader
    case NONE:
    case NO_TIMEOUT:
      break;

    //! we wait some seconds before to accept new tag
    case TIMEOUT:
    case TIMEOUT_IN:
      if(openModeTimeoutTimer)
        return;
      break;
  }

  //! the sn is in the antipassback, do not accept it
  if(passBackTimer[sn])
  {
    return;
  }

  if(antipassback > 0)
  {
    //! put the sn in the antipassback. The antipassback is in second in the database
    passBackTimer[sn] = startTimer(antipassback*1000);
  }
  else //! protect for multiple presentation
    passBackTimer[sn] = startTimer(500);


  switch(doorLockMode)
  {
    //! we only track the sn like for a timestamp reader
    case NONE:
    case NO_TIMEOUT:
      break;

    case TIMEOUT:
    case TIMEOUT_IN:
      if(openModeTimeout>0)
        openModeTimeoutTimer = startTimer(openModeTimeout*1000);
      break;
  }

  QString xml = CXmlFactory::keyDetection(QString::number(id),getAccessPluginName(), sn);

  emit deviceEvent(xml);

}


void CAccessLinkTCPIP::s_openDoorLock(QObject *p, QMap<QString, QVariant>params)
{

  CAccessLinkTCPIP *pThis = qobject_cast<CAccessLinkTCPIP *>(p);
  //! check by which access plugin the device musst be controlled
  if(pThis->getAccessPluginName() != "")
  {
    if(params.contains("PluginName"))
    {
      if(params["PluginName"].toString() != pThis->getAccessPluginName() )
        return;
    }
    else
      return;
  }

	
  //! do we need to open or close the door for free acces time 	
  if(params.contains("freeAccess"))
  {
	//! do nothing if isAccess don't change
    if( pThis->isFreeAccess == params["freeAccess"].toBool() ) return;
	
    pThis->isFreeAccess = params["freeAccess"].toBool();
	
    uchar cmd[4];
    if(pThis->isFreeAccess)
    {
        //!  enable the output
        cmd[0] = 0x05;
        cmd[1] = 0x19;
        cmd[2] = OUTPUT_1;
          
        cmd[3] =  cmd[0] ^ cmd[2] ^ cmd[3];

        pThis->appendMessage(cmd, 4);    
    }
    else
    {
        //! reset the output
        cmd[0] = 0x05;
        cmd[1] = 0x19;
        cmd[2] = 0x00;
          
        cmd[3] =  cmd[0] ^ cmd[1] ^ cmd[2];

        pThis->appendMessage(cmd, 4);    	
    }
    return;
  }  	
	
  //! don't accept open door command during a free access
  if(pThis->isFreeAccess) return;
	
  bool isAccess = false;
  if(params.contains("isAccess"))
    isAccess = params["isAccess"].toBool();

  uchar cmd[5];

  uchar o = 0;

  if(pThis->doorLockMode != NONE)
    o |= OUTPUT_1;

  if( pThis->oTime2 > 0)
    o |= OUTPUT_2;

  if( pThis->oTime3 > 0)
    o |= OUTPUT_3;

  //! reset the output
  cmd[0] = 0x05;
  cmd[1] = 0x1B;
  cmd[2] = o;
  cmd[3] = 0;
 
  cmd[4] =  cmd[0] ^ cmd[1] ^ cmd[2] ^ cmd[3];

  pThis->appendMessage(cmd, 5);

  if(isAccess)
  {
    //! do not open in this mode
    if(pThis->doorLockMode != NONE)
    {
      cmd[0] = 0x05;
      cmd[1] = 0x1B;
      cmd[2] = OUTPUT_1;
      cmd[3] = pThis->oTime1;
      
      cmd[4] =  cmd[0] ^ cmd[1] ^ cmd[2] ^ cmd[3];
  
      pThis->appendMessage(cmd, 5);
    }

    if(pThis->oTime2>0)
    {
      cmd[0] = 0x05;
      cmd[1] = 0x1B;
      cmd[2] = OUTPUT_2;
      cmd[3] = pThis->oTime2;
      
      cmd[4] =  cmd[0] ^ cmd[1] ^ cmd[2] ^ cmd[3];
  
      pThis->appendMessage(cmd, 5);
    }
  }
  else
  {
    if(pThis->oTime3>0)
    {
      cmd[0] = 0x05;
      cmd[1] = 0x1B;
      cmd[2] = OUTPUT_3;
      cmd[3] = pThis->oTime3;
      
      cmd[4] =  cmd[0] ^ cmd[1] ^ cmd[2] ^ cmd[3];
  
      pThis->appendMessage(cmd, 5); 
    }
  }    
}


/*!
    \fn CAccessLinkTCPIP::logComm(QByteArray ba)
 */
void CAccessLinkTCPIP::logComm(uchar *ba, bool isReceive, int len)
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

QDomElement CAccessLinkTCPIP::getDeviceInfo(QDomDocument xml_info )
{
  QDomElement device = xml_info.createElement( "device");
  device.setAttribute("id", QString::number(id));

  QDomElement newElement = xml_info.createElement( "name");
  QDomText text =  xml_info.createTextNode(name);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "serialNumber");
  text =  xml_info.createTextNode(serialNumber);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "isConnected");
  text =  xml_info.createTextNode(QString::number(_isConnected));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "firmwareVersion");
  text =  xml_info.createTextNode(firmwareVersion);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "input1");
  text =  xml_info.createTextNode(QString::number((bool)(input & 0x01)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "input2");
  text =  xml_info.createTextNode(QString::number((bool)(input & 0x02)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output1");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x01)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output2");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x02)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output3");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x04)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output4");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x08)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "modulation");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x10)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "antenna");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x40)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  return device;

}
