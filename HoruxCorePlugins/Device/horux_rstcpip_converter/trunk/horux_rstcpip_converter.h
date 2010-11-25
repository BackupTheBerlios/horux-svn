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

#ifndef CHRSTCPIPC_H
#define CHRSTCPIPC_H

#include <QObject>
#include <QTcpSocket>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"

class CHRstcpipC : public QObject, CDeviceInterface
{
  Q_OBJECT
  Q_INTERFACES ( CDeviceInterface )
  Q_CLASSINFO ( "Author", "Letux Sàrl" );
  Q_CLASSINFO ( "Copyright", "Letux Sàrl" );
  Q_CLASSINFO ( "Version", "0.0.1" );
  Q_CLASSINFO ( "PluginName", "horux_rstcpip_converter" );
  Q_CLASSINFO ( "PluginType", "device" );
  Q_CLASSINFO ( "PluginDescription", "Lecteur clavier permettant d'ouvrir une porte à l'aide d'un PIN code" );

public:
  CHRstcpipC( QObject *parent=0);
  CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );
  void connectChild(CDeviceInterface *);
  QVariant getParameter(QString paramName);
  void setParameter(QString paramName, QVariant value);
  bool open();
  void close();
  bool isOpened();
  QObject *getMetaObject() { return this;}
  QDomElement getDeviceInfo(QDomDocument xml_info );
  QTcpSocket* getSocket() {return socket;}

public slots:
  void dispatchMessage(QByteArray ba);
  void deviceAction(QString xml);
signals:
  void deviceEvent(QString xmlEvent);
  void deviceInputChange(int deviceId, int in, bool status);
  void deviceConnection(int deviceId, bool isConnected);
  void subDeviceMessage(QByteArray ba);

protected:
  void logComm(uchar *ba, bool isReceive, int len);

// specific to the device
protected:
  QString ip;
  QString port;
  QTcpSocket *socket;
  QTcpSocket* testSocket;
  QTimer *timer;
 QMap<int, CDeviceInterface*> childDevice;
  bool firstConnCheck;

protected slots:
  void deviceConnected();  // call by the socket if we are connected
  void deviceDiconnected();  // call by the socket if we are disconnected
  void deviceError( QAbstractSocket::SocketError socketError ); // call when the socket has error
  void deviceTestState( QAbstractSocket::SocketError socketError ); // call by the socket which check the state
  void readyRead(); // call when we receive data from the socket
  void checkConnection(); // call by a timer to check the connection state
};
#endif
