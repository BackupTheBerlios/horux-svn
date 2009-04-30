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
#include "accessLinkReaderRS485Plugin.h"
#include <QtGui>
#include "qled.h"
#include "accessLinkReaderRS485Widget.h"

accessLinkReaderRS485::accessLinkReaderRS485 ( QObject *parent ) : QObject ( parent )
{
}

accessLinkReaderRS485::~accessLinkReaderRS485()
{
	if ( widget )
	{
		widget->hide();
		delete widget;
		widget = 0;
	}

}

QObject *accessLinkReaderRS485::createInstance ( QObject *parent )
{
	return new accessLinkReaderRS485 ( parent );
}

void accessLinkReaderRS485::setWidget ( QWidget *widget ) 
{
  this->widget =  new AccessLinkReaderRS485Widget(widget);

  if ( this->widget->findChild<QPushButton*> ( "sendRfid" ) )
  {
          connect ( this->widget->findChild<QWidget*> ( "sendRfid" ),
                    SIGNAL ( clicked ( bool ) ),
                    SLOT ( onSendRfid() )
                  );

          connect ( this->widget,
                    SIGNAL ( sendTag ( unsigned long ) ),
                    SLOT ( onSendRfid( unsigned long ) )
                  );

  }
}

int accessLinkReaderRS485::getId()
{
	return id;
}

QString accessLinkReaderRS485::getName()
{
	return name;
}

void accessLinkReaderRS485::setId ( const int id )
{
	this->id = id;
}

void accessLinkReaderRS485::setName ( const QString name )
{
	this->name = name;
}

