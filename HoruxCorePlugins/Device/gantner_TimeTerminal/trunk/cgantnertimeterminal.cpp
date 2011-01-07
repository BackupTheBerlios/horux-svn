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
#include "cgantnertimeterminal.h"
#include "cconfig.h"
#include <QtCore>
#include <QtSql>

CGantnerTimeTerminal::CGantnerTimeTerminal(QObject *parent) : QObject(parent)
{
    ftp = NULL;
    _isConnected = false;
    ipOrDhcp = "";
    isAutoRestart = false;
    autoRestart = "03:27";
    displayTimeout = 5000;
    inputTimeout = 5000;
    brightness = 50;
    udpServer=true;
    checkBooking = 10 * 1000; //every 10 minutes

    autoBooking = false;

    action = WAITING;

    timerCheckBooking = 0;
    timerSendFile = 0;
    timerConfigFile = 0;
    timerConnectionAbort = 0;
    timerSyncTime = 0;

    idCheckBooking = 0;



    readFile = "";

    addFunction("addUserBalances", CGantnerTimeTerminal::s_addUserBalances);
    addFunction("addUser", CGantnerTimeTerminal::s_addUser);
    addFunction("addKey", CGantnerTimeTerminal::s_addKey);

    addFunction("removeUser", CGantnerTimeTerminal::s_removeUser);
    addFunction("removeAllUsers", CGantnerTimeTerminal::s_removeAllUsers);
    addFunction("removeKey", CGantnerTimeTerminal::s_removeKey);

    addFunction("addAbsentReason", CGantnerTimeTerminal::s_addAbsentReason);
    addFunction("removeAbsentReason", CGantnerTimeTerminal::s_removeAbsentReason);
    addFunction("removeAllAbsentReason", CGantnerTimeTerminal::s_removeAllAbsentReason);

    addFunction("setBalanceText", CGantnerTimeTerminal::s_setBalanceText);

    addFunction("reinit", CGantnerTimeTerminal::s_reinit);

    addFunction("removeBDEData", CGantnerTimeTerminal::s_removeBDEData);
    addFunction("addBDEData", CGantnerTimeTerminal::s_addBDEData);


    bookingError = false;

    udp = NULL;

    ecbDecryption = NULL;
}

CDeviceInterface *CGantnerTimeTerminal::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
  CDeviceInterface *p = new CGantnerTimeTerminal ( parent );

  p->setParameter("name",config["name"]);
  p->setParameter("_isLog",config["isLog"]);
  p->setParameter("accessPlugin",config["accessPlugin"]);
  p->setParameter("id",config["id_device"]);

  p->setParameter("ipOrDhcp",config["ipOrDhcp"]);
  p->setParameter("isAutoRestart",config["isAutoRestart"]);
  p->setParameter("autoRestart",config["autoRestart"]);
  p->setParameter("displayTimeout",config["displayTimeout"]);
  p->setParameter("inputTimeout",config["inputTimeout"]);
  p->setParameter("brightness",config["brightness"]);
  p->setParameter("udpServer",config["udpServer"]);
  p->setParameter("checkBooking",config["checkBooking"]);
  p->setParameter("autoBooking",config["autoBooking"]);

  return p;
}




void CGantnerTimeTerminal::deviceAction(QString xml)
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
     else
     {
        if( i.key() == "") return;

        qDebug("The function %s is not define in the device %s", i.key().toLatin1() .constData(), name.toLatin1().constData());
    }
  }

}


void CGantnerTimeTerminal::connectChild(CDeviceInterface *)
{

}

QVariant CGantnerTimeTerminal::getParameter(QString paramName)
{
  if(paramName == "name")
    return name;
  if(paramName == "id")
    return id;
  if(paramName == "_isLog")
    return _isLog;
  if(paramName == "accessPlugin")
    return accessPlugin;


  if(paramName == "ipOrDhcp")
    return ipOrDhcp;
  if(paramName == "isAutoRestart")
    return isAutoRestart;
  if(paramName == "autoRestart")
    return autoRestart;
  if(paramName == "displayTimeout")
    return displayTimeout;
  if(paramName == "inputTimeout")
    return inputTimeout;
  if(paramName == "brightness")
    return brightness;
  if(paramName == "udpServer")
    return udpServer;
  if(paramName == "checkBooking")
    return checkBooking;
  if(paramName == "autoBooking")
    return autoBooking;

  return "undefined";
}

void CGantnerTimeTerminal::setParameter(QString paramName, QVariant value)
{
  if(paramName == "name")
    name = value.toString();
  if(paramName == "id")
    id = value.toInt();
  if(paramName == "isLog")
    _isLog = value.toBool();
  if(paramName == "accessPlugin")
    accessPlugin = value.toString();

  if(paramName == "ipOrDhcp")
    ipOrDhcp = value.toString();
  if(paramName == "isAutoRestart")
    isAutoRestart = value.toBool();
  if(paramName == "autoRestart")
  {
    autoRestart = value.toString().left(5);
  }
  if(paramName == "displayTimeout")
    displayTimeout = value.toInt();
  if(paramName == "inputTimeout")
    inputTimeout = value.toInt();
  if(paramName == "brightness")
    brightness = value.toInt();
  if(paramName == "udpServer")
    udpServer = value.toBool();
   if(paramName == "checkBooking")
    checkBooking = 5000;//value.toInt() * 60000;
   if(paramName == "autoBooking")
     autoBooking = value.toBool();
}

