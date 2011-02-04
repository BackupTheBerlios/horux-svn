
#ifndef CMOXAIOLOGIC_H
#define CMOXAIOLOGIC_H

#include <QObject>
#include <QTcpSocket>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"
#include "mxio.h"

#define IO_NUMBER               8

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CMoxaIOLogic : public QObject, CDeviceInterface
{
  Q_OBJECT
  Q_INTERFACES ( CDeviceInterface )
  Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
  Q_CLASSINFO ( "Copyright", "Letux - 2010" );
  Q_CLASSINFO ( "Version", "1.0.0" );
  Q_CLASSINFO ( "PluginName", "moxa_iologic" );
  Q_CLASSINFO ( "PluginType", "device" );
  Q_CLASSINFO ( "PluginDescription", "Handle the Moxa IO Logic device (E1212)" );


  enum COM_STATUS {FREE, BUSY};  

public:

    CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );

    CMoxaIOLogic( QObject *parent=0);

    void connectChild(CDeviceInterface *device);

    QVariant getParameter(QString paramName);
    
    void setParameter(QString paramName, QVariant value);

    bool open();
    
    void close();
  
    bool isOpened();

    QObject *getMetaObject() { return this;}

    QDomElement getDeviceInfo(QDomDocument xml_info );

public slots:

    void dispatchMessage(QByteArray ba);

    /*!
      Do something on the device (open door, set output, ...)
      Depend of the device
    */
    void deviceAction(QString xml);
    
    void connection(int deviceId, bool isConnected);

signals:
    void deviceEvent(QString xmlEvent);
    void deviceInputChange(int deviceId, int in, bool status);
    void deviceConnection(int deviceId, bool isConnected);

protected:
    void getFirmware();
    void getSerialNumber();
    void logComm(uchar *ba, bool isReceive, int len);
    int readOutput();


protected slots:
    void checkConnection();
    void reopen();
    void readInput();
    void setOutput(int output, int value, int timer);
    void resetOutput();


protected:
    static void s_accessAccepted(QObject *, QMap<QString, QVariant>);
    static void s_accessRefused(QObject *, QMap<QString, QVariant>);
    static void s_keyDetected(QObject *, QMap<QString, QVariant>);

private:

    QTimer *timerCheckConnection;
    QTimer *timerCheckInput;
    QMap<int, QTimer *>timerOutputReset;

    int socket;
    QString ip;
    int port;
    QString password;
    QString initialOutput;
    QString output0_func;
    QString output1_func;
    QString output2_func;
    QString output3_func;
    QString output4_func;
    QString output5_func;
    QString output6_func;
    QString output7_func;

    int output0Time;
    int output1Time;
    int output2Time;
    int output3Time;
    int output4Time;
    int output5Time;
    int output6Time;
    int output7Time;

    unsigned int outputValue;
    unsigned int inputValue;
};

#endif
