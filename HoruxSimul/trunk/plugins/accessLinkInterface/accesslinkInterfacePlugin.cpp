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
#include "accesslinkInterfacePlugin.h"

#include <QTimerEvent>
#include <QtGui>
#include <math.h>
#include "accessLinkInterfaceWidget.h"
#include "qled.h"

accessLinkInterface::accessLinkInterface ( QObject *parent ) :QObject ( parent )
{

ecbEncryption = NULL;
ecbDecryption = NULL;
receiveFirstAck = false;

}

QObject *accessLinkInterface::createInstance ( QObject *parent )
{
	return new accessLinkInterface ( parent );
}

accessLinkInterface::~accessLinkInterface()
{
	readerList.clear();
	if ( widget )
	{
		widget->hide();
		delete widget;
		widget = 0;
	}

  if(ecbEncryption)
  {
    delete ecbEncryption;
    ecbEncryption = NULL;
  }
  
  if(ecbDecryption)
  {
    delete ecbDecryption;
    ecbDecryption = NULL;
  }

}

void accessLinkInterface::setWidget ( QWidget *widget ) 
{
  this->widget =  new AccessLinkInterfaceWidget(widget);
}

void accessLinkInterface::init()
{
  if(ecbEncryption)
  {
    delete ecbEncryption;
    ecbEncryption = NULL;
  }
  
  if(ecbDecryption)
  {
    delete ecbDecryption;
    ecbDecryption = NULL;
  }


  enabled = false;
  helloSocket = NULL;
  commSocket = NULL;
  timeVector = 0;
  timerRestartConnexion = 0;
  timerCheckConnexion = 0;
  timerSendInfo = 0;
  timerCheckReaderOnline = 0 ;
  receiveFirstAck = false;

}

void accessLinkInterface::setEnabled ( bool enabled )
{
	this->enabled = enabled;

	if ( enabled )
	{
		startHelloHanshake();
	}
	else
	{
		if ( timerCheckReaderOnline )
		{
			killTimer ( timerCheckReaderOnline );
			timerCheckReaderOnline = 0;
		}

		if ( timerRestartConnexion )
		{
			killTimer ( timerRestartConnexion );
			timerRestartConnexion = 0;
		}

		if ( timerCheckConnexion )
		{
			killTimer ( timerCheckConnexion );
			timerCheckConnexion = 0;
		}

		if ( timerSendInfo )
		{
			killTimer ( timerSendInfo );
			timerSendInfo = 0;
		}


		if ( commSocket )
		{
			commSocket->close();
			commSocket->deleteLater();
			commSocket = 0;
		}

		timeVector = 0;
		receiveFirstAck = false;

          widget->findChild<QCheckBox*> ( "input1" )->setCheckState(Qt::Unchecked);
          widget->findChild<QCheckBox*> ( "input2" )->setCheckState(Qt::Unchecked);
          widget->findChild<QCheckBox*> ( "input3" )->setCheckState(Qt::Unchecked);
          widget->findChild<QCheckBox*> ( "input4" )->setCheckState(Qt::Unchecked);
          widget->findChild<QCheckBox*> ( "input5" )->setCheckState(Qt::Unchecked);
          widget->findChild<QCheckBox*> ( "input6" )->setCheckState(Qt::Unchecked);
          widget->findChild<QCheckBox*> ( "input7" )->setCheckState(Qt::Unchecked);
          widget->findChild<QCheckBox*> ( "input8" )->setCheckState(Qt::Unchecked);
          widget->findChild<QCheckBox*> ( "antivandal" )->setCheckState(Qt::Unchecked);


	}
}

bool accessLinkInterface::isEnabled()
{
	return enabled;
}

int accessLinkInterface::getId()
{
	return id;
}

QString accessLinkInterface::getName()
{
	return name;
}

void accessLinkInterface::setId ( const int id )
{
	this->id = id;
}

void accessLinkInterface::setName ( const QString name )
{
	this->name = name;
}