QString CGantnerTimeTerminal::getScript()
{
    if( !ecbDecryption )
    {
        QSettings settings(QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat);
        settings.beginGroup("GantnerTimeTerminal");

        QString keyscript = settings.value("keyscript","0000000000000000").toString();
        if(!settings.contains("keyscript")) settings.setValue("keyscript", "0000000000000000");

        unsigned char aesdata[16];

        for(int i=0; i<16; i++)
        {
            aesdata[i] = keyscript.at(i).toLatin1();
        }

        ecbDecryption = new ECB_Mode<AES >::Decryption(aesdata, AES::DEFAULT_KEYLENGTH);
    }

    QFile file( QCoreApplication::instance()->applicationDirPath() + "/timeterminal.js.aes");
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
        qDebug() << "Protocol Gantner p.time loaded";
        return script;

    }

    qDebug() << "Protocol Gantner p.time not loaded";
    return "-1";
}

bool CGantnerTimeTerminal::decrypt(const unsigned char *encrypt_msg,
                                   const int encrypt_len,
                                   unsigned char *clear_msg,
                                   int *clear_len)
{
  int padding = 0;
  int blockNbre = encrypt_len / 16; //! How many 16byte blocks do we have?
  padding = 16 - (encrypt_len % 16); //! How many padding bytes do we have to add?

  //! if the padding is less that 16, the message is wrong
  if(padding < 16)
  {
    return false;
  }


  int index = 0;
  //! uncrypt each 16 bytes blocks
  for(int i = 0; i<blockNbre; i++)
  {
    ecbDecryption->ProcessData( (byte*)clear_msg+index, (const byte*)encrypt_msg+index, 16);

    index += 16;
  }
  *clear_len = blockNbre*16;
  return true;
}

bool CGantnerTimeTerminal::open()
{
  if( ftp )
  {
      return true;
  }

  // get the encrypted Gantner protocol
  QString script = getScript();

  if(script != "-1")
  {
    // check the validity of the script
    QScriptValue result = engine.evaluate(script);

    if(engine.hasUncaughtException())
    {
        QString xml = CXmlFactory::deviceEvent(QString::number(id), "1017", "Gantner script protocol for Time Terminal error (line:" + QString::number(engine.uncaughtExceptionLineNumber()) + ","+ result.toString() + ")");
        emit deviceEvent(xml);
        return false;
    }

    //creat the ftp connection
    ftp = new QFtp(this);
    connect( ftp, SIGNAL(commandFinished ( int, bool )), this, SLOT(commandFinished( int, bool)));
    connect( ftp, SIGNAL(readyRead () ), this, SLOT(readyRead ()));

    // Check if the device was replace by a new one
    action = READ_REPLACE;

    //start the process by stating the connection
    connectionToFtp();

    // set the device as connected
    _isConnected = true;


    udp = new QUdpSocket(this);

    connect(udp, SIGNAL(readyRead ()), this, SLOT(readUdp()));

    result = engine.evaluate("readInfo");

    udp->connectToHost(QHostAddress(ipOrDhcp), 8216);
    udp->write(result.call().toString().toLatin1());

    /*result= engine.evaluate("setUnitTime");
    QScriptValueList args;
    args << QScriptValue(&engine,QDateTime::currentDateTime().toString(Qt::ISODate));

    udp->write(result.call(QScriptValue(), args).toString().toLatin1());*/

    timerSyncTime = startTimer(1000 * 60 * 60); // sync the time every 1 hour
    result = engine.evaluate("setUnitTime");
    QScriptValueList args;
    args << QScriptValue(&engine,QDateTime::currentDateTime().toString(Qt::ISODate));
    udp->write(result.call(QScriptValue(), args).toString().toLatin1());

    return true;
  }

  return false;
}

void CGantnerTimeTerminal::connectionToFtp()
{
    //start the process by stating the connection
    timerConnectionAbort = startTimer(TIMER_CONNECTION);
    idConnectHost = ftp->connectToHost(ipOrDhcp);
}

