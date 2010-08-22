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

CVeloPark::CVeloPark(QObject *parent) : QObject(parent)
{
    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

    settings.beginGroup ( "Velopark" );

    if ( !settings.contains ( "type" ) ) settings.setValue ( "type", "gantner" );
    type = settings.value ( "saas", "gantner" ).toString();

    if(type == "gantner")
    {
        timerCheckDb = new QTimer(this);
        connect(timerCheckDb, SIGNAL(timeout()), this, SLOT(checkDb()));
        timerCheckDb->start(TIME_DB_CHECKING);
    }
}

void CVeloPark::deviceEvent(QString xml)
{
    QMap<QString, QVariant>params = CXmlFactory::deviceEvent(xml) ;

    QString event = params["event"].toString();



    if(type != "gantner")
    {
        //! handle only the key detection
        if(event != "keyDetected")
            return;

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
                    if(accessInterfaces["access_horux"]->isAccess(params, false, false))
                    {
                            acceptAccess(params, isAccess(params, true, true));

                    }
            else
            {
              acceptAccess(params, false);
            }
            }
            else
                    qDebug("The access plugin velopark depend on the access plugin access_horux");

    }
    else
    {
        //! handle only the key detection
        if(event != "Gantner_AccessTerminal_accessDetected" && event != "Gantner_AccessTerminal_accessDetectedBeforeBooking")
            return;

        // check if the user musst be checked by this plugin
        QString sqlUser="SELECT accessPlugin, uga.id_user FROM hr_keys AS k LEFT JOIN hr_keys_attribution AS ka ON ka.id_key=k.id LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=ka.id_user LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE serialNumber='" + params["key"].toString() + "'  AND accessPlugin='velopark'";

        QSqlQuery queryUser(sqlUser);

        //if not return
        if(!queryUser.next()) {
            QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
            while(queryMessage.next())
            {
                QString ids = queryMessage.value(7).toString();
                QStringList idsList= ids.split(',');

                if(idsList.contains(params["deviceId"].toString()))
                {
                    displayMessage(params, queryMessage.value(3).toString());
                }
            }
            return;
        }

        QString isAccessStr = "";
        if(isAccess(params, false, false))
        {
            isAccessStr = "1";
        }
        else
        {
            //display the no access message
            isAccessStr = "0";

            // display the non access message on the specific device
            QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
            while(queryMessage.next())
            {
                QString ids = queryMessage.value(7).toString();
                QStringList idsList= ids.split(',');

                if(idsList.contains(params["deviceId"].toString()))
                {
                    displayMessage(params, queryMessage.value(4).toString());
                }
            }

        }

        // do not insert in the tracking, this will be done when we receive the booking
        if(event == "Gantner_AccessTerminal_accessDetectedBeforeBooking")
            return;

        // maybe the user was deleted during a device reinitialisation
        if(params["userId"].toString() == "0")
        {
            params["userId"] = queryUser.value(1).toString();
        }

        // get the key id
        QString keyId = "1";
        QSqlQuery queryKey("SELECT id FROM hr_keys WHERE serialNumber='" + params["key"].toString() + "'");

        if(queryKey.next())
                keyId = queryKey.value(0).toString();


        // insert in the tracking
        QString query = "INSERT INTO `hr_tracking` (  `id_user` , `id_key` , `time` , `date` , `id_entry` , `is_access` , `id_comment`, `key` ) VALUES ('" +
                                        params["userId"].toString() +
                                        "','" +
                                        keyId +
                                        "', '" + params["time"].toString()  + "', '" + params["date"].toString() + "', '" +
                                        params["deviceId"].toString() +
                                        "', '" +
                                        isAccessStr +
                                        "', '" +
                                        "10" +
                                        "', '" +
                                        params["key"].toString() +
                                        "')";
        QSqlQuery tracking(query);
    }


}

void CVeloPark::displayMessage(QMap<QString, QVariant> params, QString message)
{

    QMap<QString, QString> p;
    p.clear();
    QString f = "displayMessage";
    p["message"] = message;
    QString xmlFunc = CXmlFactory::deviceAction( params["deviceId"].toString()  ,f, p);
    emit accessAction(xmlFunc);
}

bool CVeloPark::isAccess(QMap<QString, QVariant> params, bool emitAction, bool )
{
  return checkAccess(params, emitAction);
}


