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
#include "cgantneraccessterminal.h"
#include <QtCore>
#include <QtSql>

CGantnerAccessTerminal::CGantnerAccessTerminal(QObject *parent) : QObject(parent)
{
    _isConnected = false;
    ecbDecryption = NULL;
    tcp = NULL;
    status = FREE;
    forceReinit = false;
    timeoutMsgError = 0;
    msgWaitResponse = NULL;
    timerSetDateTime = NULL;
    timerPollingTerminalInformation = NULL;
    timerPollingAccessStatus = NULL;
    is2personAccess.clear();
    readerInfoError = 0;
    checkBooking = 500;
    lastCardNumber = 0;

    addFunction("Gantner_AccessTerminal_setDayPlan", CGantnerAccessTerminal::s_setDayPlan);
    addFunction("Gantner_AccessTerminal_setSchedule", CGantnerAccessTerminal::s_setSchedule);
    addFunction("Gantner_AccessTerminal_setSpecialDay", CGantnerAccessTerminal::s_setSpecialDay);
    addFunction("Gantner_AccessTerminal_setRelayPlan", CGantnerAccessTerminal::s_setRelayPlan);
    addFunction("Gantner_AccessTerminal_settingActualizing", CGantnerAccessTerminal::s_SettingActualizing);
    addFunction("Gantner_AccessTerminal_loadPersonnel", CGantnerAccessTerminal::s_loadPersonnel);
    addFunction("Gantner_AccessTerminal_unloadPersonnel", CGantnerAccessTerminal::s_unloadPersonnel);
    addFunction("Gantner_AccessTerminal_reinit", CGantnerAccessTerminal::s_reinit);
    addFunction("Gantner_AccessTerminal_openByCommand", CGantnerAccessTerminal::s_openByCommand);

}

CDeviceInterface *CGantnerAccessTerminal::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
  CDeviceInterface *p = new CGantnerAccessTerminal ( parent );

  p->setParameter("name",config["name"]);
  p->setParameter("_isLog",config["isLog"]);
  p->setParameter("accessPlugin",config["accessPlugin"]);
  p->setParameter("id",config["id_device"]);  
  p->setParameter("checkBooking",config["checkBooking"]);

  p->setParameter("ipOrDhcp",config["ipOrDhcp"]);
  p->setParameter("userMemory",config["userMemory"]);
  p->setParameter("accessMemory",config["accessMemory"]);

  p->setParameter("subscriberNumber",config["subscriberNumber"]);
  p->setParameter("plantNumber",config["plantNumber"]);
  p->setParameter("mainCompIdCard",config["mainCompIdCard"]);
  p->setParameter("bookingCodeSumWinSwitchOver",config["bookingCodeSumWinSwitchOver"]);
  p->setParameter("switchOverLeap",config["switchOverLeap"]);
  p->setParameter("waitingTimeInput",config["waitingTimeInput"]);
  p->setParameter("monitoringTime",config["monitoringTime"]);
  p->setParameter("monitorinChangingTime",config["monitorinChangingTime"]);
  p->setParameter("cardReaderType",config["cardReaderType"]);

  p->setParameter("normalRelayPlan",config["normalRelayPlan"]);
  p->setParameter("specialRelayPlan",config["specialRelayPlan"]);
  p->setParameter("maxDoorOpenTime",config["maxDoorOpenTime"]);
  p->setParameter("warningTimeDoorOpenTime",config["warningTimeDoorOpenTime "]);
  p->setParameter("unlockingTime",config["unlockingTime"]);
  p->setParameter("relay1",config["relay1"]);
  p->setParameter("timeRelay1",config["timeRelay1"]);
  p->setParameter("relay2",config["relay2"]);
  p->setParameter("timeRelay2",config["timeRelay2"]);
  p->setParameter("relay3",config["relay3"]);
  p->setParameter("timeRelay3",config["timeRelay3"]);
  p->setParameter("relay4",config["relay4"]);
  p->setParameter("timeRelay4",config["timeRelay4"]);
  p->setParameter("opto1",config["opto1"]);
  p->setParameter("opto2",config["opto2"]);
  p->setParameter("opto3",config["opto3"]);
  p->setParameter("opto4",config["opto4"]);
  p->setParameter("enterExitInfo",config["enterExitInfo"]);
  p->setParameter("autoUnlocking",config["autoUnlocking"]);
  p->setParameter("lockUnlockCommand",config["lockUnlockCommand"]);
  p->setParameter("holdUpPINCode",config["holdUpPINCode"]);
  p->setParameter("twoPersonAccess",config["twoPersonAccess"]);
  p->setParameter("barriereRepeatedAccess",config["barriereRepeatedAccess"]);
  p->setParameter("timeBookingControl",config["timeBookingControl"]);
  p->setParameter("antiPassActive",config["antiPassActive"]);
  p->setParameter("relayExpanderControl",config["relayExpanderControl"]);
  p->setParameter("terminalType",config["terminalType"]);
  p->setParameter("doorOpenTimeUnit",config["doorOpenTimeUnit"]);

  p->setParameter("readerTimeout",config["readerTimeout"]);
  p->setParameter("readerFiu",config["readerFiu"]);
  p->setParameter("readerEntryExit",config["readerEntryExit"]);

  p->setParameter("optionalCompanyID1",config["optionalCompanyID1"]);
  p->setParameter("optionalCompanyID2",config["optionalCompanyID2"]);
  p->setParameter("optionalCompanyID3",config["optionalCompanyID3"]);
  p->setParameter("optionalCompanyID4",config["optionalCompanyID4"]);
  p->setParameter("optionalCompanyID5",config["optionalCompanyID5"]);
  p->setParameter("optionalCompanyID6",config["optionalCompanyID6"]);
  p->setParameter("optionalCompanyID7",config["optionalCompanyID7"]);
  p->setParameter("optionalCompanyID8",config["optionalCompanyID8"]);
  p->setParameter("optionalCompanyID9",config["optionalCompanyID9"]);
  p->setParameter("optionalCompanyID10",config["optionalCompanyID10"]);

  p->setParameter("optionalCardStructur",config["optionalCardStructur"]);
  p->setParameter("optionalGantnerNationalCode",config["optionalGantnerNationalCode"]);
  p->setParameter("optionalGantnerCustomerCode1",config["optionalGantnerCustomerCode1"]);
  p->setParameter("optionalGantnerCustomerCode2",config["optionalGantnerCustomerCode2"]);
  p->setParameter("optionalGantnerCustomerCode3",config["optionalGantnerCustomerCode3"]);
  p->setParameter("optionalGantnerCustomerCode4",config["optionalGantnerCustomerCode4"]);
  p->setParameter("optionalGantnerCustomerCode5",config["optionalGantnerCustomerCode5"]);
  p->setParameter("optionalReaderInitialisation",config["optionalReaderInitialisation"]);
  p->setParameter("optionalTableCardType",config["optionalTableCardType"]);

  return p;
}




void CGantnerAccessTerminal::deviceAction(QString xml)
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
        if( i.key() == "openDoor") return;

        // try to push the action to a child device
        foreach(CDeviceInterface *d, childDevice)
        {
            xml.replace("<deviceAction id=\"" + QString::number(id)  + "\">", "<deviceAction id=\"" + d->getParameter("id").toString() + "\">");
            d->deviceAction(xml);
        }

        //qDebug("The function %s is not define in the device %s", i.key().toLatin1() .constData(), name.toLatin1().constData());
    }
  }

}


void CGantnerAccessTerminal::connectChild(CDeviceInterface *device)
{
    if(!childDevice.contains(device->getParameter("id").toInt()))
    {
        if(device)
        {
            childDevice[device->getParameter("id").toInt()] = device;
            device->open();
        }
    }
}