void CGantnerTimeTerminal::commandFinished(  int id, bool error)
{
    //check connection
    if( id == idConnectHost)
    {
        if(!error)
        {
            killTimer( timerConnectionAbort ) ;
            timerConnectionAbort = 0;

            QScriptValue result = engine.evaluate("getFtpPassword");
            QString password = result.call().toString();

            result = engine.evaluate("getFtpUsername");
            QString username = result.call().toString();

            // after a connection to the host, do a login
            idLogin = ftp->login(username, password);
            //qDebug("FTP CONNECTED");
        }

        idConnectHost = 0;
        return;
    }

    //check login connection
    if( id == idLogin)
    {
        if(!error)
        {
            switch(action)
            {
                case WAITING:
                    ftp->close();
                    break;
                case READ_REPLACE:
                    //check if the device was replace by a new one
                    idReadReplace = ftp->get("reload.txt");
                    break;
                case READ_CONFIG_FILE:
                    break;
                case READ_BOOKING:
                    idCheckBooking = ftp->get("Bookings.exp");
                    break;
                case SEND_DOWN:
                    idRemoveDown = ftp->remove("Down.info");
                    break;
                case SEND_CONFIG: {
                        QTextCodec *codec = QTextCodec::codecForName("UTF-8");
                        idSendConfigCmd = ftp->put(codec->fromUnicode(configFile), "config.dat");
                    }
                    break;
                case REINIT:
                    reinit();
                    break;
            }
        }
        else
        {
            ftp->close();
        }

        idLogin = 0;
        return;
    }

    if(id == idReadReplace)
    {
        if(!error)
        {
            //qDebug("YES reload.txt");
            // no error, this mean that the device was replace and must be ialized
            idRemoveReplace = ftp->remove("reload.txt");
        }
        else
        {
            // cannot read the file replace.txt, this mean that the device was not replaced
            //qDebug("NO reload.txt");
            // read the file config
            //qDebug("READ THE CONFIG FILE");
            action = READ_CONFIG_FILE;
            readFile = "";
            idReadConfig = ftp->get("GatTimeCe.Config");
        }

        idReadReplace = 0;
        return;
    }

    if(id == idRemoveReplace)
    {
        if(!error)
        {
            //qDebug("Remove reload.txt OK");
            reinit();
            //start the timer allowing to check the bookings
            //qDebug("START THE BOOKING TIMER 2");
            timerCheckBooking = startTimer(checkBooking);
            timerSendFile = startTimer(3000);
            timerConfigFile = startTimer(3000);
        }
        else
        {
            //qDebug("Remove reload.txt KO");
            QString xml = CXmlFactory::deviceEvent(QString::number(id), "1017", "Cannot remove the file reload.txt, please check the connection (" + ftp->errorString() + ")");
            emit deviceEvent(xml);
        }

        if(action != WAITING)
        {
            action = WAITING;
            ftp->close();
        }
    }

    if(id == idReadConfig)
    {
        if(!error)
        {
            if(readFile.length() > 0)
            {
                checkConfigFile(readFile);
            }

            readFile = "";
        }
        else
        {
            QString xml = CXmlFactory::deviceEvent(QString::number(this->id), "1017", "Cannot read the config file (" + ftp->errorString() + ")");
            emit deviceEvent(xml);

            action = WAITING;
            ftp->close();
        }
        return;
    }

    if(id == idSendConfigCmd)
    {
        if(!error)
        {
            for(int i=0; i<numberOfConfigCommand; i++)
                sendConfigList.removeFirst();

            action = WAITING;
            ftp->close();
        }
        else
        {
            QString xml = CXmlFactory::deviceEvent(QString::number(this->id), "1017", "Cannot write the config command file (" + ftp->errorString() + ")");
            emit deviceEvent(xml);

            action = WAITING;
            ftp->close();
        }
        return;
    }

    if(id == idSendConfig)
    {
        if(!error)
        {
            //qDebug("REBOOT UNIT");
            QScriptValue result = engine.evaluate("reboot");

            QTextCodec *codec = QTextCodec::codecForName("UTF-8");

            ftp->put(codec->fromUnicode(result.call().toString()), "Down.dat");
            action = WAITING;
            ftp->close();
            //qDebug("START THE BOOKING TIMER 3");
            timerCheckBooking = startTimer(checkBooking);
        }
        else
        {
            QString xml = CXmlFactory::deviceEvent(QString::number(this->id), "1017", "Cannot write the config file (" + ftp->errorString() + ")");
            emit deviceEvent(xml);

            action = WAITING;
            ftp->close();
        }
        return;

    }

    if(id == idCheckBooking)
    {
        if(!error)
        {

            if(readFile.length() > 0)
            {
                if(_isLog)
                {
                   QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

                   QFile booking(logPath + name + "_booking_" + date + ".txt");
                   if(booking.open(QIODevice::WriteOnly))
                   {
                       QTextStream bookingTS(&booking);
                       //qDebug() << readFile;
                       bookingTS << readFile;
                       booking.close();
                   }

                }

                dispatchMessage(readFile.toLatin1());
            }

            idRemoveBookings = ftp->remove("Bookings.exp");
            bookingError = false;
            readFile = "";
        }
        else
        {
            if(!bookingError)
            {                                
                QString xml = CXmlFactory::deviceEvent(QString::number(this->id), "1017", "Cannot read the bookings file (" + ftp->errorString() + ")");
                emit deviceEvent(xml);
                bookingError = true;
            }

            action = WAITING;
            ftp->close();
        }
        return;
    }

    if(id == idRemoveBookings)
    {
        if(!error)
        {
            //qDebug("REMOVE BOOKING OK");
        }
        else
        {
            //qDebug("REMOVE BOOKING KO");
            QString xml = CXmlFactory::deviceEvent(QString::number(this->id), "1017", "Cannot remove the bookings file (" + ftp->errorString() + ")");
            emit deviceEvent(xml);
        }

        action = WAITING;
        ftp->close();
        return;
    }

    if(id == idRemoveDown)
    {
        if(!error)
        {
            //qDebug("REMOVE Down.info OK");
            QTextCodec *codec = QTextCodec::codecForName("UTF-8");
            idSendDown = ftp->put(codec->fromUnicode( sendFile ), "Down.dat");
        }
        else
        {
            //qDebug("NO Down.info");
            QTextCodec *codec = QTextCodec::codecForName("UTF-8");
            idSendDown = ftp->put(codec->fromUnicode( sendFile ), "Down.dat");
        }
    }

    if(id == idSendDown)
    {
        if(!error)
        {
            //qDebug("SEND Down.dat OK");
            QTimer::singleShot(1000, this, SLOT(readDownInfo()));
        }
        else
        {
            //qDebug("SEND Down.dat KO");
            QString xml = CXmlFactory::deviceEvent(QString::number(this->id), "1017", "Cannot send the Down.dat file (" + ftp->errorString() + ")");
            emit deviceEvent(xml);
        }

        return;
    }

    if(id == idReadDown)
    {
        if(!error)
        {
            //qDebug("READ Down.info OK");
            if(!readFile.contains("Error"))
            {
                //qDebug("Down.info do not contains error");
                //qDebug() << "numberOfSendCommand:" << numberOfSendCommand;
                readFile = "";
                for(int i=0; i<numberOfSendCommand; i++)
                    sendFileList.removeFirst();
                action = WAITING;
                ftp->close();
            }
            else
            {
                //qDebug("Down.info contains error");
                readFile = "";
                action = WAITING;
                ftp->close();
            }
        }
        else
        {
            //qDebug("READ Down.info ERROR");
            // try again
            QTimer::singleShot(1000, this, SLOT(readDownInfo()));
        }

        return;
    }
}