void CVeloPark::acceptAccess(QMap<QString, QVariant> params, bool isOk)
{
  if(isOk)
  {
    QMap<QString, QString> param;

    param["isAccess"] = "1";
    param["key"] = params["key"].toString();

    int index = metaObject()->indexOfClassInfo ( "PluginName" );

    if ( index != -1 )
    {
        param["PluginName"] = metaObject()->classInfo ( index ).value();
    }

    QString xml = CXmlFactory::deviceAction(params["deviceId"].toString(), "openDoor", param);

    emit accessAction(xml);

    //!display the message in the display
    displayMessage("ok",params["deviceId"].toString());

   
    //! Set the new status of the light information
    setLightStatus(params["deviceId"].toString(), params["key"].toString());
  }
  else
  {
    QMap<QString, QString> param;

    param["isAccess"] = "0";
    param["key"] = params["key"].toString();

    int index = metaObject()->indexOfClassInfo ( "PluginName" );

    if ( index != -1 )
    {
        param["PluginName"] = metaObject()->classInfo ( index ).value();
    }

    QString xml = CXmlFactory::deviceAction(params["deviceId"].toString(), "openDoor", param);

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

    query = "INSERT INTO `hr_tracking` ( `id_user` , `id_key` , `time` , `date` , `id_entry` , `is_access` , `id_comment`, `key` ) VALUES ( '" +
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
    bool userInTerminal = true;

    // check if the key is blocked
    QString sqlKey="SELECT * FROM hr_keys WHERE serialNumber='" +  params["key"].toString() + "' AND isBlocked=0";
    QSqlQuery queryKey(sqlKey);
    if(!queryKey.next())
        return false;


    //! if we receive the event Gantner_AccessTerminal_accessDetectedBeforeBooking, we do not have the user id. So, we execute the following request to obtain the user id
    QString sql="SELECT accessPlugin, uga.id_user FROM hr_keys AS k LEFT JOIN hr_keys_attribution AS ka ON ka.id_key=k.id LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=ka.id_user LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE serialNumber='" + params["key"].toString() + "'  AND accessPlugin='velopark'";

    QSqlQuery query(sql);

    if(query.next())
    {
        // check if the user has access to the device or if the user is bloqued
        QSqlQuery queryUser("SELECT u.id, uga.id_group, CONCAT(u.name,' ' ,u.firstname) AS fullname, k.serialNumber, u.pin_code,u.validity_date, u.masterAuthorization FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id LEFT JOIN hr_keys AS k ON k.id=ka.id_key LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=u.id  LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE ug.accessPlugin='velopark' AND u.name!='??' AND u.isBlocked=0  AND u.id=" + query.value(1).toString() );

        if(queryUser.next())
        {
            QString groupId = queryUser.value(1).toString();

            QSqlQuery queryHasAccessDevice("SELECT * FROM hr_user_group_access WHERE id_device=" + params["deviceId"].toString() + " AND id_group=" + groupId);

            if(!queryHasAccessDevice.next())
                return false;
        }
        else
        {
            return false;
        }

        // the user has access. We receive the event Gantner_AccessTerminal_accessDetected with an user id 0,
        // maybe the user was deleted during a device reinitialisation
        if(params["userId"].toString() == "0" && params["event"].toString() == "Gantner_AccessTerminal_accessDetected" )
        {
            params["userId"] = query.value(1).toString();
            userInTerminal = false;
        }

        // do we have a subscription with the status "started"
        QString sub = "SELECT * FROM hr_vp_subscription_attribution WHERE status='started' AND user_id="+query.value(1).toString();
        QSqlQuery querySub(sub);

        if(querySub.next())
        {

                //! check the validity of the current subscription
                if( checkSubDate( querySub.value(6).toDateTime(), querySub.value(7).toDateTime() ) )
                {
                    if(!userInTerminal)
                    {
                        //update the user access control
                        if(querySub.value(9).toInt() == 1)
                        {
                            updateUser(params, true);

                            //check if only one subscription is existing
                            checkLastCredit(params);
                        }
                        else
                        {
                            updateUser(params, false);
                        }
                        //open the door
                        QMap<QString, QString> p;
                        p.clear();
                        QString f = "Gantner_AccessTerminal_openByCommand";
                        p["controlCode"] = "o";
                        QString xmlFunc = CXmlFactory::deviceAction( params["deviceId"].toString()  ,f, p);
                        emit accessAction(xmlFunc);
                    }

                    if(params["event"].toString() == "Gantner_AccessTerminal_accessDetectedBeforeBooking")
                    {
                        if(querySub.value(9).toInt() == 1)
                        {
                            //display the message for the multiticket
                            QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                            while(queryMessage.next())
                            {
                                QString ids = queryMessage.value(7).toString();
                                QStringList idsList= ids.split(',');

                                if(idsList.contains(params["deviceId"].toString()))
                                {
                                    QString message = queryMessage.value(8).toString();
                                    message.replace("{credit}", querySub.value(5).toString().rightJustified(2, ' '));
                                    displayMessage(params, message);
                                }
                            }
                        }
                        else
                        {
                            QDate date = querySub.value(7).toDate();
                            //display the message for the single
                            QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                            while(queryMessage.next())
                            {
                                QString ids = queryMessage.value(7).toString();
                                QStringList idsList= ids.split(',');

                                if(idsList.contains(params["deviceId"].toString()))
                                {
                                    QString message = queryMessage.value(9).toString();
                                    message.replace("{date}", QString(date.toString("dd-MM-yyyy")));
                                    displayMessage(params, message);
                                }
                            }
                        }
                    }

                    //! accept access
                    return true;
                }
                else
                {
                    // is it a multi ticket
                    if(querySub.value(9).toInt() == 1)
                    {

                        //! do we have credit of used (multiple ticket)
                        if(querySub.value(5).toInt() == 0 )
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

                                checkLastCredit(params);

                                if(!userInTerminal)
                                {
                                    //update the user access control
                                    if(querySub.value(9).toInt() == 1)
                                    {
                                        updateUser(params, true);

                                        //check if only one subscription is existing
                                        checkLastCredit(params);
                                    }
                                    else
                                    {
                                        updateUser(params, false);
                                    }
                                    //open the door
                                    QMap<QString, QString> p;
                                    p.clear();
                                    QString f = "Gantner_AccessTerminal_openByCommand";
                                    p["controlCode"] = "o";
                                    QString xmlFunc = CXmlFactory::deviceAction( params["deviceId"].toString()  ,f, p);
                                    emit accessAction(xmlFunc);
                                }
                                // the sub is ok, we can leave the function

                                if(params["event"].toString() == "Gantner_AccessTerminal_accessDetectedBeforeBooking")
                                {
                                    if(querySub.value(9).toInt() == 1)
                                    {
                                        //display the message for the multiticket
                                        QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                                        while(queryMessage.next())
                                        {
                                            QString ids = queryMessage.value(7).toString();
                                            QStringList idsList= ids.split(',');

                                            if(idsList.contains(params["deviceId"].toString()))
                                            {
                                                QString message = queryMessage.value(8).toString();
                                                message.replace("{credit}", QString::number(querySub.value(5).toInt()-1).rightJustified(2, ' '));
                                                displayMessage(params, message);
                                            }
                                        }
                                    }
                                    else
                                    {
                                        QDate date = querySub.value(7).toDate();
                                        //display the message for the single
                                        QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                                        while(queryMessage.next())
                                        {
                                            QString ids = queryMessage.value(7).toString();
                                            QStringList idsList= ids.split(',');

                                            if(idsList.contains(params["deviceId"].toString()))
                                            {
                                                QString message = queryMessage.value(9).toString();
                                                message.replace("{date}", QString(date.toString("dd-MM-yyyy")));
                                                displayMessage(params, message);
                                            }
                                        }
                                    }
                                }

                                return true;
                        }

                        //! try for an other sub if existing
                        return checkAccess(params, emitAction);
                    }
                    else
                    {
                        //! set the sub as finished
                        QSqlQuery update("UPDATE hr_vp_subscription_attribution SET credit=0, status='finished' WHERE id="+querySub.value(0).toString());

                        //! try for an other sub if existing
                        return checkAccess(params, emitAction);
                    }
                }
        }
        else
        {
                //! check if a subscription "not start" or "waiting" is existing
                QString sub2 = "SELECT * FROM hr_vp_subscription_attribution WHERE ( status='not_start' OR status='waiting' ) AND user_id="+query.value(1).toString();
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

                        // is it a multiticket
                        if(querySub2.value(9).toInt() == 1 )
                        {
                            //! credit -1 and new start/end date
                            QSqlQuery update("UPDATE hr_vp_subscription_attribution SET credit=credit-1, status='started', start='" + startStr + "', end='" + endStr + "' WHERE id="+querySub2.value(0).toString());

                            //update the user access control
                            updateUser(params, true);

                            //check if only one subscription is existing
                            checkLastCredit(params);

                            //open the door
                            QMap<QString, QString> p;
                            p.clear();
                            QString f = "Gantner_AccessTerminal_openByCommand";
                            p["controlCode"] = "o";
                            QString xmlFunc = CXmlFactory::deviceAction( params["deviceId"].toString()  ,f, p);
                            emit accessAction(xmlFunc);
                        }
                        else
                        {
                            //! new start/end date
                            QSqlQuery update("UPDATE hr_vp_subscription_attribution SET status='started', start='" + startStr + "', end='" + endStr + "' WHERE id="+querySub2.value(0).toString());

                            //update the user access control
                            updateUser(params, false);

                            //open the door
                            QMap<QString, QString> p;
                            p.clear();
                            QString f = "Gantner_AccessTerminal_openByCommand";
                            p["controlCode"] = "o";
                            QString xmlFunc = CXmlFactory::deviceAction( params["deviceId"].toString()  ,f, p);
                            emit accessAction(xmlFunc);
                        }

                        if(!userInTerminal)
                        {
                            //update the user access control
                            if(querySub2.value(9).toInt() == 1)
                            {
                                updateUser(params, true);

                                //check if only one subscription is existing
                                checkLastCredit(params);
                            }
                            else
                            {
                                updateUser(params, false);
                            }
                            //open the door
                            QMap<QString, QString> p;
                            p.clear();
                            QString f = "Gantner_AccessTerminal_openByCommand";
                            p["controlCode"] = "o";
                            QString xmlFunc = CXmlFactory::deviceAction( params["deviceId"].toString()  ,f, p);
                            emit accessAction(xmlFunc);
                        }

                        if(params["event"].toString() == "Gantner_AccessTerminal_accessDetectedBeforeBooking")
                        {
                            if(querySub2.value(9).toInt() == 1)
                            {
                                //display the message for the multiticket
                                QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                                while(queryMessage.next())
                                {
                                    QString ids = queryMessage.value(7).toString();
                                    QStringList idsList= ids.split(',');

                                    if(idsList.contains(params["deviceId"].toString()))
                                    {
                                        QString message = queryMessage.value(8).toString();
                                        message.replace("{credit}", QString::number(querySub2.value(5).toInt()-1).rightJustified(2, ' '));
                                        displayMessage(params, message);
                                    }
                                }
                            }
                            else
                            {
                                QDate date = querySub2.value(7).toDate();
                                //display the message for the single
                                QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                                while(queryMessage.next())
                                {
                                    QString ids = queryMessage.value(7).toString();
                                    QStringList idsList= ids.split(',');

                                    if(idsList.contains(params["deviceId"].toString()))
                                    {
                                        QString message = queryMessage.value(9).toString();
                                        message.replace("{date}", QString(date.toString("dd-MM-yyyy")));
                                        displayMessage(params, message);
                                    }
                                }

                            }
                        }
                        return true;

                }
                else
                {

                    QMapIterator<int, bool> i(devices);
                    while (i.hasNext())
                    {
                        i.next();

                        if(i.value())
                        {
                            QSqlQuery queryUser("SELECT u.id FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id LEFT JOIN hr_keys AS k ON k.id=ka.id_key LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=u.id  LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE ug.accessPlugin='velopark' AND u.isBlocked=0  AND u.id=" + params["userId"].toString() );
                            if(queryUser.next())
                            {
                                QString deviceId = QString::number(i.key());
                                // remove the user from the access control
                                QMap<QString, QString> p;
                                p.clear();
                                QString f = "Gantner_AccessTerminal_unloadPersonnel";

                                p["cardNumber"] = params["key"].toString();

                                QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                                emit accessAction(xmlFunc);
                            }
                        }
                    }



                    return false;
                }

        }

    }

    //! no access
    return false;
}


