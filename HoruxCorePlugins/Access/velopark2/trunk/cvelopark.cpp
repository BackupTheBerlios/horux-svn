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
#include <QSslError>
#include <QNetworkReply>

void CVeloPark::test() {
    QString xml = "<deviceEvent id=\"6\"><event>keyDetected</event><params><param><name>code</name><value>1</value></param><param><name>date</name><value>2010-11-17</value></param><param><name>deviceId</name><value>6</value></param><param><name>entering</name><value>1</value></param><param><name>key</name><value>988775685</value></param><param><name>time</name><value>09:10:57</value></param><param><name>userId</name><value>-1</value></param></params></deviceEvent>";
    qDebug() << "Teste la fonction";
    deviceEvent(xml);
}


CVeloPark::CVeloPark(QObject *parent) : QObject(parent)
{
    initSAASMode();

    // get the velopark installation type
    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );
    settings.beginGroup ( "Velopark" );
    if ( !settings.contains ( "type" ) ) settings.setValue ( "type", "gantner" );
    type = settings.value ( "type", "gantner" ).toString();

    // if the installation is a Gantner type, start the checking of the db to see if we new standalone action
    if(type == "gantner")
    {
        timerCheckDb = new QTimer(this);
        connect(timerCheckDb, SIGNAL(timeout()), this, SLOT(checkDb()));
        timerCheckDb->start(TIME_DB_CHECKING);
    }

    // pour les testes
    /*timerTest = new QTimer(this);
    connect(timerTest, SIGNAL(timeout()), this, SLOT(test()));
    timerTest->start(10000);*/
    // fin code de test
}



