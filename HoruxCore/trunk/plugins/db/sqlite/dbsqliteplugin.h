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

#ifndef CDBMYSQLPLUGIN_H
#define CDBMYSQLPLUGIN_H

#include <QtSql>
#include <QObject>


#include "cdbinterface.h"


class DbSqlitePlugin : public QObject, CDbInterface
{
    Q_OBJECT
    Q_INTERFACES(CDbInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2008" );
    Q_CLASSINFO ( "Version", "0.0.1" );
    Q_CLASSINFO ( "PluginName", "horux_sqlite_db" );
    Q_CLASSINFO ( "PluginType", "db" );
    Q_CLASSINFO ( "PluginDescription", "Handle a Sqlite database for Horux Core" );

 public:
    bool open(const QString host,
                      const QString db,
                      const QString username,
                      const QString password);

    void close();

    QMap<int, QString> getDeviceList();

    int getParentDevice(int deviceId);

    QMap<QString, QVariant> getDeviceConfiguration(const int deviceId, QString type);

    QObject *getMetaObject() { return this;}

    QVariant getConfigParam(QString paramName);

    bool isXMLRPCAccess(QString username, QString password);

    int countNotification( QMap<QString, QVariant> params);

protected:
  QSqlDatabase dbase;

 };

#endif
