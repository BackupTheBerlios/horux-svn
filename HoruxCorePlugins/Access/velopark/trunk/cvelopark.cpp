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
#include "cvelopark.h"
#include <QCoreApplication>
#include <QtCore>
#include <QtXml>


void CVeloPark::deviceEvent(QMap<QString, QVariant> params)
{
    QString key = params["key"].toString();
    QString deviceId = params["deviceId"].toString();

    //! get the access plugin name used for the key
    QString sql = "SELECT accessPlugin FROM hr_keys AS k LEFT JOIN hr_keys_attribution AS ka ON ka.id_key=k.id LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=ka.id_user LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE serialNumber='" + key + "' AND accessPlugin!='NULL' AND accessPlugin!=''";
  
    QSqlQuery query(sql);

    int index = metaObject()->indexOfClassInfo ( "PluginName" ); 

    if(query.next() && query.value(0).toString() != metaObject()->classInfo ( index ).value()) return;


	//! we check first if the key has access with the standard access control
	if(accessInterfaces.contains("access_horux")) 
	{
		if(accessInterfaces["access_horux"]->isAccess(params, false))
		{
			acceptAccess(params, isAccess(params, true));
				
		}
        else
        {
          acceptAccess(params, false);
        }
	}
	else
		qDebug("The access plugin velopark depend on the access plugin access_horux");
}

bool CVeloPark::isAccess(QMap<QString, QVariant> params, bool emitAction)
{
  return checkAccess(params, emitAction);
}


void CVeloPark::acceptAccess(QMap<QString, QVariant> params, bool isOk)
{
  if(isOk)
  {
    QString xml = "<deviceAction id=\"" + params["deviceId"].toString()  + "\">";
    xml += "<action>";
    xml += "<function>openDoor</function>";
    xml += "<params>";
    xml += "<param>";
    xml += "<name>";
    xml += "isAccess";
    xml += "</name>";
    xml += "<value>";
    xml += "1";
    xml += "</value>";
    xml += "</param>";

    xml += "<param>";
    xml += "<name>";
    xml += "key";
    xml += "</name>";
    xml += "<value>";
    xml += params["key"].toString();
    xml += "</value>";
    xml += "</param>";

	int index = metaObject()->indexOfClassInfo ( "PluginName" );

    if ( index != -1 )
		{
			xml += "<param>";
			xml += "<name>";
			xml += "PluginName";
			xml += "</name>";
			xml += "<value>";
			xml += metaObject()->classInfo ( index ).value(); 
			xml += "</value>";
			xml += "</param>";
		}

    xml += "</params>";
    xml += "</action>";
    xml += "</deviceAction>";


	emit accessAction(xml);	

    //!display the message in the display
    displayMessage("ok",params["deviceId"].toString());

   
    //! Set the new status of the light information
    setLightStatus(params["deviceId"].toString(), params["key"].toString());
  }
  else
  {
    QString xml = "<deviceAction id=\"" + params["deviceId"].toString()  + "\">";
    xml += "<action>";
    xml += "<function>openDoor</function>";
    xml += "<params>";
    xml += "<param>";
    xml += "<name>";
    xml += "isAccess";
    xml += "</name>";
    xml += "<value>";
    xml += "0";
    xml += "</value>";
    xml += "</param>";

    xml += "<param>";
    xml += "<name>";
    xml += "key";
    xml += "</name>";
    xml += "<value>";
    xml += params["key"].toString();
    xml += "</value>";
    xml += "</param>";


	int index = metaObject()->indexOfClassInfo ( "PluginName" );

    if ( index != -1 )
		{
			xml += "<param>";
			xml += "<name>";
			xml += "PluginName";
			xml += "</name>";
			xml += "<value>";
			xml += metaObject()->classInfo ( index ).value(); 
			xml += "</value>";
			xml += "</param>";
		}

    xml += "</params>";
    xml += "</action>";
    xml += "</deviceAction>";

   	emit accessAction(xml);

    //!display the message in the display
    displayMessage("ko",params["deviceId"].toString());

  }

  QCoreApplication::processEvents();

  QString isAccessStr;
  QString keyId = "1";
  QString userId = "1";
 
  if(isOk)
    isAccessStr = "1";
  else
    isAccessStr = "0";


	//! try to find the keyId according to the serialNumber
	QSqlQuery query("SELECT id FROM hr_keys WHERE serialNumber='" + params["key"].toString() + "'");
	
	if(query.next())
		keyId = query.value(0).toString();


    //! try to find the userId according to the serialNumber
    query = "SELECT u.id FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id WHERE ka.id_key=" + keyId;

    if(query.next())
      userId = query.value(0).toString();

	query = "INSERT INTO `hr_tracking` ( `id` , `id_user` , `id_key` , `time` , `date` , `id_entry` , `is_access` , `id_comment`, `key` ) VALUES ('', '" +
					userId +
					"','" +
					keyId +
					"', CURTIME(), CURDATE(), '" +
					params["deviceId"].toString() +
					"', '" +
					isAccessStr +
					"', '" +
					"10" +
					"', '" +
					params["key"].toString() +
					"')";
}