/*
    Function deviceEvent
    This function reveive an device event. This function handle the following event:
    When type equal "gantner":
            Gantner_AccessTerminal_accessDetected -> received when the terminal register the booking
            Gantner_AccessTerminal_accessDetectedBeforeBooking -> received immediatly after a tag presentation
    When type equal "a3m":
            keyDetected -> received immediatly after a tag presentation

*/
void CVeloPark::deviceEvent(QString xml)
{

    QMap<QString, QVariant>params = CXmlFactory::deviceEvent(xml) ;

    QString event = params["event"].toString();

    QString isAccessStr = "";

    /*
      Online system like A3M
    */
    if(type == "a3m" /*Maybe other type will comming*/)
    {
        // handle only the key detection
        if(event != "keyDetected")
            return;

        if(checkAccessOnline(params)) {
            isAccessStr = "1";
            accessAccepted(params, true);
        } else {
            // display the no access message
            isAccessStr = "0";
            accessAccepted(params, false);
            // display the non access message on the specific device
            QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
            while(queryMessage.next())
            {
                QString ids = queryMessage.value(7).toString();
                QStringList idsList= ids.split(',');

                if(idsList.contains(params["deviceParentId"].toString()))
                {
                    displayMessage(params, queryMessage.value(4).toString());
                }
            }
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

    /*
      Gantner system based on GAT Terminal 3100
    */
    if(type == "gantner")
    {

        // handle only the key detection
        if(event != "Gantner_AccessTerminal_accessDetected" && event != "Gantner_AccessTerminal_accessDetectedBeforeBooking")
            return;

        // check if the user musst be checked by this plugin
        QString sqlUser="SELECT accessPlugin, uga.id_user FROM hr_keys AS k LEFT JOIN hr_keys_attribution AS ka ON ka.id_key=k.id LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=ka.id_user LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE serialNumber='" + params["key"].toString() + "'  AND accessPlugin='velopark'";

        QSqlQuery queryUser(sqlUser);

        // if not return
        if(!queryUser.next()) {
            QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
            while(queryMessage.next())
            {
                QString ids = queryMessage.value(7).toString();
                QStringList idsList= ids.split(',');

                if(idsList.contains(params["deviceParentId"].toString()))
                {
                    displayMessage(params, queryMessage.value(3).toString());
                }
            }

            accessAccepted(params, false);

            return;
        }


        if(checkAccessGantner(params))
        {
            isAccessStr = "1";
            accessAccepted(params, true);
        }
        else
        {
            // display the no access message
            isAccessStr = "0";
            accessAccepted(params, false);
            // display the non access message on the specific device
            QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
            while(queryMessage.next())
            {
                QString ids = queryMessage.value(7).toString();
                QStringList idsList= ids.split(',');

                if(idsList.contains(params["deviceParentId"].toString()))
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
    QString xmlFunc = CXmlFactory::deviceAction( params["deviceParentId"].toString()  ,f, p);
    emit accessAction(xmlFunc);
}

bool CVeloPark::isAccess(QMap<QString, QVariant> , bool , bool )
{
    qDebug() << "Not implemented";

    return false;
}

bool CVeloPark::checkAccessOnline(QMap<QString, QVariant> params) {


    // check if the key is blocked
    QString sqlKey="SELECT * FROM hr_keys WHERE serialNumber='" +  params["key"].toString() + "'";
    QSqlQuery queryKey(sqlKey);
    if(queryKey.next()) {
        if(queryKey.value(4).toInt() == 1)
            return false;
    }


    float creditValue = 0;

    QSqlQuery queryParking("SELECT device_ids, creditValue FROM hr_vp_parking");
    while(queryParking.next() && creditValue==0)
    {
        QString ids = queryParking.value(0).toString();
        QStringList idsList= ids.split(',');

        if(idsList.contains(params["deviceId"].toString()))
        {
            creditValue = queryParking.value(1).toFloat();
        }
    }

    //if userId != -1, this means that the key is attribute to a user
    if(params["userId"].toString() != "-1") {

        // check if the user has access to the device or if the user is bloqued
        QSqlQuery queryUser("SELECT u.id, uga.id_group, CONCAT(u.name,' ' ,u.firstname) AS fullname, k.serialNumber, u.pin_code,u.validity_date, u.masterAuthorization FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id LEFT JOIN hr_keys AS k ON k.id=ka.id_key LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=u.id  LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE ug.accessPlugin='velopark' AND u.name!='??' AND u.isBlocked=0  AND u.id=" + params["userId"].toString() );

        if(queryUser.next())
        {
            QString groupId = queryUser.value(1).toString();

            QSqlQuery queryHasAccessDevice("SELECT * FROM hr_user_group_access WHERE id_device=" + params["deviceId"].toString() + " AND id_group=" + groupId);

            if(!queryHasAccessDevice.next()) {
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    // do we have a subscription with the status "started" for user
    QString sub = "SELECT * FROM hr_vp_subscription_attribution WHERE status='started' AND user_id="+params["userId"].toString();

    if(params["userId"].toString() == "-1") { // do we have a subscription with the status "started" for a key not attributed
        sub = "SELECT * FROM hr_vp_subscription_attribution WHERE status='started' AND serialNumber='"+ params["key"].toString()+"'";
    }

    QSqlQuery querySub(sub);

    if(querySub.next())
    {
        // check the validity date if not equal
        if( querySub.value(6).toDateTime() != querySub.value(7).toDateTime() && checkSubDate( querySub.value(6).toDateTime(), querySub.value(7).toDateTime() ) ) {

            // display the message
            if(querySub.value(9).toInt() == 1)
            {
                //display the message for the multiticket
                QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                while(queryMessage.next())
                {
                    QString ids = queryMessage.value(7).toString();
                    QStringList idsList= ids.split(',');

                    if(idsList.contains(params["deviceParentId"].toString()))
                    {
                        float solde = querySub.value(5).toFloat()-creditValue;
                        QString soldeStr = QString::number(solde);

                        QString message = queryMessage.value(8).toString();
                        message.replace("{credit}", soldeStr.rightJustified(2, ' '));
                        displayMessage(params, message);
                    }
                }
            }
            else
            {
                QDateTime date = querySub.value(7).toDateTime();
                //display the message for the single
                QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                while(queryMessage.next())
                {
                    QString ids = queryMessage.value(7).toString();
                    QStringList idsList= ids.split(',');

                    if(idsList.contains(params["deviceParentId"].toString()))
                    {
                        QString message = queryMessage.value(9).toString();
                        message.replace("{date}", QString(date.toString("dd-MM-yyyy")));
                        message.replace("{time}", QString(date.toString("hh:mm")));
                        displayMessage(params, message);
                    }
                }
            }

            // access acepted
            return true;
        } else {

            // is it a multi ticket
            if(querySub.value(9).toInt() == 1)
            {
                // do we have credit avalaible (multiple ticket)
                if(querySub.value(5).toFloat() == 0 )
                {
                        // set the sub as finished
                        QSqlQuery update("UPDATE hr_vp_subscription_attribution SET credit=0, status='finished' WHERE id="+querySub.value(0).toString());
                }
                else
                {
                    // not enough credit ?
                    if(querySub.value(5).toFloat()<creditValue) {
                        qDebug() << "not enough credit";

                        //! check if a multicket with the status "not start" or "waiting" is existing
                        QString sub2 = "SELECT * FROM hr_vp_subscription_attribution WHERE ( status='not_start' OR status='waiting' ) AND multiticket=1 AND user_id="+params["userId"].toString();

                        if(params["userId"].toString() == "-1") { // do we have a subscription with the status "started" for a key not attributed
                            sub2 = "SELECT * FROM hr_vp_subscription_attribution WHERE ( status='not_start' OR status='waiting' ) AND multiticket=1 AND serialNumber="+ params["key"].toString();
                        }

                        QSqlQuery querySub2(sub2);

                        if(querySub2.next())
                        {
                            QSqlQuery update("UPDATE hr_vp_subscription_attribution SET credit=credit+" + querySub.value(5).toString()  + " WHERE id="+querySub2.value(0).toString());
                            QSqlQuery update2("UPDATE hr_vp_subscription_attribution SET credit=0, status='finished' WHERE id="+querySub.value(0).toString());

                            return checkAccessOnline(params);
                        }

                        return false;
                    }


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

                    //! credit - X and new start/end date
                    QSqlQuery update("UPDATE hr_vp_subscription_attribution SET credit=credit-" + QString::number(creditValue) + ", start='" + startStr + "', end='" + endStr + "' WHERE id="+querySub.value(0).toString());

                    //display the message for the multiticket
                    QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                    while(queryMessage.next())
                    {
                        QString ids = queryMessage.value(7).toString();
                        QStringList idsList= ids.split(',');

                        if(idsList.contains(params["deviceParentId"].toString()))
                        {
                            float solde =  querySub.value(5).toFloat()-creditValue;
                            QString soldeStr = QString::number(solde);

                            QString message = queryMessage.value(8).toString();
                            message.replace("{credit}", soldeStr.rightJustified(2, ' '));
                            displayMessage(params, message);
                        }
                    }

                    return true;
                }

                //! try for an other sub if existing
                return checkAccessOnline(params);
            }
            else
            {
                if(querySub.value(6).toDateTime() < QDateTime::currentDateTime()) {
                    //! set the sub as finished
                    QSqlQuery update("UPDATE hr_vp_subscription_attribution SET credit=0, status='finished' WHERE id="+querySub.value(0).toString());

                    //! try for an other sub if existing
                    return checkAccessOnline(params);
                } else {
                    return false;
                }
            }
        }


    }
    else
    {
        //! check if a subscription "not start" or "waiting" is existing
        QString sub2 = "SELECT * FROM hr_vp_subscription_attribution WHERE ( status='not_start' OR status='waiting' ) AND user_id="+params["userId"].toString();

        if(params["userId"].toString() == "-1") { // do we have a subscription with the status "started" for a key not attributed
            sub2 = "SELECT * FROM hr_vp_subscription_attribution WHERE ( status='not_start' OR status='waiting' ) AND serialNumber="+ params["key"].toString();
        }

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
            int periodId = validity.section(":",4,4).toInt();

            QDateTime start = QDateTime::currentDateTime();
            QDateTime end = start;
            end = end.addYears(year);
            end = end.addMonths(month);
            end = end.addDays(day);
            end = end.addSecs(hour*3600);

            // check if the subcription is a period
            if(periodId  > 0) {
                QSqlQuery period("SELECT * FROM hr_vp_period WHERE id=" + QString::number(periodId));
                period.next();

                int hour = 0;
                int minute = 0;

                hour = period.value(2).toString().section(":",0,0).toInt();
                minute = period.value(2).toString().section(":",1,1).toInt();

                start.setDate(QDate::currentDate());
                start.setTime(QTime(hour, minute, 0, 0));

                hour = period.value(3).toString().section(":",0,0).toInt();
                minute = period.value(3).toString().section(":",1,1).toInt();

                end.setDate(QDate::currentDate());
                start.setTime(QTime(hour, minute, 0, 0));
            }

            QString startStr = start.toString("yyyy-MM-dd hh:mm:ss");
            QString endStr = end.toString("yyyy-MM-dd hh:mm:ss");

            // is it a multiticket
            if(querySub2.value(9).toInt() == 1 )
            {
                //! credit -1 and new start/end date
                QSqlQuery update("UPDATE hr_vp_subscription_attribution SET credit=credit-" + QString::number(creditValue) + ", status='started', start='" + startStr + "', end='" + endStr + "' WHERE id="+querySub2.value(0).toString());

            }
            else
            {
                //! new start/end date
                QSqlQuery update("UPDATE hr_vp_subscription_attribution SET status='started', start='" + startStr + "', end='" + endStr + "' WHERE id="+querySub2.value(0).toString());
            }

            if(querySub2.value(9).toInt() == 1)
            {
                //display the message for the multiticket
                QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                while(queryMessage.next())
                {
                    QString ids = queryMessage.value(7).toString();
                    QStringList idsList= ids.split(',');

                    if(idsList.contains(params["deviceParentId"].toString()))
                    {
                        int solde = querySub2.value(5).toFloat()-creditValue;
                        QString soldeStr = QString::number(solde);

                        QString message = queryMessage.value(8).toString();
                        message.replace("{credit}", soldeStr.rightJustified(2, ' '));
                        displayMessage(params, message);
                    }
                }
            }
            else
            {
                QDateTime date = querySub2.value(7).toDateTime();
                //display the message for the single
                QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                while(queryMessage.next())
                {
                    QString ids = queryMessage.value(7).toString();
                    QStringList idsList= ids.split(',');

                    if(idsList.contains(params["deviceParentId"].toString()))
                    {
                        QString message = queryMessage.value(9).toString();
                        message.replace("{date}", QString(date.toString("dd-MM-yyyy")));
                        message.replace("{time}", QString(date.toString("hh:mm")));
                        displayMessage(params, message);
                    }
                }

            }
            return true;

        }
        else
        {
            return false;
        }
    }
}


bool CVeloPark::checkAccessGantner(QMap<QString, QVariant> params)
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

                                if(idsList.contains(params["deviceParentId"].toString()))
                                {
                                    int solde =  querySub.value(5).toInt();
                                    QString soldeStr = QString::number(solde);

                                    QString message = queryMessage.value(8).toString();
                                    message.replace("{credit}", soldeStr.rightJustified(2, ' '));
                                    displayMessage(params, message);
                                }
                            }
                        }
                        else
                        {
                            QDateTime date = querySub.value(7).toDateTime();
                            //display the message for the single
                            QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                            while(queryMessage.next())
                            {
                                QString ids = queryMessage.value(7).toString();
                                QStringList idsList= ids.split(',');

                                if(idsList.contains(params["deviceParentId"].toString()))
                                {
                                    QString message = queryMessage.value(9).toString();
                                    message.replace("{date}", QString(date.toString("dd-MM-yyyy")));
                                    message.replace("{time}", QString(date.toString("hh:mm")));
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
                                    //display the message for the multiticket
                                    QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                                    while(queryMessage.next())
                                    {
                                        QString ids = queryMessage.value(7).toString();
                                        QStringList idsList= ids.split(',');

                                        if(idsList.contains(params["deviceParentId"].toString()))
                                        {
                                            int solde = querySub.value(5).toInt()-1;
                                            QString soldeStr = QString::number(solde);

                                            QString message = queryMessage.value(8).toString();
                                            message.replace("{credit}", soldeStr.rightJustified(2, ' '));
                                            displayMessage(params, message);
                                        }
                                    }
                                }

                                return true;
                        }

                        //! try for an other sub if existing
                        return checkAccessGantner(params);
                    }
                    else
                    {
                        if(querySub.value(6).toDateTime() < QDateTime::currentDateTime()) {
                            //! set the sub as finished
                            QSqlQuery update("UPDATE hr_vp_subscription_attribution SET credit=0, status='finished' WHERE id="+querySub.value(0).toString());

                            //! try for an other sub if existing
                            return checkAccessGantner(params);
                        } else {

                            return false;
                        }
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

                                    if(idsList.contains(params["deviceParentId"].toString()))
                                    {
                                        int solde = querySub2.value(5).toInt()-1;
                                        QString soldeStr = QString::number(solde);

                                        QString message = queryMessage.value(8).toString();
                                        message.replace("{credit}", soldeStr.rightJustified(2, ' '));
                                        displayMessage(params, message);
                                    }
                                }
                            }
                            else
                            {
                                QDateTime date = querySub2.value(7).toDateTime();
                                //display the message for the single
                                QSqlQuery queryMessage("SELECT * FROM hr_vp_parking");
                                while(queryMessage.next())
                                {
                                    QString ids = queryMessage.value(7).toString();
                                    QStringList idsList= ids.split(',');

                                    if(idsList.contains(params["deviceParentId"].toString()))
                                    {
                                        QString message = queryMessage.value(9).toString();
                                        message.replace("{date}", QString(date.toString("dd-MM-yyyy")));
                                        message.replace("{time}", QString(date.toString("hh:mm")));
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

                            QSqlQuery queryHasAccessDevice("SELECT * FROM hr_user_group_access WHERE id_device=" + deviceId + " AND id_group=" + groupId);

                            if(queryHasAccessDevice.next())
                            {
                                //check subscription
                                bool isSubscription = false;                               
                                QSqlQuery queryHasSubscription("SELECT * FROM hr_vp_subscription_attribution WHERE user_id=" + userId + " AND status='started'"  );
                                if(queryHasSubscription.next()) {
                                    if(queryHasSubscription.value(6).toDateTime() <= QDateTime::currentDateTime()) {
                                        isSubscription = true;
                                        qDebug() << "AAAAAAAAAAAAAAAAAa";
                                    }
                                }

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

                                ids << id;
                            }
                        }


                        if(saas)
                        {
                            if( ids.count()>0 ) {
                                QtSoapMessage message;
                                message.setMethod("callServiceComponent");

                                QtSoapArray *array = new QtSoapArray(QtSoapQName("params"));

                                array->insert(0, new QtSoapSimpleType(QtSoapQName("component"),"velopark"));
                                array->insert(1, new QtSoapSimpleType(QtSoapQName("class"),"velopark"));
                                array->insert(2, new QtSoapSimpleType(QtSoapQName("function"),"syncStandalone"));
                                array->insert(3, new QtSoapSimpleType(QtSoapQName("params"),ids.join(",")));

                                message.addMethodArgument(array);

                                soapClient.submitRequest(message, saas_path+"/index.php?soap=soapComponent&password=" + saas_password + "&username=" + saas_username);
                            } else {
                                timerCheckDb->start(TIME_DB_CHECKING);
                            }
                        }
                        else
                        {
                            if( ids.count()>0 ) {
                                QSqlQuery queryDel("DELETE FROM hr_gantner_standalone_action WHERE id IN (" + ids.join(",") + ")" );
                            }

                            timerCheckDb->start(TIME_DB_CHECKING);
                        }
                    }
                }
            }

        }
    }

    if(!saas) {
        QSqlQuery queryOptimize("OPTIMIZE TABLE hr_gantner_standalone_action");
    }

}

