
#ifndef CACCESSLINKINTERFACE_H
#define CACCESSLINKINTERFACE_H

#include <QObject>
#include <QMap>
#include <QTcpSocket>
#include <QUdpSocket>
#include <QDateTime>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"

#include "cryptopp/modes.h"
#include "cryptopp/aes.h"

#define ENCRYPT_KEY			100
#define DECRYPT_KEY			101

#define V2_EEP_MACADR			0
#define V2_EEP_MACADR_LEN		6

#define V2_EEP_IPADR			6
#define V2_EEP_IPADR_LEN		4

#define V2_EEP_IPADR_SVR1		10

#define V2_EEP_IPADR_SVR2		14

#define V2_EEP_IPADR_SVR3		18

#define V2_EEP_IPSUBNET			22

#define V2_EEP_IPPORT_HELLO		26
#define V2_EEP_IPPORT_LEN		2

#define V2_EEP_IPPORT_DATA		28

#define V2_EEP_IPGWAY			30

#define V2_EEP_SNTP_ADR			34

#define V2_EEP_DAYLIGHT			38
#define V2_EEP_DAYLIGHT_LEN		1

#define V2_EEP_SCAN_LIST		39
#define V2_EEP_SCAN_LIST_LEN	4

#define V2_EEP_SIZE				43

#define V2_EEP_PASSWD			43
#define V2_EEP_PASSWD_LEN		8

using namespace CryptoPP;

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CAccessLinkInterface : public QObject, CDeviceInterface
{
  Q_OBJECT
  Q_INTERFACES ( CDeviceInterface )
  Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
  Q_CLASSINFO ( "Copyright", "Letux - 2008" );
  Q_CLASSINFO ( "Version", "0.0.1" );
  Q_CLASSINFO ( "PluginName", "accessLink_Interface" );
  Q_CLASSINFO ( "PluginType", "device" );
  Q_CLASSINFO ( "PluginDescription", "Handle a Access Link Interface" );


  enum COM_STATUS {FREE, BUSY};

public:

    CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );

    CAccessLinkInterface( QObject *parent=0);
    ~CAccessLinkInterface();

    void connectChild(CDeviceInterface *device);

    QVariant getParameter(QString paramName);

    void setParameter(QString paramName, QVariant value);

    bool open();
    
    void close();

    bool isOpened();

    QObject *getMetaObject() { return this;}

    //! plubic interface called by the custom XMLRPC interface

    static void setOutput(QObject* p, QMap<QString, QVariant> params);

    QDomElement getDeviceInfo(QDomDocument xml_info );

public slots:

    void dispatchMessage(QByteArray ba);
    void writeMessage(const unsigned char *, const int len, const bool subDeviceMessage=false);
    void deviceAction(QString xml);

signals:
    void dispatch(QByteArray ba);
    void deviceEvent(QString xmlEvent);
    void deviceInputChange(int deviceId, int in, bool status);
    void deviceConnection(int deviceId, bool isConnected);

protected:
    QMap<QString, CDeviceInterface *> deviceRs485; 
    QString ip;
    QString mask;
    QString gateway;
    int data_port;
    QString ipServer1;
    QString ipServer2;
    QString ipServer3;
    QString password;
    int timeZone;
    int tempMax;
    int memory_free;
    int temperature;
    unsigned long online_reader;
    bool deviceReady;
    uchar input;
    uchar output;
    bool antivandale;

    int priorityServer;
    QDateTime cryptoDateTime;
    QUdpSocket *udpSocket;
    COM_STATUS status;
    QList<QByteArray *> pendingMessage;

    int checkConnectionTimer;
    int msgTimeoutTimer;

    //! Used by AES to uncrypt message
    ECB_Mode<AES >::Decryption *ecbDecryption;

    //! Used by AES to encrypt message
    ECB_Mode<AES >::Encryption *ecbEncryption;

protected:
    //! This function uncrypt the message
    /*!
    * 	
          @param encrypt_msg Encrpyted message
          @param encrypt_len Lenght of the encrypted message. The length is modulo 16 accroding to AES
          @param clear_msg buffer receiving the clear message
          @param clear_len lenght of the clear message
          @return return true if ok
    */
    bool decrypt(
                  const unsigned char *encrypt_msg, 
                  const int encrypt_len,
                  unsigned char *clear_msg = NULL, 
                  int *clear_len = 0
                          );
    
    //! This function encrypt a clear message
    /*!
    * 	
          @param  clear_msg message having to be crypted
          @param  clear_len length of the clear message
          @param  encrypt_msg buffer containing the encrypted message
          @param  encrypt_len lenght of the encrypted message. The value must be a modulo 16 according to AES
          @return return true if ok
    */
    bool encrypt(
                const unsigned char *clear_msg, 
                const int clear_len,
                unsigned char *encrypt_msg = NULL, 
                int *encrypt_len = 0
                          );
    
    //! Initialise the vector for AES used for the encryption and uncryption
    /*!
          @param vector Bytes use to init the encoding keys
          @param crypt_or_decrpyt Boolean to know if we init en crypt or decrypt key
          @return Return true if it is ok else return false
    */
    bool initCryptoKey();

    void sendAck();

    void dispatch_interface(QByteArray ba);

    void timerEvent(QTimerEvent *e);

    void readInterfaceStatus(QByteArray ba);

    void sendCheckEEPROM();

    void checkEEPROM(QByteArray data);
    void rewriteEEPROM();
    void memoryChange(int mem) ;
    void tempTooHot(float temp);
    void logComm(uchar *ba, bool isReceive, int len);

protected slots:
    void newConnection(QTcpSocket *);
    void readHelloData();
    void readPendingDatagrams();
    void helloDisconnect();
    void udpDestroyed(QObject *);

public slots:
    void connected();
};

#endif