bool accessLinkReaderRS485::initParam ( QDomElement &element )
{
	address = element.attribute ( "rdadd" ).toInt();

        isLog = (bool)element.attribute ( "isLog" ).toInt();

        rat  = element.attribute ( "relayActiveTime" ).toInt();
        if(rat == 0) rat = 5;

	osVer = element.attribute ( "osVer" );
	if ( osVer.isEmpty() ) osVer = "603.1";

	appVer = element.attribute ( "appVer" );
	if ( appVer.isEmpty() ) appVer = "601.2";

	dbSize = ( DBSIZE ) element.attribute ( "dbSize" ).toInt();
	if ( dbSize == DBNONE ) dbSize = DB200;

	serialNumber = element.attribute ( "serialNumber" );
	if ( serialNumber.isEmpty() ) serialNumber = "4294967295";

        QDomElement rfid = element.firstChildElement("rfid");

        QWidget *wid;
        modulation = element.attribute ( "modulation" ).toInt();
        wid = widget->findChild<QWidget*> ( "modulation" );
        ( ( QLed * ) wid )->setValue ( modulation );

        antenna = element.attribute ( "antenna" ).toInt();
        wid = widget->findChild<QWidget*> ( "antenna" );
        ( ( QLed * ) wid )->setValue ( antenna );

        output1 = element.attribute ( "output1" ).toInt();
        wid = widget->findChild<QWidget*> ( "output1" );
        ( ( QLed * ) wid )->setValue ( output1 );

        output2 = element.attribute ( "output2" ).toInt();
        wid = widget->findChild<QWidget*> ( "output2" );
        ( ( QLed * ) wid )->setValue ( output2 );

        output3 = element.attribute ( "output3" ).toInt();
        wid = widget->findChild<QWidget*> ( "output3" );
        ( ( QLed * ) wid )->setValue ( output3 );

        output4 = element.attribute ( "output4" ).toInt();
        wid = widget->findChild<QWidget*> ( "output4" );
        ( ( QLed * ) wid )->setValue ( output4 );


        while( !rfid.isNull ( ) )
        {
            QString rfidNumber = rfid.text ();
            widget->findChild<QListWidget*> ( "rfidList" )->addItem(rfidNumber);
            rfid = rfid.nextSiblingElement();
            if(widget->findChild<QListWidget*> ( "rfidList" )->count() == dbSize)
              break;
        }

	widget->findChild<QLineEdit*> ( "name" )->setText ( name );
	connect ( widget->findChild<QLineEdit*> ( "name" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "serialNumber" )->setText ( serialNumber );
	connect ( widget->findChild<QLineEdit*> ( "serialNumber" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "appVer" )->setText ( appVer );
	connect ( widget->findChild<QLineEdit*> ( "appVer" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QLineEdit*> ( "osVer" )->setText ( osVer );
	connect ( widget->findChild<QLineEdit*> ( "osVer" ),SIGNAL ( textChanged ( const QString & ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QSpinBox*> ( "address" )->setValue ( address );
	connect ( widget->findChild<QSpinBox*> ( "address" ),SIGNAL ( valueChanged ( int ) ), this, SIGNAL ( deviceChanged() ) );

	widget->findChild<QSpinBox*> ( "relayActiveTime" )->setValue ( rat );
	connect ( widget->findChild<QSpinBox*> ( "relayActiveTime" ),SIGNAL ( valueChanged ( int ) ), this, SIGNAL ( deviceChanged() ) );


	if ( dbSize == DB200 )
        {
		widget->findChild<QRadioButton*> ( "db200" )->setChecked ( true );
		widget->findChild<QLabel*> ( "dbSizeLabel" )->setText ( QString("%1/%2").arg(widget->findChild<QListWidget*> ( "rfidList" )->count()).arg(200) );
        }
	else
        {
		widget->findChild<QRadioButton*> ( "db1000" )->setChecked ( true );
		widget->findChild<QLabel*> ( "dbSizeLabel" )->setText ( QString("%1/%2").arg(widget->findChild<QListWidget*> ( "rfidList" )->count()).arg(1000) );
        }

	connect ( widget->findChild<QRadioButton*> ( "db200" ),SIGNAL ( pressed () ), this, SIGNAL ( deviceChanged() ) );
	connect ( widget->findChild<QRadioButton*> ( "db1000" ),SIGNAL ( pressed () ), this, SIGNAL ( deviceChanged() ) );

        widget->findChild<QCheckBox*> ( "log" )->setChecked ( isLog );
	connect ( widget->findChild<QCheckBox*> ( "log" ),SIGNAL ( pressed () ), this, SIGNAL ( deviceChanged() ) );


	connect ( widget->findChild<QCheckBox*> ( "input1" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( inputChanged ( int ) ) );

	connect ( widget->findChild<QCheckBox*> ( "input2" ),
	          SIGNAL ( stateChanged ( int ) ), SLOT ( inputChanged ( int ) ) );

	if ( address == 0 )
		return false;

	return true;
}

void accessLinkReaderRS485::init()
{
	address = 0;
	status = 0x03;
	isLoggin = true;
	enabled = false;
        idDbOn = true;
        dbMode = HOST;
        timerDbMode = 0;
        timerRelay = 0;
        timerOutputControl1 = 0;
        timerOutputControl2 = 0;
        timerOutputControl3 = 0;
        timerOutputControl4 = 0;
}

void accessLinkReaderRS485::setEnabled ( bool enabled )
{
	this->enabled = enabled;

        if(timerDbMode >0)
        {
            killTimer(timerDbMode);
            timerDbMode = 0;
        }

        dbMode = HOST;
        widget->findChild<QComboBox*> ( "dbModeInfo" )->setCurrentIndex (dbMode);


        if(enabled)
        {
            timerDbMode = startTimer(1000);
        }
}

bool accessLinkReaderRS485::isEnabled()
{
        dbMode = HOST;
        widget->findChild<QComboBox*> ( "dbModeInfo" )->setCurrentIndex (dbMode);

        if(enabled)
        {
            if(timerDbMode > 0)
            {
                killTimer(timerDbMode);
                timerDbMode = 0;
                timerDbMode = startTimer(1000);
            }
            else
                timerDbMode = startTimer(1000);
        }

	return enabled;
}

QByteArray accessLinkReaderRS485::sendMessageToSubDevice ( QByteArray msg )
{
	QByteArray resp;
	QByteArray respEmpty;


	if ( msg.at ( 0 ) != address ) return resp;

	emit receiveMessage ( ( unsigned char* ) msg.data(), msg.length() );

	resp.append ( ( char ) address );

	switch ( ( const unsigned char ) msg.at ( 2 ) )
	{
		case 0x00: //reset
		{
                        setEnabled(false);
                        setEnabled(true);
			return respEmpty;
		}
		break;
		case 0x01: //read sofware identification
		{
			resp.append ( ( char ) 8 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) status );
			bool isOk;
			unsigned int osver = osVer.section ( ".",0,0 ).toInt ( &isOk, 16 );
			unsigned int osver_rev = osVer.section ( ".",1,1 ).toInt ( &isOk, 16 );
			unsigned int appver = appVer.section ( ".",0,0 ).toInt ( &isOk, 16 );
			unsigned int appver_rev = appVer.section ( ".",1,1 ).toInt ( &isOk, 16 );

			resp.append ( ( char ) ( ( osver & 0xFF0 ) >>4 ) );
			resp.append ( ( char ) ( ( osver & 0xF ) <<4 ) | ( osver_rev & 0xF ) );
			resp.append ( ( char ) ( ( appver & 0xFF0 ) >>4 ) );
			resp.append ( ( char ) ( ( appver & 0xF ) <<4 ) | ( appver_rev & 0xF ) );
		}
		break;
		case 0x02: //read serial number
		{
			resp.append ( ( char ) 8 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) status );

			unsigned long sn = serialNumber.toULong();

			resp.append ( ( char ) ( ( sn & 0xFF000000 ) >>24 ) );
			resp.append ( ( char ) ( ( sn & 0x00FF0000 ) >>16 ) );
			resp.append ( ( char ) ( ( sn & 0x0000FF00 ) >>8 ) );
			resp.append ( ( char ) ( ( sn & 0x000000FF ) ) );
		}
		break;
		case 0x03: //Application configuration read
		{
			//! @todo application configuration read must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x04: //EEPROM read
		{
			//! @todo EEPROM read must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x05: //reader address read
		{
			resp.append ( ( char ) 5 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) status );
			resp.append ( ( char ) address );
		}
		break;
		case 0x06: //log off
		{
                        //! implemeted but not used
			isLoggin = false;
			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) status );
		}
		break;
		case 0x07: //log on
		{
			//! @todo log on must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x08: //password change
		{
			//! @todo password change must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x09: //application configuration write
		{
			//! @todo application configuration write must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x0A: //reader serial number write
		{
			unsigned long sn = ( ( unsigned long ) msg.at ( 3 ) ) << 24 ;
			sn |= ( ( unsigned long ) msg.at ( 4 ) ) << 16 ;
			sn |= ( ( unsigned long ) msg.at ( 5 ) ) << 8 ;
			sn |= ( ( unsigned long ) msg.at ( 6 ) ) ;
			serialNumber = serialNumber.sprintf ( "%u",sn );
                        widget->findChild<QLineEdit*> ( "serialNumber" )->setText(serialNumber);
			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) status );
                        emit deviceChanged();
		}
		break;
		case 0x0B: //Date write
		{
			//! @todo date write must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x0C: //EEPROM write
		{
			//! @todo EEPROM write must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x0D: //tag selection
		case 0x0E: //tag selection memory
		{
			//! @todo tag selection memory must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x0F: //Reader address write
		{
			//! @todo Reader address write must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x10: //Tag status read
		{
			//! @todo Tag status read must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x11: //Reader status read
		{
			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) status );
		}
		break;
		case 0x12: //Output status read
		{
                        char outputStatus = 0;
                        if(output1) outputStatus |= 0x1;
                        if(output2) outputStatus |= 0x2;
                        if(output3) outputStatus |= 0x4;
                        if(output4) outputStatus |= 0x8;

                        if(!modulation) outputStatus |= 0x10;

                        //! simulate the tag detection
                        QString snStr = widget->findChild<QLineEdit*> ( "rfid" )->text();
                        if(snStr.length() == 16) outputStatus |= 0x20;

                        if(!antenna) outputStatus |= 0x40;

			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) output4 );

		}
		break;
		case 0x18: //write on the output memory
		case 0x19:  //write on the output
		{
                        bool o1,o2,o3,o4;
                        bool antenna, modulation;

                        if(timerOutputControl1)
                        {
                          killTimer(timerOutputControl1);
                          timerOutputControl1 = 0;
                        }

                        if(timerOutputControl2)
                        {
                          killTimer(timerOutputControl2);
                          timerOutputControl2 = 0;
                        }

                        if(timerOutputControl3)
                        {
                          killTimer(timerOutputControl3);
                          timerOutputControl3 = 0;
                        }

                        if(timerOutputControl4)
                        {
                          killTimer(timerOutputControl4);
                          timerOutputControl4 = 0;
                        }


                        o1 = msg.at ( 3 ) & 0x1;
                        o2 = msg.at ( 3 ) & 0x2;
                        o3 = msg.at ( 3 ) & 0x4;
                        o4 = msg.at ( 3 ) & 0x8;
                        modulation = ~ ( msg.at ( 3 ) & 0x10 );
                        antenna = ~ ( msg.at ( 3 ) & 0x40 );

                        QWidget *wid;
                        wid = widget->findChild<QWidget*> ( "output1" );
                        ( ( QLed * ) wid )->setValue ( o1 );
                        wid = widget->findChild<QWidget*> ( "output2" );
                        ( ( QLed * ) wid )->setValue ( o2 );
                        wid = widget->findChild<QWidget*> ( "output3" );
                        ( ( QLed * ) wid )->setValue ( o3 );
                        wid = widget->findChild<QWidget*> ( "output4" );
                        ( ( QLed * ) wid )->setValue ( o4 );
                        wid = widget->findChild<QWidget*> ( "antenna" );
                        ( ( QLed * ) wid )->setValue ( antenna );
                        wid = widget->findChild<QWidget*> ( "modulation" );
                        ( ( QLed * ) wid )->setValue ( modulation );

			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) status );

                        if( ( const unsigned char ) msg.at ( 2 ) == 0x18)
                        {
                          bool changed = false;
                          if(o1 != output1)
                          {
                            changed = true;
                            output1 = o1;
                          }

                          if(o2 != output2)
                          {
                            changed = true;
                            output2 = o2;
                          }

                          if(o3 != output3)
                          {
                            changed = true;
                            output3 = o3;
                          }

                          if(o4 != output4)
                          {
                            changed = true;
                            output4 = o4;
                          }

                          if(modulation != this->modulation)
                          {
                            changed = true;
                            this->modulation = modulation;
                          }

                          if(antenna != this->antenna)
                          {
                            changed = true;
                            this->antenna = antenna;
                          }
                          
                          if(changed)
                              emit deviceChanged();

                        }
		}
		break;
		case 0x1A: //Output blink frenquency
		{
			//! @todo Output blink frenquency must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x1B: //Output control write
		{
			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) status ); 

                        int duration = msg.at ( 4 );
                        select = msg.at ( 3 );

                        QWidget *wid;

                        if(select & 0x01)
                        {
                          wid = widget->findChild<QWidget*> ( "output1" );
                          bool r = ( ( QLed * ) wid )->value();

                          if(timerOutputControl1 == 0 && duration>0)
                          {
                            if(r)
                              ( ( QLed * ) wid )->setValue ( 0 );
                            else
                              ( ( QLed * ) wid )->setValue ( 1 );
                          }

                          if(timerOutputControl1 > 0 && duration==0)
                          {
                            if(r)
                              ( ( QLed * ) wid )->setValue ( 0 );
                            else
                              ( ( QLed * ) wid )->setValue ( 1 );
                          }
              
                          if(duration < 0xff )
                          {
                              if(timerOutputControl1)
                              {
                                killTimer(timerOutputControl1);
                                timerOutputControl1 = 0;
                              }

                              if(duration > 0)
                                timerOutputControl1 = startTimer(duration*100);

                          } 
                        }

                        if(select & 0x02)
                        {
                          wid = widget->findChild<QWidget*> ( "output2" );
                          bool r = ( ( QLed * ) wid )->value();
              
                          if(timerOutputControl2 == 0 && duration>0)
                          {
                            if(r)
                              ( ( QLed * ) wid )->setValue ( 0 );
                            else
                              ( ( QLed * ) wid )->setValue ( 1 );
                          }

                          if(timerOutputControl2 > 0 && duration==0)
                          {
                            if(r)
                              ( ( QLed * ) wid )->setValue ( 0 );
                            else
                              ( ( QLed * ) wid )->setValue ( 1 );
                          }


                          if(duration < 0xff )
                          {
                              if(timerOutputControl2)
                              {
                                killTimer(timerOutputControl2);
                                timerOutputControl2 = 0;
                              }

                              if(duration > 0)
                                timerOutputControl2 = startTimer(duration*100);
                          } 

                        }

                        if(select & 0x04)
                        {
                          wid = widget->findChild<QWidget*> ( "output3" );
                          bool r = ( ( QLed * ) wid )->value();
              
                          if(timerOutputControl3 == 0 && duration>0)
                          {
                            if(r)
                              ( ( QLed * ) wid )->setValue ( 0 );
                            else
                              ( ( QLed * ) wid )->setValue ( 1 );
                          }

                          if(timerOutputControl3 > 0 && duration==0)
                          {
                            if(r)
                              ( ( QLed * ) wid )->setValue ( 0 );
                            else
                              ( ( QLed * ) wid )->setValue ( 1 );
                          }


                          if(duration < 0xff )
                          {
                              if(timerOutputControl3)
                              {
                                killTimer(timerOutputControl3);
                                timerOutputControl3 = 0;
                              }


                              if(duration > 0)
                                timerOutputControl3 = startTimer(duration*100);
                          } 
                        }

                        if(select & 0x08)
                        {
                          wid = widget->findChild<QWidget*> ( "output4" );
                          bool r = ( ( QLed * ) wid )->value();
              
                          if(timerOutputControl4 == 0 && duration>0)
                          {
                            if(r)
                              ( ( QLed * ) wid )->setValue ( 0 );
                            else
                              ( ( QLed * ) wid )->setValue ( 1 );
                          }

                          if(timerOutputControl4 > 0 && duration==0)
                          {
                            if(r)
                              ( ( QLed * ) wid )->setValue ( 0 );
                            else
                              ( ( QLed * ) wid )->setValue ( 1 );
                          }

                          if(duration < 0xff )
                          {
                              if(timerOutputControl4)
                              {
                                killTimer(timerOutputControl4);
                                timerOutputControl4 = 0;
                              }

                              if(duration > 0)
                                timerOutputControl4 = startTimer(duration*100);
                          } 
                        }


		}
		break;
		case 0x80: //Erase database
		{
			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) 1 ); //ack

			QListWidget *wid = widget->findChild<QListWidget*> ( "rfidList" );
			wid->clear();

                        if ( dbSize == DB200 )
                        {
                                widget->findChild<QLabel*> ( "dbSizeLabel" )->setText ( QString("%1/%2").arg(widget->findChild<QListWidget*> ( "rfidList" )->count()).arg(200) );
                        }
                        else
                        {
                                widget->findChild<QLabel*> ( "dbSizeLabel" )->setText ( QString("%1/%2").arg(widget->findChild<QListWidget*> ( "rfidList" )->count()).arg(1000) );
                        }

                        emit deviceChanged();
		}
		break;
		case 0x81: //Tag accreditation
		{
                        //! send first a wait response like in the real harwdare
                        unsigned char wait[5] = {address, 4, 0x81, 0x02, 0x87} ;
                        emit sendMessage ( wait,5 );

			QListWidget *wid = widget->findChild<QListWidget*> ( "rfidList" );

			unsigned long snMSB = 0;
			unsigned long snLSB = 0;

                        unsigned char n1[4] ={
                                             msg.at ( 3 ),
                                             msg.at ( 4 ),
                                             msg.at ( 5 ),
                                             msg.at ( 6 ),
                                            };

			snMSB = n1[0] << 24;
			snMSB |= n1[1] << 16; 
			snMSB |= n1[2] << 8;
			snMSB |= n1[3];


                        unsigned char n0[4] ={
                                             msg.at ( 7 ),
                                             msg.at ( 8 ),
                                             msg.at ( 9 ),
                                             msg.at ( 10 ),
                                            };
			snLSB = n0[0] << 24;
			snLSB |= n0[1] << 16; 
			snLSB |= n0[2] << 8;
			snLSB |= n0[3];

			QString s_snMSB, s_snLSB;

			s_snMSB = QString::number(snMSB,16).rightJustified(8,'0');
			s_snLSB = QString::number(snLSB,16).rightJustified(8,'0');


			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode

			if ( wid->count() < ( long ) dbSize )
			{
                                QList<QListWidgetItem *> list = wid->findItems(s_snMSB+s_snLSB,Qt::MatchExactly);

                                if(list.size() == 0)
    				    wid->addItem ( s_snMSB+s_snLSB );
				resp.append ( ( char ) 1 ); //ack
                                emit deviceChanged();
			}
			else
				resp.append ( ( char ) 0 ); //ack

                        if ( dbSize == DB200 )
                        {
                                widget->findChild<QLabel*> ( "dbSizeLabel" )->setText ( QString("%1/%2").arg(widget->findChild<QListWidget*> ( "rfidList" )->count()).arg(200) );
                        }
                        else
                        {
                                widget->findChild<QLabel*> ( "dbSizeLabel" )->setText ( QString("%1/%2").arg(widget->findChild<QListWidget*> ( "rfidList" )->count()).arg(1000) );
                        }

		}
		break;
		case 0x82: //Tag desaccreditation
		{
                        //! send first a wait response like in the real harwdare
                        unsigned char wait[5] = {address, 4, 0x82, 0x02, 0x84} ;
                        emit sendMessage ( wait,5 );

			QListWidget *wid = widget->findChild<QListWidget*> ( "rfidList" );

			unsigned long snMSB = 0;
			unsigned long snLSB = 0;

                        unsigned char n1[4] ={
                                             msg.at ( 3 ),
                                             msg.at ( 4 ),
                                             msg.at ( 5 ),
                                             msg.at ( 6 ),
                                            };

			snMSB = n1[0] << 24;
			snMSB |= n1[1] << 16; 
			snMSB |= n1[2] << 8;
			snMSB |= n1[3];


                        unsigned char n0[4] ={
                                             msg.at ( 7 ),
                                             msg.at ( 8 ),
                                             msg.at ( 9 ),
                                             msg.at ( 10 ),
                                            };
			snLSB = n0[0] << 24;
			snLSB |= n0[1] << 16; 
			snLSB |= n0[2] << 8;
			snLSB |= n0[3];

			QString s_snMSB, s_snLSB;

			s_snMSB = QString::number(snMSB,16).rightJustified(8,'0');
			s_snLSB = QString::number(snLSB,16).rightJustified(8,'0');

			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode

			if ( wid->count() > 0 )
			{
				for ( int i = 0; i < wid->count(); ++i )
				{
					if ( wid->item ( i )->text() == s_snMSB+s_snLSB )
					{
						wid->takeItem ( i );

					}
				}

				resp.append ( ( char ) 1 ); //ack
                                emit deviceChanged();
			}
			else
				resp.append ( ( char ) 0 ); //ack

                        if ( dbSize == DB200 )
                        {
                                widget->findChild<QLabel*> ( "dbSizeLabel" )->setText ( QString("%1/%2").arg(widget->findChild<QListWidget*> ( "rfidList" )->count()).arg(200) );
                        }
                        else
                        {
                                widget->findChild<QLabel*> ( "dbSizeLabel" )->setText ( QString("%1/%2").arg(widget->findChild<QListWidget*> ( "rfidList" )->count()).arg(1000) );
                        }

		}
		break;
		case 0x84: //PIN code programmation
		{
			//! @todo PIN code programmation must be implemented
			qDebug ( "Cmd %X not implemented", msg.at ( 2 ) );
                        return respEmpty;
		}
		break;
		case 0x85:  //IFS commande (lcd, eeprom, keyboard, rtc)
		{
			switch ( ( const unsigned char ) msg.at ( 3 ) )
			{
				case 0x01:    // get the configured sub device
					resp.append ( ( char ) 6 ); //length
					resp.append ( msg.at ( 2 ) ); //opcode
					resp.append ( msg.at ( 3 ) ); //IFS sub opcode
					resp.append ( ( char ) 0x01 ); // ACK
					resp.append ( ( char ) 0x00 ); //! @todo implement IFS subdevice for access link read
					break;
			}
		}
		break;
		case 0x88: //Access authorization
		{
			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) 1 ); //Ack
                        timerRelay = startTimer(rat * 100);
                        QWidget *wid;
                        wid = widget->findChild<QWidget*> ( "output1" );
                        ( ( QLed * ) wid )->setValue ( true );

		}
		break;
		case 0x89: //Database stand-alone control
		{
			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) 1 ); //Ack
                        dbMode = (DBMODE)msg.at ( 3 );
                        widget->findChild<QComboBox*> ( "dbModeInfo" )->setCurrentIndex (dbMode);
		}
		break;
		case 0x8A: //Relay active time memory
                {
			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) 1 ); //Ack
                        rat = msg.at ( 3 );
                        widget->findChild<QSpinBox*> ( "relayActiveTime" )->setValue(rat);
                        emit deviceChanged();
                }
                break;
		case 0x8B: //Relay active time
		{
			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) 1 ); //Ack
                        rat = msg.at ( 3 );
		}
		break;
		case 0x8E: //Database size
		{
			resp.append ( ( char ) 6 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) 1 ); //Ack

			QListWidget *wid = widget->findChild<QListWidget*> ( "rfidList" );
			int size = wid->count();

			resp.append ( ( char ) ( ( size & 0xFF00 ) >>8 ) );
			resp.append ( ( char ) ( size & 0xFF ) );
		}
		break;
		case 0x8F: //Database off
		{
                        idDbOn = false;
                        QLed *led = widget->findChild<QLed*> ( "dbStatusLed" );
                        led->setValue(false);
                        QLabel *label = widget->findChild<QLabel*> ( "dbStatus" );
                        label->setText(tr("Off"));

			resp.append ( ( char ) 4 ); //length
			resp.append ( msg.at ( 2 ) ); //opcode
			resp.append ( ( char ) 1 ); //Ack
		}
		break;
		default:
		{
			qDebug ( "Don't know about the message %X", msg.at ( 2 ) );
                        return respEmpty;
		}
	}

	unsigned char checksum = resp.at ( 1 );

	for ( int i=2; i<resp.count(); i++ )
		checksum ^= resp.at ( i );

	resp.append ( ( char ) checksum );

	emit sendMessage ( ( unsigned char* ) resp.data(),resp.count() );


	return resp;
}


