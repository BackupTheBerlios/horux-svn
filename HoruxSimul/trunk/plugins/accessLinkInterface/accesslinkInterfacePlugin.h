/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
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


#ifndef ACCESSLINKINTERFACE_H
#define ACCESSLINKINTERFACE_H

#include <QObject>
#include <QByteArray>
#include <QTcpSocket>
#include <QUdpSocket>
#include <QVariant>

#include "cryptopp/modes.h"
#include "cryptopp/aes.h"

#include "deviceInterface.h"

#define ENCRYPT_KEY			100
#define DECRYPT_KEY			101
#define SUCCESS				0
#define AES_ENCRYPT_ERROR		5003
#define AES_DECRYPT_ERROR		5004

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
#define V2_EEP_SCAN_LIST_LEN	        4

#define V2_EEP_SIZE			43

#define V2_EEP_PASSWD			43
#define V2_EEP_PASSWD_LEN		8

#define V2_EEP_SIZE_TOTAL               51

//!  Qt plugin device of the access link interface
/*!
  The access link interface is a RS485/TCPIP bridge. The device handled all access link rfid reader connected on the RS485 bus.

  @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
  @version 0.1
  @date    2008

  history:
  28.02.2008  First implementation
*/


using namespace CryptoPP;


class accessLinkInterface: public QObject, public DeviceInterface
{
		Q_OBJECT
		Q_INTERFACES ( DeviceInterface )
		Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
		Q_CLASSINFO ( "Copyright", "Letux - 2008" );
		Q_CLASSINFO ( "Version", "0.1" );
		Q_CLASSINFO ( "PluginName", "accessLinkInterface" );
		Q_CLASSINFO ( "PluginType", "device" );

	public:
		accessLinkInterface ( QObject *parent=0 );
		virtual ~accessLinkInterface();
		virtual QObject* createInstance ( QObject *parent=0 );
		virtual int getId();
		virtual QString getName();
		virtual void setId ( const int id );
		virtual void setName ( const QString name );
		virtual bool initParam ( QDomElement &element );
		virtual void init();
		virtual void setEnabled ( bool enabled );
		virtual bool isEnabled();
		virtual void addSubDevice ( QObject *device );
		virtual QByteArray sendMessageToSubDevice ( QByteArray ) { return 0; };
		virtual void setWidget( QWidget *widget );
		virtual QWidget *  getWidget() {return widget;};
		virtual QVariant getParameter ( QString name );
		virtual QDomElement getXml();
                virtual bool getIsLog();

	signals:
		virtual void sendMessage ( unsigned char * msg, int len );
		virtual void receiveMessage ( unsigned char * message, int len );
		virtual void deviceChanged();

	protected slots:
		//! Read the hello connection
		/*!
		  Read the tcpip data transaction for a hello hanhshake
		*/
		void readyTcpReadHello();

		//! Called when the hello connection is done
		/*!
		  This function will send the first hello handshake message
		*/
		void connectedHello();


		//! Called when a tcp error occur
		/*!
		  \sa QAbstractSocket::error(QAbstractSocket::SocketError socketError );
		*/
		void tcpError ( QAbstractSocket::SocketError );

		//! Called when a udp error occur
		/*!
		  \sa QAbstractSocket::error(QAbstractSocket::SocketError socketError );
		*/
		void udpError ( QAbstractSocket::SocketError );

		//! Called when the data are pending on the udp connection
		/*!
		  This funtion read the udp data
		*/
		void readPendingDatagrams();

		//! Called when the udp connection is closed
		/*!
		  This function will restart automaticly the device connection with the horux server
		*/
		void disconnected();

		//! Called when the temperature changed
		void tempChanged ( int value );

		//! Called when the input changed
		void inputChanged ( int value );

		//! Called when the antivandal changed
		void antivandalChanged ( int value );

		//! message send by a reader like rfid number
		void readerMessage ( unsigned char *msg, int len );

