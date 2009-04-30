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

#ifndef INTERFACES_H
#define INTERFACES_H

#include <QtPlugin>
#include <QByteArray>
#include <QDomElement>
#include <QVariant>

//!  Qt plugin device interface
/*!
  This is the interface for device used to develop Horux Simul plugin device

  @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
  @version 0.1
  @date    2008

  history:
  28.02.2008  First implementation
*/

class DeviceInterface
{
	public:
		//! Allow to create multiple instance
		/*!
		  This function is a pure virtual function.
		*/
		virtual QObject * createInstance ( QObject *parent=0 ) = 0;

		//! Virtual destructor.
		/*!
		  Destructor of the plugin
		*/
		virtual ~DeviceInterface() {}

		//! Get the device id
		/*!
		  This function is a pure virtual function. The id is define in the
		  xml site definition id="X"
		  \return return the device id
		*/
		virtual int getId() = 0;

		//! Get the device name
		/*!
		  This function is a pure virtual function. The name is define in the
		  xml site definition name="X"
		  \return return the device name
		*/
		virtual QString getName() = 0;


		//! Set the device id
		/*!
		  This function is a pure virtual function. The id come from the
		  xml site definition name="X"
		  \param id Id of the device
		*/
		virtual void setId ( const int id ) = 0;

		//! Set the device name
		/*!
		  This function is a pure virtual function. The name come from the
		  xml site definition name="X"
		  \param id Name of the device
		*/
		virtual void setName ( const QString name ) = 0;

		//! Init all parameters device
		/*!
		  This function is a pure virtual function. These paramters are define in
		  the site xml file definition
		  \param  element QDomElement containing all attributes for the device
		  \return Return true if all necessary attribute are defined else false
		*/
		virtual bool initParam ( QDomElement &element ) = 0;

		//! Enabled or desabled the device
		/*!
		  This function is a pure virtual function. According to the paramter, the device is connected or unconnected.
		  \param  enabled enabled (true) or disabled(false) the device
		*/

		virtual void setEnabled ( bool enabled ) = 0;

		//! Get the enabled status
		/*!
		  This function is a pure virtual function. Allow to know if the device is connected or unconnected
		  \return  return the connexion status
		*/
		virtual bool isEnabled() = 0;

		//! Init the device
		/*!
		  This function is a pure virtual function. This init function initialise the device
		*/
		virtual void init() = 0;

		//! Add a sub device to this device
		/*!
		  Several hardware need to manage sub device. This function allow to add  the subdevice
		*/
		virtual void addSubDevice ( QObject *device ) = 0;

		//! Send a message to the device
		/*!
		  When the device is a sub device, this one will receive message for it's parent.
		  \param msg Message sended to the device
		  \return Return the response of the device
		*/
		virtual QByteArray sendMessageToSubDevice ( QByteArray msg ) = 0;


		//! Set the widget link to this device
		/*!
		  A widget plugin musst be exist for the device
		  \param widget Widget of the device
		*/
		virtual void setWidget ( QWidget *widget ) = 0;

		//! Get the widget link to this device
		/*!
		  A widget plugin musst be exist for the device
		  \return return the widget
		*/
		virtual QWidget * getWidget() = 0;

		//! Get a parameter of the device
		/*!
		  \param name Name of the paramater
		  \return Return a the value of the parameter in a QVariant
		*/
		virtual QVariant getParameter ( QString name ) = 0;

		//! Get the xml config of the element
		virtual QDomElement getXml() =0;

                //! Get true if the device musst be logged
                virtual bool getIsLog() = 0;

	signals:
		//! Emit the signal when a message is sended to the Horux server
		/*!
		  This signal is a pure virtual function. This signal emited the byte array of the sended message
		  \param msg byte array of the message
		  \param len message length
		*/
		virtual void sendMessage ( unsigned char *msg, int len ) = 0;

		//! Emit the signal when a message is received from the Horux server
		/*!
		  This signal is a pure virtual function. This signal emited the byte array of the received message
		  \param msg byte array of the message
		  \param len message length
		*/
		virtual void receiveMessage ( unsigned char *message, int len ) = 0;

		//! Emit the signal when the device parameters changed
		/*!
		  This signal is a pure virtual function. This signal inform that it's paramaters changed
		*/
		virtual void deviceChanged() = 0;

	protected:
		//! Id of the device
		/*!
		  Each devices has a unique id.
		*/
		int id;

		//! Name of the device
		/*!
		*/
		QString name;

		//! Device status
		/*!
		  This status inform if the device is connected or unconnected
		*/
		bool enabled;

		//! Widget allowing to manage the device
		QWidget *widget;


                //! Allow to log it's message
                bool isLog;
};

Q_DECLARE_INTERFACE ( DeviceInterface,
                      "horux.simul.PlugDevice.DeviceInterface/1.0" )

#endif
