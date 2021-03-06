
#ifndef CLCDDISPLAY_H
#define CLCDDISPLAY_H

#include <QObject>
#include <QTcpSocket>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CLCDDisplay : public QObject, CDeviceInterface
{
  Q_OBJECT
  Q_INTERFACES ( CDeviceInterface )
  Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
  Q_CLASSINFO ( "Copyright", "Letux - 2010" );
  Q_CLASSINFO ( "Version", "1.0.1" );
  Q_CLASSINFO ( "PluginName", "horux_lcddisplay" );
  Q_CLASSINFO ( "PluginType", "device" );
  Q_CLASSINFO ( "PluginDescription", "Handle an LCD Display ba TCPIP" );


  enum COM_STATUS {FREE, BUSY};

public:

    CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );

    CLCDDisplay( QObject *parent=0);

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
    
    void deviceConnected();
    void deviceDiconnected();
    void deviceError( QAbstractSocket::SocketError socketError );
    void readyRead ();

    void connection(int deviceId, bool isConnected);

signals:
    void deviceEvent(QString xmlEvent);
    void deviceInputChange(int deviceId, int in, bool status);
    void deviceConnection(int deviceId, bool isConnected);

protected:
    void getFirmware() {};
    void getSerialNumber() {};
    void appendMessage(uchar *msg, int len);
    void logComm(uchar *ba, bool isReceive, int len);
    void hasMsg(); 
    void displayMessage(QString message);

protected:
    static void s_displayMessage(QObject *, QMap<QString, QVariant>);

protected slots:
    void displayDefaulfMessage();
    void reopen();


protected:
    QPointer<QTcpSocket>socket;
    QString ip;
    int port;
    QString defaultMessage;
    int messageTimerDisplay;

    QPointer<QTimer> timeDateTimer;
    QPointer<QTimer> messageTimer;
    
    COM_STATUS status;
    QList<QByteArray *> pendingMessage;

    QByteArray currentMessage;
    QByteArray msg;

};

#endif