		void deviceDestroyed ( QObject * obj );

	private:
		//! ip address of the access link interface
		QString ip;

		//! ip address of the first horux server connection
		QString ipS1;

		//! ip address of the second horux server connection
		QString ipS2;

		//! ip address of the third horux server connection
		QString ipS3;

		//! subnet ip address of the access link interface
		QString mask;

		//! sntp ip address of the access link interface
		QString sntp;

		//! gateway ip address of the access link interface
		QString gateway;

		//! password of the access link interface
		/*!
		  The apassword allows to modify the eeprom.
		*/
		QString password;

		//! mac address of the access link interface
		QString macaddress;

		//! Port value for the communication with horux server
		/*!
		  this port is opened on the device
		*/
		int dataPort;

		//! Port value for the hello handshake horux server.
		/*!
		  this port is opened on the horux server
		*/
		int helloPort;

		//! daylight of the access link interface
		char daylight;

		//! temperature of the access link interface
		int temp;

		//! Embedded software version of the access link interface
		QString version;

		//! UDP port opended by the horux server after an hello handshake
		quint16 senderPort;

		//! Host address on horux server after an hello handshake
		QHostAddress sender;

		//! tcp socket for the hello handshake
		QTcpSocket *helloSocket;

		//! udp socket for the standart communication
		QUdpSocket *commSocket;

		//! vector for the AES encryption/decryption
		uint timeVector;

		//! Used by AES to uncrypt message
                ECB_Mode<AES >::Decryption *ecbDecryption;

		//! Used by AES to encrypt message
                ECB_Mode<AES >::Encryption *ecbEncryption;


		//! timer used to restart a connection
		int timerRestartConnexion;

		//! timer used to check id the communication is still valid
		int timerCheckConnexion;

		//! timer to send periodically the device info the horux server
		int timerSendInfo;

		//! timer to check periodically the reader connection
		int timerCheckReaderOnline;

		//! only start the timerSendInfo when horux server send it's first asck message
		/*!
		  \sa dispatch_interface_msg
		*/
		bool receiveFirstAck;

		//! Contains which readers are online
		/*!
		  This a 32 bits value. Each bit represent a reader address. Sample: bit 4 represent the reader who has the address 4
		*/
		long readerOnline;

		//! Contains the eeprom value like in the real hardware
		unsigned char eeprom[V2_EEP_SIZE_TOTAL];

		//! hash  table of the reader
		QHash<int ,QObject *> readerList;

	private:
		//! Start a hello handshake 
		void startHelloHanshake();

		//! initialise the crypto engine
		bool initCrypto ( unsigned char* unixTime );

		//! encrypt a clear message to an AES message
		int encryptAES ( unsigned char *clear_msg, int clear_len,
		                 unsigned char *encrypt_msg, int *encrypt_len );

		//! decrypt a AES message to an clear message
		int decryptAES ( unsigned char *encrypt_msg, int encrypt_len,
		                 unsigned char *clear_msg, int *clear_len );


		//! dispatch all message concerning the access link interface
		void dispatch_interface_msg ( char *msg, int len );

		//! dispatch all message concerning the access link reader
		void dispatch_link_msg ( char *msg, int len );

		//! send the access link interface info to horux server
		void sendInterfaceInfo();

		//! open the udp server to listen the message from horux server
		bool startUDPServer();

		//! send to horux the eeprom value
		void sendEEPROM ( unsigned char start, unsigned char stop, bool isOk=true );

		//! set the eeprom value received from horux
		void setEEPROM ( unsigned char start, unsigned char stop, unsigned char *msg, bool isOk=true );


	protected:
		//! This event handler can be reimplemented in a subclass to receive timer events for the object.
		/*!
		  QTimer provides a higher-level interface to the timer functionality, and also more general information about timers. The timer event is passed in the event parameter.

		  \sa QObject::timerEvent(QTimerEvent *e)
		*/
		void timerEvent ( QTimerEvent *e );

};




#endif