QVariant CGantnerAccessTerminal::getParameter(QString paramName)
{
  if(paramName == "name")
    return name;
  if(paramName == "id")
    return id;
  if(paramName == "_isLog")
    return _isLog;
  if(paramName == "accessPlugin")
    return accessPlugin;

  if(paramName == "userMemory")
    return userMemory;

  if(paramName == "accessMemory")
    return accessMemory;

  if(paramName == "subscriberNumber")
    return subscriberNumber;

  if(paramName == "plantNumber")
    return plantNumber;

  if(paramName == "mainCompIdCard")
    return mainCompIdCard;

  if(paramName == "bookingCodeSumWinSwitchOver")
    return bookingCodeSumWinSwitchOver;

  if(paramName == "switchOverLeap")
    return switchOverLeap;

  if(paramName == "waitingTimeInput")
    return waitingTimeInput;

  if(paramName == "monitoringTime")
    return monitoringTime;

  if(paramName == "monitorinChangingTime")
    return monitorinChangingTime;

  if(paramName == "cardReaderType")
    return cardReaderType;

  if(paramName == "openSchedule")
    return openSchedule;

  if(paramName == "normalRelayPlan")
    return normalRelayPlan;

  if(paramName == "specialRelayPlan")
    return specialRelayPlan;

  if(paramName == "maxDoorOpenTime")
    return maxDoorOpenTime;

  if(paramName == "warningTimeDoorOpenTime")
    return warningTimeDoorOpenTime;

  if(paramName == "unlockingTime")
    return unlockingTime;

  if(paramName == "relay1")
    return relay1;

  if(paramName == "timeRelay1")
    return timeRelay1;

  if(paramName == "relay2")
    return relay2;

  if(paramName == "timeRelay2")
    return timeRelay2;

  if(paramName == "relay3")
    return relay3;

  if(paramName == "timeRelay3")
    return timeRelay3;

  if(paramName == "relay4")
    return relay4;

  if(paramName == "timeRelay4")
    return timeRelay4;

  if(paramName == "opto1")
    return opto1;

  if(paramName == "opto2")
    return opto2;

  if(paramName == "opto3")
    return opto3;

  if(paramName == "opto4")
    return opto4;

  if(paramName == "enterExitInfo")
    return enterExitInfo;

  if(paramName == "autoUnlocking")
    return autoUnlocking;

  if(paramName == "lockUnlockCommand")
    return lockUnlockCommand;

  if(paramName == "holdUpPINCode")
    return holdUpPINCode;

  if(paramName == "twoPersonAccess")
    return twoPersonAccess;

  if(paramName == "barriereRepeatedAccess")
    return barriereRepeatedAccess;

  if(paramName == "timeBookingControl")
    return timeBookingControl;

  if(paramName == "antiPassActive")
    return antiPassActive;

  if(paramName == "relayExpanderControl")
    return relayExpanderControl;

  if(paramName == "terminalType")
    return terminalType;

  if(paramName == "doorOpenTimeUnit")
    return doorOpenTimeUnit;


  if(paramName == "readerTimeout")
    return readerTimeout;

  if(paramName == "readerFiu")
    return readerFiu;

  if(paramName == "readerEntryExit")
    return readerEntryExit;

  if(paramName == "optionalCompanyID1")
    return optionalCompanyID1;

  if(paramName == "optionalCompanyID2")
    return optionalCompanyID2;

  if(paramName == "optionalCompanyID3")
    return optionalCompanyID3;

  if(paramName == "optionalCompanyID4")
    return optionalCompanyID4;

  if(paramName == "optionalCompanyID5")
    return optionalCompanyID5;

  if(paramName == "optionalCompanyID6")
    return optionalCompanyID6;

  if(paramName == "optionalCompanyID7")
    return optionalCompanyID7;

  if(paramName == "optionalCompanyID8")
    return optionalCompanyID8;

  if(paramName == "optionalCompanyID9")
    return optionalCompanyID9;

  if(paramName == "optionalCompanyID10")
    return optionalCompanyID10;


  if(paramName == "optionalCardStructur")
    return optionalCardStructur;

  if(paramName == "optionalGantnerNationalCode")
    return optionalGantnerNationalCode;

  if(paramName == "optionalGantnerCustomerCode1")
    return optionalGantnerCustomerCode1;

  if(paramName == "optionalGantnerCustomerCode2")
    return optionalGantnerCustomerCode2;

  if(paramName == "optionalGantnerCustomerCode3")
    return optionalGantnerCustomerCode3;

  if(paramName == "optionalGantnerCustomerCode4")
    return optionalGantnerCustomerCode4;

  if(paramName == "optionalGantnerCustomerCode5")
    return optionalGantnerCustomerCode5;

  if(paramName == "optionalReaderInitialisation")
    return optionalReaderInitialisation;

  if(paramName == "optionalTableCardType")
    return optionalTableCardType;

  if(paramName == "checkBooking")
    return checkBooking;

  return "undefined";
}

void CGantnerAccessTerminal::setParameter(QString paramName, QVariant value)
{
  if(paramName == "name")
    name = value.toString();
  if(paramName == "id")
    id = value.toInt();
  if(paramName == "_isLog")
    _isLog = value.toBool();
  if(paramName == "accessPlugin")
    accessPlugin = value.toString();

  if(paramName == "ipOrDhcp")
    ipOrDhcp = value.toString();

  if(paramName == "userMemory")
    userMemory = value.toInt();

  if(paramName == "accessMemory")
    accessMemory = value.toInt();



  if(paramName == "subscriberNumber")
    subscriberNumber = value.toInt();

  if(paramName == "plantNumber")
    plantNumber = value.toInt();

  if(paramName == "mainCompIdCard")
    mainCompIdCard = value.toInt();

  if(paramName == "bookingCodeSumWinSwitchOver")
    bookingCodeSumWinSwitchOver = value.toInt();

  if(paramName == "switchOverLeap")
    switchOverLeap = value.toInt();

  if(paramName == "waitingTimeInput")
    waitingTimeInput = value.toInt();

  if(paramName == "monitoringTime")
    monitoringTime = value.toInt();

  if(paramName == "monitorinChangingTime")
    monitorinChangingTime = value.toInt();

  if(paramName == "cardReaderType")
    cardReaderType = value.toInt();

  if(paramName == "normalRelayPlan")
    normalRelayPlan = value.toInt();

  if(paramName == "specialRelayPlan")
    specialRelayPlan = value.toInt();

  if(paramName == "maxDoorOpenTime")
    maxDoorOpenTime = value.toInt();

  if(paramName == "warningTimeDoorOpenTime")
    warningTimeDoorOpenTime = value.toInt();

  if(paramName == "unlockingTime")
    unlockingTime = value.toInt();

  if(paramName == "relay1")
    relay1 = value.toInt();

  if(paramName == "timeRelay1")
    timeRelay1 = value.toInt();

  if(paramName == "relay2")
    relay2 = value.toInt();

  if(paramName == "timeRelay2")
    timeRelay2 = value.toInt();

  if(paramName == "relay3")
    relay3 = value.toInt();

  if(paramName == "timeRelay3")
    timeRelay3 = value.toInt();

  if(paramName == "relay4")
    relay4 = value.toInt();

  if(paramName == "timeRelay4")
    timeRelay4 = value.toInt();

  if(paramName == "opto1")
    opto1 = value.toInt();

  if(paramName == "opto2")
    opto2 = value.toInt();

  if(paramName == "opto3")
    opto3 = value.toInt();

  if(paramName == "opto4")
    opto4 = value.toInt();

  if(paramName == "enterExitInfo")
    enterExitInfo = value.toInt();

  if(paramName == "autoUnlocking")
    autoUnlocking = value.toInt();

  if(paramName == "lockUnlockCommand")
    lockUnlockCommand = value.toInt();

  if(paramName == "holdUpPINCode")
    holdUpPINCode = value.toString();

  if(paramName == "twoPersonAccess")
    twoPersonAccess = value.toInt();

  if(paramName == "barriereRepeatedAccess")
    barriereRepeatedAccess = value.toInt();

  if(paramName == "timeBookingControl")
    timeBookingControl = value.toInt();

  if(paramName == "antiPassActive")
    antiPassActive = value.toInt();

  if(paramName == "relayExpanderControl")
    relayExpanderControl = value.toInt();

  if(paramName == "terminalType")
    terminalType = value.toInt();

  if(paramName == "doorOpenTimeUnit")
    doorOpenTimeUnit = value.toInt();

  if(paramName == "readerTimeout")
    readerTimeout = value.toInt();

  if(paramName == "readerFiu")
    readerFiu = value.toInt();

  if(paramName == "readerEntryExit")
    readerEntryExit = value.toInt();

  if(paramName == "optionalCompanyID1")
    optionalCompanyID1 = value.toInt();

  if(paramName == "optionalCompanyID2")
    optionalCompanyID2 = value.toInt();

  if(paramName == "optionalCompanyID3")
    optionalCompanyID3 = value.toInt();

  if(paramName == "optionalCompanyID4")
    optionalCompanyID4 = value.toInt();

  if(paramName == "optionalCompanyID5")
    optionalCompanyID5 = value.toInt();

  if(paramName == "optionalCompanyID6")
    optionalCompanyID6 = value.toInt();

  if(paramName == "optionalCompanyID7")
    optionalCompanyID7 = value.toInt();

  if(paramName == "optionalCompanyID8")
    optionalCompanyID8 = value.toInt();

  if(paramName == "optionalCompanyID9")
    optionalCompanyID9 = value.toInt();

  if(paramName == "optionalCompanyID10")
    optionalCompanyID10 = value.toInt();

  if(paramName == "optionalCardStructur")
    optionalCardStructur = value.toInt();

  if(paramName == "optionalGantnerNationalCode")
    optionalGantnerNationalCode = value.toInt();

  if(paramName == "optionalGantnerCustomerCode1")
    optionalGantnerCustomerCode1 = value.toString();

  if(paramName == "optionalGantnerCustomerCode2")
   optionalGantnerCustomerCode2 = value.toString();

  if(paramName == "optionalGantnerCustomerCode3")
    optionalGantnerCustomerCode3 = value.toString();

  if(paramName == "optionalGantnerCustomerCode4")
    optionalGantnerCustomerCode4 = value.toString();

  if(paramName == "optionalGantnerCustomerCode5")
    optionalGantnerCustomerCode5 = value.toString();

  if(paramName == "optionalReaderInitialisation")
    optionalReaderInitialisation = value.toString();

  if(paramName == "optionalTableCardType")
    optionalTableCardType = value.toString();

  if(paramName == "checkBooking")
  {
    if(value.toInt()>0)
        checkBooking = value.toInt() * 1000 *60;
    else
        checkBooking = 500; // minimum 500ms
  }
}

