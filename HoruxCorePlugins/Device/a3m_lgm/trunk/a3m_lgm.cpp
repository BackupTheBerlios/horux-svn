/***************************************************************************
 *   Copyright (C) 2010 by Thierry Forchelet                               *
 *   thierry.forchelet@letux.ch                                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License.        *
 *                                                                         *
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

#include "a3m_lgm.h"
#include "QTimer"
#include "../../horux_rstcpip_converter/trunk/horux_rstcpip_converter.h"

CA3mLgm::CA3mLgm(QObject *parent) : QObject(parent)
{
   // init default values
   _isConnected = false;
   readerAction = 0;
   deviceParent = NULL;
   ecbDecryption = NULL;

   // add the support of Horux's devices function
   addFunction("accessRefused", CA3mLgm::s_accessRefused);
   addFunction("accessAccepted", CA3mLgm::s_accessAccepted);
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
   if(paramName == "serialNumber")
      return serialNumber;
   if(paramName == "firmwareVersion")
      return firmwareVersion;

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

   if(paramName == "serialNumber")
      serialNumber = value.toString();

   if(paramName == "firmwareVersion")
      firmwareVersion = value.toString();
}

bool CA3mLgm::open()
{
   if (_isConnected)
      return true;

   // init values
   busyCounter = 0;
   status = FREE;
   initReader = true;

   // get the encrypted A3M protocol
   QString script = getScript();

   if(script != "-1")
   {
      // check the validity of the script
      QScriptValue result = engine.evaluate(script);

      if(engine.hasUncaughtException())
      {
         QString xml = CXmlFactory::deviceEvent(QString::number(id), "1017", "A3M script protocol for LGM error (line:" + QString::number(engine.uncaughtExceptionLineNumber()) + ","+ result.toString() + ")");
         emit deviceEvent(xml);
         return false;
      }

      timer = new QTimer(this);
      connect(timer, SIGNAL(timeout()), this, SLOT(sendBufferContent()));

      timer->start(100);

      CHRstcpipC* parent = (CHRstcpipC*) deviceParent;
      socket = parent->getSocket();
      _isConnected = true;

      return true;
   }

   return false;
}

QString CA3mLgm::getScript()
{
   if( !ecbDecryption )
   {
      QSettings settings(QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat);
      settings.beginGroup("A3mLgm");

      QString keyscript = settings.value("keyscript","0000000000000000").toString();
      if(!settings.contains("keyscript")) settings.setValue("keyscript", "0000000000000000");

      unsigned char aesdata[16];

      for(int i=0; i<16; i++)
      {
         aesdata[i] = keyscript.at(i).toLatin1();
      }

      ecbDecryption = new ECB_Mode<AES >::Decryption(aesdata, AES::DEFAULT_KEYLENGTH);
   }

   QFile file( QCoreApplication::instance()->applicationDirPath() + "/a3mlgm.js.aes");
   if(file.open(QIODevice::ReadOnly))
   {
      QByteArray cryptedFile = file.readAll();

      int clear_len = 0;
      unsigned char uncryptedFile[8192];
      memset(uncryptedFile, 0, 8192);
      if(!decrypt((unsigned char*)cryptedFile.data(),cryptedFile.length(),uncryptedFile,&clear_len))
         return false;

      QByteArray ba((const char*)uncryptedFile,clear_len);
      QString script (ba);
      file.close();
      qDebug() << "Protocol A3M LGM loaded";

      return script;
   }

   qDebug() << "Protocol A3M LGM not loaded";
   return "-1";
}

bool CA3mLgm::decrypt(const unsigned char *encrypt_msg,
                      const int encrypt_len,
                      unsigned char *clear_msg,
                      int *clear_len)
{
   int padding = 0;
   int blockNbre = encrypt_len / 16; // how many 16byte blocks do we have?
   padding = 16 - (encrypt_len % 16); // how many padding bytes do we have to add?

   // if the padding is less that 16, the message is wrong
   if(padding < 16)
   {
      return false;
   }

   int index = 0;
   // uncrypt each 16 bytes blocks
   for(int i = 0; i<blockNbre; i++)
   {
      ecbDecryption->ProcessData( (byte*)clear_msg+index, (const byte*)encrypt_msg+index, 16);

      index += 16;
   }
   *clear_len = blockNbre*16;
   return true;
}

void CA3mLgm::connection(int deviceId, bool isConnected) {
   if (deviceId == deviceParent->getParameter("id")) {
      if (isConnected)
         open();
      else
         close();
   }
}

void CA3mLgm::sendBufferContent() {
   QByteArray p;
   if (!socket)
      return;

   if (status == BUSY)
   {
      if (busyCounter % 5 >= 4) {
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

      busyCounter++;
   }

   // init LEDs (turn off) at startup
   if (initReader && status == FREE)
   {
      initReader = false;
      p.resize(1);
      p[0] = 0x00;

      sendCmd(GET_SER_NUM);
      sendCmd(GET_VER_NUM);
      sendCmd(SET_LED, p);
      sendCmd(CMD_WIEGAND_FORMAT);
   }

   // process the next pending message if we have one and the device isn't busy
   if(pendingMessage.size() > 0 && status == FREE)
   {
      baNext = pendingMessage.takeFirst();
      if(baNext.size() && socket)
      {
         status = BUSY;
         socket->write(baNext, baNext.size());
      }
   }
}

void CA3mLgm::close()
{
    if(!_isConnected)
        return;

   _isConnected = false;

   if (timer != NULL) {
      delete timer;
      timer = NULL;
   }

   if (socket != NULL) {
      socket = NULL;
   }

   pendingMessage.clear();

   // emit the signal for the subsystems
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

   newElement = xml_info.createElement( "firmwareVersion");
   text =  xml_info.createTextNode(firmwareVersion);
   newElement.appendChild(text);
   device.appendChild(newElement);

   newElement = xml_info.createElement( "serialNumber");
   text =  xml_info.createTextNode(serialNumber);
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

   // do we read any byte
   if(msgSize == 0) return;

   // do we have at least 7 bytes
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
            QByteArray tmbBa;

            switch((uchar)msg.at(1))
            {
            case LGM_GET_SER_NUM:
               serialNumber = msg.mid(5, 8);
               if (DEBUG) qDebug()<<"Serial " << serialNumber;
               break;
            case LGM_GET_VER_NUM:
               firmwareVersion = msg.mid(6, msg.at(3)-0X02);
               if (DEBUG) qDebug() << "Firmware " << firmwareVersion;
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
            case 0X00:
               // as the reader is in passive Wiegand mode (or "mode" SEQ=0 by default) when it give us keys it return the same SEQ as we defined for Wiegand format...
               if (status == FREE) // if we don't wait for a new Wiegand format confirmation, we have a card
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
            msg.clear();
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
         else {
            qDebug() << "Wrong response...";
            status = FREE;
            timer->start(100);
         }

         if (DEBUG) qDebug() << "bUfF" << pendingMessage.size();

         msg.remove(0, etxPos+1);
      }
      // incorrect STX
      else if(msg.at(0) != 0x02)
      {
         if (DEBUG) qDebug() << "BAD STX";
         msg.clear();
      }
      // incorrect ETX
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
   QScriptValue scriptParams = engine.newArray(params.size());

   // give the params to the script
   for (int i = 0; i < params.size(); i++)
      scriptParams.setProperty(i, params.at(i));
   engine.globalObject().setProperty("params", scriptParams);

   // call the script's function
   QScriptValue result = engine.evaluate("sendCmd");
   QScriptValueList args;
   args << QScriptValue(&(engine),cmd);
   args << QScriptValue(&(engine),address.toUInt());
   args << QScriptValue(&(engine),params.size());

   // get the result and convert it to ByteArray
   result = result.call(QScriptValue(), args);
   QVariantList lst = result.toVariant().toList();
   ba.resize(lst.size());
   for (int i = 0; i < ba.size(); i++)
      ba[i] = (uchar)lst.at(i).toDouble();

   // (always have to resend later, so don't send know...)
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
   if(!_isLog)
      return;

   QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

   checkPermision(logPath + "log_" + name + ".html");

   // open the log file
   QFile file(logPath + "log_" + name + ".html");
   if (!file.open(QIODevice::Append | QIODevice::Text))
      return;

   // get a readable content from the received byte array
   QString s = "", s1;
   for(int i=0; i<len; i++)
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
   ba[1] = 0X06;
   ba[2] = 0X00;
   ba[3] = 0X00;
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
   ba[1] = 0X02;
   ba[2] = 0X00;
   ba[3] = 0X00;
   ba[4] = 0X00;
   ba[5] = 0X01;

   pThis->sendCmd(ACTIVE_BUZZER, ba);
   pThis->sendCmd(CMD_WIEGAND_FORMAT);
}

Q_EXPORT_PLUGIN2(a3mlgm, CA3mLgm);
