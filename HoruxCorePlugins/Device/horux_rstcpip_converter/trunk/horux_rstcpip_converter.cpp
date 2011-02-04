
#include "horux_rstcpip_converter.h"
#include "QTimer"
#include "QHostInfo"

CHRstcpipC::CHRstcpipC(QObject *parent) : QObject(parent)
{
   // init default values
   _isConnected = false;
   ip = "";
   firstConnCheck = true;
   socket = NULL;
   testSocket = new QTcpSocket(this);
   timer = new QTimer(this);
   connect(timer, SIGNAL(timeout()), this, SLOT(checkConnection()));
}

CDeviceInterface *CHRstcpipC::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
   CDeviceInterface *p = new CHRstcpipC ( parent );

   p->setParameter("name",config["name"]);
   p->setParameter("_isLog",config["isLog"]);
   p->setParameter("accessPlugin",config["accessPlugin"]);
   p->setParameter("id",config["id_device"]);

   p->setParameter("ip",config["ip"]);
   p->setParameter("port",config["port"]);

   return p;
}

QVariant CHRstcpipC::getParameter(QString paramName)
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
   if(paramName == "socket")
      return qVariantFromValue((QObject*)socket);;

   return "undefined";
}

void CHRstcpipC::setParameter(QString paramName, QVariant value)
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
      port = value.toString();
}

bool CHRstcpipC::open()
{
   // check if we are already connected
   if(socket && socket->isOpen())
   {
      return true;
   }
   else
      socket = new QTcpSocket(this);

   // connect the sockets's signals to their slots
   connect(socket, SIGNAL(readyRead ()), this, SLOT(readyRead()));
   connect(socket, SIGNAL(connected ()), this, SLOT(deviceConnected()));
   connect(socket, SIGNAL(disconnected ()), this, SLOT(deviceDiconnected()));
   connect(socket, SIGNAL(error ( QAbstractSocket::SocketError )), this, SLOT(deviceError( QAbstractSocket::SocketError )));

   connect(testSocket, SIGNAL(error ( QAbstractSocket::SocketError )), this, SLOT(deviceTestState( QAbstractSocket::SocketError )));

   // disconnect the check socket
   testSocket->close();
   testSocket->abort();

   // connect to the peripheral
   socket->connectToHost(ip, port.toInt());

   timer->start(10000);

   return true;
}

void CHRstcpipC::checkConnection() {
   if (!firstConnCheck)
   {
      if (testSocket->isOpen()) {
         open();
      }
      else {
         close();
      }
   }
   else
      firstConnCheck = false;

   testSocket->close();
   testSocket->abort();
   testSocket->connectToHost(ip, port.toInt(), QIODevice::ReadOnly);
}

void CHRstcpipC::close()
{
   if (!_isConnected)
      return;

   _isConnected = false;

   if(socket)
   {
      socket->close();
      socket->deleteLater();
      socket = NULL;
   }

   /*if(testSocket)
   {
      testSocket->close();
      testSocket->deleteLater();
   }*/

   /*if (timer)
      delete timer;*/

   // emit the signal for the subsystems
   emit deviceConnection(id, false);
}

bool CHRstcpipC::isOpened()
{
   return _isConnected;
}

QDomElement CHRstcpipC::getDeviceInfo(QDomDocument xml_info )
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

void CHRstcpipC::deviceConnected()
{
   _isConnected = true;
   emit deviceConnection(id, true);
   qDebug() << "connected!";

   socket->setSocketOption(QAbstractSocket::KeepAliveOption, 1);
}

void CHRstcpipC::deviceDiconnected()
{
   close();
   qDebug() << "disconnected!";
}

void CHRstcpipC::deviceError( QAbstractSocket::SocketError socketError )
{
   switch(socketError)
   {
   case QAbstractSocket::ConnectionRefusedError:
      close();
      break;
   case QAbstractSocket::HostNotFoundError:
      close();
      break;
   case QAbstractSocket::SocketAccessError:
      close();
      break;
   case QAbstractSocket::SocketResourceError:
      close();
      break;
   case QAbstractSocket::SocketTimeoutError:
      close();
      break;
   case QAbstractSocket::DatagramTooLargeError:
      close();
      break;
   case QAbstractSocket::NetworkError:
      close();
      break;
   case QAbstractSocket::RemoteHostClosedError:
      close();
      break;
   default:
      qDebug() << "Socket error (" << socketError << ") on the device" << name;
   }
   qDebug() << "Socket error (" << socketError << ") on the device" << name;
}

void CHRstcpipC::deviceTestState (QAbstractSocket::SocketError socketError )
{
   if (socketError != QAbstractSocket::ConnectionRefusedError)
      testSocket->close();
}

void CHRstcpipC::readyRead()
{
   if (socket->bytesAvailable() > 0)
   {
      QByteArray msg = socket->readAll();
      dispatchMessage(msg);
   }
}

void CHRstcpipC::dispatchMessage(QByteArray ba)
{
   emit subDeviceMessage(ba);
}

void CHRstcpipC::deviceAction(QString xml)
{
   QMap<QString, MapParam> func = CXmlFactory::deviceAction(xml, id);

   QMapIterator<QString, MapParam> i(func);

   while (i.hasNext())
   {
      i.next();
      if(interfaces[i.key()])
      {
         void (*func)(QObject *, QMap<QString, QVariant>) = interfaces[i.key()];
         func(getMetaObject(), i.value());
      }

      if( i.key() == "") break;

      // try to push the action to a child device
      /*foreach(CDeviceInterface *d, childDevice)
      {
         QString xml_tmp = xml;
         xml_tmp.replace("<deviceAction id=\"" + QString::number(id)  + "\">", "<deviceAction id=\"" + d->getParameter("id").toString() + "\">");
         xml_tmp.replace("<deviceAction id=\"" + QString::number(id)  + "\" >", "<deviceAction id=\"" + d->getParameter("id").toString() + "\">");
         d->deviceAction(xml_tmp);
      }*/
   }
}

void CHRstcpipC::connectChild(CDeviceInterface *device)
{
   if(!childDevice.contains(device->getParameter("id").toInt()))
   {
      if(device)
      {
         childDevice[device->getParameter("id").toInt()] = device;
         connect(this, SIGNAL(subDeviceMessage(QByteArray)), device->getMetaObject(), SLOT(dispatchMessage(QByteArray)));
         connect(this, SIGNAL(deviceConnection(int, bool)), device->getMetaObject(), SLOT(connection(int, bool)));
         //device->open();
      }
   }
}

void CHRstcpipC::logComm(uchar *ba, bool isReceive, int len)
{
   if(!_isLog)
      return;

   QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

   checkPermision(logPath + "log_" + name + ".html");

   // open the log file
   QFile file(logPath + "log_" + name + ".html");
   if (!file.open(QIODevice::Append | QIODevice::Text))
      return;

   // get a readable content from the received byte array
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

Q_EXPORT_PLUGIN2(hrstcpipc, CHRstcpipC);
