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
#ifndef CGANTNERTIMETERMINAL_H
#define CGANTNERTIMETERMINAL_H

#include <QObject>
#include <QtScript>
#include <QFtp>
#include <QUdpSocket>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"

#include "cryptopp/modes.h"
#include "cryptopp/aes.h"

#define ENCRYPT_KEY			100
#define DECRYPT_KEY			101

#define TIMER_CONNECTION 60000 // 1 minute

using namespace CryptoPP;

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CGantnerTimeTerminal : public QObject, CDeviceInterface
{
  Q_OBJECT
  Q_INTERFACES ( CDeviceInterface )
  Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
  Q_CLASSINFO ( "Copyright", "Letux - 2009" );
  Q_CLASSINFO ( "Version", "0.0.1" );
  Q_CLASSINFO ( "PluginName", "gantner_TimeTerminal" );
  Q_CLASSINFO ( "PluginType", "device" );
  Q_CLASSINFO ( "PluginDescription", "Handle a Gantner Time Terminal GAT p.time ST x80/ ST x81/ ST x90" );
  Q_CLASSINFO ( "DbTableUsed", "hr_gantner_TimeTerminal,hr_timux_config,hr_gantner_TimeTerminal_key");

  enum ACTIONS { WAITING, READ_REPLACE, READ_CONFIG_FILE, READ_BOOKING, SEND_DOWN, SEND_CONFIG,REINIT } ;
  enum BOOKING_FIEDS
    {
      BK_DATETIME = 2,
      BK_USERID = 4,
      BK_KEY = 6,
      BK_BOOKINGCODE = 8,
      BK_BOKKINGREASON = 9
    };

public:

    CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );

    CGantnerTimeTerminal( QObject *parent=0);

    void connectChild(CDeviceInterface *device);

    QVariant getParameter(QString paramName);
    
    void setParameter(QString paramName, QVariant value);

    bool open();
    
    void close();
  
    bool isOpened();

    QObject *getMetaObject() { return this;}

    QDomElement getDeviceInfo(QDomDocument xml_info );

public slots:

    void dispatchMessage(QByteArray bookings);

    /*!
      Do something on the device (open door, set output, ...)
      Depend of the device
    */
    void deviceAction(QString xml);

    void commandFinished( int, bool);

    void connectionToFtp();

protected:
    static void s_removeUser(QObject *, QMap<QString, QVariant>);
    static void s_removeAllUsers(QObject *, QMap<QString, QVariant>);
    static void s_addUserBalances(QObject *, QMap<QString, QVariant>);
    static void s_addUser(QObject *, QMap<QString, QVariant>);
    static void s_addKey(QObject *, QMap<QString, QVariant>);
    static void s_removeKey(QObject *, QMap<QString, QVariant>);

    static void s_addAbsentReason(QObject *, QMap<QString, QVariant>);
    static void s_removeAbsentReason(QObject *, QMap<QString, QVariant>);
    static void s_removeAllAbsentReason(QObject *, QMap<QString, QVariant>);

    static void s_setBalanceText(QObject *, QMap<QString, QVariant>);

    static void s_reinit(QObject *, QMap<QString, QVariant>);

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

protected slots:
    void readyRead ();
    void readDownInfo();
    void readUdp();
    
signals:
    void deviceEvent(QString xmlEvent);
    void deviceInputChange(int deviceId, int in, bool status);
    void deviceConnection(int deviceId, bool isConnected);

protected:
    void logComm(uchar *ba, bool isReceive, int len);
    QString getScript();
    void reinit();
    void timerEvent ( QTimerEvent * event );
    void checkConfigFile(QString xml);

protected:
    QFtp *ftp;

    bool bookingError;

    QScriptEngine engine;

    QString ipOrDhcp;
    bool isAutoRestart;
    QString autoRestart;
    int displayTimeout;
    int inputTimeout;
    int brightness;
    bool udpServer;
    QString udpClient;
    int checkBooking;

    ACTIONS action;
    int idConnectHost;      // ftp id of the host connection
    int idLogin;            // ftp id of the login
    int idReadReplace;      // ftp id when reading the file reload.txt
    int idRemoveReplace;    // ftp id when deleting the file reload.txt
    int idCheckBooking;     // ftp id when checking the booking file
    int idRemoveBookings;   // ftp id when deleting the booking file
    int idSendDown;         // ftp id when sending new commands
    int idRemoveDown;       // ftp id when deleting the commands log file
    int idReadDown;         // ftp id when reading the commands log file
    int idReadConfig;       // ftp id when reading the config file
    int idSendConfig;        // ftp id when sending the config file
    int idSendConfigCmd;        // ftp id when sending the commands config file

    int timerCheckBooking;
    int timerSendFile;
    int timerConfigFile;
    int timerConnectionAbort;

    QStringList sendFileList;
    QStringList sendConfigList;
    int numberOfSendCommand;
    int numberOfConfigCommand;


    QString readFile;
    QString sendFile;
    QString configFile;

    QUdpSocket *udp;
    QStringList infoList;

    ECB_Mode<AES >::Decryption *ecbDecryption;

};

#endif
