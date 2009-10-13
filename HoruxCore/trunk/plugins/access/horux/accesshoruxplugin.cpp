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
#include "accesshoruxplugin.h"
#include <QtCore>
#include <QtXml>

AccessHoruxPlugin::AccessHoruxPlugin(QObject *parent) : QObject(parent)
{
    
    timerFreeAccess = startTimer(10000);
}

void AccessHoruxPlugin::timerEvent(QTimerEvent *e)
{
    // check the free access
    if(timerFreeAccess == e->timerId())
    {
      if(timerFreeAccess)
      {
        killTimer(timerFreeAccess);
        timerFreeAccess = 0;
        checkFreeAccess();
        //! check each one minute the open time access
        timerFreeAccess = startTimer(60000);
      }
    }
}

void AccessHoruxPlugin::deviceConnectionMonitor(int deviceId, bool status)
{

  if(!status)
  {
    if(currentFreeAccess.contains(QString::number(deviceId)))
    {
      currentFreeAccess.removeOne(QString::number(deviceId));
    }
  }
}

void AccessHoruxPlugin::checkFreeAccess()
{
  QDate now(QDate::currentDate());
  int today = now.dayOfWeek();

  QSqlQuery query("SELECT * FROM hr_openTime_attribution");

  
  while(query.next()) 
  {
      QString isFreeAccess = "1";
      QString entryId = query.value(1).toString(); 
      QString otId = query.value(2).toString(); 

      QSqlQuery query_ot("SELECT * FROM hr_openTime WHERE id=" + otId);

      bool freeAccessFounded = false;

      while(query_ot.next() && !freeAccessFounded)
      {
        bool nonWorkingDay = query_ot.value(2).toBool();
        bool weekEnd = query_ot.value(3).toBool();
        QDate validityDateStart = query_ot.value(4).toDate();
        QDate validityDateEnd = query_ot.value(5).toDate();
        bool mondayDefault = query_ot.value(6).toBool();

        //! step 1 check if applicable during the week-end
        if((today == 6 || today == 7) && !weekEnd)   {
          continue;
        }

        //! step 2 check for the non working day
        if(!nonWorkingDay)
        {
          QSqlQuery query_nwd = "SELECT * FROM hr_non_working_day AS nwd WHERE nwd.from<='" + now.toString("yyyy-MM-dd") + "' AND nwd.until>='" + now.toString("yyyy-MM-dd") + "'";
          if(query_nwd.next())   {
            continue;
          }
        }

        //! step 3 check if the free level is still valid
        if(validityDateStart.isValid() && validityDateEnd.isValid())
        {
          if(now < validityDateStart || now > validityDateEnd)
          {
            continue;
          }
        }
        
        if(validityDateStart.isValid() && validityDateEnd.isNull())
        {
          if(validityDateStart > now )
          {
            continue;
          }
        }
        
        if(validityDateStart.isNull() && validityDateEnd.isValid())
        {
          if(now > validityDateEnd )
          {
            continue;
          }
        }

        QTime time = QTime::currentTime();
        QString timeEnMinuteStr = QString::number(time.minute() + time.hour()*60);
        QString todayStr;
      
        //! Monday default means that all days will have the same access as Monday
        if(mondayDefault)
          today = 1;
      
        switch(today)
        {
          case 1: todayStr = "lundi"; break;
          case 2: todayStr = "mardi"; break;
          case 3: todayStr = "mercredi"; break;
          case 4: todayStr = "jeudi"; break;
          case 5: todayStr = "vendredi"; break;
          case 6: todayStr = "samedi"; break;
          case 7: todayStr = "dimanche"; break;
        }
      
        QSqlQuery query_t = "SELECT * FROM hr_openTime_time AS ot WHERE ot.day='" +
                        todayStr +
                        "' AND ot.id_openTime=" +
                        otId +
                        " AND ot.from<=" +
                        timeEnMinuteStr +
                        " AND ot.until>=" +
                        timeEnMinuteStr;
      
        if(!query_t.next())     
        {
            continue;
        }

        freeAccessFounded = true;
      }


      QString sendValue = "";
      //! do we need to send the action to the reader
      if(freeAccessFounded)
      {
        if(!currentFreeAccess.contains(entryId))
        {
            currentFreeAccess << entryId;
            sendValue = "1";
        }
      }
      else
      {
        if(currentFreeAccess.contains(entryId))
        {
          currentFreeAccess.removeOne(entryId);
          sendValue = "0";
        }

      }

      if(sendValue != "")
      {

        QMap<QString, QString> param;

        param["freeAccess"] = sendValue;

        int index = metaObject()->indexOfClassInfo ( "PluginName" );

        if ( index != -1 )
        {
            param["PluginName"] = metaObject()->classInfo ( index ).value();
        }

        QString xml = CXmlFactory::deviceAction(entryId, "openDoor", param);

        emit accessAction(xml);
      }
  }
}

