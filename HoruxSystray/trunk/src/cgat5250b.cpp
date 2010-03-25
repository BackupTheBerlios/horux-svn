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

  QSettings settings ( "Horux", "HoruxGuiSys" );

  QString portStr = settings.value("port", "/dev/ttyUSB0").toString();

  port = new QextSerialPort(portStr);

  port->setBaudRate(BAUD38400 );
  //port->setFlowControl(FLOW_OFF);
  port->setParity(PAR_NONE);
  port->setDataBits(DATA_8);
  port->setStopBits(STOP_1);
 // port->setTimeout(0,1);
  if(!port->open(QIODevice::ReadWrite))
  {
    delete port;
    port = NULL;
    emit deviceError();
  }
  else
      qDebug() << "device opened";

#endif
}

CGAT5250B::~CGAT5250B()
{
    stop = true;

    while(isRunning())
    {
        QCoreApplication::processEvents ();
    }

#if defined(Q_OS_WIN)

    if(gat)
    {
        delete gat;
        gat = NULL;
    }
#elif defined(Q_WS_X11)

#endif
}

void CGAT5250B::setFID(QString _fid)
{
    fid = _fid;
    #if defined(Q_OS_WIN)
        if(gat)
            gat->setProperty("FID",fid.toInt());
    #elif defined(Q_WS_X11)

    #endif

}

void CGAT5250B::handleMsg()
{
    if(key != "")
    {
       handleKey();
    }
}

void CGAT5250B::handleKey()
{
#if defined(Q_OS_WIN)
    gat->dynamicCall("Beep(int)",100);
    gat->dynamicCall("LEDGreen(int)",1000);
#elif defined(Q_WS_X11)

#endif
    emit keyDetected(key.toLatin1());
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
        QString t = gat->dynamicCall("GetCardNumber()").toString();
        if(key != t)
        {
            key = t;
            handleMsg();
        }
        QThread::msleep(100);
    }
#elif defined(Q_WS_X11)

    while(true)
    {
           const char msg[] = {0x03, 0x14, 0x01, 0x16};

           port->write( msg , 4);
           port->flush();
           QCoreApplication::processEvents();

           QByteArray r = port->readAll();

           QString s, s1;
           for(int i=0; i<r.size(); i++)
               s += s.sprintf("%02X ", r.at(i));

           qDebug() << s;

           QThread::msleep(100);
       }
#endif
}

void CGAT5250B::close(bool )
{
    stop = true;
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    port->close();
#endif

}