void CVeloPark::updateUser(QMap<QString, QVariant> params, bool multiticket)
{
    QString sub2 = "SELECT * FROM hr_vp_subscription_attribution WHERE status='started' AND user_id="+params["userId"].toString();
    QSqlQuery querySub2(sub2);
    if(querySub2.next())
    {
        QMap<QString, QString> p;
        p.clear();
        QString f = "Gantner_AccessTerminal_loadPersonnel" ;

        p["personnelNumber"] = params["userId"].toString();
        p["cardNumber"] = params["key"].toString();
        p["cardVersion"] = "0";

        QSqlQuery queryUser("SELECT CONCAT(name, ' ', firstname) AS fullname FROM hr_user WHERE id=" + params["userId"].toString()  );
        if(queryUser.next())
            p["fullname"] = queryUser.value(0).toString().left(16);
        else
            p["fullname"] = "";

        p["masterAuhtorization"] = "0";
        p["PINCode"] = "";

        QSqlQuery queryAccessLevel("SELECT * FROM hr_access_level WHERE full_access=1 LIMIT 0,1"  );
        if(queryAccessLevel.next())
            p["scheduleNumber"] = queryAccessLevel.value(0).toString();
        else
            p["scheduleNumber"] = "00";

        p["regularRelayPlanNumber"] = "00";
        p["specialRelayPlanNumber"] = "00";

        if(!multiticket)
        {
            QDateTime valididyDate = querySub2.value(7).toDateTime();

            p["validityDate"] = valididyDate.toString("yyMMddhhmm");
            p["validityDateOption"] = "2";
        }
        else
        {
            p["validityDate"] = "0000000000";
            p["validityDateOption"] = "0";
        }

        QMapIterator<int, bool> i(devices);
        while (i.hasNext())
        {
            i.next();

            if(i.value())
            {
                QSqlQuery queryUser("SELECT u.id FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id LEFT JOIN hr_keys AS k ON k.id=ka.id_key LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=u.id  LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE ug.accessPlugin='velopark'  AND u.isBlocked=0  AND u.id=" + params["userId"].toString() );
                if(queryUser.next())
                {
                    QString deviceId = QString::number(i.key());
                    QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                    emit accessAction(xmlFunc);
                }
            }
        }

    }
}