bool CVeloPark::checkAccess(QMap<QString, QVariant> params, bool emitAction)
{
 	
	//! be sure that the key must be checked by this plugin. This means that the user group must be set to be ckecked by this plugin

	QString sql="SELECT accessPlugin, uga.id_user FROM hr_keys AS k LEFT JOIN hr_keys_attribution AS ka ON ka.id_key=k.id LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=ka.id_user LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE serialNumber='" + params["key"].toString() + "' AND accessPlugin='velopark'";
	
	QSqlQuery query(sql);

	if(query.next())
	{
		QString sub = "SELECT * FROM hr_vp_subscription_attribution WHERE status='started' AND user_id="+query.value(1).toString();
		QSqlQuery querySub(sub);

		if(querySub.next())
		{
			//! check the validity of the current subscription
			if(checkSubDate( querySub.value(6).toDateTime(), querySub.value(7).toDateTime() ) )
			{
				//! accept access
				return true;
			}
			else
			{
				//! do we have credit of used (multiple ticket)
				if(querySub.value(5).toInt() == 1)
				{
					//! set the sub as finished 
					QSqlQuery update("UPDATE hr_vp_subscription_attribution SET credit=0, status='finished' WHERE id="+querySub.value(0).toString());
				}
				else
				{
					QSqlQuery sub("SELECT * FROM hr_vp_subscription WHERE id=" + querySub.value(2).toString());
					sub.next();

					QString validity = sub.value(3).toString();
					int year = validity.section(":",0,0).toInt();
					int month = validity.section(":",1,1).toInt();
					int day = validity.section(":",2,2).toInt();
					int hour = validity.section(":",3,3).toInt();

					QDateTime start = QDateTime::currentDateTime();
					QDateTime end = start;
					end = end.addYears(year); 
					end = end.addMonths(month); 
					end = end.addDays(day); 
					end = end.addSecs(hour*3600); 

					QString startStr = start.toString("yyyy-MM-dd hh:mm:ss");
					QString endStr = end.toString("yyyy-MM-dd hh:mm:ss");

					//! credit -1 and new start/end date
					QSqlQuery update("UPDATE hr_vp_subscription_attribution SET credit=credit-1, start='" + startStr + "', end='" + endStr + "' WHERE id="+querySub.value(0).toString());
				}

				//! try for an other sub
				return checkAccess(params, emitAction);
			}
		}
		else
		{
			//! check if a subscription not start is existing
			QString sub2 = "SELECT * FROM hr_vp_subscription_attribution WHERE status='not_start' AND user_id="+query.value(1).toString();
			QSqlQuery querySub2(sub2);

			if(querySub2.next())
			{
				//! we have one, set it as the new subscription
				QSqlQuery sub("SELECT * FROM hr_vp_subscription WHERE id=" + querySub2.value(2).toString());
				sub.next();

				QString validity = sub.value(3).toString();
				int year = validity.section(":",0,0).toInt();
				int month = validity.section(":",1,1).toInt();
				int day = validity.section(":",2,2).toInt();
				int hour = validity.section(":",3,3).toInt();

				QDateTime start = QDateTime::currentDateTime();
				QDateTime end = start;
				end = end.addYears(year); 
				end = end.addMonths(month); 
				end = end.addDays(day); 
				end = end.addSecs(hour*3600); 

				QString startStr = start.toString("yyyy-MM-dd hh:mm:ss");
				QString endStr = end.toString("yyyy-MM-dd hh:mm:ss");

				//! credit -1 and new start/end date
				QSqlQuery update("UPDATE hr_vp_subscription_attribution SET status='started', start='" + startStr + "', end='" + endStr + "' WHERE id="+querySub2.value(0).toString());


				//! retry with this new one
				return checkAccess(params, emitAction);
				
			}		
			else
				return false;

		}
		
	}

	//! no access
	return false;
}