void CVeloPark::accessAccepted(QMap<QString, QVariant> params, bool isAccepted) {
    QMap<QString, QString> p;
    p.clear();

    QString f = "accessAccepted";

    if(!isAccepted)
        f = "accessRefused";

    QString xmlFunc = CXmlFactory::deviceAction( params["deviceParentId"].toString()  ,f, p);
    emit accessAction(xmlFunc);
}


void CVeloPark::initSAASMode()
{
    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

    settings.beginGroup ( "Webservice" );

    if ( !settings.contains ( "saas" ) ) settings.setValue ( "saas", false );
    if ( !settings.contains ( "saas_username" ) ) settings.setValue ( "saas_username", "" );
    if ( !settings.contains ( "saas_password" ) ) settings.setValue ( "saas_password", "" );
    if ( !settings.contains ( "saas_path" ) ) settings.setValue ( "saas_path", "" );
    if ( !settings.contains ( "saas_host" ) ) settings.setValue ( "saas_host", "" );
    if ( !settings.contains ( "saas_ssl" ) ) settings.setValue ( "saas_ssl", true );


    saas = settings.value ( "saas", "false" ).toBool();
    saas_host = settings.value ( "saas_host", "" ).toString();
    saas_ssl = settings.value ( "saas_ssl", true ).toBool();
    saas_username = settings.value ( "saas_username", "" ).toString();
    saas_password = settings.value ( "saas_password", "" ).toString();
    saas_path = settings.value ( "saas_path", "" ).toString();

    if(saas)
    {
        soapClient.setHost(saas_host,saas_ssl);

        connect(&soapClient, SIGNAL(responseReady()),this, SLOT(readSoapResponse()));
        connect(soapClient.networkAccessManager(),SIGNAL(sslErrors( QNetworkReply *, const QList<QSslError> & )),
                this, SLOT(soapSSLErrors(QNetworkReply*,QList<QSslError>)));

    }

}