QString CGantnerAccessTerminal::getScript()
{
    /*
    //for the test
    QString script = "";
    QFile file( QCoreApplication::instance()->applicationDirPath() + "/accessterminal.js");
    if(file.open(QIODevice::ReadOnly))
    {
        script = file.readAll();
        qDebug() << "Protocol Gantner Access loaded";
    }

    return script;
    */

    if( !ecbDecryption )
    {
        QSettings settings(QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat);
        settings.beginGroup("GantnerAccessTerminal");

        QString keyscript = settings.value("keyscript","0000000000000000").toString();
        if(!settings.contains("keyscript")) settings.setValue("keyscript", "0000000000000000");

        unsigned char aesdata[16];

        for(int i=0; i<16; i++)
        {
            aesdata[i] = keyscript.at(i).toLatin1();
        }

        ecbDecryption = new ECB_Mode<AES >::Decryption(aesdata, AES::DEFAULT_KEYLENGTH);
    }

    QFile file( QCoreApplication::instance()->applicationDirPath() + "/accessterminal.js.aes");
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
        qDebug() << "Protocol Gantner Access loaded";
        return script;

    }

    qDebug() << "Protocol Gantner Access not loaded";
    return "-1";
}

bool CGantnerAccessTerminal::decrypt(const unsigned char *encrypt_msg,
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

bool CGantnerAccessTerminal::open()
{
    qDebug() << "TCP opened ";

    if( tcp && tcp->state() == QAbstractSocket::ConnectedState )
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
qDebug() << "OPEN";

        tcp = new QTcpSocket(this);
        connect(tcp, SIGNAL(readyRead()), this, SLOT(readAccessTerminal()));
        connect(tcp, SIGNAL(error(QAbstractSocket::SocketError)), this, SLOT(error(QAbstractSocket::SocketError)));
        connect(tcp, SIGNAL(connected()), this, SLOT(connected()));
        connect(tcp, SIGNAL(disconnected ()), this, SLOT(deviceDiconnected()));

        tcp->abort();
        tcp->connectToHost(ipOrDhcp, 8000);

        return true;
    }

    return false;
}

void CGantnerAccessTerminal::reopen()
{
    open();
}

void CGantnerAccessTerminal::connected()
{
    qDebug() << "Connected";
    _isConnected = true;
    emit deviceConnection(this->id,true);

    msgWaitResponse = new QTimer(this);
    connect(msgWaitResponse, SIGNAL(timeout()), this, SLOT(msgTimeout()));

    timerSetDateTime = new QTimer(this);
    connect(timerSetDateTime, SIGNAL(timeout()), this, SLOT(setDateTime()));

    timerPollingTerminalInformation = new QTimer(this);
    connect(timerPollingTerminalInformation, SIGNAL(timeout()), this, SLOT(pollingTerminalInformation()));

    timerPollingAccessStatus = new QTimer(this);
    connect(timerPollingAccessStatus, SIGNAL(timeout()), this, SLOT(pollingAccessStatus()));

    //forceReinit = true;
    pollingTerminalInformation();

    pollingAccessStatus();


}

void CGantnerAccessTerminal::s_reinit(QObject *p, QMap<QString, QVariant>)
{
    CGantnerAccessTerminal *pThis = qobject_cast<CGantnerAccessTerminal *>(p);

    pThis->reinit();
}

void CGantnerAccessTerminal::reinit()
{
    /****************************************************************************************/
    // 1. read out the booking
    readOutBookings();

    /****************************************************************************************/
    // 2. set the memory configuration of the terminal
    setMemoryInitializing();

    /****************************************************************************************/
    // 3. download the key checking
    setDownloadKey();

    /****************************************************************************************/
    // 4. set the access configuration
    accessConfiguration();

    /****************************************************************************************/
    // 5.
    settingActualizing();

    /****************************************************************************************/
    // 6. read out the booking
    readOutBookings();

    /****************************************************************************************/
    // 7. set the subscriber data
    setSubscriberData();

    /****************************************************************************************/
    // 8. Obtain the serial and article number of the reader
    getReaderInfo();

}

void CGantnerAccessTerminal::deviceDiconnected()
{
    qDebug() << "Disconnected";

    close();

    QTimer::singleShot(5000, this, SLOT(reopen()));
}

void CGantnerAccessTerminal::readAccessTerminal()
{
    if(tcp->bytesAvailable () > 0)
    {
        msg += tcp->readAll();
        hasMsg();
    }
}

void CGantnerAccessTerminal::hasMsg()
{
    //! do we read any byte
    if(msg.length() == 0) return;

    //! do we have at least 2 bytes
    if(msg.length()>=2)
    {
        if(msg.at(0) == '!')
        {
            int posEndMsg = msg.indexOf(0x0D,1);
            if( posEndMsg > 0 )
            {
                if(checkCheckSum(msg.left(posEndMsg + 1 )))
                {
                    dispatchMessage( msg.left(posEndMsg + 1 ) );
                }
                else
                {
                    qDebug() << "Checksum error";
                }
            }
        }
        else
        {
            //! the first char must be a !, if not remove all bytes
            msg.clear();
        }
    }


}

void CGantnerAccessTerminal::error(QAbstractSocket::SocketError socketError)
{
    switch (socketError)
    {
        case QAbstractSocket::RemoteHostClosedError:
            qDebug() << "Remote closed error";
            break;
        case QAbstractSocket::HostNotFoundError:
            qDebug() << "Host was not found";
            break;
        case QAbstractSocket::ConnectionRefusedError:
            qDebug() << "The connection was refused by the peer.";
            break;
        case QAbstractSocket::NetworkError:
            qDebug() << "An error occurred with the network (e.g., the network cable was accidentally plugged out).";
            break;
        case QAbstractSocket::SocketTimeoutError:
            qDebug() << "The socket operation timed out.";
            break;
        default:
            qDebug() << QString("The following error occurred: %1.").arg(tcp->errorString());
     }

    close();

    QTimer::singleShot(5000, this, SLOT(reopen()));

}

bool CGantnerAccessTerminal::checkCheckSum(QByteArray message)
{
    if(message.length()<4) return false;

    QByteArray cs_ba = getCheckSum(message.left(message.length()-3));
    return cs_ba == message.mid(message.length()-3,2);
}

QByteArray CGantnerAccessTerminal::getCheckSum(QByteArray message)
{
    int cs = 0;
    QString cs_s;
    for(int i=1; i<message.length(); i++)
    {
        cs += message.at(i);
    }

    cs %= 256;
    return cs_s.sprintf("%02X", cs).toLatin1();
}

void CGantnerAccessTerminal::replaceCharTable(QByteArray *message)
{
    message->replace(QByteArray("ä"), QByteArray(QString("{").toLatin1()));
    message->replace(QByteArray("ö"), QByteArray("|"));
    message->replace(QByteArray("ü"), QByteArray("}"));
    message->replace(QByteArray("Ä"), QByteArray("["));
    message->replace(QByteArray("Ö"), QByteArray("^"));
    message->replace(QByteArray("Ü"), QByteArray("]"));
    message->replace(QByteArray("β"), QByteArray("~"));
    message->replace(QByteArray("Û"), QByteArray("$"));
}

void CGantnerAccessTerminal::appendMessage(QByteArray *newMessage)
{
  if(status == FREE)
  {
    if(isOpened())
    {
      status = BUSY;

      currentMessage.clear();

      // replace the char
     // replaceCharTable( newMessage );

      currentMessage = newMessage->constData();

      // append the checksum of the message
      newMessage->append( getCheckSum(newMessage->constData() ));

      //qDebug() << "SEND: " << newMessage->constData();

      //append the end character of the message
      newMessage->append( 0x0D );

      // start the timer to set up a timeout if we don't receive the response
      msgWaitResponse->start(500);

      tcp->write(newMessage->constData());

      tcp->flush();

      logComm((uchar*)newMessage->constData(), false, newMessage->length());

      // the message is sended, delete it
      delete newMessage;
    }
  }
  else
  {
    if(isOpened())
    {
        pendingMessage.append(newMessage);
    }
  }
}

void CGantnerAccessTerminal::msgTimeout()
{
    msgWaitResponse->stop();

    timeoutMsgError++;

    QString cmd;
    cmd = cmd.sprintf("%c%c",currentMessage.at(1),currentMessage.at(2));


    if(timeoutMsgError > 10)
    {       

        qDebug() << "TIMEOUT MESSAGE " << cmd;

        QString xml = CXmlFactory::deviceEvent(QString::number(id),"1017", "Do not receive the response from the GAT Terminal 3100 AK (" + cmd + ")");
        emit deviceEvent(xml);

        close();

        return;        
    }



    status = FREE;

    //! send next message
    if(pendingMessage.size() > 0)
    {
        QByteArray *baNext = pendingMessage.takeFirst();
        if(baNext)
        {
            appendMessage(baNext);
        }
    }

}

