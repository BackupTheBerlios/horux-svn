/***************************************************************************
 *   Copyright (C) 2008 by LETUX					   *
 *   info@letux.ch   							   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
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



#ifndef CHORUXMEDIA_H	
#define CHORUXMEDIA_H	

#include <QObject>
#include <QTcpSocket>
#include "cdeviceinterface.h"
#include "maiaXmlRpcClient.h"
#include "cxmlfactory.h"

class CHoruxMedia : public QObject, CDeviceInterface
{
  Q_OBJECT
  Q_INTERFACES ( CDeviceInterface )
  Q_CLASSINFO ( "Author", "Letux" );			
  Q_CLASSINFO ( "Copyright", "Letux - 2008" );		
  Q_CLASSINFO ( "Version", "0.0.0.1" );			
  Q_CLASSINFO ( "PluginName", "horux_media" );		
  Q_CLASSINFO ( "PluginType", "device" );
  Q_CLASSINFO ( "PluginDescription", "Handle a media device" );	

public:
    CHoruxMedia( QObject *parent=0);

    /*!
      Create an instance of the device
      @param query Settings of the device configured in the database
      @param parent QObject parent
      @return Return an instance of the device 
    */
    CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );

	/*!
		Connect a child device to this. This function is called by Horux Core according to devices configuration
		@param device Instance of the child. The instance is done by Horux Core
		
	*/
    void connectChild(CDeviceInterface *device);

	/*!
		Get a parameter value
		@param paramName Name of the parameter to be returned
		@return Value of the parameter
	*/
    QVariant getParameter(QString paramName);

	/*!
		Set the value of a parameter
		@param paramName Name of the parameter to be setted
		@param value Value of the parameter to be setted
	*/
    void setParameter(QString paramName, QVariant value);

	/*!
		Open the communication with the device
		@return Return true if the communication is ok else false
	*/
    bool open();

	/*!
		Close the communication with the device
	*/
    void close();

	/*!
		Allow to now if the communication with the device is opened or not
		@return Return true if the communication is opened else false
	*/
    bool isOpened();

	/*!
		Allow to cast the device instance to a QObject object
		@return Return a the QObject instance 
	*/
    QObject *getMetaObject() { return this;}

	/*!
		Get in XML format the device information
		@return Return an QDomElement of the device information
	*/
    QDomElement getDeviceInfo(QDomDocument xml_info );

public slots:
	/*!
		This slot must be called when information is receive in the communication port.
		This slot is used to handle the device protocol
		@param ba Byte received representing the communication protocol
	*/
    virtual void dispatchMessage(QByteArray ba);

	/*!
		This slot is called by an extern element (acces control by exemple) to apply an action on the device.
		Theses action could be; open a door, display a message, etc. The principle to apply an action is based on 
		the same principle of XMLRPC. For that, you have to add function in the constructor to the function handler:

		addFunction("openDoor", CMyDevice::s_openDoor);

		Of course, the function s_openDoor must be declared like this:

		protected:
    		static void s_openDoor(QObject *, QMap<QString, QVariant>);

		@param xml XML action. For the structure of the XML, check the documentation or see the exemple accesshoruxplugin.cpp, funtion insertTracking

	*/
    virtual void deviceAction(QString xml);

signals:
	/*!
		Signal which can be emitted when the device has chanched. This change can be when a key presentation happens.
		The signal emit a xml structure containing the change
		@param xmlChange Xml structure
	*/
    void deviceEvent(QString xmlEvent);
	
	/*!
		Almost devices have some input. This signal could be emitted when a input change. This can be used for the alarm plugin as exemple.
		@param deviceId Id of the device. This is is the primary key in the database
		@param in Input number
		@param status Status of the input
	*/
    void deviceInputChange(int deviceId, int in, bool status);

	/*!
		This signal is used to signal the Horux core if the device communication is closed or opened/reopened.
		@param deviceId Id of the device. This is is the primary key in the database
		@param isConnected True means that the connection is on else false
	*/
    void deviceConnection(int deviceId, bool isConnected);


protected:
	/*!
		This function allows to log in a file the communication when it receive or send data.
		@param ba byte send or received
		@param isReceive Flag to know sens of the communication. False means send and true means receive
		@param len Number of bytes contained in the buffer ba.
		
	*/
    void logComm(uchar *ba, bool isReceive, int len);

    void displayMessage(QString key);

private slots:
        void xmlrpcResponse(QVariant &);
        void xmlrpcFault(int, const QString &);

protected:
	QString ip;
	int port;
	int id_action_device ;
        MaiaXmlRpcClient *rpc;
};
#endif
 

