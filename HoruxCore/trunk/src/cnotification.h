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
#ifndef CNOTIFICATION_H
#define CNOTIFICATION_H

#include <QVariantList>
#include <QDebug>
#include <QObject>

class MaiaXmlRpcClient;

class CNotification : public QObject
{
    Q_OBJECT

public:
    CNotification( QObject * parent = 0);
    void notify(QMap<QString, QVariant> params);

private slots:

    /*!
        Positive response after a notification
    */
    void rpcNotificationResponse(QVariant &arg);

    /*!
        Fault response after a notification
    */
    void rpcNotificationFault(int error, const QString &message);


private:

    //! xmlrpc client
    MaiaXmlRpcClient *ptr_rpc;

    QString username;
    QString password;

};

#endif // CNOTIFICATION_H
