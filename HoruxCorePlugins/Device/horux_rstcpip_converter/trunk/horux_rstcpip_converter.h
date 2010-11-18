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
    Q_CLASSINFO ( "Author", "Letux Sàrl" );
    Q_CLASSINFO ( "Copyright", "Letux Sàrl" );
    Q_CLASSINFO ( "Version", "0.0.1" );
    Q_CLASSINFO ( "PluginName", "horux_rstcpip_converter" );
    Q_CLASSINFO ( "PluginType", "device" );
    Q_CLASSINFO ( "PluginDescription", "Lecteur clavier permettant d'ouvrir une porte à l'aide d'un PIN code" );

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

 /* Propre au périphérique */
 protected:
    QString ip; // adresse IP de notre périphérique
    QString port;
    QTcpSocket *socket; //Socket de communication utilisé pour communiquer avec celui-ci

    QMap<int, CDeviceInterface*> childDevice;

 protected slots:
    void deviceConnected();  // ce slot est appelée par la socket une fois la connexion avec le périphérique établit
    void deviceDiconnected();  // ce slot est appelé par la socket lors d'une déconnexion
    void deviceError( QAbstractSocket::SocketError socketError ); // permet d'obtenir les erreurs apparaissant sur la socket
    void readyRead (); // ce slot appelé par la socket lorsque des données sont à lire depuis le périphérique
 };
#endif

