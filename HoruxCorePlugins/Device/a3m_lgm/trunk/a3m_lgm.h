#ifndef CA3MLGM_H
#define CA3MLGM_H

#include <QObject>
#include <QTcpSocket>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"

#ifndef DEBUG
#define DEBUG 1
#endif

class CA3mLgm : public QObject, CDeviceInterface
{
   Q_OBJECT
   Q_INTERFACES ( CDeviceInterface )
   Q_CLASSINFO ( "Author", "Letux Sàrl" );
   Q_CLASSINFO ( "Copyright", "Letux Sàrl" );
   Q_CLASSINFO ( "Version", "0.0.1" );
   Q_CLASSINFO ( "PluginName", "a3m_lgm" );
   Q_CLASSINFO ( "PluginType", "device" );
   Q_CLASSINFO ( "PluginDescription", "Lecteur clavier permettant d'ouvrir une porte à l'aide d'un PIN code" );

   enum COM_STATUS {FREE, BUSY};
   enum CMD_TYPE {GET_SER_NUM, GET_VER_NUM, SET_LED, ACTIVE_LED, ACTIVE_BUZZER, SET_ADDRESS, CMD_WIEGAND_FORMAT};
public:
   CA3mLgm( QObject *parent=0);
   CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );
   void connectChild(CDeviceInterface *) {}
   QVariant getParameter(QString paramName);
   void setParameter(QString paramName, QVariant value);
   bool open();
   void close();
   bool isOpened();
   QObject *getMetaObject() { return this;}
   QDomElement getDeviceInfo(QDomDocument xml_info );

public slots:
   void dispatchMessage(QByteArray ba);
   void deviceAction(QString xml);

signals:
   void deviceEvent(QString xmlEvent);
   void deviceInputChange(int deviceId, int in, bool status);
   void deviceConnection(int deviceId, bool isConnected);

protected:
   void logComm(uchar *ba, bool isReceive, int len);

   /* Propre au périphérique */
protected:
   void hasMsg();
   bool checkCheckSum(QByteArray msg);
   QString formatData(QByteArray data, QString format, int length = 10);
   QByteArray sendCmd(CMD_TYPE cmd, QByteArray params = NULL);

   //void appendMessage(uchar *msg, int len);
   //void timerEvent(QTimerEvent *e);

   // Fonction spécifique permettant d'ouvrir la porte
   static void s_accessRefused(QObject *, QMap<QString, QVariant>);
   static void s_accessAccepted(QObject *, QMap<QString, QVariant>);
protected:
   static const uchar LGM_GET_SER_NUM = 0X80;
   static const uchar LGM_GET_VER_NUM = 0X88;
   static const uchar LGM_SET_LED = 0X90;
   static const uchar LGM_ACTIVE_LED = 0X98;
   static const uchar LGM_ACTIVE_BUZZER = 0XA0;
   static const uchar LGM_SET_ADDRESS = 0XA8;
   static const uchar LGM_CMD_WIEGAND_FORMAT = 0XB0;

   QString address;
   QString serialNumberFormat;
   int readerAction;
   QByteArray msg; // Message envoyé
   CDeviceInterface *horuxParent;
   QTcpSocket *socket;
   QTimer *timer;


   COM_STATUS status;
   QList<QByteArray> pendingMessage;
   QByteArray baNext;
   int busyCounter;

   bool initReader;
protected slots:
   void tstcmd ();
};
#endif

