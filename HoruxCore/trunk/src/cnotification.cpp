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
#include "cnotification.h"
#include "maiaXmlRpcClient.h"
#include <QSettings>
#include <QCoreApplication>
#include <QUrl>

CNotification::CNotification(QObject * parent ) :QObject(parent)
{
    //! set the notification service
    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

    settings.beginGroup ( "Notification" );

    if ( !settings.contains ( "host" ) ) settings.setValue ( "host", "localhost" );
    if ( !settings.contains ( "port" ) ) settings.setValue ( "port", "80" );
    if ( !settings.contains ( "webservice" ) ) settings.setValue ( "webservice", "" );

    QString host = settings.value ( "host", "localhost" ).toString();
    QString port = settings.value ( "port", "80" ).toString();
    QString webservice = settings.value ( "webservice", "" ).toString();

    settings.endGroup();

    settings.beginGroup ( "SQL" );

    username = settings.value ( "username", "root" ).toString();
    password = settings.value ( "password", "" ).toString();

    settings.endGroup();

    ptr_rpc = new MaiaXmlRpcClient(QUrl("http://" + host + ":" + port + "/" + webservice), this);

}

void CNotification::notify(QMap<QString, QVariant> params)
{
    params["username"] = username;
    params["password"] = password;
    QVariantList args;
    args << params;

    ptr_rpc->call("notification", args,
        this, SLOT(rpcNotificationResponse(QVariant &)),
        this, SLOT(rpcNotificationFault(int, const QString &)));
}

void CNotification::rpcNotificationResponse(QVariant &) {
}

void CNotification::rpcNotificationFault(int error, const QString &message) {
    qDebug() << error << " : " << message;
}