void CGantnerAccessTerminal::dispatchMessage(QByteArray message)
{
    // we receive the response, stop the timeout timer
    msgWaitResponse->stop();

    timeoutMsgError = 0;

    //qDebug() << "RECEIVE: " << message.constData();
    logComm((uchar*)message.constData(), true, message.length());

    bool ok;
    int cmd = message.mid(1,2).toInt(&ok, 16);

    switch(cmd)
    {
        case R_MEMORY: // memory initializing
            checkMemoryInitializing( message );
            break;
        case R_SUBSCRIBER_DATA: // subscriber data
            checkSubscriberData( message );
            break;
        case R_DATETIME: // Set Date / Time
            checkDateTimeResp( message );
            break;            
        case R_OPTIONAL_COMPANY: // Optional company
            checkOptionalCompanyID( message );
            break;            
        case R_OPTION_CARD_STRUCT: // Optional card structure
            checkOptionalCard( message );
            break;
        case R_READOUT_BOOKING:
            checkOutBookings( message );
            break;
        case R_POLLING_TERMINAL_INFO: // polling terminal info
            checkTerimalInformationResp( message );
            break;
        case R_DOWNLOAD_KEY: // download key
            checkDownloadKey( message );
            break;
        case R_ACCESS_CONFIG: // access config
            checkAccessConfiguration( message );
            break;
        case R_LOAD_SPECIAL_DAY:
            checkSpecialDay( message );
            break;
        case R_LOAD_SCHEDULE:
            checkSchedule( message );
            break;
        case R_LOAD_DAY_PLAN:
            checkDayPlan( message );
            break;
        case R_ACTUALIZING_SETTING: // actualizing settings
            checkSettingActualizing( message );
            break;
        case R_READER_SUB_COMMAND: // reader sub command
            checkReaderMessage( message );
            break;
        case R_LOAD_RELAYPLAN:
            checkRelayPlan( message );
            break;
        case R_LOAD_PERSONNEL:
            checkLoadPesonnel( message );
            break;
        case R_UNLOAD_PERSONNEL:
            checkUnloadPesonnel(message);
            break;
        case R_OPEN_BY_COMMAND:
            checkOpenByCommand(message);
            break;
        case R_ACCESS_STATUS:
            checkAccessStatus(message);
            break;
        default:
            qDebug() << "UNKNOWN RESPONSE";
            break;
    }


    msg.remove(0,message.size());

    // we are ready to send the next message
    status = FREE;

    //! receive the reader response, send the next message if has one
    if(pendingMessage.size() > 0)
    {
        QByteArray *baNext = pendingMessage.takeFirst();
        if(baNext)
        {
            appendMessage(baNext);
        }
    }

}



void CGantnerAccessTerminal::close()
{

  if(ecbDecryption)
  {
    delete ecbDecryption;
    ecbDecryption = NULL;
  }

  if(msgWaitResponse)
  {
      msgWaitResponse->stop();
      msgWaitResponse->deleteLater();
  }

  if(timerSetDateTime)
  {
      timerSetDateTime->stop();
      timerSetDateTime->deleteLater();
  }

  if(timerPollingTerminalInformation)
  {
      timerPollingTerminalInformation->stop();
      timerPollingTerminalInformation->deleteLater();
  }

  if(timerPollingAccessStatus)
  {
      timerPollingAccessStatus->stop();
      timerPollingAccessStatus->deleteLater();
  }

  if(tcp)
  {
      tcp->abort();
      tcp->deleteLater();
  }

  if(  _isConnected )
    emit deviceConnection(id, false);

  _isConnected = false;

  status = FREE;


}

bool CGantnerAccessTerminal::isOpened()
{
  return _isConnected;
}



/*!
    \fn CGantnerAccessTerminal::logComm(QByteArray ba)
 */
void CGantnerAccessTerminal::logComm(uchar *ba, bool isReceive, int len)
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
    s += ba[i];

  QTextStream out(&file);

  if(isReceive)
    out << "<span class=\"date\">" << date << "</span>" << "<span  style=\"color:blue\" class=\"receive\">" << s << "</span>" << "<br/>\n";
  else
    out << "<span class=\"date\">" << date << "</span>" << "<span style=\"color:green\" class=\"send\">" << s << "</span>" << "<br/>\n";

  file.close();
}

QDomElement CGantnerAccessTerminal::getDeviceInfo(QDomDocument xml_info )
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

  newElement = xml_info.createElement( "serviceDate");
  text =  xml_info.createTextNode(serviceDate);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "openTimeBooking");
  text =  xml_info.createTextNode(QString::number(openTimeBooking));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "openAccessBooking");
  text =  xml_info.createTextNode(QString::number(openAccessBooking));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "nbreSubReader");
  text =  xml_info.createTextNode(QString::number(nbreSubReader));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "nbreExternalDoorControl");
  text =  xml_info.createTextNode(QString::number(nbreExternalDoorControl));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "nbreRelayExpander");
  text =  xml_info.createTextNode(QString::number(nbreRelayExpander));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "downloadKey");
  text =  xml_info.createTextNode(downloadKey);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "numberIdentification");
  text =  xml_info.createTextNode(QString::number(numberIdentification));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "terminalTypeFeature");
  text =  xml_info.createTextNode(terminalTypeFeature);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "hardwareIdentification");
  text =  xml_info.createTextNode(hardwareIdentification);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "readerSerialNumber");
  text =  xml_info.createTextNode(readerSerialNumber);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "readerArticleNumber");
  text =  xml_info.createTextNode(readerArticleNumber);
  newElement.appendChild(text);
  device.appendChild(newElement);


  return device;

}

void CGantnerAccessTerminal::setDateTime()
{
    QDateTime dateTime = QDateTime::currentDateTime();
    QByteArray *msg = new QByteArray;

    QScriptValue result;
    QScriptValueList args;

    args.clear();
    args << QScriptValue(&engine,dateTime.toString("yyMMdd"));

    int weekday = dateTime.date().dayOfWeek();
    weekday = weekday == 7 ? 0 : weekday;

    args << QScriptValue(&engine,QString::number(weekday));
    args << QScriptValue(&engine,dateTime.toString("hhmmss"));

    result = engine.evaluate("setDateTime");
    msg->append( result.call(QScriptValue(), args).toString() );

    appendMessage(msg);
}

void CGantnerAccessTerminal::checkDateTimeResp( QByteArray message )
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "year not numerical");
            break;
        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "month not numerical or not in the range");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "day not numerical or not in the range");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "weekday not numerical or not in the range");
            break;
        case 5:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "hour not numerical or not in the range");
            break;
        case 6:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "minutes not numerical or not in the range");
            break;
        case 7:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "secondes not numerical or not in the range");
            break;
        case 8:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "invalide date");
            break;
        case 9:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "clock could not be set");
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::pollingTerminalInformation()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    result = engine.evaluate("pollingTerminalInformation");
    msg->append( result.call().toString() );

    appendMessage(msg);

}

void CGantnerAccessTerminal::pollingAccessStatus()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    result = engine.evaluate("pollingAccessStatus");
    msg->append( result.call().toString() );

    appendMessage(msg);
}

void CGantnerAccessTerminal::checkTerimalInformationResp( QByteArray message )
{

    if( message.length() == 11)
    {
        QString alarmXml;

        int resp = message.mid(6, 2).toInt();

        switch(resp)
        {
            case 92:
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "password active");
                break;
            case 93:
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "parity error");
                break;
            case 97:
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "set lenght invalid");
                break;
            case 98:
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + "checksum error");
                break;
        }

        if(alarmXml != "") emit deviceEvent(alarmXml);
    }
    else
    {
        firmwareVersion = message.mid(6,5);
        serviceDate = message.mid(11,6);
        openTimeBooking = message.mid(20,4).toInt();
        openAccessBooking = message.mid(24,4).toInt();
        nbreSubReader = message.mid(28,1).toInt();
        nbreExternalDoorControl = message.mid(29,1).toInt();
        nbreRelayExpander = message.mid(30,1).toInt();
        downloadKey = message.mid(31,4);
        numberIdentification = message.mid(35,6).toInt();
        terminalTypeFeature = message.mid(41,8);
        hardwareIdentification = message.mid(49,4);

        if(openAccessBooking < (accessMemory/10))
        {
            readOutBookings();
        }

        if(downloadKey != "LTUX" || forceReinit)
        {
            reinit();
            forceReinit = false;
        }
        else
        {
            if(!timerSetDateTime->isActive())
            {
                // set the date time when starting
                setDateTime();
                //reset the date and time every 1 hour
                timerSetDateTime->start(10000 * 60);
            }

            if(!timerPollingTerminalInformation->isActive())
            {
                // start the time who poll the temrinal to obtain the info
                //timerPollingTerminalInformation->start( checkBooking );
            }

            if(!timerPollingAccessStatus->isActive())
            {
                // start the time who poll the temrinal to obtain the access status
                timerPollingAccessStatus->start(500);
            }
        }

    }

}

void CGantnerAccessTerminal::getReaderInfo()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    result = engine.evaluate("getReaderInfo");
    msg->append( result.call().toString() );

    appendMessage(msg);
}

void CGantnerAccessTerminal::checkReaderMessage(QByteArray message )
{
    bool ok;
    int cmd = message.mid(6,2).toInt(&ok, 16);

    QString alarmXml;

    switch(cmd)
    {
        case 0:     //reader info
            //qDebug() << "reader commande ok";

            if(currentMessage.mid(6,1) == "0")
            {
                readerSerialNumber = message.mid(8, 10);
                readerArticleNumber = message.mid(18, 8);

                /****************************************************************************************/
                // reader FIU
                setFIUReader();

                /****************************************************************************************/
                // reader timeout
                setTimeoutReader();


                /****************************************************************************************/
                // reader configuration
                setReaderConfiguration();

                /****************************************************************************************/
                // define if the reader is an Entry or an Exit
                setEntryExitReader();

                /****************************************************************************************/
                // set the optional card
                setOptionalCard();

                /****************************************************************************************/
                // set the optional company
                setOptionalCompanyID();

                /****************************************************************************************/
                // set the date time when starting
                setDateTime();

                /****************************************************************************************/
                // ask for the terminal info when starting
                pollingTerminalInformation();

                /****************************************************************************************/
                // reload all data
                QMap<QString, QString> params;
                QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_reloadAllData", params);
                emit deviceEvent(xml);

                readerInfoError = 0;
            }
            break;
        case 1:           
            readerInfoError++;

            if(readerInfoError>10)
            {
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": serial number and article number not actual");
                readerInfoError = 0;
            }

            close();
            QTimer::singleShot(5000, this, SLOT(reopen()));
            break;
        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": communication reader error");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": error of the sub command");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": error of the sub command parameters");
            break;
        case 5:
            readerInfoError++;

            if(readerInfoError>10)
            {
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": no reader connected");
                readerInfoError = 0;
            }

            close();
            QTimer::singleShot(5000, this, SLOT(reopen()));
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
        default:
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::setMemoryInitializing()
{
    QString s;
    QString um = s.sprintf("%04d", userMemory / 10);
    QString am = s.sprintf("%04d", accessMemory / 10);

    QByteArray *msg = new QByteArray;

    QScriptValue result;
    QScriptValueList args;

    args.clear();
    args << QScriptValue(&engine,um);
    args << QScriptValue(&engine,am);

    result = engine.evaluate("setMemoryInitializing");
    msg->append( result.call(QScriptValue(), args).toString() );

    appendMessage(msg);
}

