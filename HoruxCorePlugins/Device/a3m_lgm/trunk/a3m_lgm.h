
#ifndef CA3MLGM_H
#define CA3MLGM_H

#include <QObject>
#include <QTcpSocket>
#include <QtScript>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"

#include "cryptopp/modes.h"
#include "cryptopp/aes.h"

#ifndef DEBUG
#define DEBUG 1
#endif

using namespace CryptoPP;

class CA3mLgm : public QObject, CDeviceInterface
{
   Q_OBJECT
   Q_INTERFACES ( CDeviceInterface )
   Q_CLASSINFO ( "Author", "Thierry Forchelet" );
   Q_CLASSINFO ( "Copyright", "Letux - 2010" );
   Q_CLASSINFO ( "Version", "0.0.1" );
   Q_CLASSINFO ( "PluginName", "a3m_lgm" );
   Q_CLASSINFO ( "PluginType", "device" );
   Q_CLASSINFO ( "PluginDescription", "Handle an A3M LGM 5600 reader" );

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

// specific to the device
protected:
  enum COM_STATUS {FREE, BUSY};
  enum CMD_TYPE {GET_SER_NUM = 1, GET_VER_NUM = 2, SET_LED = 3, ACTIVE_LED = 4, ACTIVE_BUZZER = 5, SET_ADDRESS = 6, CMD_WIEGAND_FORMAT = 7};


public slots:
   void dispatchMessage(QByteArray ba);
   void deviceAction(QString xml);

signals:
   void deviceEvent(QString xmlEvent);
   void deviceInputChange(int deviceId, int in, bool status);
   void deviceConnection(int deviceId, bool isConnected);

protected:
   void logComm(uchar *ba, bool isReceive, int len);

protected:
   void hasMsg();
   bool checkCheckSum(QByteArray msg);
   QString formatData(QByteArray data, QString format, int length = 9); // return an human readable representation of the data (key)
   QByteArray sendCmd(CMD_TYPE cmd, QByteArray params = NULL); // send a command to the reader or buffer it
   QString getScript(); // return the uncrypted script
   bool decrypt(const unsigned char *encrypt_msg, const int encrypt_len, unsigned char *clear_msg = NULL, int *clear_len = 0); // this function uncrypt the script

   static void s_accessRefused(QObject *, QMap<QString, QVariant>);
   static void s_accessAccepted(QObject *, QMap<QString, QVariant>);

protected slots:
   void sendBufferContent ();
   void connection(int deviceId, bool isConnected);
   void passBackTimeout();

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
   QByteArray msg;
   CDeviceInterface *horuxParent;
   QTcpSocket *socket;
   QTimer *timer;

   COM_STATUS status;
   QList<QByteArray> pendingMessage;
   QByteArray baNext;
   int busyCounter;

   ECB_Mode<AES >::Decryption *ecbDecryption;
   QScriptEngine engine;

   QMap<QString, QTimer*>passbackTimer;

};
#endif