void CVeloPark::soapSSLErrors ( QNetworkReply * reply, const QList<QSslError> & errors )
{
    foreach(QSslError sslError, errors)
    {
        if(sslError.error() == QSslError::SelfSignedCertificate)
        {
            reply->ignoreSslErrors();
        }
    }
}

void CVeloPark::readSoapResponse()
{
    // check if the response from the web service is ok
    const QtSoapMessage &response = soapClient.getResponse();

    if (response.isFault()) {
        qDebug() << "(CVeloPark) Not able to call the Horux GUI web service. (" << response.faultString().toString() << ":" << response.faultCode () << ")";
        timerCheckDb->start(TIME_DB_CHECKING);
        return;
    }

    if(response.returnValue().toString().toInt() < 0)
    {
        timerCheckDb->start(TIME_DB_CHECKING);
        return;
    }

    QStringList ids = response.returnValue().toString().split(",");

    foreach(QString id, ids)
    {
        QSqlQuery queryDel("DELETE FROM hr_gantner_standalone_action WHERE id=" + id );
    }

    QSqlQuery queryOptimize("OPTIMIZE TABLE hr_gantner_standalone_action");

    timerCheckDb->start(TIME_DB_CHECKING);

}

//! Q_EXPORT_PLUGIN2(TARGET, CLASSNAME);
Q_EXPORT_PLUGIN2(velopark, CVeloPark);