void CVeloPark::checkLastCredit(QMap<QString, QVariant> params)
{
    QString sub2 = "SELECT * FROM hr_vp_subscription_attribution WHERE status='started' AND user_id="+params["userId"].toString();
    QSqlQuery querySub2(sub2);
    if(querySub2.next())
    {
        // if only one credit is existing, fix the limit date
        if(querySub2.value(5).toInt() == 0 )
        {
            QMap<QString, QString> p;
            p.clear();
            QString f = "Gantner_AccessTerminal_loadPersonnel" ;

            p["personnelNumber"] = params["userId"].toString();
            p["cardNumber"] = params["key"].toString();
            p["cardVersion"] = "0";

            QSqlQuery queryUser("SELECT CONCAT(name, ' ', firstname) AS fullname FROM hr_user WHERE id=" + params["userId"].toString()  );
            if(queryUser.next())
                p["fullname"] = queryUser.value(0).toString().left(16);
            else
                p["fullname"] = "";

            p["masterAuhtorization"] = "0";
            p["PINCode"] = "";

            QSqlQuery queryAccessLevel("SELECT * FROM hr_access_level WHERE full_access=1 LIMIT 0,1"  );
            if(queryAccessLevel.next())
                p["scheduleNumber"] = queryAccessLevel.value(0).toString();
            else
                p["scheduleNumber"] = "00";

            p["regularRelayPlanNumber"] = "00";
            p["specialRelayPlanNumber"] = "00";

            QDateTime valididyDate = querySub2.value(7).toDateTime();

            p["validityDate"] = valididyDate.toString("yyMMddhhmm");
            p["validityDateOption"] = "2";

            QMapIterator<int, bool> i(devices);
            while (i.hasNext())
            {
                i.next();

                if(i.value())
                {
                    QSqlQuery queryUser("SELECT u.id FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id LEFT JOIN hr_keys AS k ON k.id=ka.id_key LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=u.id  LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE ug.accessPlugin='velopark'  AND u.isBlocked=0  AND u.id=" + params["userId"].toString() );
                    if(queryUser.next())
                    {
                        QString deviceId = QString::number(i.key());
                        QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                        emit accessAction(xmlFunc);
                    }
                }
            }


        }
    }
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

      QMap<QString, QString> param;

      param["message"] = msg;

      int index = metaObject()->indexOfClassInfo ( "PluginName" );

      if ( index != -1 )
      {
          param["PluginName"] = metaObject()->classInfo ( index ).value();
      }

      QString xml = CXmlFactory::deviceAction( query.value(2).toString(), "displayMessage", param);
  
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

void CVeloPark::deviceConnectionMonitor(int id, bool status)
{
    QSqlQuery query("SELECT * FROM hr_device WHERE id=" + QString::number(id));

    if(query.next())
        devices[id] = status;
}

void CVeloPark::deviceInputMonitor ( int , int , bool )
{
}

void CVeloPark::checkDb()
{
    timerCheckDb->stop();

    QStringList ids;

    QMapIterator<int, bool> i(devices);
    while (i.hasNext())
    {
        i.next();

        if(i.value())
        {

            QSqlQuery query("SELECT * FROM hr_gantner_standalone_action WHERE deviceId=" + QString::number(i.key()) + " ORDER BY id" );

            while(query.next())
            {
                QString id = query.value(0).toString();
                QString type = query.value(1).toString();
                QString func = query.value(2).toString();
                QString deviceId = QString::number(i.key());
                QString userId = query.value(3).toString();
                QString keyId = query.value(4).toString();
                QString param = query.value(6).toString();
                QString param2 = query.value(7).toString();
                QString param3 = query.value(8).toString();
                QString reasonId = query.value(9).toString();

                if(type == "user" || type == "key")
                {

                    if(func == "add")
                    {

                        QSqlQuery queryUser("SELECT u.id, uga.id_group, CONCAT(u.name,' ' ,u.firstname) AS fullname, k.serialNumber, u.pin_code,u.validity_date, u.masterAuthorization FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id LEFT JOIN hr_keys AS k ON k.id=ka.id_key LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=u.id  LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE ug.accessPlugin='velopark' AND u.name!='??' AND u.isBlocked=0  AND u.id=" + userId );

                        while(queryUser.next())
                        {
                            QString userId = queryUser.value(0).toString();
                            QString groupId = queryUser.value(1).toString();
                            QString fullName = queryUser.value(2).toString();
                            QString cardNumber = queryUser.value(3).toString();
                            QString pinCode = queryUser.value(4).toString();
                            QDate valididyDate = queryUser.value(5).toDate();

                            QSqlQuery queryHasAccessDevice("SELECT * FROM hr_user_group_access WHERE id_device=" + deviceId + " AND id_group=" + groupId);

                            if(queryHasAccessDevice.next())
                            {
                                //check subscription
                                bool isSubscription = false;                               
                                QSqlQuery queryHasSubscription("SELECT * FROM hr_vp_subscription_attribution WHERE user_id=" + userId + " AND status='started'"  );
                                if(queryHasSubscription.next())
                                    isSubscription = true;

                                //if false, the user was just created, but not subscription was attributed
                                if(isSubscription)
                                {

                                    QMap<QString, QString> p;
                                    p.clear();
                                    QString f = "Gantner_AccessTerminal_loadPersonnel" ;

                                    p["personnelNumber"] = userId;
                                    p["cardNumber"] = cardNumber;
                                    p["cardVersion"] = "0";
                                    p["fullname"] = fullName.left(16);
                                    p["masterAuhtorization"] = "0";

                                    p["PINCode"] = "";

                                    QSqlQuery queryAccessLevel("SELECT * FROM hr_access_level WHERE full_access=1 LIMIT 0,1"  );
                                    if(queryAccessLevel.next())
                                        p["scheduleNumber"] = queryAccessLevel.value(0).toString();
                                    else
                                        p["scheduleNumber"] = "00";

                                    p["regularRelayPlanNumber"] = "00";
                                    p["specialRelayPlanNumber"] = "00";

                                    if(queryHasSubscription.value(9).toInt()==0)
                                    {
                                        QDateTime valididyDate = queryHasSubscription.value(7).toDateTime();

                                        p["validityDate"] = valididyDate.toString("yyMMddhhmm");
                                        p["validityDateOption"] = "2";
                                    }
                                    else
                                    {
                                        // for a multiticket, don't set the limit date until the credit is bigger than 1
                                        p["validityDate"] = "0000000000";
                                        p["validityDateOption"] = "0";
                                    }

                                    QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);


                                    emit accessAction(xmlFunc);
                                }

                                QSqlQuery queryDel("DELETE FROM hr_gantner_standalone_action WHERE id=" + id );
                            }
                        }
                    }
                }


            }


            QSqlQuery queryOptimize("OPTIMIZE TABLE hr_gantner_standalone_action");
        }
    }

    timerCheckDb->start(TIME_DB_CHECKING);
}

//! Q_EXPORT_PLUGIN2(TARGET, CLASSNAME);
Q_EXPORT_PLUGIN2(velopark, CVeloPark);
