#include "a3m_lgm.h"

#include "QTimer"
#include "../../horux_rstcpip_converter/trunk/horux_rstcpip_converter.h"

CA3mLgm::CA3mLgm(QObject *parent) : QObject(parent)
{
   // initialisation des variables
   _isConnected = false;
   readerAction = 0;
   deviceParent = NULL;
   // on ajoute les fonctions disponible pour les sous systèmes (alarme, accès)
   addFunction("accessRefused", CA3mLgm::s_accessRefused);
   addFunction("accessAccepted", CA3mLgm::s_accessAccepted);
   status = FREE;
   busyCounter = 0;
   initReader = true;
}

CDeviceInterface *CA3mLgm::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
   CDeviceInterface *p = new CA3mLgm ( parent );

   p->setParameter("name",config["name"]);
   p->setParameter("_isLog",config["isLog"]);
   p->setParameter("accessPlugin",config["accessPlugin"]);
   p->setParameter("id",config["id_device"]);

   p->setParameter("address",config["address"]);
   p->setParameter("serialNumberFormat",config["serialNumberFormat"]);

   return p;
}

QVariant CA3mLgm::getParameter(QString paramName)
{
   if(paramName == "name")
      return name;
   if(paramName == "id")
      return id;
   if(paramName == "_isLog")
      return _isLog;
   if(paramName == "accessPlugin")
      return accessPlugin;
   if(paramName == "address")
      return address;
   if(paramName == "serialNumberFormat")
      return serialNumberFormat;

   return "undefined";
}

void CA3mLgm::setParameter(QString paramName, QVariant value)
{
   if(paramName == "name")
      name = value.toString();

   if(paramName == "id")
      id = value.toInt();

   if(paramName == "_isLog")
      _isLog = value.toBool();

   if(paramName == "accessPlugin")
      accessPlugin = value.toString();

   if(paramName == "address")
      address = value.toString();

   if(paramName == "serialNumberFormat")
      serialNumberFormat = value.toString();
}

bool CA3mLgm::open()
{
   timer = new QTimer(this);
   connect(timer, SIGNAL(timeout()), this, SLOT(tstcmd()));
   timer->start(100);

   _isConnected = true;

   CHRstcpipC* parent = (CHRstcpipC*) deviceParent;
   socket = parent->getSocket();

   return true;
}

void CA3mLgm::tstcmd() {
   QByteArray p;

   if (status == BUSY)
   {
      if (busyCounter % 5 >= 4) {
         //timer->setInterval(100);

         if (busyCounter <= 25)
         {
            socket->write(baNext, baNext.size());
            if (DEBUG) qDebug() << "RESEND!!!" << baNext.size();
         }
         else
         {
            qDebug()<<"stopRES" << busyCounter;
            _isConnected = false;
            status = FREE;
            busyCounter = 0;
            timer->stop();
         }
      }

      //timer->setInterval(100);
      busyCounter++;
   }

   // Init LEDs (turn off) at startup
   if (initReader && status == FREE)
   {
      initReader = false;
      p.resize(1);
      p[0] = 0x00;

      sendCmd(SET_LED, p);
      sendCmd(CMD_WIEGAND_FORMAT);
   }

   // Process the next pending message if we have one and the device isn't busy
   if(pendingMessage.size() > 0 && status == FREE)
   {
      baNext = pendingMessage.takeFirst();
      if(baNext.size())
      {
         status = BUSY;
         socket->write(baNext, baNext.size());
      }
   }
}

void CA3mLgm::close()
{
   _isConnected = false;

   pendingMessage.clear();
   delete timer;

   // émet le signal pour les sous systèmes
   emit deviceConnection(id, false);
}

bool CA3mLgm::isOpened()
{
   return _isConnected;
}

QDomElement CA3mLgm::getDeviceInfo(QDomDocument xml_info )
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