bool CVeloPark::checkSubDate(QDateTime start, QDateTime end)
{
	if(start <= QDateTime::currentDateTime() &&  QDateTime::currentDateTime() <= end)
		return true;
	
	return false;
}

void CVeloPark::displayMessage(QString type, QString deviceId)
{
  QSqlQuery query("SELECT * FROM hr_vp_parking WHERE accesspoint_id=" + deviceId);

  if(query.next())
  {
    //! do we have a display device defined
    if(query.value(2).toInt() > 0 )
    {
      QString msg = "";
      if(type == "default") msg = query.value(3).toString();
      if(type == "ok") msg = query.value(4).toString();
      if(type == "ko") msg = query.value(5).toString();

      if(type != "default")
        displayTimeTimer[startTimer(query.value(6).toInt() * 1000)] = deviceId.toInt();

      QString xml = "<deviceAction id=\"" + query.value(2).toString()  + "\">";
      xml += "<action>";
      xml += "<function>displayMessage</function>";
      xml += "<params>";
      xml += "<param>";
      xml += "<name>";
      xml += "message";
      xml += "</name>";
      xml += "<value>";
      xml += msg;
      xml += "</value>";
      xml += "</param>";
  
  
      int index = metaObject()->indexOfClassInfo ( "PluginName" );
  
      if ( index != -1 )
          {
              xml += "<param>";
              xml += "<name>";
              xml += "PluginName";
              xml += "</name>";
              xml += "<value>";
              xml += metaObject()->classInfo ( index ).value(); 
              xml += "</value>";
              xml += "</param>";
          }
  
      xml += "</params>";
      xml += "</action>";
      xml += "</deviceAction>";
  
      emit accessAction(xml);
    }
  }
}

void CVeloPark::setLightStatus(QString deviceId, QString key)
{
  QSqlQuery query("SELECT count(*) FROM `hr_tracking` WHERE `hr_tracking`.key='"+key+"' AND is_access=1 AND `id_entry`="+deviceId);

  query.next();

  if(key != "")
  {
    if((query.value(0).toInt() +1) % 2 == 0 )
    {
      //exit
      QSqlQuery query("UPDATE hr_vp_parking SET filling=filling-1 WHERE accesspoint_id=" + deviceId);
    }
    else
    {
      //entry
      QSqlQuery query("UPDATE hr_vp_parking SET filling=filling+1 WHERE accesspoint_id=" + deviceId);
    }
  }

}

void CVeloPark::timerEvent(QTimerEvent *e)
{
  if( displayTimeTimer.contains(e->timerId()) )
  {
    int deviceId = displayTimeTimer[e->timerId()];
    displayTimeTimer.remove(e->timerId());
    killTimer(e->timerId());
  
    displayMessage("default", QString::number(deviceId));
  }
}

//! Q_EXPORT_PLUGIN2(TARGET, CLASSNAME);
Q_EXPORT_PLUGIN2(velopark, CVeloPark);