void CGantnerTimeTerminal::checkConfigFile(QString xml)
{

    CConfig config;

    QString errorStr;
    int errorLine;
    int errorColumn;

    bool isOk = true;

    if( !xml.startsWith('<') )
    {
        int startXml = xml.indexOf("<");

        if(startXml>0)
            xml = xml.right(xml.length()-startXml);
    }

    if( !config.setContent(xml, true, &errorStr, &errorLine, &errorColumn) )
    {
        QString xml = CXmlFactory::deviceEvent(QString::number(this->id), "1017", QString("XML Config file error at line %1, column %2 : %3").arg(errorLine).arg(errorColumn).arg(errorStr));
        emit deviceEvent(xml);
        action = WAITING;
        ftp->close();
        isOk = false;
    }
    else
    {
       config.parsXml();

       if(!config.isError())
       {
           bool isDiff = false;

           if( config.getBrightness() != brightness )
           {
                config.setBrightness( brightness );
                isDiff = true;
           }

           if( config.getDisplayTimeout() != displayTimeout )
           {
               config.setDisplayTimeout(displayTimeout);
                isDiff = true;
           }

           if( config.getInputTimeout() != inputTimeout )
           {
               config.setInputTimeout(inputTimeout);
                isDiff = true;
           }

           if( config.getAutoRestartEnabled() != isAutoRestart)
           {
                config.setAutoRestartEnabled(isAutoRestart);
                isDiff = true;
           }

           if( config.getAutoRestartTime() != autoRestart)
           {
                config.setAutoRestartTime(autoRestart);
                isDiff = true;
           }

           if( config.getUdpServerEnabled() != udpServer)
           {
                config.setUdpServerEnabled(udpServer);
                isDiff = true;
           }

           if( config.getUdpServerClient() != udpClient)
           {
               config.setUdpServerClient(udpClient);
               isDiff = true;
           }

           if( isDiff )
           {
               //qDebug("CONFIG DIFF");
               QTextCodec *codec = QTextCodec::codecForName("UTF-8");
               idSendConfig = ftp->put(codec->fromUnicode(config.toString()), "GatTimeCe.Config");
               return;
           }
       }
       else
       {
            QString xml = CXmlFactory::deviceEvent(QString::number(this->id), "1017", QString("XML Config file content error"));
            emit deviceEvent(xml);
            action = WAITING;
            ftp->close();
            isOk = false;
       }
    }

   if(isOk)
    {
        //qDebug("CONFIG OK");
        action = WAITING;
        ftp->close();

        emit deviceConnection(this->id,true);

        //send down.dat every three secondes if available
        timerSendFile = startTimer(3000);
        timerConfigFile = startTimer(3000);


        //start the timer allowing to check the bookings
        //qDebug("START THE BOOKING TIMER 1");
        timerCheckBooking = startTimer(checkBooking);
    }
    else
    {
        //qDebug("CONFIG ERROR");
    }
}

void CGantnerTimeTerminal::readDownInfo()
{
    readFile = "";
    idReadDown = ftp->get("Down.info");
}

void CGantnerTimeTerminal::dispatchMessage(QByteArray bookings)
{
    QTextStream books( bookings );

    QString line;

    do
    {
        line = books.readLine();
qDebug() << line;
        if(line.length() > 0)
        {
            QStringList data = line.split(";");

            QString dateTime = data.at(BK_DATETIME);
            QString bookingType = data.at(BK_BOOKINGTYPE);
            QString userId = data.at(BK_USERID);
            QString cardType = data.at(BK_CARDTYPE);
            QString key = data.at(BK_KEY);
            QString personalNo = data.at(BK_PERSONALNO);
            QString bookingCode = data.at(BK_BOOKINGCODE);
            QString bookingReason = data.at(BK_BOOKINGREASON);
            QString fiuinfo = data.at(BK_FIUINFO);
            QString fiuMatchingScore = data.at(BK_FIUMATCHINGSCORE);
            QString preBooking = data.size() > BK_PREBOOKING ? data.at(BK_PREBOOKING) : "";
            QString BDEValue1 = data.size() > BK_BDEVALUE1 ? data.at(BK_BDEVALUE1) : "";
            QString BDEValue2 = data.size() > BK_BDEVALUE2 ? data.at(BK_BDEVALUE2) : "";
            QString BDEValue3 = data.size() > BK_BDEVALUE3 ? data.at(BK_BDEVALUE3) : "";
            QString BDEValue4 = data.size() > BK_BDEVALUE4 ? data.at(BK_BDEVALUE4) : "";
            QString BDEValue5 = data.size() > BK_BDEVALUE5 ? data.at(BK_BDEVALUE5) : "";
            QString BDEValue6 = data.size() > BK_BDEVALUE6 ? data.at(BK_BDEVALUE6) : "";
            QString BDEValue7 = data.size() > BK_BDEVALUE7 ? data.at(BK_BDEVALUE7) : "";
            QString BDEValue8 = data.size() > BK_BDEVALUE8 ? data.at(BK_BDEVALUE8) : "";
            QString BDEValue9 = data.size() > BK_BDEVALUE9 ? data.at(BK_BDEVALUE9) : "";
            QString BDEValue10 = data.size() > BK_BDEVALUE10 ? data.at(BK_BDEVALUE10) : "";
            QString BDEValue11 = data.size() > BK_BDEVALUE11 ? data.at(BK_BDEVALUE11) : "";
            QString BDEValue12 = data.size() > BK_BDEVALUE12 ? data.at(BK_BDEVALUE12) : "";
            QString BDEValue13 = data.size() > BK_BDEVALUE13 ? data.at(BK_BDEVALUE13) : "";
            QString BDEValue14 = data.size() > BK_BDEVALUE14 ? data.at(BK_BDEVALUE14) : "";
            QString BDEValue15 = data.size() > BK_BDEVALUE15 ? data.at(BK_BDEVALUE15) : "";
            QString BDEValue16 = data.size() > BK_BDEVALUE16 ? data.at(BK_BDEVALUE16) : "";
            QString BDEValue17 = data.size() > BK_BDEVALUE17 ? data.at(BK_BDEVALUE17) : "";
            QString BDEValue18 = data.size() > BK_BDEVALUE18 ? data.at(BK_BDEVALUE18) : "";
            QString BDEValue19 = data.size() > BK_BDEVALUE19 ? data.at(BK_BDEVALUE19) : "";
            QString BDEValue20 = data.size() > BK_BDEVALUE20 ? data.at(BK_BDEVALUE20) : "";

            QStringList dt = dateTime.split(".");
            QString date = dt.at(0) + "-" + dt.at(1) + "-" + dt.at(2);
            QString time = dt.at(3) + ":" + dt.at(4) + ":" + dt.at(5);

            QMap<QString, QString> params;
            params["date"] = date;
            params["time"] = time;
            params["bookingType"] = bookingType;
            params["userId"] = userId;
            params["cardType"] = cardType;
            params["key"] = key;
            params["personalNo"] = personalNo;
            params["code"] = bookingCode;
            params["reason"] = bookingReason;
            params["fiuinfo"] = fiuinfo;
            params["fiuMatchingScore"] = fiuMatchingScore;
            params["preBooking"] = preBooking;
            params["BDEValue1"] = BDEValue1;
            params["BDEValue2"] = BDEValue2;
            params["BDEValue3"] = BDEValue3;
            params["BDEValue4"] = BDEValue4;
            params["BDEValue5"] = BDEValue5;
            params["BDEValue6"] = BDEValue6;
            params["BDEValue7"] = BDEValue7;
            params["BDEValue8"] = BDEValue8;
            params["BDEValue9"] = BDEValue9;
            params["BDEValue10"] = BDEValue10;
            params["BDEValue11"] = BDEValue11;
            params["BDEValue12"] = BDEValue12;
            params["BDEValue13"] = BDEValue13;
            params["BDEValue14"] = BDEValue14;
            params["BDEValue15"] = BDEValue15;
            params["BDEValue16"] = BDEValue16;
            params["BDEValue17"] = BDEValue17;
            params["BDEValue18"] = BDEValue18;
            params["BDEValue19"] = BDEValue19;
            params["BDEValue20"] = BDEValue20;
            //! unknown user/card
            QString xml = CXmlFactory::deviceEvent(QString::number(id), "bookingDetected", params);
            emit deviceEvent(xml);
        }
    }
    while (!line.isNull());
}