void CGantnerAccessTerminal::checkMemoryInitializing(QByteArray message )
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": number of personnell not numerical");
            break;
        case 11:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": number of booking time not numerical");
            break;
        case 12:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": number of booking access not numerical");
            break;
        case 16:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": too little memory capacity");
            break;
        case 17:
            readOutBookings();
            setMemoryInitializing();            
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::readOutBookings()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    result = engine.evaluate("readOutBookings");
    msg->append( result.call().toString() );

    appendMessage(msg);
}

void CGantnerAccessTerminal::checkOutBookings(QByteArray message )
{
    if( message.length() == 11)
    {
        QString alarmXml;

        int resp = message.mid(6, 2).toInt();

        switch(resp)
        {
            case 1:
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": faulty booking indicator");
                break;
            case 92:
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
                break;
            case 93:
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
                break;
            case 95:
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": error at file-treatment");
                break;
            case 96:
                break;
            case 97:
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
                break;
            case 98:
                alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
                break;
        }

        if(alarmXml != "") emit deviceEvent(alarmXml);
    }
    else
    {
        qDebug() << "BOOKING: " << message.constData();

        int type = message.mid(6, 1).toInt();

        // access booking
        if(type == 2)
        {
            QString subscriber = message.mid(7, 4);
            QString plant = message.mid(11, 2);
            QString userId =  message.mid(13, 8);
            QDate date( message.mid(21, 2).toInt(), message.mid(23, 2).toInt(), message.mid(25, 2).toInt());
            QTime time( message.mid(27, 2).toInt(), message.mid(29, 2).toInt(), message.mid(31, 2).toInt()) ;
            QString accessCode = message.mid(33, 1);
            QString optionData = message.mid(34, 8);
            QString entering = message.mid(42, 1);
            QString key = message.mid(43, 11);

            ulong userIdl = userId.toLongLong();
            userId = QString::number(userIdl);

            ulong keyl = key.toLongLong();
            key = QString::number(keyl);

            QMap<QString, QString> params;
            params["date"] = date.toString(Qt::ISODate);
            params["time"] = time.toString("hh:mm:ss");
            params["userId"] = userId;
            params["key"] = key;
            params["code"] = accessCode;
            params["entering"] = entering;
            params["deviceId"] = QString::number(id);

            switch((char)accessCode.at(0).toLatin1())
            {
                case '1':   //valid access
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                            params["code"] = "C";
                        }

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);
                    }
                    break;
                case '2':   // company ID incorrect
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                        }

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);
                    }
                    break;
                case '3':   // card identification
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                        }

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);
                    }
                    break;
                case '4':   // card number or card version invalid
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                        }

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);
                    }
                    break;
                case '5':  //PIN CODE incorrect
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                        }

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);

                    }
                    break;
                case '6':  //access time out of schedule
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                        }

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);
                    }
                    break;
                case '7':   // access without authorization
                    qDebug() << "Booking 7 must be implemented";
                    break;
                case '8':   //daylight saving
                    qDebug() << "Booking 8 must be implemented";
                    break;
                case '9':   //change status general control 1
                    qDebug() << "Booking 9 must be implemented";
                    break;
                case 'A':   //change status general control 2
                    qDebug() << "Booking A must be implemented";
                    break;
                case 'B':   //Unlocking by button
                    qDebug() << "Booking B must be implemented";
                    break;
                case 'C':   //1. Person for 2 persons access
                    is2personAccess.clear();
                    is2personAccess = params;
                    break;
                case 'D':   //attempt for access refused because a 2 person access without master was tried
                    qDebug() << "Booking D must be implemented";
                    break;
                case 'E':   //attempt for access refused because repeated access barrier is active
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                        }

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);
                    }
                    break;
                case 'F':   //door was shut afain after a "door open too long alarm"
                    {
                        QString xml = CXmlFactory::systemAlarm(QString::number(id), "1005", "door was shut afain after a door-open-too-long-alarm");
                        emit deviceEvent(xml);
                    }
                    break;
                case 'G':   //access without door opening
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                        }

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);
                    }

                    break;
                case 'H':   //access with hold-up PIN Code
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                        }

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);

                        // send an alarm
                        xml = CXmlFactory::systemAlarm(QString::number(id) ,"1102", "Pin code Hold up was used");
                        emit deviceEvent(xml);
                    }
                    break;
                case 'I':   //power-up notification
                    qDebug() << "Booking I must be implemented";
                    break;
                case 'J':   // anti-pass barrier
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                        }
                        params["code"] = 'E';

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);
                    }
                    break;
                case 'K':   //authorization period of person is not valid
                    {
                        if(is2personAccess.size() > 0)
                        {
                            QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", is2personAccess);
                            emit deviceEvent(xml);
                            is2personAccess.clear();
                        }

                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetected", params);
                        emit deviceEvent(xml);
                    }
                    break;
                case 'L':   //peripherical device defect
                    {
                        QString xml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": peripherical device defect");
                        emit deviceEvent(xml);
                    }
                    break;
                case 'M':   //door-open-too-long-alarm
                    {
                        QString xml = CXmlFactory::systemAlarm(QString::number(id), "1006", "door-open-too-long-alarm");
                        emit deviceEvent(xml);
                    }
                    break;
                case 'N':   // control by command
                    qDebug() << "Booking N must be implemented";
                    break;
                case 'O':   // relay actuated on terminal
                    qDebug() << "Booking O must be implemented";
                    break;
                case 'P':   //selection of a deposit box wihtout authorization
                    qDebug() << "Booking P must be implemented";
                    break;
            }

        }

       readOutBookings();
    }
}

void CGantnerAccessTerminal::setDownloadKey()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    result = engine.evaluate("setDownloadKey");
    msg->append( result.call().toString() );

    appendMessage(msg);
}

void CGantnerAccessTerminal::checkDownloadKey(QByteArray message )
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);

}

void CGantnerAccessTerminal::accessConfiguration()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    result = engine.evaluate("accessConfiguration");
    msg->append( result.call().toString() );


    QSqlQuery osQuery("SELECT COUNT(*) FROM hr_openTime_attribution WHERE id_device=" + QString::number(id));
    osQuery.next();

    if( osQuery.value(0).toInt() > 0 )
        openSchedule = 99;
    else
        openSchedule = 0;


    msg->append(QString::number(openSchedule).rightJustified(2,'0').toLatin1() );    // generally open schedule Nr. 00=>not used, 99=>schedule

    //Currently not used
    msg->append(QString::number(normalRelayPlan).rightJustified(2,'0').toLatin1());    // 00 - normal relayplannumber for generally open
    msg->append(QString::number(specialRelayPlan).rightJustified(2,'0').toLatin1());    // 00 - special relayplannumber for generally offen


    msg->append(QString::number(maxDoorOpenTime).rightJustified(2,'0').toLatin1());    // 00 - maximum of door open time in seconds
    msg->append(QString::number(warningTimeDoorOpenTime).rightJustified(2,'0').toLatin1());    // 00 - warning time for door open time in seconds
    msg->append(QString::number(unlockingTime).rightJustified(2,'0').toLatin1());    // 00 - unlocking time in seconds
    msg->append(QString::number(relay1).toLatin1());    //  0 -relay 1
    msg->append(QString::number(timeRelay1).rightJustified(2,'0').toLatin1());    // 00 - time for Relay 1
    msg->append(QString::number(relay2).toLatin1());    //  0 -relay 2
    msg->append(QString::number(timeRelay2).rightJustified(2,'0').toLatin1());    // 00 - time for relay 2 in seconds
    msg->append(QString::number(relay3).toLatin1());    //  0 -relay 3 (mit ETS)
    msg->append(QString::number(timeRelay3).rightJustified(2,'0').toLatin1());    // 00 - time for relay 3 (mit ETS)
    msg->append(QString::number(relay4).toLatin1());    //  0 -relay 4 (mit ETS)
    msg->append(QString::number(timeRelay4).rightJustified(2,'0').toLatin1());    // 00 - time for relay 4 in seconds (mit ETS)
    msg->append(QString::number(opto1).toLatin1());    //  0 -Opto 1 / IN
    msg->append(QString::number(opto2).toLatin1());    //  0 -Opto 2 / IN
    msg->append(QString::number(opto3).toLatin1());    //  0 -Opto 3 (mit ETS)  / IN
    msg->append(QString::number(opto4).toLatin1());    //  0 -Opto 4 (mit ETS)  / IN
    msg->append(QString::number(enterExitInfo).toLatin1());    //  0 -enter/exit information
    msg->append(QString::number(autoUnlocking).toLatin1());    //  0 -autonomous unlocking
    msg->append(QString::number(lockUnlockCommand).toLatin1());    //  0 -lock and unlock by command possible
    msg->append(holdUpPINCode.rightJustified(8,'0').toLatin1());    //  00000000 -hold-up-PIN-Code (8 digits)
    msg->append(QString::number(twoPersonAccess).toLatin1());    //  0 -2-persons-access control
    msg->append(QString::number(barriereRepeatedAccess).rightJustified(3,'0').toLatin1());    //  000 -barriere at repeated access
    msg->append(QString::number(timeBookingControl).toLatin1());    //  0 -time booking control
    msg->append(QString::number(antiPassActive).toLatin1());    //  0 -anti-pass active
    msg->append(QString::number(relayExpanderControl).toLatin1());    //  0 -relay expander control
    msg->append(QString::number(terminalType).toLatin1());    //  0 -terminal type
    msg->append(QString::number(doorOpenTimeUnit).toLatin1());    //  0 -door open time unit
    msg->append("00000");    //reserved 5 digits - Fingerprint

    appendMessage(msg);
}

