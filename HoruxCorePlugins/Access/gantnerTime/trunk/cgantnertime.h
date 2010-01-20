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
#ifndef GANTNERTIME_H
#define GANTNERTIME_H

#include <QObject>
#include <QtSql>
#include <QtSoapHttpTransport>

#include "caccessinterface.h"
#include "cxmlfactory.h"

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CGantnerTime : public QObject, CAccessInterface
{
    Q_OBJECT
    Q_INTERFACES(CAccessInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2008" );
    Q_CLASSINFO ( "Version", "0.0.1" );
    Q_CLASSINFO ( "PluginName", "gantnertime" );
    Q_CLASSINFO ( "PluginType", "access" );
    Q_CLASSINFO ( "PluginDescription", "Handle the Gantner time unit GAT p.time ST X80 / ST X81 / ST X90" );
    Q_CLASSINFO ( "DbTableUsed", "hr_keys,hr_tracking,hr_timux_config,hr_timux_booking,hr_user,hr_user_group,hr_user_group_attribution,hr_user_group_access,hr_device,hr_gantner_standalone_action,hr_keys_attribution,hr_gantner_TimeTerminal,hr_timux_timecode,hr_timux_activity_counter");
    Q_CLASSINFO ( "DbTrackingTable", "hr_timux_booking,hr_tracking");

public:
    CGantnerTime(QObject *parent=NULL);
    void deviceEvent(QMap<QString, QVariant> params);
    bool isAccess(QMap<QString, QVariant> params, bool emitAction);
    QObject *getMetaObject() { return this;}

public slots:
    void deviceEvent(QString xml);
    void deviceConnectionMonitor(int, bool);
    void deviceInputMonitor ( int , int , bool );

protected:
    void reloadAllData();
    void initSAASMode();

protected slots:
    void checkDb();

    /*!
      Read the soap response from Horux Gui
    */
    void readSoapResponse();

    /*!
      Read the SSL error when doing a SOAP transaction
    */
    void soapSSLErrors ( QNetworkReply * reply, const QList<QSslError> & errors );

signals:
  void accessAction(QString xml);
  void notification(QMap<QString, QVariant>param);

private:
  QTimer *timerCheckDb;
  QMap<int, bool> devices;
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