void CGantnerTimeTerminal::reinit()
{
    qDebug("REINIT");



    QSettings settings(QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat);
    settings.beginGroup("GantnerTimeTerminal");

    QString lang = settings.value("lang","fr").toString();
    if(!settings.contains("lang")) settings.setValue("lang", "fr");

    QString arriveText = settings.value("arriveText","Arriver").toString();
    if(!settings.contains("arriveText")) settings.setValue("arriveText", "Arriver");

    QString leaveText = settings.value("leaveText","Partir").toString();
    if(!settings.contains("leaveText")) settings.setValue("leaveText", "Partir");

    QString fid = settings.value("fid","9999").toString();
    if(!settings.contains("fid")) settings.setValue("fid", "9999");


    settings.endGroup();


    QString config = "";
    QScriptValue result;
    QScriptValueList args;

    result = engine.evaluate("resetDeviceConfig");
    config += result.call().toString() + "\n";

    result = engine.evaluate("resetReaderConfig");
    config += result.call().toString() + "\n";


    // set the reader configuration
    args.clear();

    args << QScriptValue(&engine,fid);
    result = engine.evaluate("readerConfig");
    config += result.call(QScriptValue(), args).toString() + "\n";

    QSqlQuery queryLang("SELECT language FROM hr_gantner_TimeTerminal WHERE id_device=" + QString::number(id));
    queryLang.next();

    // set the language configuration
    args.clear();
    args << QScriptValue(&engine,queryLang.value(0).toString());
    result = engine.evaluate("loadLanguage");
    config += result.call(QScriptValue(), args).toString() + "\n";



    // set the booking timer configuration
    QSqlQuery query("SELECT hoursBlockMorning1, hoursBlockMorning2, hoursBlockMorning3, hoursBlockMorning4, hoursBlockAfternoon1,hoursBlockAfternoon2,hoursBlockAfternoon3,hoursBlockAfternoon4 FROM hr_timux_config");
    if(autoBooking && query.next())
    {
        args.clear();
        args << QScriptValue(&engine,1);
        args << QScriptValue(&engine,query.value(0).toString().replace(":","."));
        args << QScriptValue(&engine,query.value(1).toString().replace(":","."));
        args << QScriptValue(&engine,arriveText);
        args << QScriptValue(&engine,leaveText);

        result = engine.evaluate("getBookingTimer");
        config += result.call(QScriptValue(), args).toString() + "\n";

        args.clear();
        args << QScriptValue(&engine,2);
        args << QScriptValue(&engine,query.value(2).toString().replace(":","."));
        args << QScriptValue(&engine,query.value(3).toString().replace(":","."));
        args << QScriptValue(&engine,arriveText);
        args << QScriptValue(&engine,leaveText);

        result = engine.evaluate("getBookingTimer");
        config += result.call(QScriptValue(), args).toString() + "\n";


        args.clear();
        args << QScriptValue(&engine,3);
        args << QScriptValue(&engine,query.value(4).toString().replace(":","."));
        args << QScriptValue(&engine,query.value(5).toString().replace(":","."));
        args << QScriptValue(&engine,arriveText);
        args << QScriptValue(&engine,leaveText);

        result = engine.evaluate("getBookingTimer");
        config += result.call(QScriptValue(), args).toString() + "\n";

        args.clear();
        args << QScriptValue(&engine,4);
        args << QScriptValue(&engine,query.value(6).toString().replace(":","."));
        args << QScriptValue(&engine,query.value(7).toString().replace(":","."));
        args << QScriptValue(&engine,arriveText);
        args << QScriptValue(&engine,leaveText);

        result = engine.evaluate("getBookingTimer");
        config += result.call(QScriptValue(), args).toString() + "\n";
    } else {
        result = engine.evaluate("resetBookingTimer");
        config += result.call().toString() + "\n";
    }

    //remove all fixed keys
    result = engine.evaluate("removeAllFixedKey");
    config += result.call().toString() + "\n";

    //remove all soft keys
    result = engine.evaluate("removeAllSoftKey");
    config += result.call().toString() + "\n";

    QSqlQuery queryButton("SELECT * FROM hr_gantner_TimeTerminal_key WHERE device_id=" + QString::number(id));

    bool isInputData = false;

    while(queryButton.next())
    {
        if(queryButton.value(1).toString() == "fixed")
        {
            args.clear();
            args << QScriptValue(&engine,queryButton.value(2).toString());

            args << QScriptValue(&engine,QString( queryButton.value(3).toString())  );

            if(!queryButton.value(4).toString().contains("dlg_InputData") ) {
                args << QScriptValue(&engine,queryButton.value(4).toString());
            }
            else {
                QString inputData;
                if(queryButton.value(4).toString() =="<dlg_InputData,150>")
                      inputData = "<dlg_InputData,150,," + queryButton.value(5).toString() + ">";
                else
                    inputData = "<dlg_InputData,155,," + queryButton.value(5).toString() + ">";
                args << QScriptValue(&engine,inputData);
                isInputData = true;
            }

            result = engine.evaluate("setFixedKey");
            config += result.call(QScriptValue(), args).toString() + "\n";

        }
        if(queryButton.value(1).toString() == "soft")
        {
            args.clear();
            args << QScriptValue(&engine,queryButton.value(2).toString());

            args << QScriptValue(&engine,QString( queryButton.value(3).toString())  );

            if(!queryButton.value(4).toString().contains("dlg_InputData") ) {
                args << QScriptValue(&engine,queryButton.value(4).toString());
            }
            else {
                QString inputData;
                if(queryButton.value(4).toString() =="<dlg_InputData,150>")
                      inputData = "<dlg_InputData,150,," + queryButton.value(5).toString() + ">";
                else
                    inputData = "<dlg_InputData,155,," + queryButton.value(5).toString() + ">";
                args << QScriptValue(&engine,inputData);
                isInputData = true;
            }

            QString img = "";
            if(queryButton.value(4).toString() == "<dlg_Attendance_View,0>")
                img = "Present.bmp";
            if(queryButton.value(4).toString() == "<dlg_Attendance_View,1>")
                img = "Present.bmp";
            if(queryButton.value(4).toString() == "<dlg_Attendance_View,2>")
                img = "Absent.bmp";


            if(queryButton.value(4).toString().contains("dlg_Info"))
                img = "Info.bmp";

            if(queryButton.value(4).toString().contains("<dlg_PersBooking,255,1,0,0,1>"))
                img = "Coming.bmp";

            if(queryButton.value(4).toString().contains("<dlg_PersBooking,255,1,2,0,1>"))
                img = "Coming.bmp";

            if(queryButton.value(4).toString().contains("<dlg_PersBooking,254,2,0,0,1>"))
                img = "Leaving.bmp";

            if(queryButton.value(4).toString().contains("<dlg_PersBooking,254,2,2,0,1>"))
                img = "Leaving.bmp";

            if(queryButton.value(4).toString().contains("<dlg_Reasons,100,0,IN>"))
                img = "Coming_with.bmp";

            if(queryButton.value(4).toString().contains("<dlg_Reasons,100,2,IN>"))
                img = "Coming_with.bmp";

            if(queryButton.value(4).toString().contains("<dlg_Reasons,100,0,OUT>"))
                img = "Leaving_with.bmp";

            if(queryButton.value(4).toString().contains("<dlg_Reasons,100,2,OUT>"))
                img = "Leaving_with.bmp";

            if(queryButton.value(4).toString().contains("<Language>"))
                img = "Lng.bmp";

            args << QScriptValue(&engine,img);

            result = engine.evaluate("setSoftKey");
            config += result.call(QScriptValue(), args).toString() + "\n";

        }
    }


    if(isInputData) {
        QSqlQuery queryTimuxConfig("SELECT * FROM hr_gantner_TimeTerminal WHERE id_device=" + QString::number(id));
        queryTimuxConfig.next();

        for(int i=1; i<=20; i++) {

            QString text = queryTimuxConfig.value(i+11).toString();
            int check = queryTimuxConfig.value(i+31).toInt();
            QString format = queryTimuxConfig.value(i+51).toString();

            if(text != "") {
                args.clear();
                args << QScriptValue(&engine,i);
                args << QScriptValue(&engine,text);
                result = engine.evaluate("setDBETextDefinition");
                config += result.call(QScriptValue(), args).toString() + "\n";

                args.clear();
                args << QScriptValue(&engine,i);
                args << QScriptValue(&engine,format);

                result = engine.evaluate("setDisplayDefinitionBDE");
                config += result.call(QScriptValue(), args).toString() + "\n";

                args.clear();
                args << QScriptValue(&engine,i);
                args << QScriptValue(&engine,check);

                result = engine.evaluate("checkBDEInput");
                config += result.call(QScriptValue(), args).toString() + "\n";

            }
        }

    }

    //Remove all absent reason
    result = engine.evaluate("removeAllAbsentReason");
    config += result.call().toString() + "\n";
qDebug() << config;
    QTextCodec *codec = QTextCodec::codecForName("UTF-8");

    ftp->put(codec->fromUnicode(config), "config.dat");

    QMap<QString, QVariant> p;
    CGantnerTimeTerminal::s_removeAllUsers(this,p);

    QMap<QString, QString> params;
    QString xml = CXmlFactory::deviceEvent(QString::number(id), "reloadAllData", params);
    emit deviceEvent(xml);

    action = WAITING;
    ftp->close();

    emit deviceConnection(id, true);
}

