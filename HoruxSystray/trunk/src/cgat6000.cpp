#include "cgat6000.h"
#include <QSettings>
#include <QtCore>

CGAT6000::CGAT6000(QObject *parent)
 : CDevice(parent)
{
 #if defined(Q_OS_WIN)
    gat = new QAxObject(this);

    gat->setControl("0A530613-6024-11D5-A3AC-0050BF2CF639");

#elif defined(Q_WS_X11)

    readPort = new QTimer(this);
    connect(readPort, SIGNAL(timeout()), this, SLOT(readyRead()));

    isStarted = false;
#endif

    beep = false;
}

CGAT6000::~CGAT6000()
{
    stop = true;


#if defined(Q_OS_WIN)
    while(isRunning())
    {
        QCoreApplication::processEvents ();
    }

    if(gat)
    {
        delete gat;
        gat = NULL;
    }
#elif defined(Q_WS_X11)
    if(port)
    {
        delete port;
        port = NULL;
    }
#endif
}

void CGAT6000::isBeep(bool flag)
{
    beep = flag;
}

void CGAT6000::open()
{
#if defined(Q_OS_WIN)



    if(gat->dynamicCall("OpenDevice()").toBool())
    {
        gat->dynamicCall("Beep(int)",100);
        gat->dynamicCall("LEDGreen(int)",1000);
        gat->dynamicCall("LEDRed(int)",1000);
    }
    else
        qDebug() << "Not ready";

#elif defined(Q_WS_X11)
    QSettings settings ( "Horux", "HoruxGuiSys" );
    QString portStr = settings.value("port", "").toString();


    port = new QextSerialPort(portStr);
    port->setBaudRate(BAUD38400 );
    port->setFlowControl(FLOW_OFF);
    port->setParity(PAR_NONE);
    port->setDataBits(DATA_8);
    port->setStopBits(STOP_1);
    port->setTimeout(0,16);

    if(!port->open(QIODevice::ReadWrite))
    {
        delete port;
        port = NULL;
        emit deviceError();
        return;
    }
    else
    {
        port->setDtr(false);
        port->setRts(true);

        smIdle();

        port->write(msgList.at(0));
        port->flush();

    }

    readPort->start(100);
#endif
}

void CGAT6000::smIdle()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    unsigned char TxSmIdle[3] = {0x02, 0x04, 0x06};
    QByteArray TxBuffer;

    for(int i=0;i<3; i++)
        TxBuffer.append(TxSmIdle[i]);

    msgList.append(TxBuffer);
#endif
}

void CGAT6000::setGreenLED()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port && port->isOpen())
    {
        unsigned char TxGreen[6] = {0x05,0xA5,0x01,0x03,0xE8,0x4A};
        QByteArray TxBuffer;

        for(int i=0;i<6; i++)
            TxBuffer.append(TxGreen[i]);

        msgList.append(TxBuffer);
    }
#endif
}

void CGAT6000::setRedLED()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port && port->isOpen())
    {
        unsigned char TxRed[6] = {0x05,0xA5,0x02,0x03,0xE8,0x49};
        QByteArray TxBuffer;

        for(int i=0;i<6; i++)
            TxBuffer.append(TxRed[i]);

        msgList.append(TxBuffer);
    }
#endif
}

void CGAT6000::setBeep()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port && port->isOpen())
    {
        unsigned char TxBeep[5] = {0x04,0xA6,0x00,0x64,0xC6};
        QByteArray TxBuffer;

        for(int i=0;i<5; i++)
            TxBuffer.append(TxBeep[i]);

        msgList.append(TxBuffer);
    }
#endif
}

void CGAT6000::readUniqueSerialNumber()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port && port->isOpen())
    {

        unsigned char TxSN[3] = {0x02, 0x04, 0x06};
        QByteArray TxBuffer;

        for(int i=0;i<3; i++)
            TxBuffer.append(TxSN[i]);

        msgList.append(TxBuffer);
    }
#endif
}