void CGantnerAccessTerminal::checkAccessConfiguration(QByteArray message )
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": generally open schedule number invalid");
            break;
        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": normal relay plan # for generally open invalid");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": special relay plan # for generally open invalid");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": maximum door open time invalid");
            break;
        case 5:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": warning time for door open time invalid");
            break;
        case 6:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": unlocking time invalid");
            break;
        case 7:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": function relay 1 invalid");
            break;
        case 8:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": time for relay 1 invalid");
            break;
        case 9:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": function relay 2 invalid");
            break;
        case 10:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": time for relay 2 invalid");
            break;
        case 11:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": function relay 3 invalid");
            break;
        case 12:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": time for relay 3 invalid");
            break;
        case 13:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": function relay 4 invalid");
            break;
        case 14:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": time for relay 4 invalid");
            break;
        case 15:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": function opto 1 invalid");
            break;
        case 16:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": function opto 2 invalid");
            break;
        case 17:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": function opto 3 invalid");
            break;
        case 18:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": function opto 4 invalid");
            break;
        case 19:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": enter/exit information invalid");
            break;
        case 20:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": autonomous unlocking invalid");
            break;
        case 21:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": locking and unlocking invalid");
            break;
        case 22:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": hold-up PIN Code invalid");
            break;
        case 23:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": 2-persons-access control invalid");
            break;
        case 24:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": repeated access invalid");
            break;
        case 25:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": time booking control invalid");
            break;
        case 26:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": anti-pass active invalid");
            break;
        case 27:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay expander control invalid");
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::settingActualizing()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    result = engine.evaluate("settingActualizing");
    msg->append( result.call().toString() );

    appendMessage(msg);
}

void CGantnerAccessTerminal::checkSettingActualizing(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 95:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": error bei file treatment");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::setSubscriberData()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    result = engine.evaluate("setSubscriberData");
    msg->append( result.call().toString() );

    msg->append(QString::number(subscriberNumber).rightJustified(4,'0').toLatin1() );    //
    msg->append(QString::number(plantNumber).rightJustified(2,'0').toLatin1() );    //
    msg->append(QString::number(mainCompIdCard).rightJustified(4,'0').toLatin1() );    //
    msg->append("0");    //
    msg->append("000");    //
    msg->append("000");    //
    msg->append("000");    //
    msg->append(QString::number(bookingCodeSumWinSwitchOver).rightJustified(3,'0').toLatin1() );    //
    msg->append("000");    //
    msg->append("000");    //
    msg->append("000");    //

    //For Central Europe, the time for automatic switch-over is the last Sunday in March at 2.00 am .
    QDate dateSum(QDate::currentDate().year(),3, 31);

    while(dateSum.dayOfWeek() != Qt::Sunday)
        dateSum = dateSum.addDays(-1);

    msg->append( dateSum.toString("yyMMdd") + "0200" );    //

    //For Central Europe, the time for automatic switch-over is the last Sunday in October at 3.00 am.
    QDate dateWin(QDate::currentDate().year(),10, 31);

    while(dateWin.dayOfWeek() != Qt::Sunday)
        dateWin = dateWin.addDays(-1);

    msg->append( dateWin.toString("yyMMdd") + "0300" );    //

    msg->append(QString::number(switchOverLeap).rightJustified(1,'0').toLatin1() );    //
    msg->append(QString::number(waitingTimeInput).rightJustified(2,'0').toLatin1() );    //
    msg->append(QString::number(monitoringTime).rightJustified(2,'0').toLatin1() );    //
    msg->append(QString::number(monitorinChangingTime).rightJustified(2,'0').toLatin1() );    //
    msg->append("0");    //
    msg->append(QString::number(cardReaderType).rightJustified(1,'0').toLatin1() );    //
    msg->append("0" );    //
    msg->append("0");    //
    msg->append("00");    //
    msg->append("000");    //
    msg->append("0");    //
    msg->append("0");    //
    msg->append("0");    //
    msg->append("00");    // expirationDate
    msg->append("000000");    //
    appendMessage(msg);

}

void CGantnerAccessTerminal::checkSubscriberData(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 11:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day change at summertime switch-over.");
            break;
        case 12:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day change at wintertime switch-over.");
            break;
        case 13:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": switch-over leap not numerical");
            break;
        case 14:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": waiting time input not numerical");
            break;
        case 15:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": monitoring time not numerical");
            break;
        case 16:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": monitoring changing time not numerical");
            break;
        case 18:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": card reader type not valid");
            break;
        case 24:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": subscriber number not numerical");
            break;
        case 25:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": plant number not numerical");
            break;
        case 26:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": permanent lighting not logical");
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::setEntryExitReader()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    QScriptValueList args;

    args.clear();
    args << QScriptValue(&engine,readerSerialNumber);
    args << QScriptValue(&engine,readerArticleNumber);

    if(readerEntryExit)
    {
        args << QScriptValue(&engine,"0");
    }
    else
    {
        args << QScriptValue(&engine,"1");
    }

    result = engine.evaluate("setEntryExitReader");

    msg->append( result.call(QScriptValue(), args).toString() );


    appendMessage(msg);
}

void CGantnerAccessTerminal::setFIUReader()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    QScriptValueList args;

    args.clear();
    args << QScriptValue(&engine,readerSerialNumber);
    args << QScriptValue(&engine,readerArticleNumber);

    if(readerFiu>0)
    {
        args << QScriptValue(&engine,"01");
    }
    else
    {
        args << QScriptValue(&engine,"00");
    }

    result = engine.evaluate("setFIUReader");
    msg->append( result.call(QScriptValue(), args).toString() );


    appendMessage(msg);
}

void CGantnerAccessTerminal::setTimeoutReader()
{
    QByteArray *msg = new QByteArray;

    QScriptValue result;

    QScriptValueList args;

    args.clear();
    args << QScriptValue(&engine,readerSerialNumber);
    args << QScriptValue(&engine,readerArticleNumber);
    args << QScriptValue(&engine,QString::number(readerTimeout).rightJustified(2,'0'));

    result = engine.evaluate("setTimeoutReader");
    msg->append( result.call(QScriptValue(), args).toString() );

    appendMessage(msg);
}

void CGantnerAccessTerminal::setReaderConfiguration()
{
    QScriptValue result;
    QScriptValueList args;

    //GANTNER Zeit-/Zutritt-Segment
    QByteArray *msg = new QByteArray;
    args.clear();
    args << QScriptValue(&engine,readerSerialNumber);
    args << QScriptValue(&engine,readerArticleNumber);
    args << QScriptValue(&engine,QString::number(2).rightJustified(2,'0'));
    args << QScriptValue(&engine,"GantnerAccess");

    result = engine.evaluate("setReaderConfiguration");
    msg->append( result.call(QScriptValue(), args).toString() );

    appendMessage(msg);

    // UID
    QByteArray *msg2 = new QByteArray;
    args.clear();
    args << QScriptValue(&engine,readerSerialNumber);
    args << QScriptValue(&engine,readerArticleNumber);
    args << QScriptValue(&engine,QString::number(3).rightJustified(2,'0'));
    args << QScriptValue(&engine,"MFUL");

    result = engine.evaluate("setReaderConfiguration");
    msg2->append( result.call(QScriptValue(), args).toString() );

    appendMessage(msg2);


    for(int i=4; i<=10; i++)
    {
        QByteArray *msg = new QByteArray;
        args.clear();
        args << QScriptValue(&engine,readerSerialNumber);
        args << QScriptValue(&engine,readerArticleNumber);
        args << QScriptValue(&engine,QString::number(i,16).rightJustified(2,'0'));
        args << QScriptValue(&engine,"NONE");

        result = engine.evaluate("setReaderConfiguration");
        msg->append( result.call(QScriptValue(), args).toString() );

        appendMessage(msg);

    }
}

void CGantnerAccessTerminal::setOptionalCompanyID()
{
    QScriptValue result;
    QScriptValueList args;
    QByteArray *msg = new QByteArray;

    args.clear();
    args << QScriptValue(&engine,QString::number(optionalCompanyID1).rightJustified(4,'0'));
    args << QScriptValue(&engine,QString::number(optionalCompanyID2).rightJustified(4,'0'));
    args << QScriptValue(&engine,QString::number(optionalCompanyID3).rightJustified(4,'0'));
    args << QScriptValue(&engine,QString::number(optionalCompanyID4).rightJustified(4,'0'));
    args << QScriptValue(&engine,QString::number(optionalCompanyID5).rightJustified(4,'0'));
    args << QScriptValue(&engine,QString::number(optionalCompanyID6).rightJustified(4,'0'));
    args << QScriptValue(&engine,QString::number(optionalCompanyID7).rightJustified(4,'0'));
    args << QScriptValue(&engine,QString::number(optionalCompanyID8).rightJustified(4,'0'));
    args << QScriptValue(&engine,QString::number(optionalCompanyID9).rightJustified(4,'0'));
    args << QScriptValue(&engine,QString::number(optionalCompanyID10).rightJustified(4,'0'));

    result = engine.evaluate("setOptionalCompanyID");
    msg->append( result.call(QScriptValue(), args).toString() );

    appendMessage(msg);
}