void CA3mLgm::hasMsg()
{
   if (!_isConnected) _isConnected = true;

   QString tmp, msgD;
   for (int i = 0; i < msg.size(); i++) {
      msgD += tmp.sprintf("%02X ", (uchar)msg.at(i));
   }
   if (DEBUG) qDebug() << "# " << msgD;

   QString idCard = "0x";
   int msgSize = msg.size();

   //! do we read any byte
   if(msgSize == 0) return;

   //! do we have at least 7 bytes
   if(msgSize >= 7)
   {
      uchar etxPos = msg.at(3)+5;

      if(msg.at(0) == 0x02 && etxPos < msgSize && msg.at(etxPos) == 0x03)
      {
         QString alarmXml;

         if (checkCheckSum(msg))
         {
            QString key;
            QString xml;
            int idParent;

            switch((uchar)msg.at(1))
            {
            case LGM_GET_SER_NUM:
               break;
            case LGM_GET_VER_NUM:
               break;
            case LGM_SET_LED:
               if (DEBUG) qDebug() << "setLed";
               break;
            case LGM_ACTIVE_LED:
               if (DEBUG) qDebug() << "activeLed";
               break;
            case LGM_ACTIVE_BUZZER:
               if (DEBUG) qDebug() << "activeBuzzer";
               break;
            case LGM_SET_ADDRESS:
               break;
            case LGM_CMD_WIEGAND_FORMAT:
               // Has the reader is in passive Wiegand mode when it give us keys it return the same SEQ as we defined for Wiegand format...
               if (status == FREE) // If we don't wait for a new Wiegand format confirmation, we have a card
               {
                  key = formatData(msg.mid(5, 7), serialNumberFormat);

                  readerAction = 1;
                  if (DEBUG) qDebug() << "CARD ID : " << key;

                  idParent = id;

                  if (deviceParent)
                     idParent = deviceParent->getParameter("id").toInt();

                  xml = CXmlFactory::keyDetection(QString::number(id), QString::number(idParent), getAccessPluginName(),key);

                  emit deviceEvent(xml);
               }
               else
                  if (DEBUG) qDebug() << "wiegand";

               break;
            default:
               if (DEBUG) qDebug() << "UNKNOWN RESPONSE";
               break;
            }
         }
         else
         {
            if (DEBUG) qDebug() << "Checksum error";
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
         }

         if(alarmXml != "")
            emit deviceEvent(alarmXml);

         if (baNext.at(1) == msg.at(1))
         {
            if (!pendingMessage.size())
            {
               timer->stop();
               if (DEBUG) qDebug()<<"Stop tmr";
            }
            status = FREE;
         }

         if (DEBUG) qDebug() << "bUfF" << pendingMessage.size();

         msg.remove(0, etxPos+1);
      }
      // Incorrect STX
      else if(msg.at(0) != 0x02)
      {
         if (DEBUG) qDebug() << "BAD STX";
         msg.clear();
      }
      // Incorrect ETX
      else if (etxPos < msgSize)
      {
         if (DEBUG) qDebug() << "BAD ETX";
         msg.remove(0, etxPos+1);
      }
   }
}

