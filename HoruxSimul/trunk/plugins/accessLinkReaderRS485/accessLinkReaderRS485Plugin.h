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
#ifndef ACCESSLINKREADERRS485_H
#define ACCESSLINKREADERRS485_H

#include <QObject>
#include <QByteArray>
#include <QVariant>

#include "deviceInterface.h"


//!  Qt plugin device of the access link reader RS485
/*!
  Access link RFID reader

  @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
  @version 0.1
  @date    2008

  history:
  28.02.2008  First implementation
*/

class accessLinkReaderRS485: public QObject, public DeviceInterface
{
		Q_OBJECT
		Q_INTERFACES ( DeviceInterface )
		Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
		Q_CLASSINFO ( "Copyright", "Letux - 2008" );
		Q_CLASSINFO ( "Version", "0.1" );
		Q_CLASSINFO ( "PluginName", "accessLinkReaderRS485" );
		Q_CLASSINFO ( "PluginType", "device" );


	private:
		enum DBSIZE {DBNONE=0,DB200=200,DB1000=1000};
                enum DBMODE {STANDALONE = 1, HOST = 0};
	public:
		accessLinkReaderRS485 ( QObject *parent=0 );
		virtual ~accessLinkReaderRS485();
		virtual QObject *createInstance ( QObject *parent=0 );
		virtual int getId();
		virtual QString getName();
		virtual void setId ( const int id );
		virtual void setName ( const QString name );
		virtual bool initParam ( QDomElement &element );
		virtual void init();
		virtual void setEnabled ( bool enabled );
		virtual bool isEnabled();
		virtual void addSubDevice ( QObject * ) {};
		virtual QByteArray sendMessageToSubDevice ( QByteArray );
		virtual void setWidget ( QWidget *widget );
		virtual QWidget *  getWidget() {return widget;};
		virtual QVariant getParameter ( QString name );
		virtual QDomElement getXml();
                virtual bool getIsLog();
	signals:
		virtual void sendMessage ( unsigned char * msg, int len );
		virtual void receiveMessage ( unsigned char * message, int len );
		virtual void deviceChanged();

	private slots:
		void onSendRfid(unsigned long key=0);
                void inputChanged ( int value );

	protected:
		//! This event handler can be reimplemented in a subclass to receive timer events for the object.
		/*!
		  QTimer provides a higher-level interface to the timer functionality, and also more general information about timers. The timer event is passed in the event parameter.

		  \sa QObject::timerEvent(QTimerEvent *e)
		*/
		void timerEvent ( QTimerEvent *e );

	private:
		//! Reader address on the bus 485
		int address;
                
                //! Reader status
		int status; 

		QString appVer;
		QString osVer;
		DBSIZE dbSize;
                bool idDbOn;
		QString serialNumber;
		bool isLoggin;
                int rat;
                DBMODE dbMode;
                int timerDbMode;
                int timerRelay;
                int timerOutputControl1;
                int timerOutputControl2;
                int timerOutputControl3;
                int timerOutputControl4;
                int select;
                bool output1;
                bool output2;
                bool output3;
                bool output4;
                bool antenna;
                bool modulation;
};

#endif
