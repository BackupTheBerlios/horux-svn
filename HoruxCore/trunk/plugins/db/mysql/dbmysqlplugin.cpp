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

bool DbMysqlPlugin::loadSchema ( const QString host,
                    const QString db,
                    const QString username,
                    const QString password,
                    const QString queries)
{

    dbase = QSqlDatabase::addDatabase("QMYSQL");
    dbase.setHostName(host);
    dbase.setUserName(username);
    dbase.setPassword(password);

    bool result = dbase.open();

    if(!result)
    {
        qDebug() << "Cannot open the db connection";
        return false;
    }

    //create the database
    QSqlQuery createDb;

    if(createDb.exec("CREATE DATABASE " + db))
    {
        qDebug() << "Database created";

        dbase.close();
        dbase.setDatabaseName(db);
        dbase.open();
        //create the table from queries

        QStringList queryList = queries.split("\n\n");

        foreach(QString q, queryList)
        {
            if(!q.isEmpty())
            {
                QSqlQuery createTable;
                if(!createTable.exec(q))
                {
                    QSqlQuery drop;
                    drop.exec("DROP DATABASE " + db);
                    return false;
                }
            }
        }

        return true;
    }

    return false;
}

bool DbMysqlPlugin::loadData ( const QString queries )
{
    if(dbase.isOpen())
    {
        QStringList queryList = queries.split("\n\n");

        foreach(QString q, queryList)
        {
            if(!q.isEmpty())
            {
                QSqlQuery createTable;
                if(!createTable.exec(q))
                {
                    qWarning() << createTable.lastError().databaseText();
                    return false;
                }
            }
        }

        return true;
    }

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

  if(query.next())
      return query.value(0).toInt();
  else
      return -1;

}

QMap<QString, QVariant> DbMysqlPlugin::getDeviceConfiguration(const int deviceId, QString type)
{
  QMap<QString, QVariant> values;

  QString sql = "SELECT d.name, d.isLog, d.accessPlugin, d.isActive, dt.* FROM hr_device AS d LEFT JOIN hr_" + type + " AS dt ON d.id = dt.id_device WHERE d.id="+QString::number(deviceId);

  QSqlQuery query(sql);
  if(!query.next())
      return values;

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
   if(!dbase.isOpen())
       return 0;

  QSqlQuery query("SELECT " + paramName + " FROM hr_config");

  if(query.next())
    return query.value(0);

  return "unknow parameter";
}

bool DbMysqlPlugin::isXMLRPCAccess(QString username, QString password)
{
  QSqlQuery query;
  query.prepare("SELECT COUNT(*) FROM hr_superusers AS su LEFT JOIN hr_superuser_group as sug ON su.group_id = sug.id  WHERE su.name=:name AND su.password=:password AND sug.webservice=1");
  query.bindValue(":name",username);
  query.bindValue(":password",password);
  query.exec();

  query.next();

  return (bool)query.value(0).toInt();
}