void accessLinkReaderRS485::onSendRfid(unsigned long key)
{

        unsigned long snMSB = 0;
        unsigned long snLSB = 0;
        QString snStr;

        if(key == 0)
        {
          snStr = widget->findChild<QLineEdit*> ( "rfid" )->text();
          snStr = snStr.rightJustified(16, '0');
        
          bool ok;
	  snMSB = snStr.left(8).toULong(&ok, 16);
	  snLSB = snStr.right(8).toULong(&ok, 16);
        }
        else
        {
           snStr = QString::number(key);
           snLSB = key; 
        }

	if ( snLSB>0 )
	{
		unsigned char msg[12] =
		{
			address,
			0x0b, //len
			0x90, //opcode
			( snMSB & 0xFF000000 ) >> 24,
			( snMSB & 0x00FF0000 ) >> 16,
			( snMSB & 0x0000FF00 ) >> 8,
			( snMSB & 0x000000FF ),
			( snLSB & 0xFF000000 ) >> 24,
			( snLSB & 0x00FF0000 ) >> 16,
			( snLSB & 0x0000FF00 ) >> 8,
			( snLSB & 0x000000FF ),
			0x00 //checksum
		};

		unsigned char checksum = msg[1];

		for ( int i=2; i<11; i++ )
			checksum ^= msg[i];

		msg[11] = checksum;

		emit sendMessage ( msg,12 );
        
                if(dbMode == STANDALONE)
                {
                    QList<QListWidgetItem*> r = widget->findChild<QListWidget*> ( "rfidList" )->findItems(snStr,Qt::MatchExactly);
                    if(r.count()>0)
                    {
                      QWidget *wid;
                      wid = widget->findChild<QWidget*> ( "output1" );
                      ( ( QLed * ) wid )->setValue ( true );
        
                      wid = widget->findChild<QWidget*> ( "output2" );
                      ( ( QLed * ) wid )->setValue ( true );
        
                      timerRelay = startTimer(rat * 100);
                    }
                }
        }
}