void CGAT6000::readCardNumberNumber()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port && port->isOpen())
    {
        unsigned char TxSN[12] = {0x0b,0x80,0x00,0x12,0x0a,0x01,0x00,0x1C,0x05,0x01,0x1A, 0x90 };
        QByteArray TxBuffer;

        for(int i=0;i<12; i++)
            TxBuffer.append(TxSN[i]);

        msgList.append(TxBuffer);
    }
#endif
}

void CGAT6000::setFID(QString _fid)
{
    fid = _fid;
    #if defined(Q_OS_WIN)
        if(gat)
        {
            gat->setProperty("CryptKey","31AEE34AFEF8D4F6F1821C487ACB8DC9");
            gat->setProperty("FID",fid.toInt());
        }
    #elif defined(Q_WS_X11)
        if(port)
        {

        }
    #endif

}

void CGAT6000::handleMsg()
{
#if defined(Q_OS_WIN)
    if(key != "")
    {
       handleKey();
    }
#elif defined(Q_WS_X11)

    readPort->stop();

    QString s, s1;

    for(int i=0; i<msg.size(); i++)
        s += s1.sprintf("%02X ",(unsigned char) msg.at(i));

    int len = (unsigned char)msg.at(0);

    if(msg.size()-1 >= len)
    {

        unsigned int cmd = (unsigned char)msg.at(1);

        switch(cmd)
        {
           case 0xa5:
           case 0xa6:
                qDebug("Set LED/Beep ok");
                break;
           case 0x04:   //unique serial number
               {

                   if(len == 0x0A)
                   {
                       bool ok;
                       QString s1;
                       QString s = s1.sprintf("%02X%02X%02X%02X", (unsigned char)msg.at(3), (unsigned char)msg.at(4), (unsigned char)msg.at(5), (unsigned char)msg.at(6));

                       unsigned long long sn = s1.toLongLong(&ok,16);

                       if(key != QString::number(sn))
                       {
                            key = QString::number(sn);

                            handleKey();
                       }
                   }
                   else
                      key = "";
                }
                break;
           default:
                break;
        }

        msg.remove(0, len+1);

        if(msg.size() > 0)
            handleMsg();
    }

    readPort->start(100);
#endif
}

void CGAT6000::handleKey()
{
#if defined(Q_OS_WIN)
    if(beep)
        gat->dynamicCall("Beep(int)",100);
    gat->dynamicCall("LEDGreen(int)",1000);
#elif defined(Q_WS_X11)
    setGreenLED();
    if(beep)
        setBeep();
#endif
    emit keyDetected(key.toLatin1());
}

void CGAT6000::readyRead()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    int ret = port->bytesAvailable();

    if(!isStarted)
    {
        if(ret < 0)
        {
            if(msgList.size()>0)
                msgList.removeFirst();

            readPort->stop();
            port->close();
            delete port;
            port = NULL;

            open();
        }
        else
        {
            if(msgList.size()>0)
                msgList.removeFirst();
            port->readAll();
            isStarted = true;
            setBeep();
            setGreenLED();
            setRedLED();

            port->write(msgList.at(0));
            port->flush();


        }
    }
    else
    {

        if( ret > 0)
        {
            if(msgList.size()>0)
                msgList.removeFirst();

            msg += port->readAll();

            handleMsg();

            if(msgList.size() > 0)
            {
                port->write(msgList.at(0));
                port->flush();
            }

            return;
        }
        else
        {
            if(msgList.size() > 0)
            {
                port->write(msgList.at(0));
                port->flush();
            }
            else
                readUniqueSerialNumber();


        }
    }

#endif
}



void CGAT6000::run()
{
   stop = false;
#if defined(Q_OS_WIN)
   QString keyTmp = "";
    bool ok;
    while(!stop)
    {
        QString t =  gat->dynamicCall("GetUniqueNumber()").toString();

        if(t!= keyTmp)
        {
            unsigned long long sn = t.toLongLong();
            key = QString::number(sn,16);
            key = key.rightJustified(14, '0');
            key = key.left(8);
            key = QString::number(key.toLong(&ok,16));
            keyTmp = t;
            if(t!="")
                handleMsg();
        }

        QThread::msleep(100);
    }
#elif defined(Q_WS_X11)

#endif
}

void CGAT6000::close(bool )
{
    stop = true;
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port)
        port->close();
#endif

}

