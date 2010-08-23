/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License.     *
 *                                       *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/
#ifndef CGANTNERACCESSERMINAL_H
#define CGANTNERACCESSTERMINAL_H

#include <QObject>
#include <QtScript>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"
#include <QTcpSocket>
#include "cryptopp/modes.h"
#include "cryptopp/aes.h"

#define ENCRYPT_KEY			100
#define DECRYPT_KEY			101


using namespace CryptoPP;

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CGantnerAccessTerminal : public QObject, CDeviceInterface
{
  Q_OBJECT
  Q_INTERFACES ( CDeviceInterface )
  Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
  Q_CLASSINFO ( "Copyright", "Letux - 2010" );
  Q_CLASSINFO ( "Version", "1.0.1" );
  Q_CLASSINFO ( "PluginName", "gantner_AccessTerminal" );
  Q_CLASSINFO ( "PluginType", "device" );
  Q_CLASSINFO ( "PluginDescription", "Handle a Gantner Access Terminal GAT 3100 AK" );
  Q_CLASSINFO ( "DbTableUsed", "hr_gantner_AccessTerminal");


  enum COM_STATUS {FREE, BUSY};
  enum FLEX_RESPONSE {
                        R_MEMORY = 0x01,
                        R_SUBSCRIBER_DATA = 0x02,
                        R_DATETIME = 0x03,
                        R_OPTIONAL_COMPANY = 0x06,
                        R_OPTION_CARD_STRUCT = 0x07,
                        R_UNLOAD_PERSONNEL = 0x24,
                        R_READOUT_BOOKING = 0x61,
                        R_POLLING_TERMINAL_INFO = 0x71,
                        R_LOAD_PERSONNEL = 0x81,
                        R_DOWNLOAD_KEY = 0x82,
                        R_OPEN_BY_COMMAND = 0x83,
                        R_LOAD_RELAYPLAN = 0x84,
                        R_ACCESS_CONFIG = 0x85,
                        R_LOAD_SPECIAL_DAY = 0x86,
                        R_LOAD_SCHEDULE = 0x87,
                        R_LOAD_DAY_PLAN = 0x88,
                        R_ACCESS_STATUS = 0x89,
                        R_ACTUALIZING_SETTING = 0x90,
                        R_READER_SUB_COMMAND = 0xFE
                     };

public:

    CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );

    CGantnerAccessTerminal( QObject *parent=0);

    void connectChild(CDeviceInterface *device);

    QVariant getParameter(QString paramName);
    
    void setParameter(QString paramName, QVariant value);

    bool open();
    
    void close();
  
    bool isOpened();

    QObject *getMetaObject() { return this;}

    QDomElement getDeviceInfo(QDomDocument xml_info );

public slots:

    void dispatchMessage(QByteArray message);

    /*!
      Do something on the device (open door, set output, ...)
      Depend of the device
    */
    void deviceAction(QString xml);


protected:
    //! This function uncrypt the script
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

   signals:
    void deviceEvent(QString xmlEvent);
    void deviceInputChange(int deviceId, int in, bool status);
    void deviceConnection(int deviceId, bool isConnected);

protected slots:
    void reopen();
    void connected();
    void deviceDiconnected();
    void readAccessTerminal();
    void error(QAbstractSocket::SocketError);
    void msgTimeout();
    void setDateTime();
    void pollingTerminalInformation();
    void pollingAccessStatus();



protected:
    void logComm(uchar *ba, bool isReceive, int len);
    QString getScript();
    QByteArray getCheckSum(QByteArray message);
    bool checkCheckSum(QByteArray message);
    void replaceCharTable(QByteArray *message);
    void hasMsg();
    void appendMessage(QByteArray *newMessage);
    void reinit();
    void getReaderInfo();


    void setMemoryInitializing();
    void readOutBookings();
    void setDownloadKey();
    void accessConfiguration();
    void settingActualizing();
    void setSubscriberData();
    void setOptionalCompanyID();
    void setOptionalCard();

    void setEntryExitReader();
    void setFIUReader();
    void setTimeoutReader();
    void setReaderConfiguration();

    void checkDateTimeResp( QByteArray message );
    void checkTerimalInformationResp( QByteArray message );
    void checkReaderMessage(QByteArray message );
    void checkMemoryInitializing(QByteArray message );
    void checkOutBookings(QByteArray message );
    void checkDownloadKey(QByteArray message );
    void checkAccessConfiguration(QByteArray message );
    void checkSettingActualizing(QByteArray message);
    void checkSubscriberData(QByteArray message);
    void checkOptionalCompanyID(QByteArray message);
    void checkOptionalCard(QByteArray message);
    void checkDayPlan(QByteArray message);
    void checkSchedule(QByteArray message);
    void checkSpecialDay(QByteArray message);
    void checkRelayPlan(QByteArray message);
    void checkLoadPesonnel(QByteArray message);
    void checkUnloadPesonnel(QByteArray message);
    void checkOpenByCommand(QByteArray message);
    void checkAccessStatus(QByteArray message);