QVariant accessLinkReaderRS485::getParameter ( QString name )
{
	QVariant v;

	if ( name == "address" )
		v = address;
        if ( name == "enabled" )
                v = enabled; 
	return v;
}

QDomElement accessLinkReaderRS485::getXml()
{
	QDomDocument doc;
	QDomElement device = doc.createElement ( "device" );
	device.setAttribute ( "id", id );
	device.setAttribute ( "plugin","accessLinkReaderRS485" );
	device.setAttribute ( "widget","AccessLinkReaderRS485" );
	device.setAttribute ( "name",widget->findChild<QLineEdit*> ( "name" )->text() );
	device.setAttribute ( "rdadd",widget->findChild<QSpinBox*> ( "address" )->value() );

        device.setAttribute ( "isLog",widget->findChild<QCheckBox*> ( "log" )->isChecked());

	device.setAttribute ( "relayActiveTime",widget->findChild<QSpinBox*> ( "relayActiveTime" )->value() );


	device.setAttribute ( "osVer",widget->findChild<QLineEdit*> ( "osVer" )->text() );
	device.setAttribute ( "appVer",widget->findChild<QLineEdit*> ( "appVer" )->text() );
	if ( widget->findChild<QRadioButton*> ( "db200" )->isChecked() )
		device.setAttribute ( "dbSize",200 );
	else
		device.setAttribute ( "dbSize",1000 );
	device.setAttribute ( "serialNumber",widget->findChild<QLineEdit*> ( "serialNumber" )->text() );

        QWidget *wid;
        wid = widget->findChild<QWidget*> ( "output1" );
        device.setAttribute ( "output1",output1);

        wid = widget->findChild<QWidget*> ( "output2" );
        device.setAttribute ( "output2",output2);

        wid = widget->findChild<QWidget*> ( "output3" );
        device.setAttribute ( "output3",output3);

        wid = widget->findChild<QWidget*> ( "output4" );
        device.setAttribute ( "output4",output4);

        wid = widget->findChild<QWidget*> ( "antenna" );
        device.setAttribute ( "antenna",antenna);

        wid = widget->findChild<QWidget*> ( "modulation" );
        device.setAttribute ( "modulation",modulation);

        for(int i=0; i<widget->findChild<QListWidget*> ( "rfidList" )->count(); i++)
        {
            QDomElement rfid = doc.createElement ( "rfid" );
            QListWidgetItem *item = widget->findChild<QListWidget*> ( "rfidList" )->item(i);
            QDomText number = doc.createTextNode(item->text());
            rfid.appendChild(number);
            device.appendChild(rfid);
        }

	return device;
}