void AccessHoruxPlugin::deviceEvent(QString xml)
{
    QDomDocument doc;
    doc.setContent ( xml );

    QDomElement root = doc.documentElement();

    QDomNode node = root.firstChild();

    //! check if it is a device event
    if ( root.tagName() != "deviceEvent" )
    {
        return;
    }

    QString deviceId = root.attribute ( "id" );

    QMap<QString, QVariant>funcParams;

    QDomNode eventNode = root.firstChild();

    QDomElement event = eventNode.toElement();

    //! check if the request contain the tag "event"
    if ( event.tagName() == "event" )
    {

        funcParams["event"] = event.text();

        //! check if the request is not empty
        if ( event.text() != "" )
        {

            eventNode = eventNode.nextSibling();

            QDomElement params = eventNode.toElement();

            QDomNode paramsNode = params.firstChild();
            while ( !paramsNode.isNull() )
            {
                QDomElement params = paramsNode.toElement();

                if ( params.tagName() == "param" )
                {
                    QString pName;
                    QVariant pValue;
                    QDomNode p = params.firstChild();
                    if ( p.toElement().tagName() == "name" )
                    {
                        pName = p.toElement().text();
                        p = p.nextSibling();
                        if ( p.toElement().tagName() == "value" )
                        {
                            pValue = p.toElement().text();
                            funcParams[pName] = pValue;

                        }
                    }
                }

                paramsNode = paramsNode.nextSibling();
            }

            funcParams["deviceId"] = deviceId;
        }
        else
            return;
    }
    else
        return;

    bool mustBeCheck = false;

    //! Check if the device must be checked by a specfic access plugin
    QString pName = "";
    int index = metaObject()->indexOfClassInfo ( "PluginName" );

    if ( index != -1 )
        pName = metaObject()->classInfo ( index ).value();

    //! if specified in the xml deviceEvent
    if ( funcParams.contains ( "AccessPluginName" ) )
    {
        if ( funcParams["AccessPluginName"] == "" || funcParams["AccessPluginName"] == pName )
            mustBeCheck = true;
    }
    else
        mustBeCheck = true;


    if ( !mustBeCheck )
    {
        return;
    }


    //! handle only the key detection
    if(event.text() != "keyDetected")
        return;

    QString key = funcParams["key"].toString();

    //! get the access plugin name used for the key
    QString sql = "SELECT accessPlugin FROM hr_keys AS k LEFT JOIN hr_keys_attribution AS ka ON ka.id_key=k.id LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=ka.id_user LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE serialNumber='" + key + "' AND accessPlugin!='NULL' AND accessPlugin!=''";
  
    QSqlQuery query(sql);

    if(query.next() && query.value(0).toString() != metaObject()->classInfo ( index ).value()) return;

    isAccess(funcParams, true);
}

bool AccessHoruxPlugin::isAccess(QMap<QString, QVariant> params, bool emitAction)
{
  return checkAccess(params, emitAction);
}

