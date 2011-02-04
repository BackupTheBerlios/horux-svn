
#ifndef CACCESSLINKRS485_H
#define CACCESSLINKRS485_H

#include <QObject>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"

#define OUTPUT_OFF 0x00  //clear the output
#define OUTPUT_1  0x01  //door
#define OUTPUT_2  0x02  //green led
#define OUTPUT_3  0x04  //red led
#define OUTPUT_4  0x08  // orange led

#define INPUT_NUMBER 2 // the access link reader has 2 input

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CAccessLinkRS485 : public QObject, CDeviceInterface
{
  Q_OBJECT
  Q_INTERFACES ( CDeviceInterface )
  Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
  Q_CLASSINFO ( "Copyright", "Letux - 2008" );
  Q_CLASSINFO ( "Version", "0.0.1" );
  Q_CLASSINFO ( "PluginName", "accessLink_ReaderRS485" );
  Q_CLASSINFO ( "PluginType", "device" );
  Q_CLASSINFO ( "PluginDescription", "Handle a Access Link Reader" );


  enum MEMORY {M_200, M_1000};
  enum COM_STATUS {FREE, BUSY};
  enum DOOR_LOCK_MODE {NONE, NO_TIMEOUT, TIMEOUT, TIMEOUT_IN};

public:

    CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );

    CAccessLinkRS485( QObject *parent=0);

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

signals:
    void deviceEvent(QString xmlEvent);
    void deviceInputChange(int deviceId, int in, bool status);
    void deviceConnection(int deviceId, bool isConnected);
    void sendMessage(const unsigned char *, const int ,  const bool);

protected:
    void getFirmware();
    void getSerialNumber();
    void getDbSize();
    void getRdStatus();
    void getOutputStatus();
    void resetOutput();
    void setAntenna(bool flag);
    void checkReaderStatus(uchar status);
    void appendMessage(uchar *msg, int len);
    void timerEvent(QTimerEvent *e);
    void handleSn(QString sn);
    void logComm(uchar *ba, bool isReceive, int len);
    void checkStandalone();

protected:
    static void s_openDoorLock(QObject *, QMap<QString, QVariant>);
    static void s_setOutput(QObject *, QMap<QString, QVariant>);


protected:
    int address;
    MEMORY memory;
    bool rtc;
    bool lcd;
    bool keyboard;
    bool eeprom;
    QString defaultText;
    int oTime1;
    int oTime2;
    int oTime3;
    int oTime4;
    int antipassback;
    bool standalone;
    int openModeTimeout;
    int openModeInput;

    uchar input;
    uchar output;
    int dbSize;

    COM_STATUS status;
    QByteArray currentMessage;
    QList<QByteArray *> pendingMessage;
    int msgTimeoutTimer;

    DOOR_LOCK_MODE doorLockMode;

    QMap<QString, int> passBackTimer;

    int openModeTimeoutTimer;
    int checkStandaloneTimer;

};

#endif