QByteArray CA3mLgm::sendCmd(CMD_TYPE cmd, QByteArray params)
{

   QByteArray ba;
   QByteArray ret = NULL;

   // (SEQ=0x80, 0x88, 0x90, 0x98, 0xA0, 0xA8, 0xB0...)
   switch (cmd)
   {
   case GET_SER_NUM: // GetSerNum
      ba.resize(8);
      ba[0] = 0X02;
      ba[1] = 0X80;
      ba[2] = address.toUInt();
      ba[3] = 0X09;
      ba[4] = 0X01;
      ba[5] = 0X00;
      ba[6] = ba[1] ^ ba[2] ^ ba[3] ^ ba[4] ^ ba[5];
      ba[7] = 0X03;
      break;
   case GET_VER_NUM: // GetVerNum
      ba.resize(8);
      ba[0] = 0X02;
      ba[1] = 0X88;
      ba[2] = address.toUInt();
      ba[3] = 0X0A;
      ba[4] = 0X01;
      ba[5] = 0X00;
      ba[6] = ba[1] ^ ba[2] ^ ba[3] ^ ba[4] ^ ba[5];
      ba[7] = 0X03;
      break;
   case SET_LED: // SetLED
      if (params.size() < 1) return ret;

      ba.resize(9);
      ba[0] = 0X02;
      ba[1] = 0X90;
      ba[2] = address.toUInt();
      ba[3] = 0X24;
      ba[4] = 0X02;
      ba[5] = 0X00;
      ba[6] = params.at(0); // (0=red LED, 1=green LED, 2=both)
      ba[7] = ba[1] ^ ba[2] ^ ba[3] ^ ba[4] ^ ba[5] ^ ba[6];
      ba[8] = 0X03;
      break;
   case ACTIVE_LED: // ActiveLED
      if (params.size() < 3) return ret;
      ba.resize(11);
      ba[0] = 0X02;
      ba[1] = 0X98;
      ba[2] = address.toUInt();
      ba[3] = 0X25;
      ba[4] = 0X04;
      ba[5] = 0X00;
      ba[6] = params.at(0); // Selected LED (01=red, 02=green, 03=both)
      ba[7] = params.at(1); // Units of on time. Each unit is 100ms.
      ba[8] = params.at(2); // Number of cycles to turn on/of the LED. The cycle time is one second. (0xFF will toggle the LED continually.)
      ba[9] = ba[1] ^ ba[2] ^ ba[3] ^ ba[4] ^ ba[5] ^ ba[6] ^ ba[7] ^ ba[8];
      ba[10] = 0X03;
      break;
   case ACTIVE_BUZZER: // ActiveBuzzer
      if (params.size() < 6) return ret;
      ba.resize(14);
      ba[0] = 0X02;
      ba[1] = 0XA0;
      ba[2] = address.toUInt();
      ba[3] = 0X26;
      ba[4] = 0X07;
      ba[5] = 0X00;
      ba[6] = params.at(0);  // Mode Control (00=off, 01=on, 04=sound pattern (DATA[1..h]))
      ba[7] = params.at(1);  // Units of first on time. Each unit is 100ms
      ba[8] = params.at(2);  // Units of first on time. Each unit is 100ms
      ba[9] = params.at(3);  // Units of first on time. Each unit is 100ms
      ba[10] = params.at(4); // Units of first on time. Each unit is 100ms
      ba[11] = params.at(5); // Cycle times
      ba[12] = ba[1] ^ ba[2] ^ ba[3] ^ ba[4] ^ ba[5] ^ ba[6] ^ ba[7] ^ ba[8] ^ ba[9] ^ ba[10] ^ ba[11];
      ba[13] = 0X03;
      break;
   case SET_ADDRESS: // SetAddress
      if (params.size() < 10) return ret;
      ba.resize(18);
      ba[0] = 0X02;
      ba[1] = 0XA8;
      ba[2] = address.toUInt();
      ba[3] = 0X06;
      ba[4] = 0X0B;
      ba[5] = 0X00;
      ba[6] = params.at(0);  // Enable Serial Number Checking (00=don't, 01=do)
      ba[7] = params.at(1);  // The new Device ( Reader ) Address to be set
      ba[8] = params.at(2);  // Reader Serial Number
      ba[9] = params.at(3);  // Reader Serial Number
      ba[10] = params.at(4); // Reader Serial Number
      ba[11] = params.at(5); // Reader Serial Number
      ba[12] = params.at(6); // Reader Serial Number
      ba[13] = params.at(7); // Reader Serial Number
      ba[14] = params.at(8); // Reader Serial Number
      ba[15] = params.at(9); // Reader Serial Number
      ba[16] = ba[1] ^ ba[2] ^ ba[3] ^ ba[4] ^ ba[5] ^ ba[6] ^ ba[7] ^ ba[8] ^ ba[9] ^ ba[10] ^ ba[11] ^ ba[12] ^ ba[13] ^ ba[14] ^ ba[15];
      ba[17] = 0X03;
      break;
   case CMD_WIEGAND_FORMAT: // CMD_WiegandFormat
      ba.resize(19);
      ba[0] = 0X02;
      ba[1] = 0XB0;
      ba[2] = address.toUInt();
      ba[3] = 0X18;
      ba[4] = 0x0C;
      ba[5] = 0X00;
      ba[6] = 0X00;  // wiegand output mode
      ba[7] = 0X00;  // basic mode
      ba[8] = 0X26;  // request mode (26=IDLE, 52=ALL)
      ba[9] = 0x10;  // wiegand flag (0x12=auto led and buzzer...)
      ba[10] = 0X55; //
      ba[11] = 0XAA;
      ba[12] = 0X00;
      ba[13] = 0X06;
      ba[14] = 0XFF;
      ba[15] = 0X00;
      ba[16] = 0X00;
      ba[17] = ba[1] ^ ba[2] ^ ba[3] ^ ba[4] ^ ba[5] ^ ba[6] ^ ba[7] ^ ba[8] ^ ba[9] ^ ba[10] ^ ba[11] ^ ba[12] ^ ba[13] ^ ba[14] ^ ba[15] ^ ba[16];
      ba[18] = 0X03;
      break;
   default:
      return ret;
   }

   // (Always had to resend later, so don't send know...)
   /*if (!pendingMessage.size() && status == FREE)
   {
      status = BUSY;
      baNext = ba;
      socket->write(ba, ba.size());
   }
   else*/
   pendingMessage.append(ba);

   if (!timer->isActive() && pendingMessage.size())
   {
      timer->start(100);
      if (DEBUG) qDebug()<<"Start tmr";
   }

   return ba;
}