void CGantnerTimeTerminal::close()
{
    //qDebug() << "CLOSE";

  if(ecbDecryption)
  {
    delete ecbDecryption;
    ecbDecryption = NULL;
  }

  if(timerCheckBooking)
  {
      killTimer(timerCheckBooking);
      timerCheckBooking = 0;
  }

  if(timerConfigFile)
  {
      killTimer(timerConfigFile);
      timerConfigFile = 0;
  }


  if(timerSendFile)
  {
      killTimer(timerSendFile);
      timerSendFile = 0;
  }

  if(timerConnectionAbort)
  {
      killTimer(timerConnectionAbort);
      timerConnectionAbort = 0;
  }

  if(timerSyncTime)
  {
      killTimer(timerSyncTime);
      timerSyncTime = 0;
  }

  ftp->abort();
  ftp->close();
  ftp->deleteLater();
  ftp = NULL;

  udp->close();
  udp->deleteLater();
  udp = NULL;

  bookingError = false;
  action = WAITING;

  if(  _isConnected )
    emit deviceConnection(id, false);

  _isConnected = false;

}

bool CGantnerTimeTerminal::isOpened()
{
  return _isConnected;
}

void CGantnerTimeTerminal::timerEvent ( QTimerEvent * event )
{
    // if the terminal is configure to reboot automaticly at every specific time, don't try to communicate with him during this time
    if(isAutoRestart) {
        QTime current = QTime::currentTime();
        QStringList t = autoRestart.split(":");
        if(t.count()>=2) {
            QTime rebootTime(t.at(0).toInt() ,t.at(1).toInt(),0);

            if(rebootTime <= current && current <= rebootTime.addSecs(600) )
                return;
        }

    }


    if(timerSyncTime == event->timerId() )
    {
        QScriptValue result = engine.evaluate("setUnitTime");
        QScriptValueList args;
        args << QScriptValue(&engine,QDateTime::currentDateTime().toString(Qt::ISODate));
        udp->write(result.call(QScriptValue(), args).toString().toLatin1());

        return;
    }

    if( timerCheckBooking == event->timerId() )
    {
        // if action is different than WAITING, do it later
        if(action == WAITING)
        {
            action = READ_BOOKING;
            connectionToFtp();
        }

        return;
    }

    if(timerSendFile == event->timerId() )
    {
        if(sendFileList.size() > 0 && action == WAITING)
        {
            numberOfSendCommand = 0;
            sendFile = "";
            for(int i=0; i<sendFileList.size(); i++)
            {
                sendFile += sendFileList.at(i);
                sendFile += "\n";
                numberOfSendCommand++;

            }
            qDebug() << sendFile;
            action = SEND_DOWN;
            readFile = "";
            connectionToFtp();
        }

        return;
    }

    if(timerConfigFile == event->timerId() )
    {
        if(sendConfigList.size() > 0 && action == WAITING)
        {
            numberOfConfigCommand = 0;
            configFile = "";
            for(int i=0; i<sendConfigList.size(); i++)
            {
                configFile += sendConfigList.at(i);
                configFile += "\n";
                numberOfConfigCommand++;

            }
            //qDebug() << configFile;
            action = SEND_CONFIG;
            connectionToFtp();
        }

        return;
    }

    if(timerConnectionAbort ==  event->timerId() )
    {
        //qDebug("FTP CONNECTION ERROR");
        killTimer( timerConnectionAbort ) ;
        timerConnectionAbort = 0;
        close();
        open();

        return;
    }
}