void accessLinkReaderRS485::timerEvent ( QTimerEvent *e )
{
    if(timerOutputControl1 == e->timerId())
    {
          killTimer(timerOutputControl1);
          timerOutputControl1 = 0;
          QWidget *wid;

          wid = widget->findChild<QWidget*> ( "output1" );
          bool r = ( ( QLed * ) wid )->value();

          if(r)
          {
            ( ( QLed * ) wid )->setValue ( 0 );
            output1 = 0;
          }
          else
          {
            ( ( QLed * ) wid )->setValue ( 1 );
            output1 = 1;
          }
          return;

    }

    if(timerOutputControl2 == e->timerId())
    {
          killTimer(timerOutputControl2);
          timerOutputControl2 = 0;
          QWidget *wid;

            wid = widget->findChild<QWidget*> ( "output2" );

            bool r = ( ( QLed * ) wid )->value();

            if(r)
            {
              ( ( QLed * ) wid )->setValue ( 0 );
              output2 = 0;
            }
            else
            {
              ( ( QLed * ) wid )->setValue ( 1 );
              output2 = 1;
            }
          return;

    }

    if(timerOutputControl3 == e->timerId())
    {
          killTimer(timerOutputControl3);
          timerOutputControl3 = 0;
          QWidget *wid;

            wid = widget->findChild<QWidget*> ( "output3" );
            bool r = ( ( QLed * ) wid )->value();

            if(r)
            {
              ( ( QLed * ) wid )->setValue ( 0 );
              output3 = 0;
            }
            else
            {
              ( ( QLed * ) wid )->setValue ( 1 );
              output3 = 1;
            }
          return;
    }

    if(timerOutputControl4 == e->timerId())
    {
          killTimer(timerOutputControl4);
          timerOutputControl4 = 0;
          QWidget *wid;

            wid = widget->findChild<QWidget*> ( "output4" );
            bool r = ( ( QLed * ) wid )->value();

            if(r)
            {
              ( ( QLed * ) wid )->setValue ( 0 );
              output4 = 0;
            }
            else
            {
              ( ( QLed * ) wid )->setValue ( 1 );
              output4 = 1;
            }

          return;
    }

    if(timerDbMode == e->timerId())
    {
        killTimer(timerDbMode);
        timerDbMode = 0;
        dbMode = STANDALONE;
        widget->findChild<QComboBox*> ( "dbModeInfo" )->setCurrentIndex (dbMode);
          return;
    }

    if(timerRelay == e->timerId())
    {
        killTimer(timerRelay);
        timerRelay = 0;
        QWidget *wid;
        wid = widget->findChild<QWidget*> ( "output1" );
        ( ( QLed * ) wid )->setValue ( false );
      
        return;
    }
}

bool accessLinkReaderRS485::getIsLog()
{
  return isLog;
}

void accessLinkReaderRS485::inputChanged ( int value )
{
    bool v1,v2;

    v1 = qFindChild<QCheckBox*> ( widget, "input1" )->checkState() == Qt::Checked;
    v2 = qFindChild<QCheckBox*> ( widget, "input2" )->checkState() == Qt::Checked;

    if(v1)
      status &= 0xFE;
    else
      status |= 0x01;

    if(v2)
      status &= 0xFD;
    else
      status |= 0x02;

    unsigned char msg[5] = {address, 4, 0x11, status, 4^0x11^status};

    emit sendMessage(msg, 5);
}

Q_EXPORT_PLUGIN2 ( libaccessLinkReaderRS485, accessLinkReaderRS485 )