void CGantnerAccessTerminal::checkOptionalCompanyID(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": invalid character in optional company ID 1");
            break;
        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": invalid character in optional company ID 2");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": invalid character in optional company ID 3");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": invalid character in optional company ID 4");
            break;
        case 5:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": invalid character in optional company ID 5");
            break;
        case 6:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": invalid character in optional company ID 6");
            break;
        case 7:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": invalid character in optional company ID 7");
            break;
        case 8:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": invalid character in optional company ID 8");
            break;
        case 9:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": invalid character in optional company ID 9");
            break;
        case 10:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": invalid character in optional company ID 10");
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}


void CGantnerAccessTerminal::setOptionalCard()
{
    QScriptValue result;
    QScriptValueList args;
    QByteArray *msg = new QByteArray;

    args.clear();
    args << QScriptValue(&engine,QString::number(optionalCardStructur).rightJustified(2,'0'));
    args << QScriptValue(&engine,QString::number(optionalGantnerNationalCode).rightJustified(2,'0'));

    args << QScriptValue(&engine,optionalGantnerCustomerCode1.rightJustified(2,' '));
    args << QScriptValue(&engine,optionalGantnerCustomerCode2.rightJustified(2,' '));
    args << QScriptValue(&engine,optionalGantnerCustomerCode3.rightJustified(2,' '));
    args << QScriptValue(&engine,optionalGantnerCustomerCode4.rightJustified(2,' '));
    args << QScriptValue(&engine,optionalGantnerCustomerCode5.rightJustified(2,' '));

    if(optionalReaderInitialisation.length() <= 40 )
        args << QScriptValue(&engine,optionalReaderInitialisation.leftJustified(40,' '));

    if(optionalReaderInitialisation.length() > 40 )
        args << QScriptValue(&engine,optionalReaderInitialisation.leftJustified(100,' '));

    if(optionalTableCardType.length() > 0 )
        args << QScriptValue(&engine,optionalTableCardType.leftJustified(60,' '));


    result = engine.evaluate("setOptionalCard");
    msg->append( result.call(QScriptValue(), args).toString() );

    appendMessage(msg);
}

void CGantnerAccessTerminal::checkOptionalCard(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": Card structur invalid");
            break;
        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": Gantner national code invalid");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": Gantner customer code 1 invalid");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": Gantner customer code 2 invalid");
            break;
        case 5:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": Gantner customer code 3 invalid");
            break;
        case 6:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": Gantner customer code 4 invalid");
            break;
        case 7:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": Gantner customer code 5 invalid");
            break;
        case 8:
        case 9:
        case 10:
        case 11:
        case 12:
        case 13:
        case 14:
        case 15:
        case 16:
        case 17:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": Initialisation of the reader structur invalid (struct 5->10)");
            break;
        case 18:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": Initialisation of the reader structur invalid (struct 99)");
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::s_setDayPlan(QObject *p, QMap<QString, QVariant> params)
{
    CGantnerAccessTerminal *pThis = qobject_cast<CGantnerAccessTerminal *>(p);

    QScriptValue result;
    QByteArray *msg = new QByteArray;

    result = pThis->engine.evaluate("setDayPlan");
    msg->append( result.call().toString() );

    msg->append( params["dayPlanNumber"].toString().rightJustified(2,'0').toLatin1() );

    msg->append(params["fromTime1"].toString().toLatin1());
    msg->append(params["toTime1"].toString().toLatin1());
    msg->append(params["flags1"].toString().toLatin1());
    msg->append(params["fromTime2"].toString().toLatin1());
    msg->append(params["toTime2"].toString().toLatin1());
    msg->append(params["flags2"].toString().toLatin1());
    msg->append(params["fromTime3"].toString().toLatin1());
    msg->append(params["toTime3"].toString().toLatin1());
    msg->append(params["flags3"].toString().toLatin1());
    msg->append(params["fromTime4"].toString().toLatin1());
    msg->append(params["toTime4"].toString().toLatin1());
    msg->append(params["flags4"].toString().toLatin1());
    msg->append(params["fromTime5"].toString().toLatin1());
    msg->append(params["toTime5"].toString().toLatin1());
    msg->append(params["flags5"].toString().toLatin1());
    msg->append(params["fromTime6"].toString().toLatin1());
    msg->append(params["toTime6"].toString().toLatin1());
    msg->append(params["flags6"].toString().toLatin1());
    msg->append(params["fromTime7"].toString().toLatin1());
    msg->append(params["toTime7"].toString().toLatin1());
    msg->append(params["flags7"].toString().toLatin1());
    msg->append(params["fromTime8"].toString().toLatin1());
    msg->append(params["toTime8"].toString().toLatin1());
    msg->append(params["flags8"].toString().toLatin1());
    msg->append(params["fromTime9"].toString().toLatin1());
    msg->append(params["toTime9"].toString().toLatin1());
    msg->append(params["flags9"].toString().toLatin1());
    msg->append(params["fromTime10"].toString().toLatin1());
    msg->append(params["toTime10"].toString().toLatin1());
    msg->append(params["flags10"].toString().toLatin1());

    pThis->appendMessage( msg );
}

