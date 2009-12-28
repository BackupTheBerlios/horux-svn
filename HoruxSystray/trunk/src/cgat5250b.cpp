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
    if( (ftdic = ftdi_new()) == NULL)
        qDebug("Cannot initialise the context");
    else
    {
        int vendorid = 0x0403;
        int product = 0xED6B;
        int ret;

        // Open the ftdi device
        if((ret = ftdi_usb_open(ftdic, vendorid, product)) < 0) {
            qDebug() << ret << "/" << ret, ftdi_get_error_string(ftdic);
        }
    }
#endif
}

CGAT5250B::~CGAT5250B()
{
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
        QString t = gat->dynamicCall("GetUniqueNumber()").toString();
        if(key != t)
        {
            key = t;
            handleMsg();
        }
        QThread::msleep(100);
    }
#elif defined(Q_WS_X11)
   unsigned char r[10] = {0,0,0,0,0,0,0,0,0,0};
             unsigned char m[] = { 0x03, 0x14, 0x01, 0x16};

            if(ftdi_write_data(ftdic,m,4) == 4)
            {
                qDebug() << "WRITE OK";
            }
            else
                qDebug() << "Write ERROR";
    while(!stop)
    {
       if(ftdi_read_data(ftdic,r,10)>0)
        {
            qDebug("%02X %02X %02X %02X ",r[0],r[1],r[2],r[3]);
        }
        QThread::msleep(100);
    }
#endif
}

void CGAT5250B::close(bool )
{
    stop = true;
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
    ftdi_usb_close(ftdic);
    ftdi_deinit(ftdic);
#endif

}
