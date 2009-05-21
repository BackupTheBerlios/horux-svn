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
#include "dbmysqlplugin.h"

#include <QtCore>


bool DbMysqlPlugin::open(const QString host, 
                  const QString db, 
                  const QString username, 
                  const QString password)
{
    dbase = QSqlDatabase::addDatabase("QMYSQL");
    dbase.setHostName(host);
    dbase.setDatabaseName(db);
    dbase.setUserName(username);
    dbase.setPassword(password);
    bool result = dbase.open();
    
    if ( password == "" )
        qWarning ( "It is not recommended to have an empty password for your database" );

    if(result)
    {
      return true;
    }
    qWarning("%s",dbase.lastError().databaseText().toLatin1().constData());
    return false;
}

void DbMysqlPlugin::close()
{
  if(dbase.isOpen())
  {
    dbase.close();
  }
}

QMap<int, QString> DbMysqlPlugin::getDeviceList()
{
  QMap<int, QString> list;

  QSqlQuery query("SELECT id, type FROM hr_device");

  while (query.next()) 
  {
    int id = query.value(0).toInt();
    QString type = query.value(1).toString();
    list[id] = type;
  }

  return list;
}

int DbMysqlPlugin::getParentDevice(int deviceId)
{
  QSqlQuery query("SELECT parent_id FROM hr_device WHERE id=" + QString::number(deviceId));

  query.next();

  return query.value(0).toInt();

}

QMap<QString, QVariant> DbMysqlPlugin::getDeviceConfiguration(const int deviceId, QString type)
{
  QMap<QString, QVariant> values;

  QString sql = "SELECT d.name, d.isLog, d.accessPlugin , dt.* FROM hr_device AS d LEFT JOIN hr_" + type + " AS dt ON d.id = dt.id_device WHERE d.id="+QString::number(deviceId);

  QSqlQuery query(sql);
  query.next();

  QSqlRecord r = query.record();

  for(int i=0; i<r.count(); i++)
  {
    values[r.fieldName(i)] = r.value(i);
  }

  return values;
}

Q_EXPORT_PLUGIN2(dbmysqlplugin, DbMysqlPlugin);



QVariant DbMysqlPlugin::getConfigParam(QString paramName)
{
  QSqlQuery query("SELECT " + paramName + " FROM hr_config");

  if(query.next())
    return query.value(0);

  return 0;
}

bool DbMysqlPlugin::isXMLRPCAccess(QString username, QString password)
{
  QSqlQuery query;
  query.prepare("SELECT COUNT(*) FROM hr_superusers WHERE name=:name AND password=:password");
  query.bindValue(":name",username);
  query.bindValue(":password",password);
  query.exec();

  query.next();

  return (bool)query.value(0).toInt();
}

int DbMysqlPlugin::countNotification( QMap<QString, QVariant> params)
{
    // check if the parameter "type" is existing
    if(!params.contains("type")) return 0;

    // check if the parameter "code" is existing
    if(!params.contains("code")) return 0;


    QSqlQuery query("SELECT COUNT(*) FROM hr_notification_code AS nc LEFT JOIN hr_notification AS n ON n.id=nc.id_notification WHERE nc.type='" + params["type"].toString() + "' AND nc.code='" + params["code"].toString()  + "' AND n.id!='NULL'");

    query.next();

    return query.value(0).toInt();

}