void CGantnerAccessTerminal::checkDayPlan(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":Day plan # invalid");
            break;

        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":from time 1 invalid");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 1 invalid");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 1 smaller than from time 1");
            break;
        case 5:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":flag 1 invalid");
            break;

        case 6:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":from time 2 invalid");
            break;
        case 7:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 2 invalid");
            break;
        case 8:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 2 smaller than from time 2");
            break;
        case 9:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":flag 2 invalid");
            break;

        case 10:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":from time 3 invalid");
            break;
        case 11:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 3 invalid");
            break;
        case 12:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 3 smaller than from time 3");
            break;
        case 13:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":flag 3 invalid");
            break;

        case 14:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":from time 4 invalid");
            break;
        case 15:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 4 invalid");
            break;
        case 16:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 4 smaller than from time 4");
            break;
        case 17:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":flag 4 invalid");
            break;

        case 18:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":from time 5 invalid");
            break;
        case 19:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 5 invalid");
            break;
        case 20:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 5 smaller than from time 5");
            break;
        case 21:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":flag 5 invalid");
            break;

        case 22:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":from time 6 invalid");
            break;
        case 23:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 6 invalid");
            break;
        case 24:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 6 smaller than from time 6");
            break;
        case 25:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":flag 6 invalid");
            break;

        case 26:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":from time 7 invalid");
            break;
        case 27:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 7 invalid");
            break;
        case 28:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 7 smaller than from time 7");
            break;
        case 29:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":flag 7 invalid");
            break;

        case 30:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":from time 8 invalid");
            break;
        case 31:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 8 invalid");
            break;
        case 32:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 8 smaller than from time 8");
            break;
        case 33:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":flag 8 invalid");
            break;

        case 34:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":from time 9 invalid");
            break;
        case 35:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 9 invalid");
            break;
        case 36:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 9 smaller than from time 9");
            break;
        case 37:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":flag 9 invalid");
            break;

        case 38:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":from time 10 invalid");
            break;
        case 39:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 10 invalid");
            break;
        case 40:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":to time 10 smaller than from time 10");
            break;
        case 41:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":flag 10 invalid");
            break;

        case 42:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":time windows are overlapping");
            break;

        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ":checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::s_setSchedule(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerAccessTerminal *pThis = qobject_cast<CGantnerAccessTerminal *>(p);

    QScriptValue result;
    QByteArray *msg = new QByteArray;

    result = pThis->engine.evaluate("setSchedule");
    msg->append( result.call().toString() );

    msg->append( params["scheduleNumber"].toString().rightJustified(2,'0').toLatin1() );

    msg->append(params["monday"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["tuesday"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["wednesday"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["thursday"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["friday"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["saturday"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["sunday"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["special1"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["special2"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["special3"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["special4"].toString().rightJustified(2,'0').toLatin1());
    msg->append(params["special5"].toString().rightJustified(2,'0').toLatin1());

    pThis->appendMessage( msg );

}

void CGantnerAccessTerminal::checkSchedule(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": Schedule # invalid");
            break;

        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for monday invalid");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for tuesday invalid");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for wednesday invalid");
            break;
        case 5:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for thurday invalid");
            break;

        case 6:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for friday invalid");
            break;
        case 7:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for saturday invalid");
            break;
        case 8:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for sunday invalid");
            break;
        case 9:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for special day 1 invalid");
            break;

        case 10:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for special day 2 invalid");
            break;
        case 11:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for special day 3 invalid");
            break;
        case 12:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for special day 4 invalid");
            break;
        case 13:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": schedule for special day 5 invalid");
            break;

        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::s_setSpecialDay(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerAccessTerminal *pThis = qobject_cast<CGantnerAccessTerminal *>(p);

    QScriptValue result;
    QByteArray *msg = new QByteArray;

    result = pThis->engine.evaluate("setSpecialDay");
    msg->append( result.call().toString() );

    msg->append( params["month"].toString().rightJustified(2,'0').toLatin1() );
    msg->append( params["specialDay"].toString().rightJustified(31,'0').toLatin1() );

    pThis->appendMessage( msg );

}

void CGantnerAccessTerminal::checkSpecialDay(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": month invalid");
            break;
        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 1 invalid");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 2 invalid");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 3 invalid");
            break;
        case 5:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 4 invalid");
            break;
        case 6:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 5 invalid");
            break;
        case 7:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 6 invalid");
            break;
        case 8:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 7 invalid");
            break;
        case 9:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 8 invalid");
            break;
        case 10:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 9 invalid");
            break;
        case 11:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 10 invalid");
            break;
        case 12:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 11 invalid");
            break;
        case 13:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 12 invalid");
            break;
        case 14:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 13 invalid");
            break;
        case 15:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 14 invalid");
            break;
        case 16:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 15 invalid");
            break;
        case 17:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 16 invalid");
            break;
        case 18:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 17 invalid");
            break;
        case 19:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 18 invalid");
            break;
        case 20:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 19 invalid");
            break;
        case 21:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 20 invalid");
            break;
        case 22:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 21 invalid");
            break;
        case 23:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 22 invalid");
            break;
        case 24:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 23 invalid");
            break;
        case 25:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 24 invalid");
            break;
        case 26:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 25 invalid");
            break;
        case 27:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 26 invalid");
            break;
        case 28:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 27 invalid");
            break;
        case 29:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 28 invalid");
            break;
        case 30:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 29 invalid");
            break;
        case 31:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 30 invalid");
            break;
        case 32:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": day 31 invalid");
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::s_setRelayPlan(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerAccessTerminal *pThis = qobject_cast<CGantnerAccessTerminal *>(p);

    QScriptValue result;
    QByteArray *msg = new QByteArray;

    result = pThis->engine.evaluate("setRelayPlan");
    msg->append( result.call().toString() );


    msg->append( params["relayPlanNumber"].toString().rightJustified(2,'0').toLatin1() );
    msg->append( params["relays"].toString().rightJustified(32,'0').toLatin1() );

    pThis->appendMessage( msg );

}

void CGantnerAccessTerminal::checkRelayPlan(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay plan number invalid");
            break;
        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 1 invalid");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 2 invalid");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 3 invalid");
            break;
        case 5:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 4 invalid");
            break;
        case 6:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 5 invalid");
            break;
        case 7:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 6 invalid");
            break;
        case 8:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 7 invalid");
            break;
        case 9:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 8 invalid");
            break;
        case 10:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 9 invalid");
            break;
        case 11:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 10 invalid");
            break;
        case 12:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 11 invalid");
            break;
        case 13:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 12 invalid");
            break;
        case 14:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 13 invalid");
            break;
        case 15:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 14 invalid");
            break;
        case 16:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 15 invalid");
            break;
        case 17:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 16 invalid");
            break;
        case 18:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 17 invalid");
            break;
        case 19:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 18 invalid");
            break;
        case 20:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 19 invalid");
            break;
        case 21:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 20 invalid");
            break;
        case 22:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 21 invalid");
            break;
        case 23:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 22 invalid");
            break;
        case 24:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 23 invalid");
            break;
        case 25:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 24 invalid");
            break;
        case 26:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 25 invalid");
            break;
        case 27:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 26 invalid");
            break;
        case 28:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 27 invalid");
            break;
        case 29:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 28 invalid");
            break;
        case 30:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 29 invalid");
            break;
        case 31:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 30 invalid");
            break;
        case 32:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay 31 invalid");
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::s_SettingActualizing(QObject *p, QMap<QString, QVariant>)
{
    CGantnerAccessTerminal *pThis = qobject_cast<CGantnerAccessTerminal *>(p);

    pThis->settingActualizing();
}

void CGantnerAccessTerminal::s_unloadPersonnel(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerAccessTerminal *pThis = qobject_cast<CGantnerAccessTerminal *>(p);

    QScriptValue result;
    QByteArray *msg = new QByteArray;
    QScriptValueList args;

    args.clear();
    args << QScriptValue(&(pThis->engine),params["cardNumber"].toString().rightJustified(11,'0'));

    result = pThis->engine.evaluate("unloadPersonnel");
    msg->append( result.call(QScriptValue(), args).toString() );

    pThis->appendMessage( msg );
}

void CGantnerAccessTerminal::checkUnloadPesonnel(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": card number invalid");
            break;
        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": person not in the terminal");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": no 'C' on position 17");
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 95:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": error at file treatment");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017", QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::s_loadPersonnel(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerAccessTerminal *pThis = qobject_cast<CGantnerAccessTerminal *>(p);

    QScriptValue result;
    QByteArray *msg = new QByteArray;
    QScriptValueList args;

    args.clear();
    args << QScriptValue(&(pThis->engine),params["personnelNumber"].toString().rightJustified(8,'0'));
    args << QScriptValue(&(pThis->engine),params["cardNumber"].toString().rightJustified(11,'0'));
    args << QScriptValue(&(pThis->engine),params["cardVersion"].toString().rightJustified(2,'0'));
    args << QScriptValue(&(pThis->engine),params["fullname"].toString().leftJustified(16,' '));
    args << QScriptValue(&(pThis->engine),params["masterAuhtorization"].toString().rightJustified(1,'0'));
    args << QScriptValue(&(pThis->engine),params["PINCode"].toString().rightJustified(8,' '));
    args << QScriptValue(&(pThis->engine),params["scheduleNumber"].toString().rightJustified(2,'0'));
    args << QScriptValue(&(pThis->engine),params["regularRelayPlanNumber"].toString().rightJustified(2,'0'));
    args << QScriptValue(&(pThis->engine),params["specialRelayPlanNumber"].toString().rightJustified(2,'0'));
    args << QScriptValue(&(pThis->engine),params["validityDate"].toString().rightJustified(10,'0'));
    args << QScriptValue(&(pThis->engine),params["validityDateOption"].toString().rightJustified(1,'0'));

    result = pThis->engine.evaluate("loadPersonnel");
    msg->append( result.call(QScriptValue(), args).toString() );

    pThis->appendMessage( msg );

}

void CGantnerAccessTerminal::checkLoadPesonnel(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": personnel number invalid");
            break;
        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": card number invalid");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": card version invalid");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": master authorization invalid");
            break;
        case 5:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": PIN-Code invalid");
            break;
        case 6:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": time program number invalid");
            break;
        case 7:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": regular relay plan invalid");
            break;
        case 8:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": special relay plan invalid");
            break;
        case 9:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": date of validity invalid");
            break;
        case 10:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": option for validity date invalid");
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 94:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": person cannot be stored");
            break;
        case 95:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": memory is not formatted yet");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017", QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);
}

void CGantnerAccessTerminal::s_openByCommand(QObject *p, QMap<QString, QVariant>params)
{
    CGantnerAccessTerminal *pThis = qobject_cast<CGantnerAccessTerminal *>(p);

    QScriptValue result;
    QByteArray *msg = new QByteArray;
    QScriptValueList args;

    args.clear();
    args << QScriptValue(&(pThis->engine),params["controlCode"].toString() );

    result = pThis->engine.evaluate("openByCommand");
    msg->append( result.call(QScriptValue(), args).toString() );

    pThis->appendMessage( msg );
}

void CGantnerAccessTerminal::checkOpenByCommand(QByteArray message)
{
    int resp = message.mid(6, 2).toInt();

    QString alarmXml;

    switch(resp)
    {
        case 0:     // valid
            break;
        case 1:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": control code invalid");
            break;
        case 2:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": function not released in the configuration record");
            break;
        case 3:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": no relay defined");
            break;
        case 4:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": relay actuation blocked via optocoupler");
            break;
        case 92:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": password active");
            break;
        case 93:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": parity error");
            break;
        case 97:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017",QString(__FUNCTION__) + ": set lenght invalid");
            break;
        case 98:
            alarmXml = CXmlFactory::deviceEvent(QString::number(id), "1017", QString(__FUNCTION__) + ": checksum error");
            break;
    }

    if(alarmXml != "") emit deviceEvent(alarmXml);

}

void CGantnerAccessTerminal::checkAccessStatus(QByteArray message)
{
    if(message.length() == 27)
    {
        //int alarm = message.mid(6,1).toInt();
        //int autoMessage = message.mid(7,1).toInt();
        int nonStored1 = message.mid(8,1).toInt();
        int nonStored2 = message.mid(9,1).toInt();
        unsigned long long cardNumber = message.mid(10,11).toLongLong();
        //int cardVersion = message.mid(21,2).toInt();
        int entering = message.mid(23,1).toInt();

        if(nonStored2 & 0x01)
        {
            if(lastCardNumber != cardNumber)
            {
                qDebug() << "Unlock";
                lastCardNumber = cardNumber;

                QMap<QString, QString> params;
                params["date"] = QDate::currentDate().toString(Qt::ISODate);
                params["time"] = QTime::currentTime().toString("hh:mm:ss");
                params["userId"] = "0";
                params["key"] = QString::number(cardNumber);
                params["code"] = "1";
                params["entering"] = QString::number(entering);
                params["deviceId"] = QString::number(id);

                QString xml = CXmlFactory::deviceEvent(QString::number(id), "Gantner_AccessTerminal_accessDetectedBeforeBooking", params);
                emit deviceEvent(xml);
            }
        }
        else
        {
            lastCardNumber = 0;
        }

        if(nonStored1 & 0x01)
        {
            pollingTerminalInformation();
        }
    }

}

Q_EXPORT_PLUGIN2(gantneraccessterminal, CGantnerAccessTerminal);