bool AccessHoruxPlugin::checkAccess(QMap<QString, QVariant> params, bool emitAction)
{
  if(params.contains("key") && params.contains("deviceId"))
  {
    QString key = params["key"].toString();
    QString deviceId = params["deviceId"].toString();


    //! Step 1 is key exists
    QSqlQuery query("SELECT COUNT(*) FROM hr_keys WHERE serialNumber='" + key + "'");
    query.next();

    if(query.value(0).toInt() == 0)
    {
     	insertTracking("1", "1", deviceId, KEY_UNKNOW_BY_APP, false, key,emitAction);
      return false;
    }

    //! Step 2 is the key blocked
    query = "SELECT COUNT(*) FROM hr_keys WHERE serialNumber='" + key + "' AND isBlocked=0";
    query.next();

    if(query.value(0).toInt() == 0)
    {
      insertTracking("1", "1", deviceId, KEY_BLOCKED, false, key,emitAction);
      return false;
    }

    //! Step 3 is the key attributed
    query = "SELECT COUNT(*) FROM hr_keys_attribution AS ka LEFT JOIN hr_keys AS k ON k.id=ka.id_key WHERE k.serialNumber='" + key + "'";
    query.next();

    if(query.value(0).toInt() == 0)
    {
    	insertTracking("1", "1", deviceId, KEY_NOT_ATTRIBUTED, false, key,emitAction);
      return false;
    }

    //! Step 4 is the user attribution is blocked
    query = "SELECT u.id AS uid, k.id AS kid, k.serialNumber FROM hr_keys_attribution AS ka LEFT JOIN hr_keys AS k ON k.id=ka.id_key LEFT JOIN hr_user AS u ON ka.id_user=u.id WHERE k.serialNumber='" + key + "' AND u.isBlocked=0";

    if(!query.next())
    {

     	insertTracking("1", "1", deviceId, USER_BLOCKED, false, key,emitAction);
      return false;
    }

    QString userId = query.value(0).toString(); 
    QString keyId = query.value(1).toString();
    QString keyNumber = query.value(2).toString(); 

    //! Step 5 is the user belong to one or more group
    query = "SELECT id_group  FROM hr_user_group_attribution WHERE id_user=" + userId;

    if(!query.next())
    {
    	insertTracking(userId, keyId, deviceId, USER_HAS_NO_GROUP, false, key,emitAction);
      return false;
    }

    QString reason = "0"; 

    QString groupId = query.value(0).toString();

		//! check if this group must be handle by this plugin
		query = "SELECT accessPlugin FROM hr_user_group WHERE id=" + groupId;
		QString plName = "";
		if(query.next())
		{
			plName = query.value(0).toString();
		}

		if(plName == "access_horux" || plName == "" || !emitAction)
		{
			do
			{
				//! Step 6 check for all groups if there is an access level defined
	
				if(checkAccessLevel(groupId, deviceId, &reason))
				{
						//! we can send an order to the device to open the door-lock
						insertTracking(userId, keyId, deviceId, ACCESS_OK, true, key,emitAction);
						return true;
				}
				
			}
			while(query.next());

	   	insertTracking(userId, keyId, deviceId, reason, false, key,emitAction);
		}	
  }
	
	return false;
}

bool AccessHoruxPlugin::checkAccessLevel(QString groupId, QString deviceId, QString *reason)
{
  QSqlQuery query("SELECT id_access_level FROM hr_user_group_access WHERE id_group=" + groupId + " AND id_device=" + deviceId);

  if(!query.next())  
  {
    *reason = GROUP_HAS_NO_ACCESS_LEVEL;
    return false;
  }

  QString id_access_level = query.value(0).toString();


  query = "SELECT * FROM hr_access_level WHERE id=" + id_access_level;

  if(!query.next()) 
  {
    *reason = GROUP_HAS_NO_ACCESS_LEVEL; 
    return false;
  }

  bool fullAccess = query.value(2).toBool();
  bool nonWorkingDay = query.value(3).toBool();
  bool weekEnd = query.value(4).toBool();
  QDate validityDateStart = query.value(5).toDate();
  QDate validityDateEnd = query.value(6).toDate();
  bool mondayDefault = query.value(7).toBool();

  //! step 1 check for full access
  if(fullAccess)  return true;

  QDate now(QDate::currentDate());
  int today = now.dayOfWeek();

  //! step 2 check if the person has access during the week-end
  if((today == 6 || today == 7) && !weekEnd)   {
    *reason = BLOCK_DURING_THE_WEEK_END; 
    return false;
  }


  //! step 3 check for the non working day
  if(!nonWorkingDay)
  {
    query = "SELECT * FROM hr_non_working_day AS nwd WHERE nwd.from<='" + now.toString("yyyy-MM-dd") + "' AND nwd.until>='" + now.toString("yyyy-MM-dd") + "'";

    if(query.next())   {
      *reason = BLOCK_DURING_NON_WORK_DAY; 
      return false;
    }
  }


  //! step 4 check if the access level is still valid
  if(validityDateStart.isValid() && validityDateEnd.isValid())
  {
    if(now < validityDateStart || now > validityDateEnd)
    {
      *reason = VALIDITY_DATE_OUT;
      return false;
    }
  }
  
  if(validityDateStart.isValid() && validityDateEnd.isNull())
  {
    if(validityDateStart > now )
    {
      *reason = VALIDITY_DATE_OUT;
      return false;
    }
  }
  
  if(validityDateStart.isNull() && validityDateEnd.isValid())
  {
    if(now > validityDateEnd )
    {
      *reason = VALIDITY_DATE_OUT;
      return false;
    }
  }

  //! step 5 Check the time area

  QTime time = QTime::currentTime();
  QString timeEnMinuteStr = QString::number(time.minute() + time.hour()*60);
  QString todayStr;

  //! Monday default means that all days will have the same access as Monday
  if(mondayDefault)
    today = 1;

  switch(today)
  {
    case 1: todayStr = "lundi"; break;
    case 2: todayStr = "mardi"; break;
    case 3: todayStr = "mercredi"; break;
    case 4: todayStr = "jeudi"; break;
    case 5: todayStr = "vendredi"; break;
    case 6: todayStr = "samedi"; break;
    case 7: todayStr = "dimanche"; break;
  }

  query = "SELECT * FROM hr_access_time AS at WHERE at.day='" +
                  todayStr +
                  "' AND at.id_access_level=" +
                  id_access_level +
                  " AND at.from<=" +
                  timeEnMinuteStr +
                  " AND at.until>=" +
                  timeEnMinuteStr;

  if(!query.next())     {
      *reason = TIME_BLOCK; 
      return false;
  }

  return true;

}

