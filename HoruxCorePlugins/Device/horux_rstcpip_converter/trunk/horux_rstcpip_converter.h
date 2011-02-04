#ifndef CHRSTCPIPC_H
#define CHRSTCPIPC_H

#include <QObject>
#include <QTcpSocket>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"

class CHRstcpipC : public QObject, CDeviceInterface
{
  Q_OBJECT
  Q_INTERFACES ( CDeviceInterface )
  Q_CLASSINFO ( "Author", "Thierry Forchelet" );
  Q_CLASSINFO ( "Copyright", "Letux - 2010" );
  Q_CLASSINFO ( "Version", "0.0.1" );
  Q_CLASSINFO ( "PluginName", "horux_rstcpip_converter" );
  Q_CLASSINFO ( "PluginType", "device" );
  Q_CLASSINFO ( "PluginDescription", "Handle a rs / tcp-ip converter" );

public:
  CHRstcpipC( QObject *parent=0);
  CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );
  void connectChild(CDeviceInterface *);
  QVariant getParameter(QString paramName);
  void setParameter(QString paramName, QVariant value);
  bool open();
  void close();
  bool isOpened();
  QObject *getMetaObject() { return this;}
  QDomElement getDeviceInfo(QDomDocument xml_info );
  QTcpSocket* getSocket() {return socket;}

public slots:
  void dispatchMessage(QByteArray ba);
  void deviceAction(QString xml);
signals:
  void deviceEvent(QString xmlEvent);
  void deviceInputChange(int deviceId, int in, bool status);
  void deviceConnection(int deviceId, bool isConnected);
  void subDeviceMessage(QByteArray ba);

protected:
  void logComm(uchar *ba, bool isReceive, int len);

// specific to the device
protected:
  QString ip;
  QString port;
  QTcpSocket *socket;
  QTcpSocket* testSocket;
  QTimer *timer;
 QMap<int, CDeviceInterface*> childDevice;
  bool firstConnCheck;

protected slots:
  void deviceConnected();  // call by the socket if we are connected
  void deviceDiconnected();  // call by the socket if we are disconnected
  void deviceError( QAbstractSocket::SocketError socketError ); // call when the socket has error
  void deviceTestState( QAbstractSocket::SocketError socketError ); // call by the socket which check the state
  void readyRead(); // call when we receive data from the socket
  void checkConnection(); // call by a timer to check the connection state
};
#endif
