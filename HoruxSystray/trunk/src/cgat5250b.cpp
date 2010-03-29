#include "cgat5250b.h"
#include <QSettings>
#include <QtCore>

CGAT5250B::CGAT5250B(QObject *parent)
 : CDevice(parent)
{
/* #if defined(Q_OS_WIN)
    gat = new QAxObject(this);
    gat->setControl("0A530613-6024-11D5-A3AC-0050BF2CF639");
#elif defined(Q_WS_X11)*/


//#endif

 /*if( FT_SetVIDPID(0x403, 0xED6B ) == FT_OK )
     qDebug() << "FT_SetVIDPID ok";
 else
     qDebug() << "FT_SetVIDPID ko";*/

    FT_STATUS ftStatus;

    ftStatus = FT_Open(0,&ftHandle);
    if (ftStatus == FT_OK)
    {
        qDebug() << " FT_Open OK, use ftHandle to access device";

        ftStatus = FT_SetBaudRate(ftHandle, 9600); // Set baud rate to 115200
        if (ftStatus == FT_OK) {
          qDebug() << "FT_SetBaudRate OK";
        }
        else {
          qDebug() << "FT_SetBaudRate Failed";
        }

        ftStatus = FT_SetDataCharacteristics(ftHandle, FT_BITS_8, FT_STOP_BITS_1,
        FT_PARITY_NONE);
          if (ftStatus == FT_OK) {
            qDebug() << "FT_SetDataCharacteristics OK";
          }
          else {
            qDebug() << "FT_SetDataCharacteristics Failed";
          }

          ftStatus = FT_SetFlowControl(ftHandle, FT_FLOW_NONE,0, 0);
            if (ftStatus == FT_OK) {
              qDebug() << "FT_SetFlowControl OK";
            }
            else {
              qDebug() << " FT_SetFlowControl Failed";
            }

            ftStatus = FT_ClrDtr(ftHandle);
            if (ftStatus == FT_OK) {
              qDebug() << "FT_ClrDtr OK ";
            }
            else {
              qDebug() << "FT_ClrDtr failed";
            }

            ftStatus = FT_SetRts(ftHandle);
            if (ftStatus == FT_OK) {
              qDebug() << "FT_SetRts OK ";
            }
            else {
              qDebug() << "FT_SetRts failed";
            }


    }
    else {
      qDebug() << "FT_Open failed";
    }


}

CGAT5250B::~CGAT5250B()
{
    stop = true;

    while(isRunning())
    {
        QCoreApplication::processEvents ();
    }

/*#if defined(Q_OS_WIN)

    if(gat)
    {
        delete gat;
        gat = NULL;
    }
#elif defined(Q_WS_X11)

#endif*/
}

void CGAT5250B::setFID(QString _fid)
{
    fid = _fid;
    /*#if defined(Q_OS_WIN)
        if(gat)
            gat->setProperty("FID",fid.toInt());
    #elif defined(Q_WS_X11)

    #endif*/

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
/*#if defined(Q_OS_WIN)
    gat->dynamicCall("Beep(int)",100);
    gat->dynamicCall("LEDGreen(int)",1000);
#elif defined(Q_WS_X11)

#endif*/
    emit keyDetected(key.toLatin1());
}

void CGAT5250B::run()
{
    stop = false;
//0x03,0x30,00,0x33

    FT_STATUS ftStatus;

    DWORD BytesWritten;
    char TxBuffer[4] = {0x3,0x14, 0x1,0x16}; // Contains data to write to device

    DWORD BytesWritten2;
    char TxBuffer2[4] = {0x3,0x30, 0x00,0x33}; // Contains data to write to device
    ftStatus = FT_Write(ftHandle, TxBuffer2, sizeof(TxBuffer2), &BytesWritten2);
    FT_SetTimeouts(ftHandle,5000,5000);

    DWORD TxBytes;
    DWORD RxBytes = 22;
    DWORD BytesReceived;
    unsigned char RxBuffer[256];


    //ftStatus = FT_Write(ftHandle, TxBuffer, sizeof(TxBuffer), &BytesWritten);


    while(true)
    {

        FT_GetQueueStatus(ftHandle,&RxBytes);
        ftStatus = FT_Read(ftHandle,RxBuffer,RxBytes,&BytesReceived);
        if (ftStatus == FT_OK) {
          if (BytesReceived == RxBytes) {
            //qDebug() << "FT_Read OK " << RxBytes;

            QString s = "RECEIVE: ", s1;
            for(int i=0;i<BytesReceived;i++)
                s += s1.sprintf("%02X ",RxBuffer[i]);

            qDebug() << s;
            //ftStatus = FT_Write(ftHandle, TxBuffer2, sizeof(TxBuffer2), &BytesWritten2);
            ftStatus = FT_Write(ftHandle, TxBuffer, sizeof(TxBuffer), &BytesWritten);

            s = "SEND: ", s1;
            for(int i=0;i<BytesWritten;i++)
                s += s1.sprintf("%02X ",TxBuffer[i]);
                qDebug() << s;


          }
          else {
            qDebug() << "FT_Read Timeout";
          }
        }

        else {
          qDebug() << "FT_Read Failed";
        }

        QThread::msleep(500);
    }


 /*#if defined(Q_OS_WIN)
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
#elif defined(Q_WS_X11)*/

//#endif
}

void CGAT5250B::close(bool )
{
    stop = true;
/*#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)*/

//#endif

}
