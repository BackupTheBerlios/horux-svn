#include "cgat5250b.h"
#include <QSettings>
#include <QtCore>

CGAT5250B::CGAT5250B(QObject *parent)
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

CGAT5250B::~CGAT5250B()
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

void CGAT5250B::isBeep(bool flag)
{
    beep = flag;
}

void CGAT5250B::open()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    QSettings settings ( "Horux", "HoruxGuiSys" );
    QString portStr = settings.value("port", "").toString();


    port = new QextSerialPort(portStr);
    port->setBaudRate(BAUD9600 );
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

void CGAT5250B::smIdle()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    unsigned char TxSmIdle[4] = {0x03, 0x14, 0x01, 0x16};
    QByteArray TxBuffer;

    for(int i=0;i<4; i++)
        TxBuffer.append(TxSmIdle[i]);

    msgList.append(TxBuffer);
#endif
}

void CGAT5250B::setGreenLED()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port && port->isOpen())
    {
        unsigned char TxGreen[7] = {0x06,0x74,0x00,0x00,0x00,0x8a,0xf8};
        QByteArray TxBuffer;

        for(int i=0;i<7; i++)
            TxBuffer.append(TxGreen[i]);

        msgList.append(TxBuffer);
    }
#endif
}

void CGAT5250B::setRedLED()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port && port->isOpen())
    {
        unsigned char TxRed[7] = {0x06,0x74,0x00,0x00,0x8a,0x00,0xf8};
        QByteArray TxBuffer;

        for(int i=0;i<7; i++)
            TxBuffer.append(TxRed[i]);

        msgList.append(TxBuffer);
    }
#endif
}

void CGAT5250B::setBeep()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port && port->isOpen())
    {
        unsigned char TxBeep[7] = {0x06,0x74,0x00,0x81,0x00,0x00,0xf3};
        QByteArray TxBuffer;

        for(int i=0;i<7; i++)
            TxBuffer.append(TxBeep[i]);

        msgList.append(TxBuffer);
    }
#endif
}

void CGAT5250B::readUniqueSerialNumber()
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port && port->isOpen())
    {
        unsigned char TxSN[11] = {0x0A,0x80,0x00,0x07,0x04,0x01,0x00,0x00,0x04,0x01,0x8D };
        QByteArray TxBuffer;

        for(int i=0;i<11; i++)
            TxBuffer.append(TxSN[i]);

        msgList.append(TxBuffer);
    }
#endif
}

void CGAT5250B::readCardNumberNumber()
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

void CGAT5250B::setFID(QString _fid)
{
    fid = _fid;
    #if defined(Q_OS_WIN)
        if(gat)
            gat->setProperty("FID",fid.toInt());
    #elif defined(Q_WS_X11)
        if(port)
        {

        }
    #endif

}

void CGAT5250B::handleMsg()
{
#if defined(Q_OS_WIN)
    if(key != "")
    {
       handleKey();
    }
#elif defined(Q_WS_X11)

    QString s, s1;

    for(int i=0; i<msg.size(); i++)
        s += s1.sprintf("%02X ",(unsigned char) msg.at(i));

    qDebug() << s;

    int len = (unsigned char)msg.at(0);

    if(msg.size()-1 >= len)
    {

        unsigned int cmd = (unsigned char)msg.at(1);

        switch(cmd)
        {
           case 0x74:
                qDebug("Set LED/Beep ok");
                break;
           case 0x80:   //unique serial number
               {

                   if(len == 0x14)
                   {
                       bool ok;
                       QString s1;
                       QString fidCard = s1.sprintf("%02X%02X", (unsigned char)msg.at(14), (unsigned char)msg.at(15));

                       if(fid == fidCard)
                       {
                           QString s = s1.sprintf("%02X%02X%02X", (unsigned char)msg.at(17), (unsigned char)msg.at(18), (unsigned char)msg.at(19));
                           unsigned long sn = s.toLong();
                           qDebug() << sn;
                           key = QString::number(sn);
                           qDebug()<< key;
                           handleKey();
                       }

                   }
                   else
                   {
                       bool ok;
                       QString s1;
                       QString s = s1.sprintf("%02X%02X%02X%02X", (unsigned char)msg.at(10), (unsigned char)msg.at(11), (unsigned char)msg.at(12), (unsigned char)msg.at(13));
                       qDebug() << s;

                       unsigned long sn = s.toLong(&ok, 16);
                       qDebug() << sn;
                       key = QString::number(sn);
                       qDebug()<< key;
                       handleKey();
                   }
                }
                break;
           default:
                break;
        }

        msg.remove(0, len+1);

        if(msg.size() > 0)
            handleMsg();
    }


#endif
}

void CGAT5250B::handleKey()
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

void CGAT5250B::readyRead()
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
                readCardNumberNumber();
                //readUniqueSerialNumber();


        }
    }

#endif
}



void CGAT5250B::run()
{
    stop = false;
#if defined(Q_OS_WIN)
    gat->dynamicCall("Beep(int)",100);
    gat->dynamicCall("LEDGreen(int)",1000);
    gat->dynamicCall("LEDRed(int)",1000);
    while(!stop)
    {
        QString t =  gat->dynamicCall("GetCardNumber()").toString();
        if(key != t)
        {
            key = t;
            handleMsg();
        }
        QThread::msleep(100);
    }
#elif defined(Q_WS_X11)


#endif
}

void CGAT5250B::close(bool )
{
    stop = true;
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    if(port)
        port->close();
#endif

}