protected:
    static void s_setDayPlan(QObject *, QMap<QString, QVariant>);
    static void s_setSchedule(QObject *, QMap<QString, QVariant>);
    static void s_setSpecialDay(QObject *, QMap<QString, QVariant>);
    static void s_setRelayPlan(QObject *, QMap<QString, QVariant>);
    static void s_SettingActualizing(QObject *, QMap<QString, QVariant>);
    static void s_loadPersonnel(QObject *, QMap<QString, QVariant>);
    static void s_unloadPersonnel(QObject *, QMap<QString, QVariant>);
    static void s_reinit(QObject *, QMap<QString, QVariant>);
    static void s_openByCommand(QObject *, QMap<QString, QVariant>);


protected:
    ECB_Mode<AES >::Decryption *ecbDecryption;

    QScriptEngine engine;

    QString ipOrDhcp;
    int userMemory;
    int accessMemory;
    int readerInfoError;
    int checkBooking;

    QPointer<QTcpSocket>tcp;

    QByteArray msg;

    QByteArray currentMessage;
    COM_STATUS status;
    QList<QByteArray *> pendingMessage;
    QPointer<QTimer>msgWaitResponse;

    QPointer<QTimer>timerSetDateTime;
    QPointer<QTimer>timerPollingTerminalInformation;
    QPointer<QTimer>timerPollingAccessStatus;

    bool forceReinit;
    int timeoutMsgError;

    QString serviceDate;
    int openTimeBooking;
    int openAccessBooking;
    int nbreSubReader;
    int nbreExternalDoorControl;
    int nbreRelayExpander;
    QString downloadKey;
    int numberIdentification;
    QString terminalTypeFeature;
    QString hardwareIdentification;
    QString readerSerialNumber;
    QString readerArticleNumber;

    //subscriber parameters
    int subscriberNumber;
    int plantNumber;
    int mainCompIdCard;
    int bookingCodeSumWinSwitchOver;
    int switchOverLeap;
    int waitingTimeInput;
    int monitoringTime;
    int monitorinChangingTime;
    int cardReaderType;

    //access parameters
    int openSchedule;
    int normalRelayPlan;
    int specialRelayPlan;
    int maxDoorOpenTime;
    int warningTimeDoorOpenTime;
    int unlockingTime;
    int relay1;
    int timeRelay1;
    int relay2;
    int timeRelay2;
    int relay3;
    int timeRelay3;
    int relay4;
    int timeRelay4;
    int opto1;
    int opto2;
    int opto3;
    int opto4;
    int enterExitInfo;
    int autoUnlocking;
    int lockUnlockCommand;
    QString holdUpPINCode;
    int twoPersonAccess;
    int barriereRepeatedAccess;
    int timeBookingControl;
    int antiPassActive;
    int relayExpanderControl;
    int terminalType;
    int doorOpenTimeUnit;

    // reader parameters
    int readerTimeout;
    int readerFiu;
    int readerEntryExit;

    // optional company ID
    int optionalCompanyID1;
    int optionalCompanyID2;
    int optionalCompanyID3;
    int optionalCompanyID4;
    int optionalCompanyID5;
    int optionalCompanyID6;
    int optionalCompanyID7;
    int optionalCompanyID8;
    int optionalCompanyID9;
    int optionalCompanyID10;

    // optional card data
    int optionalCardStructur;
    int optionalGantnerNationalCode;
    QString optionalGantnerCustomerCode1;
    QString optionalGantnerCustomerCode2;
    QString optionalGantnerCustomerCode3;
    QString optionalGantnerCustomerCode4;
    QString optionalGantnerCustomerCode5;
    QString optionalReaderInitialisation;
    QString optionalTableCardType;

    QMap<QString, QString> is2personAccess;

    QMap<int,CDeviceInterface *> childDevice;

    unsigned long long lastCardNumber;
};

#endif