void CGantnerTimeTerminal::readyRead ()
{
    if(action == READ_BOOKING || action == SEND_DOWN || action == READ_CONFIG_FILE)
    {
        readFile += ftp->readAll();
        return;
    }

}

void CGantnerTimeTerminal::readUdp()
{
    QString info = udp->readAll();
    infoList = info.split(";");

    for(int i=0; i<infoList.size(); i++)
    {
        if(infoList.at(i).contains("SN:"))
        {
            QStringList sn = infoList.at(i).split(":");
            serialNumber = sn.at(1);
        }
        if(infoList.at(i).contains("FW:"))
        {
            QStringList fw = infoList.at(i).split(":");
            firmwareVersion =  fw.at(1);
        }

        if(infoList.at(i).contains("DateTime="))
        {

        }

    }
}

/*!
    \fn CGantnerTimeTerminal::logComm(QByteArray ba)
 */
void CGantnerTimeTerminal::logComm(uchar *ba, bool isReceive, int len)
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

QDomElement CGantnerTimeTerminal::getDeviceInfo(QDomDocument xml_info )
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

  newElement = xml_info.createElement( "serialNumber");
  text =  xml_info.createTextNode(serialNumber);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "firmwareVersion");
  text =  xml_info.createTextNode(firmwareVersion);
  newElement.appendChild(text);
  device.appendChild(newElement);

  for(int i=0; i<infoList.size(); i++)
  {
      QStringList pList = infoList.at(i).split(":");
      if(pList.at(0) != "SN" && pList.at(0) != "FW")
      {
          if(pList.size()==2)
          {
              newElement = xml_info.createElement( pList.at(0));
              text =  xml_info.createTextNode(pList.at(1));
              newElement.appendChild(text);
              device.appendChild(newElement);
          }
      }
  }
  return device;

}