bool accessLinkInterface::initParam ( QDomElement &element )
{
	ip = element.attribute ( "ip" );
	ipS1 = element.attribute ( "ipS1" );

        isLog = (bool)element.attribute ( "isLog" ).toInt();

	macaddress = element.attribute ( "macaddress" );
	if ( macaddress.isEmpty() ) macaddress = "01:01:01:01:01:01";


	ipS2 = element.attribute ( "ipS2" );
	if ( ipS2.isEmpty() ) ipS2 = ipS1;

	ipS3 = element.attribute ( "ipS3" );
	if ( ipS3.isEmpty() ) ipS3 = ipS1;

	mask = element.attribute ( "mask" );
	if ( mask.isEmpty() ) mask = "255.255.255.0";

	gateway = element.attribute ( "gateway" );
	if ( gateway.isEmpty() ) gateway = "0.0.0.0";

	sntp = element.attribute ( "sntp" );
	if ( sntp.isEmpty() ) sntp = "0.0.0.0";


	password = element.attribute ( "password" );
	if ( password.isEmpty() ) password = "oel";


	dataPort = element.attribute ( "dataPort" ).toInt();
	if ( dataPort == 0 ) dataPort = 1025;

	helloPort = element.attribute ( "helloPort" ).toInt();
	if ( helloPort == 0 ) helloPort = 1027;

	daylight = element.attribute ( "daylight" ).toInt();

	temp = element.attribute ( "temp" ).toInt();
	if ( temp == 0 ) temp = 40;

	readerOnline = element.attribute ( "online" ).toInt();


	version = element.attribute ( "version" );
	if ( version == "" ) version = "Version X";


	//mac address (6 bytes)
	eeprom[0] = macaddress.section ( ':',0,0 ).toInt();
	eeprom[1] = macaddress.section ( ':',1,1 ).toInt();
	eeprom[2] = macaddress.section ( ':',2,2 ).toInt();
	eeprom[3] = macaddress.section ( ':',3,3 ).toInt();
	eeprom[4] = macaddress.section ( ':',4,4 ).toInt();
	eeprom[5] = macaddress.section ( ':',5,5 ).toInt();

	//ip address (4 bytes)
	eeprom[6] = ip.section ( '.',0,0 ).toInt();
	eeprom[7] = ip.section ( '.',1,1 ).toInt();
	eeprom[8] = ip.section ( '.',2,2 ).toInt();
	eeprom[9] = ip.section ( '.',3,3 ).toInt();

	//ip server 1 (4 bytes)
	eeprom[10] = ipS1.section ( '.',0,0 ).toInt();
	eeprom[11] = ipS1.section ( '.',1,1 ).toInt();
	eeprom[12] = ipS1.section ( '.',2,2 ).toInt();
	eeprom[13] = ipS1.section ( '.',3,3 ).toInt();

	//ip server 2 (4 bytes)
	eeprom[14] = ipS2.section ( '.',0,0 ).toInt();
	eeprom[15] = ipS2.section ( '.',1,1 ).toInt();
	eeprom[16] = ipS2.section ( '.',2,2 ).toInt();
	eeprom[17] = ipS2.section ( '.',3,3 ).toInt();

	//ip server 3 (4 bytes)
	eeprom[18] = ipS3.section ( '.',0,0 ).toInt();
	eeprom[19] = ipS3.section ( '.',1,1 ).toInt();
	eeprom[20] = ipS3.section ( '.',2,2 ).toInt();
	eeprom[21] = ipS3.section ( '.',3,3 ).toInt();

	//subnet (4 bytes)
	eeprom[22] = mask.section ( '.',0,0 ).toInt();
	eeprom[23] = mask.section ( '.',1,1 ).toInt();
	eeprom[24] = mask.section ( '.',2,2 ).toInt();
	eeprom[25] = mask.section ( '.',3,3 ).toInt();

	//port hello (2 bytes)
	eeprom[26] = ( helloPort & 0x0000ff00 ) >>8;
	eeprom[27] = ( helloPort & 0x000000ff );

	//port data (2 bytes)
	eeprom[28] = ( dataPort & 0x0000ff00 ) >>8;
	eeprom[29] = ( dataPort & 0x000000ff );

	//getway (4 bytes)
	eeprom[30] = gateway.section ( '.',0,0 ).toInt();
	eeprom[31] = gateway.section ( '.',1,1 ).toInt();
	eeprom[32] = gateway.section ( '.',2,2 ).toInt();
	eeprom[33] = gateway.section ( '.',3,3 ).toInt();

	//sntp (4 bytes)
	eeprom[34] = sntp.section ( '.',0,0 ).toInt();
	eeprom[35] = sntp.section ( '.',1,1 ).toInt();
	eeprom[36] = sntp.section ( '.',2,2 ).toInt();
	eeprom[37] = sntp.section ( '.',3,3 ).toInt();

	//daylight (1 bytes)
	eeprom[38] = daylight;

	//reader scan list  (4 bytes)
	eeprom[39] = ( readerOnline & 0xff000000 ) >>24;
	eeprom[40] = ( readerOnline & 0x00ff0000 ) >>16;
	eeprom[41] = ( readerOnline & 0x0000ff00 ) >>8;
	eeprom[42] = ( readerOnline & 0x000000ff );

	//password (8 bytes)

	for ( int i=0;i<8; i++ )
	{
		if ( password.length() >i )
			eeprom[i+43] = password.at ( i ).toLatin1();
		else
			eeprom[i+43] = 0;
	}

	widget->findChild<QLineEdit*> ( "name" )->setText ( name );
	connect ( widget->findChild<QLineEdit*> ( "name" ),SIGNAL ( textChanged ( const QString & ) ), SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "mac" )->setText ( macaddress );
	connect ( widget->findChild<QLineEdit*> ( "mac" ),SIGNAL ( textChanged ( const QString & ) ), SIGNAL ( deviceChanged() ) );


	widget->findChild<QLineEdit*> ( "version" )->setText ( version );
	connect ( widget->findChild<QLineEdit*> ( "version" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "ip" )->setText ( ip );
	connect ( widget->findChild<QLineEdit*> ( "ip" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "ipS1" )->setText ( ipS1 );
	connect ( widget->findChild<QLineEdit*> ( "ipS1" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "ipS2" )->setText ( ipS2 );
	connect ( widget->findChild<QLineEdit*> ( "ipS2" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "ipS3" )->setText ( ipS3 );
	connect ( widget->findChild<QLineEdit*> ( "ipS3" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "subnet" )->setText ( mask );
	connect ( widget->findChild<QLineEdit*> ( "subnet" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "gateway" )->setText ( gateway );
	connect ( widget->findChild<QLineEdit*> ( "gateway" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "sntp" )->setText ( sntp );
	connect ( widget->findChild<QLineEdit*> ( "sntp" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "password" )->setText ( password );
	connect ( widget->findChild<QLineEdit*> ( "password" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );


	QString s;
	s = s.sprintf ( "%u", readerOnline );
	widget->findChild<QLineEdit*> ( "online" )->setText ( s );
	connect ( widget->findChild<QLineEdit*> ( "online" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );


	widget->findChild<QSpinBox*> ( "helloPort" )->setValue ( helloPort );
	connect ( widget->findChild<QSpinBox*> ( "helloPort" ),SIGNAL ( valueChanged ( int ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QSpinBox*> ( "dataPort" )->setValue ( dataPort );
	connect ( widget->findChild<QSpinBox*> ( "dataPort" ),SIGNAL ( valueChanged ( int ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QSpinBox*> ( "daylight" )->setValue ( daylight );
	connect ( widget->findChild<QSpinBox*> ( "daylight" ),SIGNAL ( valueChanged ( int ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QSpinBox*> ( "temp" )->setValue ( temp );
	connect ( widget->findChild<QSpinBox*> ( "temp" ),SIGNAL ( valueChanged ( int ) ), this, SIGNAL ( deviceChanged() ) );


	connect ( widget->findChild<QSpinBox*> ( "temp" ),
	          SIGNAL ( valueChanged ( int ) ), SLOT ( tempChanged ( int ) ) );

	connect ( widget->findChild<QCheckBox*> ( "input1" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( inputChanged ( int ) ) );
	connect ( widget->findChild<QCheckBox*> ( "input2" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( inputChanged ( int ) ) );
	connect ( widget->findChild<QCheckBox*> ( "input3" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( inputChanged ( int ) ) );
	connect ( widget->findChild<QCheckBox*> ( "input4" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( inputChanged ( int ) ) );
	connect ( widget->findChild<QCheckBox*> ( "input5" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( inputChanged ( int ) ) );
	connect ( widget->findChild<QCheckBox*> ( "input6" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( inputChanged ( int ) ) );
	connect ( widget->findChild<QCheckBox*> ( "input7" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( inputChanged ( int ) ) );
	connect ( widget->findChild<QCheckBox*> ( "input8" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( inputChanged ( int ) ) );
	connect ( widget->findChild<QCheckBox*> ( "antivandal" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( antivandalChanged ( int ) ) );

        widget->findChild<QCheckBox*> ( "log" )->setChecked ( isLog );
	connect ( widget->findChild<QCheckBox*> ( "log" ),SIGNAL ( pressed () ), this, SIGNAL ( deviceChanged() ) );

	if ( ip.isEmpty() || ipS1.isEmpty() )
		return false;

	return true;
}

/*!
    \fn accessLinkInterface::startUDPServer()
 */
bool accessLinkInterface::startUDPServer()
{
	commSocket = new QUdpSocket ( this );

	connect ( commSocket, SIGNAL ( readyRead() ), SLOT ( readPendingDatagrams() ) );
	connect ( commSocket, SIGNAL ( disconnected() ), SLOT ( disconnected() ) );
	connect ( commSocket, SIGNAL ( error ( QAbstractSocket::SocketError ) ),SLOT ( udpError ( QAbstractSocket::SocketError ) ) );

	commSocket->bind ( widget->findChild<QSpinBox*> ( "dataPort" )->value()  );

	timerCheckReaderOnline = startTimer ( 10 );

	return true;
}


void accessLinkInterface::startHelloHanshake()
{
	helloSocket = new QTcpSocket ( this );

	connect ( helloSocket, SIGNAL ( connected() ),SLOT ( connectedHello() ) );
	connect ( helloSocket, SIGNAL ( readyRead() ),SLOT ( readyTcpReadHello() ) );
	connect ( helloSocket, SIGNAL ( error ( QAbstractSocket::SocketError ) ),SLOT ( tcpError ( QAbstractSocket::SocketError ) ) );

        

	helloSocket->connectToHost ( ipS1, widget->findChild<QSpinBox*> ( "helloPort" )->value() );
}

/*!
    \fn accessLinkInterface::connect()
 */
void accessLinkInterface::connectedHello()
{
	qDebug ( "connexion done" );

	//type, verison, priority, ip, reader list
	QString helloMessage = "I," + version + ",1," + ip + ",1111111";

	helloSocket->write ( helloMessage.toLatin1(),helloMessage.length() );
}

/*!
    \fn accessLinkInterface::readyTcpReadHello()
 */
void accessLinkInterface::readyTcpReadHello()
{
	char UNIXTime[5];
	memset ( UNIXTime,0,5 );

	helloSocket->read ( UNIXTime, 5 );

	if ( !initCrypto ( ( unsigned char* ) UNIXTime+1 ) )
	{
		qDebug ( "Init crypto failed" );
		helloSocket->close();
		helloSocket->deleteLater();
		return;
	}
	else
		qDebug ( "Init crypto well done" );

	startUDPServer();

	helloSocket->close();

	helloSocket->deleteLater();
	
}

/*!
    \fn accessLinkInterface::initCrypto(char unixTime)
 */
bool accessLinkInterface::initCrypto ( unsigned char* unixTime )
{
	QHostAddress hostAddress ( ipS1 );
	int prioriotyServer = 0;

	timeVector = ( uint ) unixTime[0] << 24;
	timeVector |= ( uint ) unixTime[1] << 16;
	timeVector |= ( uint ) unixTime[2] << 8;
	timeVector |= ( uint ) unixTime[3];

	union u_ipRemote
	{
		long l_ip;
		unsigned char b_ip[4];
	};

	u_ipRemote ipRemote;

	ipRemote.b_ip[3] = ip.section ( ".",0,0 ).toInt();
	ipRemote.b_ip[2] = ip.section ( ".",1,1 ).toInt();
	ipRemote.b_ip[1] = ip.section ( ".",2,2 ).toInt();
	ipRemote.b_ip[0] = ip.section ( ".",3,3 ).toInt();

	long IpAdrSvr,IpAdr;

	if ( helloSocket )
	{
		IpAdrSvr = hostAddress.toIPv4Address();
		IpAdr = ipRemote.l_ip;

		unsigned char aesdata[16];
		memset ( aesdata,0,16 );

		unsigned char aesdata2[16];
		memset ( aesdata2,0,16 );

		unsigned char aeskey[16];
		memset ( aeskey,0,16 );

		for ( int i=0; i<16; i++ )
			aeskey[i]=i*13;

		aesdata[0]= ( prioriotyServer+1 ) * 83;
		aesdata[1]= ( unsigned char ) ( IpAdrSvr >> ( 22+prioriotyServer ) );
		aesdata[2]= ( prioriotyServer+1 ) * 27;
		aesdata[3]= ( unsigned char ) ( IpAdr >> ( 6+prioriotyServer ) );
		aesdata[4]= ( unsigned char ) ( IpAdrSvr >> ( 14+prioriotyServer ) );
		aesdata[5]= ( unsigned char ) ( ( timeVector & 0xFF000000 ) >>24 ); //GMT 1
		aesdata[6]= ( unsigned char ) ( IpAdr >> ( 13+prioriotyServer ) );
		aesdata[7]= ( unsigned char ) ( ( timeVector & 0x00FF0000 ) >>16 );// GMT 2
		aesdata[8]= ( unsigned char ) ( IpAdr >> ( 0+prioriotyServer ) );
		aesdata[9]= ( unsigned char ) ( IpAdrSvr >> ( 7+prioriotyServer ) );
		aesdata[10]= ( prioriotyServer+1 ) * 39;
		aesdata[11]= ( unsigned char ) ( IpAdr >> ( 2+prioriotyServer ) );
		aesdata[12]= ( unsigned char ) ( ( timeVector & 0x0000FF00 ) >>8 ); //GMT 3
		aesdata[13]= ( unsigned char ) ( IpAdrSvr >> ( 1+prioriotyServer ) );
		aesdata[14]= ( unsigned char ) ( ( timeVector & 0x000000FF ) );// GMT 4
		aesdata[15]= ( prioriotyServer+1 ) * 71;

                ECB_Mode<AES >::Encryption ini(aeskey, AES::DEFAULT_KEYLENGTH);
                ini.ProcessData((byte*)aesdata2, (const byte*)aesdata, 16);

                ecbEncryption = new ECB_Mode<AES >::Encryption(aesdata2, AES::DEFAULT_KEYLENGTH);

                ecbDecryption = new ECB_Mode<AES >::Decryption(aesdata2, AES::DEFAULT_KEYLENGTH);


		return true;
	}
	else
	{
		return false;
	}
}


int accessLinkInterface::decryptAES ( unsigned char *encrypt_msg, int encrypt_len,
                                      unsigned char *clear_msg, int *clear_len )
{
	int padding = 0;
	int blockNbre = encrypt_len / 16; //! How many 16byte blocks do we have?
	padding = 16 - ( encrypt_len % 16 ); //! How many padding bytes do we have to add?

	//! if the paddin is less that 16, the message is wrong
	if ( padding < 16 )
	{
		return -1;
	}


	int index = 0;
	//! uncrypt each 16 bytes blocks
	for ( int i = 0; i<blockNbre; i++ )
	{
            ecbDecryption->ProcessData( (byte*)clear_msg+index, (const byte*)encrypt_msg+index, 16);
    	    index += 16;
	}
	*clear_len = blockNbre*16;
	return SUCCESS;
}

int accessLinkInterface::encryptAES ( unsigned char *clear_msg, int clear_len,
                                      unsigned char *encrypt_msg, int *encrypt_len )
{
	int padding = 0;
	int blockNbre = clear_len / 16; //! How many 16byte blocks do we have?
	padding = 16 - ( clear_len % 16 ); //! How many padding bytes do we have to add?

	//! if the paddin is less that 16, the message is wrong
	if ( padding < 16 )
	{
		blockNbre++;
	}

	//! buffer where the encrypte message will be copy
	char *tmp_src = new char[clear_len+padding];

	Q_CHECK_PTR ( tmp_src );

	if ( tmp_src )
	{
		//! clear the encrypt buffer
		memset ( tmp_src, 0, clear_len+padding );

		//! prepare the encrypt message buffer with the clear message
		memcpy ( tmp_src, clear_msg,clear_len );

		int index = 0;
		//! encrypt 16 bytes per 16 bytes
		for ( int i = 0; i<blockNbre; i++ )
		{
                      ecbEncryption->ProcessData( (byte*)encrypt_msg+index, (const byte*)tmp_src+index, 16);
		      index += 16;
		}
		delete [] tmp_src;
		*encrypt_len = blockNbre*16;
		return SUCCESS;
	}
	else
	{
		return AES_ENCRYPT_ERROR;
	}
}

/*!
    \fn accessLinkInterface::tcpError()
 */
void accessLinkInterface::tcpError ( QAbstractSocket::SocketError error )
{
	if ( QAbstractSocket::ConnectionRefusedError == error )
	{
		if ( timerRestartConnexion == 0 & this->enabled)
			timerRestartConnexion = startTimer ( 2000 );
	}

	if ( helloSocket );
	helloSocket->deleteLater();
}

/*!
    \fn accessLinkInterface::readPendingDatagrams()
 */
void accessLinkInterface::readPendingDatagrams()
{
	char ack[2] = {0xff,0};

	int len = commSocket->pendingDatagramSize();

	if ( len<=0 ) return;

	char datagram[len];

	commSocket->readDatagram ( datagram, len, &sender, &senderPort );

        if ( timerCheckConnexion )
        {
                killTimer ( timerCheckConnexion );
                timerCheckConnexion = 0;
                timerCheckConnexion = startTimer ( 2000 );
        }

        //! we send an acknoledge to accept the message
	commSocket->writeDatagram ( ack, 2, sender, senderPort );
	commSocket->flush();


	unsigned char clear_msg[1023];
	int nlen = 0;

        //! now, we handled the message
	for ( int i=0; i<len; i++ )
	{
		long brut_len = ( unsigned char ) datagram[i];
		//! how many AES packet (16 bytes) the message contains
		unsigned int npacket = ( int ) ( brut_len / 16 ) + ( brut_len % 16 > 0 ? 1 : 0 );

		if ( SUCCESS == decryptAES ( ( unsigned char * ) datagram+i+1,npacket * 16,clear_msg,&nlen ) )
		{

			if ( clear_msg[1] == 0x01 )
				dispatch_link_msg ( ( char * ) clear_msg+2, brut_len-2 );
			else
				dispatch_interface_msg ( ( char * ) clear_msg+1, brut_len-1 );
		}
		else
		{
			return;
		}


		i += ( npacket * 16 ) + 1;
		i--;
	}

	emit sendMessage ( ( unsigned char * ) ack, 2 );
}

/*!
    \fn accessLinkInterface::dispatch_link_msg(char *msg, int len)
 */
void accessLinkInterface::dispatch_link_msg ( char *msg, int len )
{
	QByteArray send;

	for ( int i=0; i<len; i++ )
	{
		send.append ( msg[i] );
	}

	QHashIterator<int ,QObject *> i ( readerList );

	QByteArray resp;


	while ( i.hasNext() )
	{
		i.next();

		resp = ( qobject_cast<DeviceInterface *> ( i.value() ) )->sendMessageToSubDevice ( send );

		if ( resp.count() >0 )
                {
                        resp.prepend((char)1); //! link message
                        resp.prepend((char)0); //! message number not realy used
			break;
                }

	}

	if ( resp.count() >0 )
        {
            int nbreOfbytes = resp.count() / 17;
            if ( resp.count() % 17 >0 )
                    nbreOfbytes++;
    
            nbreOfbytes *=17;
    
            unsigned char msg_encrypted[nbreOfbytes];
    
            msg_encrypted[0] = resp.count();
    
            int nlen = 0;
            if ( encryptAES ( ( unsigned char* ) resp.data(),resp.count(),msg_encrypted+1, &nlen ) == SUCCESS )
            {
                    commSocket->writeDatagram ( ( char* ) msg_encrypted, nbreOfbytes, sender, senderPort );
                    commSocket->flush();
            }
        }
}


/*!
    \fn accessLinkInterface::dispatch_interface_msg(char *msg, int len)
 */
void accessLinkInterface::dispatch_interface_msg ( char *msg, int len )
{
	emit receiveMessage ( ( unsigned char* ) msg, len );

	switch ( msg[0] )
	{
		case 0x03: //receive a ack from horuxd
		{
			if ( timerCheckConnexion )
			{
				killTimer ( timerCheckConnexion );
				timerCheckConnexion = 0;
			}

			if ( !receiveFirstAck )
			{
				timerSendInfo = startTimer ( 10 );
				receiveFirstAck = true;
			}

			timerCheckConnexion = startTimer ( 2000 );
		}
		break;

		case 0x14:  //read eeprom
		{
			/*Start address (1byte) | len address(1byte) | password 8bytes */
			int startAddressEE = msg[1];
			int len = msg[2];

			char pwd[8];
			memcpy ( pwd,msg+3,8 );
			QString password ( pwd );
			if ( this->password == password )
			{
				sendEEPROM ( startAddressEE, len );
			}
			else
			{
				sendEEPROM ( startAddressEE, len, false );
			}
		}
		break;

		case 0x04: //write eeprom
		{
			/*Start address (1byte) | len address(1byte) | password 8bytes */
			int startAddressEE = msg[1];
			int len = msg[2];

			char pwd[8];
			memcpy ( pwd,msg+3,8 );
			QString password ( pwd );
			if ( this->password == password )
			{
				setEEPROM ( startAddressEE, len, ( unsigned char* ) msg+11 );
			}
			else
			{
				setEEPROM ( startAddressEE, len, ( unsigned char* ) msg+11, false );
			}
		}
		break;
		case 0x07: //output changed
		{
		  int output = msg[1];
                  QWidget *wid;
                  wid = widget->findChild<QWidget*> ( "output1" );
                  ((QLed *)wid)->setValue ( output & 1 );
                  wid = widget->findChild<QWidget*> ( "output2" );
                  ((QLed *)wid)->setValue ( output & 2 );
                  wid = widget->findChild<QWidget*> ( "output3" );
                  ((QLed *)wid)->setValue ( output & 4 );
                  wid = widget->findChild<QWidget*> ( "output4" );
                  ((QLed *)wid)->setValue ( output & 8 );
                  wid = widget->findChild<QWidget*> ( "output5" );
                  ((QLed *)wid)->setValue ( output & 16 );
                  wid = widget->findChild<QWidget*> ( "output6" );
                  ((QLed *)wid)->setValue ( output & 32 );
                  wid = widget->findChild<QWidget*> ( "output7" );
                  ((QLed *)wid)->setValue ( output & 64 );
                  wid = widget->findChild<QWidget*> ( "output8" );
                  ((QLed *)wid)->setValue ( output & 128 );
		}
		break;
	}
}

/*!
    \fn accessLinkInterface::disconnected()
 */
void accessLinkInterface::disconnected()
{
	qDebug ( "disconnected" );
}

/*!
    \fn accessLinkInterface::udpError(QAbstractSocket::SocketError)
 */
void accessLinkInterface::udpError ( QAbstractSocket::SocketError error )
{

	if ( timerRestartConnexion == 0 && this->enabled)
		timerRestartConnexion = startTimer ( 2000 );
}

/*!
    \fn accessLinkInterface::timerEvent(QTimerEvent *e)
 */
void accessLinkInterface::timerEvent ( QTimerEvent *e )
{
	if ( timerCheckReaderOnline == e->timerId() )
	{
		QHashIterator<int ,QObject *> i ( readerList );
		long EEPROMonline = widget->findChild<QLineEdit*> ( "online" )->text().toLong();

		while ( i.hasNext() )
		{
			i.next();
			bool isEnabled = ( qobject_cast<DeviceInterface *> ( i.value() ) )->isEnabled();
			QVariant address = ( qobject_cast<DeviceInterface *> ( i.value() ) )->getParameter ( "address" );

			if ( isEnabled )
			{
				long mask = 1 << ( address.toInt()-1 ) ;
				readerOnline |= mask;
			}
			else
			{
				long mask = ~ ( 1 << ( address.toInt()-1 ) );
				readerOnline &= mask;
			}

		}
		readerOnline &=EEPROMonline;

                return;
	}

	if ( timerSendInfo == e->timerId() )
	{
		killTimer ( timerSendInfo );
		timerSendInfo = 0;
		sendInterfaceInfo();
    return;
	}


	if ( timerRestartConnexion  == e->timerId() )
	{

		killTimer ( timerRestartConnexion );
		timerRestartConnexion = 0;
		setEnabled ( false );
		setEnabled ( true );
    return;
	}

	if ( timerCheckConnexion  == e->timerId() )
	{

		killTimer ( timerCheckConnexion );
		timerCheckConnexion = 0;
		if ( timerRestartConnexion == 0 && this->enabled)
			timerRestartConnexion = startTimer ( 2000 );
                return;
	}
}

/*!
    \fn accessLinkInterface::sendInterfaceInfo()
 */
void accessLinkInterface::sendInterfaceInfo()
{

	float Rt = 1000.0/ ( exp ( 4500.0* ( 1/ ( 273.15+25.0 )-1/ ( ( float ) temp+273.0 ) ) ) );

	float temp_d = ( Rt*1024.0 ) / ( Rt+1000 );

	unsigned char msg_encrypted[17];
	//                       mem, mem, tmp, tmp, ron  ron  ron  ron
	char msg[10] = {0x1,0x03,0x10,0x00,
	                ( ( int ) temp_d & 0x0000FF00 ) >>8,
	                ( int ) temp_d & 0x000000FF,
	                ( readerOnline & 0xFF000000 ) >>24,
	                ( readerOnline & 0x00FF0000 ) >>16,
	                ( readerOnline & 0x0000FF00 ) >>8,
	                readerOnline & 0x000000FF
	               };
	msg_encrypted[0] = 10;

	int nlen = 0;
	if ( encryptAES ( ( unsigned char* ) msg,10,msg_encrypted+1, &nlen ) == SUCCESS )
        {
		commSocket->writeDatagram ( ( char* ) msg_encrypted, 17, sender, senderPort );
                commSocket->flush();
        }

	timerSendInfo = startTimer ( 1000 );

	emit sendMessage ( ( unsigned char* ) msg, 10 );
}

/*!
    \fn accessLinkInterface::sendEEPROM(unsigned char start, unsigned char stop, bool isOk=true))
 */
void accessLinkInterface::sendEEPROM ( unsigned char start, unsigned char stop, bool isOk )
{
	if ( isOk && stop-start<=V2_EEP_SIZE_TOTAL )
	{
		int nbreOfbytes = ( stop - start ) / 16;
		if ( ( stop - start ) % 16 >0 )
			nbreOfbytes++;

		nbreOfbytes *=16;

		unsigned char msg_encrypted[nbreOfbytes+1];

		char msg[stop - start + 2 ];

		memcpy ( msg+2, eeprom+start, stop - start );
		msg[0] = 0;
		msg[1] = 0x14;

		msg_encrypted[0] = stop - start + 2;

		int nlen = 0;
		if ( encryptAES ( ( unsigned char* ) msg,stop - start + 2,msg_encrypted+1, &nlen ) == SUCCESS )
                {
			commSocket->writeDatagram ( ( char* ) msg_encrypted, nbreOfbytes+1, sender, senderPort );
                        commSocket->flush();
                }

		emit sendMessage ( ( unsigned char * ) msg, stop - start + 2 );
	}
	else
	{
		unsigned char msg_encrypted[17];
		char msg[4] = { 0x0, 0x14, 0x00, 0x00 };

		msg_encrypted[0] = 4;

		int nlen = 0;
		if ( encryptAES ( ( unsigned char* ) msg,4,msg_encrypted+1, &nlen ) == SUCCESS )
                {
			commSocket->writeDatagram ( ( char* ) msg_encrypted, 17, sender, senderPort ); 
                        commSocket->flush();
                }

		emit sendMessage ( ( unsigned char * ) msg, 4 );

	}
}

void accessLinkInterface::setEEPROM ( unsigned char start, unsigned char stop, unsigned  char *msg, bool isOk )
{
	if ( isOk && stop-start<=V2_EEP_SIZE_TOTAL )
	{
		memcpy ( eeprom+start, msg, stop );

		QString s;
		macaddress = s.sprintf ( "%02u:%02u:%02u:%02u:%02u:%02u",
		                         eeprom[0],
		                         eeprom[1],
		                         eeprom[2],
		                         eeprom[3],
		                         eeprom[4],
		                         eeprom[5] );
                widget->findChild<QLineEdit*> ( "mac" )->setText ( macaddress );

		ip = s.sprintf ( "%u.%u.%u.%u", eeprom[6],eeprom[7],eeprom[8],eeprom[9] );
                widget->findChild<QLineEdit*> ( "ip" )->setText ( ip );

		ipS1 = s.sprintf ( "%u.%u.%u.%u", eeprom[10],eeprom[11],eeprom[12],eeprom[13] );
                widget->findChild<QLineEdit*> ( "ipS1" )->setText ( ipS1 );

		ipS2 = s.sprintf ( "%u.%u.%u.%u", eeprom[14],eeprom[15],eeprom[16],eeprom[17] );
                widget->findChild<QLineEdit*> ( "ipS2" )->setText ( ipS2 );

		ipS3 = s.sprintf ( "%u.%u.%u.%u", eeprom[18],eeprom[19],eeprom[20],eeprom[21] );
                widget->findChild<QLineEdit*> ( "ipS3" )->setText ( ipS3 );

		mask = s.sprintf ( "%u.%u.%u.%u", eeprom[22],eeprom[23],eeprom[24],eeprom[25] );
                widget->findChild<QLineEdit*> ( "subnet" )->setText ( mask );

		helloPort = ( ( unsigned char ) eeprom[26] ) <<8;
		helloPort |= ( ( unsigned char ) eeprom[27] );
                widget->findChild<QSpinBox*> ( "helloPort" )->setValue ( helloPort );

		dataPort = ( ( unsigned char ) eeprom[28] ) <<8;
		dataPort |= ( ( unsigned char ) eeprom[29] );
                widget->findChild<QSpinBox*> ( "dataPort" )->setValue ( dataPort );

		gateway = s.sprintf ( "%u.%u.%u.%u", eeprom[30],eeprom[31],eeprom[32],eeprom[33] );
                widget->findChild<QLineEdit*> ( "gateway" )->setText ( gateway );

		sntp = s.sprintf ( "%u.%u.%u.%u", eeprom[34],eeprom[35],eeprom[36],eeprom[37] );
                widget->findChild<QLineEdit*> ( "sntp" )->setText ( sntp );

		daylight = ( unsigned char ) eeprom[38];
                widget->findChild<QSpinBox*> ( "daylight" )->setValue ( daylight );

		readerOnline = ( ( unsigned char ) eeprom[39] ) <<24;
		readerOnline |= ( ( unsigned char ) eeprom[40] ) <<16;
		readerOnline |= ( ( unsigned char ) eeprom[41] ) <<8;
		readerOnline |= ( ( unsigned char ) eeprom[42] );
                widget->findChild<QLineEdit*> ( "online" )->setText ( QString::number(readerOnline) );


		password = "";
		for ( int i=0; i<8; i++ )
		{
			if ( eeprom[43+i] != 0 )
				password += s.sprintf ( "%c",eeprom[43+i] );
		}
                widget->findChild<QLineEdit*> ( "password" )->setText ( password );


		emit deviceChanged();

		unsigned char msg_encrypted[17];
		char msg[4] = { 0x0, 0x04, start, stop };

		msg_encrypted[0] = 4;

		int nlen = 0;
		if ( encryptAES ( ( unsigned char* ) msg,4,msg_encrypted+1, &nlen ) == SUCCESS )
                {
			commSocket->writeDatagram ( ( char* ) msg_encrypted, 17, sender, senderPort );
                        commSocket->flush();
                }

		emit sendMessage ( ( unsigned char* ) msg, 4 );
	}
	else
	{
		unsigned char msg_encrypted[17];
		char msg[4] = { 0x0, 0x04, 0x00, 0x00 };

		msg_encrypted[0] = 4;

		int nlen = 0;
		if ( encryptAES ( ( unsigned char* ) msg,4,msg_encrypted+1, &nlen ) == SUCCESS )
                {
			commSocket->writeDatagram ( ( char* ) msg_encrypted, 17, sender, senderPort );
                        commSocket->flush();
                }

		emit sendMessage ( ( unsigned char* ) msg, 4 );
	}

}

void accessLinkInterface::addSubDevice ( QObject *device )
{
	readerList[ ( qobject_cast<DeviceInterface *> ( device ) )->getId() ] = device;

	connect ( device,
	          SIGNAL ( destroyed ( QObject * ) ),
	          SLOT ( deviceDestroyed ( QObject * ) )
	        );

	connect ( device,
	          SIGNAL ( sendMessage ( unsigned char*, int ) ),
	          SLOT ( readerMessage ( unsigned char*, int ) ) );
}

void accessLinkInterface::tempChanged ( int value )
{

	temp = value;
}

void accessLinkInterface::inputChanged ( int value )
{
	if ( !commSocket ) return;


	uchar inputStatus = 0;
	bool v1,v2,v3,v4,v5,v6,v7,v8;

	v1 = qFindChild<QCheckBox*> ( widget, "input1" )->checkState() == Qt::Checked;
	v2 = qFindChild<QCheckBox*> ( widget, "input2" )->checkState() == Qt::Checked;
	v3 = qFindChild<QCheckBox*> ( widget, "input3" )->checkState() == Qt::Checked;
	v4 = qFindChild<QCheckBox*> ( widget, "input4" )->checkState() == Qt::Checked;
	v5 = qFindChild<QCheckBox*> ( widget, "input5" )->checkState() == Qt::Checked;
	v6 = qFindChild<QCheckBox*> ( widget, "input6" )->checkState() == Qt::Checked;
	v7 = qFindChild<QCheckBox*> ( widget, "input7" )->checkState() == Qt::Checked;
	v8 = qFindChild<QCheckBox*> ( widget, "input8" )->checkState() == Qt::Checked;

	inputStatus |= v1 ? 1:0;
	inputStatus |= v2 ? 2:0;
	inputStatus |= v3 ? 4:0;
	inputStatus |= v4 ? 8:0;
	inputStatus |= v5 ? 16:0;
	inputStatus |= v6 ? 32:0;
	inputStatus |= v7 ? 64:0;
	inputStatus |= v8 ? 128:0;

	unsigned char msg_encrypted[17];
	char msg[3] = { 0x0, 0x08, inputStatus};

	msg_encrypted[0] = 3;

	int nlen = 0;
	if ( encryptAES ( ( unsigned char* ) msg,3,msg_encrypted+1, &nlen ) == SUCCESS )
        {
		commSocket->writeDatagram ( ( char* ) msg_encrypted, 17, sender, senderPort );
                commSocket->flush();
        }

	emit sendMessage ( ( unsigned char * ) msg, 3 );
}

void accessLinkInterface::antivandalChanged ( int value )
{
	if ( !commSocket ) return;


	uchar inputStatus = 0;

	inputStatus = qFindChild<QCheckBox*> ( widget, "antivandal" )->checkState() == Qt::Checked;

	unsigned char msg_encrypted[17];
	char msg[3] = { 0x0, 0x06, inputStatus};

	msg_encrypted[0] = 3;

	int nlen = 0;
	if ( encryptAES ( ( unsigned char* ) msg,3,msg_encrypted+1, &nlen ) == SUCCESS )
        {
	         commSocket->writeDatagram ( ( char* ) msg_encrypted, 17, sender, senderPort );
                 commSocket->flush();
        }

	emit sendMessage ( ( unsigned char * ) msg, 3 );

}

void accessLinkInterface::readerMessage ( unsigned char *msg, int len )
{
	if ( commSocket == 0 ) return;


	if ( msg[2] == 0x90 )
	{
                unsigned char msgLink[14];
                msgLink[0] = 0; //! message number, not realy used
                msgLink[1] = 1; //! link message
                memcpy(msgLink+2,msg, len);

		unsigned char msg_encrypted[17];
		msg_encrypted[0] = 14;

		int nlen = 0;
		if ( encryptAES ( ( unsigned char* ) msgLink,14,msg_encrypted+1, &nlen ) == SUCCESS )
                {
			commSocket->writeDatagram ( ( char* ) msg_encrypted, 17, sender, senderPort );
                        commSocket->flush();
                }

	}

	if ( msg[2] == 0x11 )
	{
                unsigned char msgLink[7];
                msgLink[0] = 0; //! message number, not realy used
                msgLink[1] = 1; //! link message
                memcpy(msgLink+2,msg, len);

		unsigned char msg_encrypted[17];
		msg_encrypted[0] = 6;

		int nlen = 0;
		if ( encryptAES ( ( unsigned char* ) msgLink,7,msg_encrypted+1, &nlen ) == SUCCESS )
                {
			commSocket->writeDatagram ( ( char* ) msg_encrypted, 17, sender, senderPort );
                        commSocket->flush();
                }

	}
}

QVariant accessLinkInterface::getParameter ( QString name )
{
	QVariant v;

        if ( name == "enabled" )
                v = enabled; 
	return v;
}

QDomElement accessLinkInterface::getXml()
{
	QDomDocument doc;
	QDomElement device = doc.createElement ( "device" );
	device.setAttribute ( "id", id );
	device.setAttribute ( "plugin","accessLinkInterface" );
	device.setAttribute ( "widget","AccessLinkInterface" );
	device.setAttribute ( "name",widget->findChild<QLineEdit*> ( "name" )->text() );
	device.setAttribute ( "ip",widget->findChild<QLineEdit*> ( "ip" )->text() );
	device.setAttribute ( "ipS1",widget->findChild<QLineEdit*> ( "ipS1" )->text() );
	device.setAttribute ( "ipS2",widget->findChild<QLineEdit*> ( "ipS2" )->text() );
	device.setAttribute ( "ipS3",widget->findChild<QLineEdit*> ( "ipS3" )->text() );
	device.setAttribute ( "version",widget->findChild<QLineEdit*> ( "version" )->text() );
	device.setAttribute ( "macaddress",widget->findChild<QLineEdit*> ( "mac" )->text() );
	device.setAttribute ( "mask",widget->findChild<QLineEdit*> ( "subnet" )->text() );
	device.setAttribute ( "gateway",widget->findChild<QLineEdit*> ( "gateway" )->text() );
	device.setAttribute ( "sntp",widget->findChild<QLineEdit*> ( "sntp" )->text() );
	device.setAttribute ( "password",widget->findChild<QLineEdit*> ( "password" )->text() );
	device.setAttribute ( "dataPort",widget->findChild<QSpinBox*> ( "dataPort" )->value() );
	device.setAttribute ( "helloPort",widget->findChild<QSpinBox*> ( "helloPort" )->value() );

	device.setAttribute ( "daylight",widget->findChild<QSpinBox*> ( "daylight" )->value() );
	device.setAttribute ( "temp",widget->findChild<QSpinBox*> ( "temp" )->value() );
	device.setAttribute ( "online",widget->findChild<QLineEdit*> ( "online" )->text() );

        device.setAttribute ( "isLog",widget->findChild<QCheckBox*> ( "log" )->isChecked());

	QHashIterator<int ,QObject *> i ( readerList );

	while ( i.hasNext() )
	{
		i.next();
		QDomElement element = ( qobject_cast<DeviceInterface *> ( i.value() ) )->getXml();
		device.appendChild ( element );
	}

	return device;
}

void accessLinkInterface::deviceDestroyed ( QObject * obj )
{
	QHashIterator<int ,QObject *> i ( readerList );
	while ( i.hasNext() )
	{
		i.next();
		if ( i.value() == obj )
		{
			readerList.remove ( i.key() );
			break;
		}
	}
}

bool accessLinkInterface::getIsLog()
{
  return isLog;
}

Q_EXPORT_PLUGIN2 ( libaccessLinkInterface, accessLinkInterface )