bool CA3mLgm::checkCheckSum(QByteArray msg)
{
   unsigned int cs = 0x00;
   int msgSize = msg.size();

   if (msgSize < 7) return false;

   for(int i=1; i<msgSize-2; i++)
   {
      cs ^= (uchar)msg.at(i);
   }

   cs %= 256;

   return cs == (uchar)msg.at(msgSize-2);
}

QString CA3mLgm::formatData(QByteArray data, QString format, int length)
{
   int dataSize = data.size();
   QString ret, tmp;

   if (format.size() != dataSize) return "";

   if (format.contains('X') || format.contains('D'))
   {
      for (int i = 0; i < dataSize; i++) {
         if (format.at(i) != '_')
            ret += tmp.sprintf("%02X", (uchar)data.at(i));
      }
   }
   else
   {
      for (int i = dataSize-1; i > -1; i--) {
         if (format.at(dataSize-1-i) != '_')
            ret += tmp.sprintf("%02X", (uchar)data.at(i));
      }
   }

   if (format.contains('D') || format.contains('d'))
   {
      QString f = "%0" + tmp.setNum(length) + "d";
      bool ok;

      ret = "0X" + ret;
      ret = tmp.sprintf(f.toAscii(), ret.toUInt(&ok,16));
   }

   return ret;
}

void CA3mLgm::dispatchMessage(QByteArray ba)
{
   msg += ba;
   hasMsg();
}

void CA3mLgm::deviceAction(QString xml)
{
   int parentId = 0;
   if(deviceParent)
      parentId = deviceParent->getParameter("id").toInt();

   QMap<QString, MapParam> func = CXmlFactory::deviceAction(xml, id,parentId );

   QMapIterator<QString, MapParam> i(func);
   while (i.hasNext())
   {
      i.next();
      if(interfaces[i.key()])
      {
         void (*func)(QObject *, QMap<QString, QVariant>) = interfaces[i.key()];
         func(getMetaObject(), i.value());
      }
      else
         qDebug("The function %s is not define in the device %s", i.key().toLatin1() .constData(), name.toLatin1().constData());
   }

}

void CA3mLgm::logComm(uchar *ba, bool isReceive, int len)
{
   // a-t-on besoin de journaliser
   if(!_isLog)
      return;

   // date du message
   QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

   // afin de pouvoir être exploiter par un Horux Gui, nous vérifions les permissions du fichier pour être sûre qu'il soit exploitable le serveur web
   checkPermision(logPath + "log_" + name + ".html");

   // ouverture du fichier de journalisations
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

void CA3mLgm::s_accessRefused(QObject *p, QMap<QString, QVariant>/*params*/)
{
   CA3mLgm *pThis = qobject_cast<CA3mLgm *>(p);

   QByteArray ba;

   // Red
   ba.resize(3);
   ba[0] = 0X01;
   ba[1] = 0XFF;
   ba[2] = 0X02;
   pThis->sendCmd(ACTIVE_LED, ba);

   // Beep
   ba.resize(6);
   ba[0] = 0X04;
   ba[1] = 0X01;
   ba[2] = 0X00;
   ba[3] = 0X01;
   ba[4] = 0X00;
   ba[5] = 0X01;
   pThis->sendCmd(ACTIVE_BUZZER, ba);
   pThis->sendCmd(CMD_WIEGAND_FORMAT);
}

void CA3mLgm::s_accessAccepted(QObject *p, QMap<QString, QVariant>/*params*/)
{
   CA3mLgm *pThis = qobject_cast<CA3mLgm *>(p);

   QByteArray ba;

   // Green
   ba.resize(3);
   ba[0] = 0X02;
   ba[1] = 0XFF;
   ba[2] = 0X02;
   pThis->sendCmd(ACTIVE_LED, ba);

   // Beep
   ba.resize(6);
   ba[0] = 0X04;
   ba[1] = 0X03;
   ba[2] = 0X00;
   ba[3] = 0X00;
   ba[4] = 0X00;
   ba[5] = 0X01;
   pThis->sendCmd(ACTIVE_BUZZER, ba);
   pThis->sendCmd(CMD_WIEGAND_FORMAT);
}

Q_EXPORT_PLUGIN2(a3mlgm, CA3mLgm);
