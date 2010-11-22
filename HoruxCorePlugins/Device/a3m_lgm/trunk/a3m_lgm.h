/***************************************************************************
 *   Copyright (C) 2010 by Thierry Forchelet                               *
 *   thierry.forchelet@letux.ch                                            *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License.        *
 *                                                                         *
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
   Q_CLASSINFO ( "Author", "Letux Sàrl" );
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

public slots:
   void dispatchMessage(QByteArray ba);
   void deviceAction(QString xml);

signals:
   void deviceEvent(QString xmlEvent);
   void deviceInputChange(int deviceId, int in, bool status);
   void deviceConnection(int deviceId, bool isConnected);

protected:
   void logComm(uchar *ba, bool isReceive, int len);

// Specific to the device
protected:
   enum COM_STATUS {FREE, BUSY};
   enum CMD_TYPE {GET_SER_NUM = 1, GET_VER_NUM = 2, SET_LED = 3, ACTIVE_LED = 4, ACTIVE_BUZZER = 5, SET_ADDRESS = 6, CMD_WIEGAND_FORMAT = 7};

protected:
   void hasMsg();
   bool checkCheckSum(QByteArray msg);
   QString formatData(QByteArray data, QString format, int length = 10); // Return an human readable representation of the data (key)
   QByteArray sendCmd(CMD_TYPE cmd, QByteArray params = NULL); // Send a command to the reader or buffer it
   QString getScript(); // Return the uncrypted script
   bool decrypt(const unsigned char *encrypt_msg, const int encrypt_len, unsigned char *clear_msg = NULL, int *clear_len = 0); // This function uncrypt the script

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
   QByteArray msg;
   CDeviceInterface *horuxParent;
   QTcpSocket *socket;
   QTimer *timer;

   COM_STATUS status;
   QList<QByteArray> pendingMessage;
   QByteArray baNext;
   int busyCounter;

   bool initReader;
   ECB_Mode<AES >::Decryption *ecbDecryption;
   QScriptEngine engine;
protected slots:
   void sendBufferContent ();
   void connection(int deviceId, bool isConnected);
};
#endif