void CGantnerTimeTerminal::s_addUser(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result = pThis->engine.evaluate("addUser");
    QScriptValueList args;
    args << QScriptValue(&(pThis->engine),params["userId"].toString());
    args << QScriptValue(&(pThis->engine),params["userNo"].toString());
    args << QScriptValue(&(pThis->engine),QString(params["displayName"].toString()));
    args << QScriptValue(&(pThis->engine),params["lang"].toString());
    args << QScriptValue(&(pThis->engine),params["fiuUse"].toString());
    args << QScriptValue(&(pThis->engine),params["attendanceStatus"].toString());
    args << QScriptValue(&(pThis->engine),params["b0"].toString());
    args << QScriptValue(&(pThis->engine),params["b1"].toString());
    args << QScriptValue(&(pThis->engine),params["b2"].toString());
    args << QScriptValue(&(pThis->engine),params["b3"].toString());
    args << QScriptValue(&(pThis->engine),params["b4"].toString());
    args << QScriptValue(&(pThis->engine),params["b5"].toString());
    args << QScriptValue(&(pThis->engine),params["b6"].toString());
    args << QScriptValue(&(pThis->engine),params["b7"].toString());
    args << QScriptValue(&(pThis->engine),params["b8"].toString());
    args << QScriptValue(&(pThis->engine),params["b9"].toString());

    pThis->sendFileList.append(result.call(QScriptValue(), args).toString());
}


void CGantnerTimeTerminal::s_removeUser(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result = pThis->engine.evaluate("removeUser");
    QScriptValueList args;
    args << QScriptValue(&(pThis->engine),params["userId"].toString());

    pThis->sendFileList.append(result.call(QScriptValue(), args).toString());
}

void CGantnerTimeTerminal::s_addKey(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result = pThis->engine.evaluate("addKey");
    QScriptValueList args;
    args << QScriptValue(&(pThis->engine),params["userId"].toString());
    args << QScriptValue(&(pThis->engine),params["key"].toString());
    args << QScriptValue(&(pThis->engine),params["keyType"].toString());

    pThis->sendFileList.append(result.call(QScriptValue(), args).toString());
}


void CGantnerTimeTerminal::s_removeAllUsers(QObject *p, QMap<QString, QVariant>)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result = pThis->engine.evaluate("removeAllUsers");
    pThis->sendFileList.append(result.call().toString());
}

void CGantnerTimeTerminal::s_addUserBalances(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result = pThis->engine.evaluate("addUserBalances");
    QScriptValueList args;
    args << QScriptValue(&(pThis->engine),params["userId"].toString());
    args << QScriptValue(&(pThis->engine),QString(params["balances"].toString().toUtf8()));

    pThis->sendFileList.append(result.call(QScriptValue(), args).toString());
}


void CGantnerTimeTerminal::s_removeKey(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result = pThis->engine.evaluate("removeKey");
    QScriptValueList args;
    args << QScriptValue(&(pThis->engine),params["key"].toString());
    args << QScriptValue(&(pThis->engine),params["keyType"].toString());

    pThis->sendFileList.append(result.call(QScriptValue(), args).toString());
}

void CGantnerTimeTerminal::s_addAbsentReason(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result = pThis->engine.evaluate("addAbsentReason");
    QScriptValueList args;
    args << QScriptValue(&(pThis->engine),params["reasonId"].toString());
    args << QScriptValue(&(pThis->engine),QString(params["text"].toString().toUtf8()));
    args << QScriptValue(&(pThis->engine),params["status"].toString());
    args << QScriptValue(&(pThis->engine),params["group"].toString());

    pThis->sendFileList.append(result.call(QScriptValue(), args).toString());
}

void CGantnerTimeTerminal::s_removeAbsentReason(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result = pThis->engine.evaluate("removeAbsentReason");
    QScriptValueList args;
    args << QScriptValue(&(pThis->engine),params["reasonId"].toString());

    pThis->sendFileList.append(result.call(QScriptValue(), args).toString());
}

void CGantnerTimeTerminal::s_removeAllAbsentReason(QObject *p, QMap<QString, QVariant>)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result = pThis->engine.evaluate("removeAllAbsentReason");

    pThis->sendFileList.append(result.call().toString());
}

void CGantnerTimeTerminal::s_setBalanceText(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result;

    result= pThis->engine.evaluate("setBalanceText");
    QScriptValueList args;
    args << QScriptValue(&(pThis->engine),params["fieldNo"].toString());
    args << QScriptValue(&(pThis->engine),QString(params["text"].toString().toUtf8()));

    pThis->sendConfigList.append(result.call(QScriptValue(), args).toString());
}

void CGantnerTimeTerminal::s_reinit(QObject *p, QMap<QString, QVariant>)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);
    pThis->action = REINIT;
    pThis->connectionToFtp();
}

void CGantnerTimeTerminal::s_removeBDEData(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result;

    result= pThis->engine.evaluate("removeBDEData");
    QScriptValueList args;
    args << QScriptValue(&(pThis->engine),params["BDEfieldNo"].toString());
    args << QScriptValue(&(pThis->engine),QString(params["value"].toString().toUtf8()));

    pThis->sendFileList.append(result.call(QScriptValue(), args).toString());

}

void CGantnerTimeTerminal::s_addBDEData(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerTimeTerminal *pThis = qobject_cast<CGantnerTimeTerminal *>(p);

    QScriptValue result;

    result= pThis->engine.evaluate("addBDEData");
    QScriptValueList args;
    args << QScriptValue(&(pThis->engine),params["BDEfieldNo"].toString());
    args << QScriptValue(&(pThis->engine),QString(params["value"].toString().toUtf8()));
    args << QScriptValue(&(pThis->engine),QString(params["valueText"].toString().toUtf8()));

    pThis->sendFileList.append(result.call(QScriptValue(), args).toString());

}


Q_EXPORT_PLUGIN2(gantnertimeterminal, CGantnerTimeTerminal);