void AccessHoruxPlugin::insertTracking(QString userId, QString keyId, QString entryId, QString reason, bool isAccess, QString serialNumber, bool emitAction)
{

  if(isAccess)
  {
    if(emitAction)
    {
        QMap<QString, QString> param;

        param["isAccess"] = "1";
        param["key"] = serialNumber;

        int index = metaObject()->indexOfClassInfo ( "PluginName" );

        if ( index != -1 )
        {
            param["PluginName"] = metaObject()->classInfo ( index ).value();
        }

        QString xml = CXmlFactory::deviceAction(entryId, "openDoor", param);

        emit accessAction(xml);
    }
  }
  else
  {
    if(emitAction)
    {
       QMap<QString, QString> param;

       param["isAccess"] = "0";
       param["key"] = serialNumber;

       int index = metaObject()->indexOfClassInfo ( "PluginName" );

       if ( index != -1 )
       {
           param["PluginName"] = metaObject()->classInfo ( index ).value();
       }

       QString xml = CXmlFactory::deviceAction(entryId, "openDoor", param);

       emit accessAction(xml);
    }
  }


  QString isAccessStr;

  if(isAccess)
    isAccessStr = "1";
  else
    isAccessStr = "0";


  if(keyId == "1")
  {
    //! try to find the keyId according to the serialNumber
    QSqlQuery query("SELECT id FROM hr_keys WHERE serialNumber='" + serialNumber + "'");

    if(query.next())
      keyId = query.value(0).toString();
  }


  if(userId == "1" && keyId != "1")
  {
    //! try to find the userId according to the serialNumber
    QSqlQuery query("SELECT u.id FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id WHERE ka.id_key=" + keyId);

    if(query.next())
      userId = query.value(0).toString();

  }

  if(reason == KEY_BLOCKED && userId != "1")
  {
    QString xml = CXmlFactory::accessAlarm( userId, "1100", "The key is currently blocked");
  
    emit accessAction(xml);
  }

  if(reason == USER_BLOCKED && userId != "1")
  {
    QString xml = CXmlFactory::accessAlarm( userId, "1101", "The user is currently blocked");

    emit accessAction(xml);
  }

  if(emitAction)
  {
        QSqlQuery query("INSERT INTO `hr_tracking` ( `id` , `id_user` , `id_key` , `time` , `date` , `id_entry` , `is_access` , `id_comment`, `key` ) VALUES ('', '" +
                    userId +
                    "','" +
                    keyId +
                    "', CURTIME(), CURDATE(), '" +
                    entryId +
                    "', '" +
                    isAccessStr +
                    "', '" +
                    reason +
                    "', '" +
                    serialNumber +
                    "')"
                    );

        QMap<QString, QVariant> p;
        p["type"] = "ACCESS"; //ALARM, LOG, CUSTOM
        p["code"] = reason;
        p["userId"] = userId;
        p["serialNumber"] = serialNumber;
        p["entryId"] = entryId;
                        
        emit notification(p);
    }
}

Q_EXPORT_PLUGIN2(horuxaccessplugin, AccessHoruxPlugin);
