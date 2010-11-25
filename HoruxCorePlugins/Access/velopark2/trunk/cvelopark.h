/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
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
#ifndef VELOPARK_H
#define VELOPARK_H

#include <QObject>
#include <QtSql>
#include <QtSoapHttpTransport>

#include "caccessinterface.h"
#include "cxmlfactory.h"

#define TIME_DB_CHECKING 5000

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CVeloPark : public QObject, CAccessInterface
{
    Q_OBJECT
    Q_INTERFACES(CAccessInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2010" );
    Q_CLASSINFO ( "Version", "1.0.3" );
    Q_CLASSINFO ( "PluginName", "velopark" );
    Q_CLASSINFO ( "PluginType", "access" );
    Q_CLASSINFO ( "PluginDescription", "Handle the access for the plugin velopark" );
    Q_CLASSINFO ( "DbTableUsed", "hr_vp_parking,hr_keys,hr_tracking,hr_keys_attribution,hr_user_group_attribution,hr_user_group,hr_user_group_access,hr_vp_subscription_attribution,hr_gantner_standalone_action,hr_access_level");
    Q_CLASSINFO ( "DbTrackingTable", "hr_tracking");

public:
    CVeloPark(QObject *parent=NULL);
    bool isAccess( QMap<QString, QVariant> params, bool emitAction, bool emitNotification );
    QObject *getMetaObject() { return this;}



protected:
    bool checkAccessGantner(QMap<QString, QVariant> params);
    bool checkAccessOnline(QMap<QString, QVariant> params);
    bool checkSubDate(QDateTime start, QDateTime end);
    void initSAASMode();

signals:
    void accessAction(QString xml);
    void notification(QMap<QString, QVariant>param);

public slots:
    void deviceEvent(QString xml);
    void deviceConnectionMonitor(int, bool);
    void deviceInputMonitor ( int , int , bool );

    void test();

protected slots:
    void checkDb();

    /*!
      Read the soap response from Horux Gui
    */
    void readSoapResponse();
    void readSoapBalancesResponse();

    /*!
      Read the SSL error when doing a SOAP transaction
    */
    void soapSSLErrors ( QNetworkReply * reply, const QList<QSslError> & errors );

protected:
    void checkLastCredit(QMap<QString, QVariant> params);
    void updateUser(QMap<QString, QVariant> params, bool multiticket);
    void displayMessage(QString id, QString message);
    void accessAccepted(QMap<QString, QVariant> params, bool isAccepted);

protected:
    QMap<int, int> displayTimeTimer; //! <Timer,displayId>
    QString type;
    QTimer *timerCheckDb;
    QMap<int, bool> devices;
    QTimer *timerTest;

    QtSoapHttpTransport soapClient;

    //! saas param
    bool saas;
    QString saas_host;
    bool saas_ssl;
    QString saas_username;
    QString saas_password;
    QString saas_path;
};

#endif
