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
#include "cserver.h"
#include <QHostAddress>
#include <QSettings>
#include <QSqlQuery>
#include <QTcpSocket>
#include <QCoreApplication>

CServer *CServer::pThis = NULL;

CServer::CServer(QObject *parent) : QTcpServer(parent)
{
}


CServer::~CServer()
{
  CServer::pThis = NULL;
}

CServer* CServer::getInstance()
{
  if(pThis)
    return pThis;
  else
  {
    pThis = new CServer();
    return pThis;
  }
}


/*!
    \fn CServer::start()
 */
bool CServer::start()
{
    if( !isListening() )
    {
        QSettings settings(QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat);
        settings.beginGroup("AccessLinkInterface");
        int hello_port = settings.value("helloPort", 6998).toInt();
        if(!settings.contains("helloPort")) settings.setValue("helloPort", 6998);
        settings.endGroup();

        connect(this, SIGNAL(newConnection()), SLOT(newInternfaceConnection()));

        return listen( QHostAddress::Any,hello_port); 
    }

    return true;
}


/*!
    \fn CServer::newIternfaceConnection()
 */
void CServer::newInternfaceConnection()
{
    QTcpSocket *socket = nextPendingConnection () ;

    //! filter the connection 
    QSqlQuery query("SELECT COUNT(ip) FROM hr_accessLink_Interface WHERE ip='" + socket->peerAddress().toString() + "'");

    query.next();

    if(query.value(0).toInt()>0)
      emit newConnection(socket);
    else
    {
      socket->close();
      socket->deleteLater();
    }
}
