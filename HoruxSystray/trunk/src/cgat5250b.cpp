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

#endif
}

CGAT5250B::~CGAT5250B()
{
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
    gat->dynamicCall("Beep(int)",100);
    gat->dynamicCall("LEDGreen(int)",1000);
    emit keyDetected(key.toLatin1());
}

void CGAT5250B::run()
{

 #if defined(Q_OS_WIN)
    stop = false;
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

#endif
}

void CGAT5250B::close(bool isError)
{
 #if defined(Q_OS_WIN)
       stop = true;
#elif defined(Q_WS_X11)

#endif

}
