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
#include "cgantnertime.h"
#include <QCoreApplication>
#include <QtCore>
#include <QtXml>
#include <QSslError>
#include <QNetworkReply>


CGantnerTime::CGantnerTime(QObject *parent) : QObject(parent)
{
    timerCheckDb = new QTimer(this);
    connect(timerCheckDb, SIGNAL(timeout()), this, SLOT(checkDb()));
    timerCheckDb->start(TIME_DB_CHECKING);

    initSAASMode();
}

bool CGantnerTime::isAccess(QMap<QString, QVariant> params, bool, bool )
{
    QString userId = params["userId"].toString();
    QString deviceId = params["deviceId"].toString();
    QString date = params["date"].toString();
    QString time = params["time"].toString();
    QString key = params["key"].toString();
    QString code = params["code"].toString();
    QString reason = params["reason"].toString() == "" ? "0" : params["reason"].toString();

    QString keyId = "";


    if(userId == "0") {
        QSqlQuery querykey = "SELECT ka.id_user FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id LEFT JOIN hr_keys AS k ON k.id=ka.id_key  WHERE k.serialNumber='" + key + "'";
        if(querykey.next()) {
            userId = querykey.value(0).toString();
        }
        else {
            return false;
        }
    }

    // get the id of the key
    QSqlQuery querykey("SELECT id FROM hr_keys WHERE serialNumber='" + key + "'");

    if(querykey.next()) {
        keyId = querykey.value(0).toString();
    }

    if(code == "150") {

            QString BDE1 =  params["BDEValue1"].toString();
            QString BDE2 =  params["BDEValue2"].toString();
            QString BDE3 =  params["BDEValue3"].toString();
            QString BDE4 =  params["BDEValue4"].toString();
            QString BDE5 =  params["BDEValue5"].toString();
            QString BDE6 =  params["BDEValue6"].toString();
            QString BDE7 =  params["BDEValue7"].toString();
            QString BDE8 =  params["BDEValue8"].toString();
            QString BDE9 =  params["BDEValue9"].toString();
            QString BDE10 =  params["BDEValue10"].toString();
            QString BDE11 =  params["BDEValue11"].toString();
            QString BDE12 =  params["BDEValue12"].toString();
            QString BDE13 =  params["BDEValue13"].toString();
            QString BDE14 =  params["BDEValue14"].toString();
            QString BDE15 =  params["BDEValue15"].toString();
            QString BDE16 =  params["BDEValue16"].toString();
            QString BDE17 =  params["BDEValue17"].toString();
            QString BDE18 =  params["BDEValue18"].toString();
            QString BDE19 =  params["BDEValue19"].toString();
            QString BDE20 =  params["BDEValue20"].toString();

            QSqlQuery bookquery("INSERT INTO `hr_timux_booking_bde` ( `user_id` , `device_id`, `date`, `time`, `code`, `BDE1`, `BDE2`, `BDE3`, `BDE4`, `BDE5`, `BDE6`, `BDE7`, `BDE8`, `BDE9`, `BDE10`, `BDE11`, `BDE12`, `BDE13`, `BDE14`, `BDE15`, `BDE16`, `BDE17`, `BDE18`, `BDE19`, `BDE20` ) VALUES (" +
                        userId +
                        "," +
                        deviceId +
                        ",'" +
                        date +
                        "','" +
                        time +
                        "','" +
                        code +
                        "','" +
                        BDE1 +
                        "','" +
                        BDE2 +
                        "','" +
                        BDE3 +
                        "','" +
                        BDE4 +
                        "','" +
                        BDE5 +
                        "','" +
                        BDE6 +
                        "','" +
                        BDE7 +
                        "','" +
                        BDE8 +
                        "','" +
                        BDE9 +
                        "','" +
                        BDE10 +
                        "','" +
                        BDE11 +
                        "','" +
                        BDE12 +
                        "','" +
                        BDE13 +
                        "','" +
                        BDE14 +
                        "','" +
                        BDE15 +
                        "','" +
                        BDE16 +
                        "','" +
                        BDE17 +
                        "','" +
                        BDE18 +
                        "','" +
                        BDE19 +
                        "','" +
                        BDE20 +
                        "')"
                        );

            setUserPresence(userId, true);

        } else {



        // compute the round of the time
        QStringList timeSplit = time.split(":");

        QTime roundBooking ;
        QTime noRoundBooking(timeSplit.at(0).toInt(),timeSplit.at(1).toInt(),timeSplit.at(2).toInt()) ;

        QSqlQuery queryTimuxConfig("SELECT bookingRounding  FROM hr_timux_config");
        if(queryTimuxConfig.next())
        {
            int round = queryTimuxConfig.value(0).toInt();

            if(round == 0)
            {
                roundBooking = noRoundBooking;
                if(code == "255" || reason.right(3) == "_IN" || code == "155") // arrive
                {
                    setUserPresence(userId, true);
                }
                else
                {
                     if(code == "254" || reason.right(4) == "_OUT") // leave
                     {
                        setUserPresence(userId, false);
                     }
                }
            }
            else
            {
                if(round == 1)
                {
                   if(code == "255" || reason.right(3) == "_IN" || code == "155") // arrive
                   {
                        roundBooking.setHMS(noRoundBooking.hour(),noRoundBooking.minute()+1,0);
                        setUserPresence(userId, true);
                   }
                   else
                   {
                        if(code == "254" || reason.right(4) == "_OUT") // leave
                        {
                            roundBooking.setHMS(noRoundBooking.hour(),noRoundBooking.minute(),0);
                            setUserPresence(userId, false);
                        }
                   }

                }
                else
                {
                    int m = noRoundBooking.minute();
                    while(m % round != 0)
                    {
                        if(code == "255" || reason.right(3) == "_IN" || code == "155") // arrive
                        {
                            m++;                            
                        }
                        else
                        {
                            if(code == "254" || reason.right(4) == "_OUT") // leave
                            {
                                m--;
                            }
                        }
                    }

                    if(code == "255" || reason.right(3) == "_IN" || code == "155") // arrive
                    {
                        roundBooking.setHMS(noRoundBooking.hour(),m,0);
                        setUserPresence(userId, true);
                    }
                    else
                    {
                        if(code == "254" || reason.right(4) == "_OUT") // leave
                        {
                            roundBooking.setHMS(noRoundBooking.hour(),m,0);
                            setUserPresence(userId, false);
                        }
                    }
                }
            }
        }

        QString lastTrackingId;

        // this is an entry BDE
        if(code == "155") {

            // be sure the last booking was an exit. Wo have to check this. If not the case, we have to insert an exit booking with the same time than
            // this booking
            QSqlQuery lastBooking("SELECT tb.action FROM  hr_timux_booking AS tb LEFT JOIN hr_tracking AS t ON tb.tracking_id=t.id WHERE t.id_user=" + userId + " ORDER BY t.date DESC, t.time DESC, tb.tracking_id DESC LIMIT 0,1");
            if(lastBooking.next()) {
                if(lastBooking.value(0) == "255") { // last booking was an already an entry, we have to insert an exit

                    QSqlQuery query = "INSERT INTO `hr_tracking` ( `id` , `id_user` , `id_key` , `time` , `date` , `id_entry` , `is_access` , `id_comment`, `key`, `extData` ) VALUES ('', '" +
                                userId +
                                "','" +
                                keyId +
                                "', '" + time +"', '" + date +"', '" +
                                deviceId +
                                "', '" +
                                "1" +
                                "', '" +
                                "0" +
                                "', '" +
                                key +
                                "', '" +
                                "hr_timux_booking"
                                "')";

                    QSqlQuery lastTracking = "SELECT id FROM `hr_tracking` WHERE id_entry=" + deviceId + " AND id_user=" + userId + " ORDER BY id DESC LIMIT 0,1";
                    lastTracking.next();
                    QString last = lastTracking.value(0).toString();

                    QSqlQuery bookquery("INSERT INTO `hr_timux_booking` ( `tracking_id` , `action` , `actionReason`, `roundBooking` ) VALUES (" +
                                last +
                                "," +
                                "254" +
                                ",'" +
                                reason +
                                "','" +
                                roundBooking.toString("hh:mm:ss") +
                                "')"
                                );

                }

            }

            QSqlQuery query("INSERT INTO `hr_tracking` ( `id` , `id_user` , `id_key` , `time` , `date` , `id_entry` , `is_access` , `id_comment`, `key`, `extData` ) VALUES ('', '" +
                        userId +
                        "','" +
                        keyId +
                        "', '" + time +"', '" + date +"', '" +
                        deviceId +
                        "', '" +
                        "1" +
                        "', '" +
                        "0" +
                        "', '" +
                        key +
                        "', '" +
                        "hr_timux_booking"
                        "')"
                        );

            QSqlQuery lastTracking = "SELECT id FROM `hr_tracking` WHERE id_entry=" + deviceId + " AND id_user=" + userId + " ORDER BY id DESC LIMIT 0,1";
            lastTracking.next();
            lastTrackingId = lastTracking.value(0).toString();


            QString BDE1 =  params["BDEValue1"].toString();
            QString BDE2 =  params["BDEValue2"].toString();
            QString BDE3 =  params["BDEValue3"].toString();
            QString BDE4 =  params["BDEValue4"].toString();
            QString BDE5 =  params["BDEValue5"].toString();
            QString BDE6 =  params["BDEValue6"].toString();
            QString BDE7 =  params["BDEValue7"].toString();
            QString BDE8 =  params["BDEValue8"].toString();
            QString BDE9 =  params["BDEValue9"].toString();
            QString BDE10 =  params["BDEValue10"].toString();
            QString BDE11 =  params["BDEValue11"].toString();
            QString BDE12 =  params["BDEValue12"].toString();
            QString BDE13 =  params["BDEValue13"].toString();
            QString BDE14 =  params["BDEValue14"].toString();
            QString BDE15 =  params["BDEValue15"].toString();
            QString BDE16 =  params["BDEValue16"].toString();
            QString BDE17 =  params["BDEValue17"].toString();
            QString BDE18 =  params["BDEValue18"].toString();
            QString BDE19 =  params["BDEValue19"].toString();
            QString BDE20 =  params["BDEValue20"].toString();

            QSqlQuery bookquery("INSERT INTO `hr_timux_booking_bde` (  `tracking_id` , `user_id` , `device_id`, `date`, `time`, `code`, `BDE1`, `BDE2`, `BDE3`, `BDE4`, `BDE5`, `BDE6`, `BDE7`, `BDE8`, `BDE9`, `BDE10`, `BDE11`, `BDE12`, `BDE13`, `BDE14`, `BDE15`, `BDE16`, `BDE17`, `BDE18`, `BDE19`, `BDE20` ) VALUES (" +
                        lastTrackingId +
                        "," +
                        userId +
                        "," +
                        deviceId +
                        ",'" +
                        date +
                        "','" +
                        roundBooking.toString("hh:mm:ss") +
                        "','" +
                        code +
                        "','" +
                        BDE1 +
                        "','" +
                        BDE2 +
                        "','" +
                        BDE3 +
                        "','" +
                        BDE4 +
                        "','" +
                        BDE5 +
                        "','" +
                        BDE6 +
                        "','" +
                        BDE7 +
                        "','" +
                        BDE8 +
                        "','" +
                        BDE9 +
                        "','" +
                        BDE10 +
                        "','" +
                        BDE11 +
                        "','" +
                        BDE12 +
                        "','" +
                        BDE13 +
                        "','" +
                        BDE14 +
                        "','" +
                        BDE15 +
                        "','" +
                        BDE16 +
                        "','" +
                        BDE17 +
                        "','" +
                        BDE18 +
                        "','" +
                        BDE19 +
                        "','" +
                        BDE20 +
                        "')"
                        );

            code = "255";


        } else {

            QSqlQuery query("INSERT INTO `hr_tracking` ( `id` , `id_user` , `id_key` , `time` , `date` , `id_entry` , `is_access` , `id_comment`, `key`, `extData` ) VALUES ('', '" +
                        userId +
                        "','" +
                        keyId +
                        "', '" + time +"', '" + date +"', '" +
                        deviceId +
                        "', '" +
                        "1" +
                        "', '" +
                        "0" +
                        "', '" +
                        key +
                        "', '" +
                        "hr_timux_booking"
                        "')"
                        );

            QSqlQuery lastTracking = "SELECT id FROM `hr_tracking` WHERE id_entry=" + deviceId + " AND id_user=" + userId + " ORDER BY id DESC LIMIT 0,1";
            lastTracking.next();
            lastTrackingId = lastTracking.value(0).toString();
        }

        QSqlQuery bookquery("INSERT INTO `hr_timux_booking` ( `tracking_id` , `action` , `actionReason`, `roundBooking` ) VALUES (" +
                    lastTrackingId +
                    "," +
                    code +
                    ",'" +
                    reason +
                    "','" +
                    roundBooking.toString("hh:mm:ss") +
                    "')"
                    );

        checkBalances(userId.toInt());
    }

    return true;
}

void CGantnerTime::setUserPresence(QString userId, bool isPresent) {


    QMapIterator<int, bool> i(devices);
    while (i.hasNext())
    {
        i.next();

        if(i.value() ) {
            QSqlQuery userQuery;
            userQuery.prepare("SELECT CONCAT(name, ' ', firstname) AS fullname, language FROM hr_user AS u LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=u.id LEFT JOIN hr_user_group_access AS ugac ON ugac.id_group=uga.id_group  WHERE u.id=:id AND ugac.id_device=:id_device");
            userQuery.bindValue(":id", userId);
            userQuery.bindValue(":id_device", i.key());
            userQuery.exec();

            if(userQuery.next()) {

                QMap<QString, QString> p;
                QString f = "addUser";
                p["userId"] = userId;
                p["userNo"] = userId;
                p["displayName"] = userQuery.value(0).toString();
                p["lang"] = userQuery.value(1).toString();
                p["fiuUse"] = "0";

                if(isPresent)
                    p["attendanceStatus"] = "1";
                else
                    p["attendanceStatus"] = "2";

                QString xmlFunc = CXmlFactory::deviceAction( QString::number(i.key()) ,f, p);

                emit accessAction(xmlFunc);
            }
        }
    }

}

void CGantnerTime::deviceEvent(QString xml)
{
    QMap<QString, QVariant>params = CXmlFactory::deviceEvent(xml) ;

    QString event = params["event"].toString();

    if(event == "bookingDetected")
        isAccess(params, true, true);

    if(event == "reloadAllData" )
        reloadAllData();
}

void CGantnerTime::reloadAllData()
{
    //Reload all user
    QSqlQuery userQuery("SELECT id, CONCAT(name, ' ', firstname) AS fullname, language FROM hr_user");
    while(userQuery.next())
    {
        QSqlQuery groupAttributionQuery;
        groupAttributionQuery.prepare("SELECT pg.id, pg.name, pg.comment FROM hr_user_group AS pg LEFT JOIN hr_user_group_attribution AS ga ON ga.id_group=pg.id LEFT JOIN hr_user AS pe ON pe.id=ga.id_user WHERE pe.id=:id");
        groupAttributionQuery.bindValue(":id", userQuery.value(0));
        groupAttributionQuery.exec();

        while( groupAttributionQuery.next())
        {
            QString groupId = groupAttributionQuery.value(0).toString();

            QSqlQuery deviceQuery;
            deviceQuery.prepare("SELECT uga.id_device FROM hr_user_group_access AS uga LEFT JOIN hr_device AS d ON d.id = uga.id_device WHERE id_group=:id AND d.type='gantner_TimeTerminal'");
            deviceQuery.bindValue(":id", groupId);
            deviceQuery.exec();

            while( deviceQuery.next())
            {
                QString deviceId = deviceQuery.value(0).toString();
                QSqlQuery insertQuery;
                insertQuery.prepare("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`, `param3`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param, :param2, :param3)");
                insertQuery.bindValue(":func", "add");
                insertQuery.bindValue(":type", "user");
                insertQuery.bindValue(":userId", userQuery.value(0) );
                insertQuery.bindValue(":keyId", 0 );
                insertQuery.bindValue(":deviceId", deviceId );
                insertQuery.bindValue(":param", userQuery.value(1)  );
                insertQuery.bindValue(":param2",userQuery.value(2) );


                //check the last booking to know if the user is present or absent
                QSqlQuery checkLastBooking;
                checkLastBooking.prepare("SELECT * FROM hr_tracking AS t LEFT JOIN hr_timux_booking AS tb ON t.id=tb.tracking_id WHERE t.id_user=:userId ORDER BY t.date DESC, t.time DESC LIMIT 0,1");
                checkLastBooking.bindValue(":userId", userQuery.value(0) );
                checkLastBooking.exec();
                if(checkLastBooking.next()) {
                    if(checkLastBooking.value(11).toInt() == 255 || checkLastBooking.value(11).toInt() == 155 || checkLastBooking.value(12).toString().right(3) == "_IN" ) {
                        insertQuery.bindValue(":param3",1 );
                    } else {
                        insertQuery.bindValue(":param3",2 );
                    }
                } else {
                    //no booking, the user is absent
                    insertQuery.bindValue(":param3",2 );
                }



                insertQuery.exec();

                QSqlQuery keyQuery;
                keyQuery.prepare("SELECT k.id, serialNumber FROM hr_keys_attribution AS ka LEFT JOIN hr_keys AS k ON k.id=ka.id_key WHERE id_user=:id AND k.isBlocked=0");
                keyQuery.bindValue(":id", userQuery.value(0) );
                keyQuery.exec();

                while(keyQuery.next())
                {
                    QSqlQuery insertQuery;
                    insertQuery.prepare("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param, :param2)");
                    insertQuery.bindValue(":func", "add");
                    insertQuery.bindValue(":type", "key");
                    insertQuery.bindValue(":userId", userQuery.value(0) );
                    insertQuery.bindValue(":keyId", keyQuery.value(0) );
                    insertQuery.bindValue(":deviceId", deviceId );
                    insertQuery.bindValue(":param", keyQuery.value(1)  );
                    insertQuery.bindValue(":param2", "" );
                    insertQuery.exec();
                }
            }
        }
    }

    // Reload all absent reason
    QSqlQuery deviceQuery("SELECT id_device FROM hr_gantner_TimeTerminal");

    while( deviceQuery.next())
    {
        QSqlQuery timeCodeInQuery("SELECT * FROM hr_timux_timecode WHERE signtype='both' OR signtype='in'");

        while( timeCodeInQuery.next())
        {
            QSqlQuery insertQuery;
            insertQuery.prepare("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`,`reasonId`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param, :param2,:reasonId)");
            insertQuery.bindValue(":func", "add");
            insertQuery.bindValue(":type", "reason");
            insertQuery.bindValue(":userId", 0 );
            insertQuery.bindValue(":keyId", 0 );
            insertQuery.bindValue(":deviceId", deviceQuery.value(0) );
            insertQuery.bindValue(":param", timeCodeInQuery.value(13).toString()  );
            insertQuery.bindValue(":param2", "1" );
            insertQuery.bindValue(":reasonId", timeCodeInQuery.value(0).toString() + "_IN" );
            insertQuery.exec();
        }

        QSqlQuery timeCodeOutQuery("SELECT * FROM hr_timux_timecode WHERE signtype='both' OR signtype='out'");

        while( timeCodeOutQuery.next())
        {
            QSqlQuery insertQuery;
            insertQuery.prepare("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`,`reasonId`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param, :param2,:reasonId)");
            insertQuery.bindValue(":func", "add");
            insertQuery.bindValue(":type", "reason");
            insertQuery.bindValue(":userId", 0 );
            insertQuery.bindValue(":keyId", 0 );
            insertQuery.bindValue(":deviceId", deviceQuery.value(0) );

            insertQuery.bindValue(":param", timeCodeOutQuery.value(13).toString()  );

            insertQuery.bindValue(":param2", "2" );
            insertQuery.bindValue(":reasonId", timeCodeOutQuery.value(0).toString() + "_OUT" );
            insertQuery.exec();
        }

        //Reload balances text
        QSqlQuery balancesTextQuery("SELECT * FROM hr_timux_timecode WHERE (type='overtime' OR type='leave') AND (defaultHoliday=1 OR defaultOvertime=1) ORDER BY type");
        int fieldNo = 0;
        while( balancesTextQuery.next())
        {
            QSqlQuery insertQuery;
            insertQuery.prepare("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param, :param2)");
            insertQuery.bindValue(":func", "add");
            insertQuery.bindValue(":type", "balancesText");
            insertQuery.bindValue(":userId", 0 );
            insertQuery.bindValue(":keyId", 0 );
            insertQuery.bindValue(":deviceId", deviceQuery.value(0) );
            insertQuery.bindValue(":param",fieldNo  );
            insertQuery.bindValue(":param2",balancesTextQuery.value(13).toString()  );
            insertQuery.exec();
            fieldNo++;
        }

        for(int i=fieldNo; i<10; i++)
        {
            QSqlQuery insertQuery;
            insertQuery.prepare("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param, :param2)");
            insertQuery.bindValue(":func", "add");
            insertQuery.bindValue(":type", "balancesText");
            insertQuery.bindValue(":userId", 0 );
            insertQuery.bindValue(":keyId", 0 );
            insertQuery.bindValue(":deviceId", deviceQuery.value(0) );
            insertQuery.bindValue(":param",i  );
            insertQuery.bindValue(":param2","");
            insertQuery.exec();
        }

        //Reload user balances
        checkBalances();

        // Remove all the load time code
        for(int i=1; i<=20; i++ ) {
            QSqlQuery insertQuery;
            insertQuery.prepare("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param)");
            insertQuery.bindValue(":func", "sub");
            insertQuery.bindValue(":type", "load");
            insertQuery.bindValue(":userId", 0 );
            insertQuery.bindValue(":keyId", 0 );
            insertQuery.bindValue(":deviceId", deviceQuery.value(0) );
            insertQuery.bindValue(":param",i  );
            insertQuery.exec();

        }

        //Reload the load time code
        QSqlQuery loadQuery("SELECT * FROM hr_timux_timecode WHERE type='load' ORDER BY name");
        while( loadQuery.next()) {
            QSqlQuery insertQuery;
            insertQuery.prepare("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`, `param3`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param, :param2, :param3)");
            insertQuery.bindValue(":type", "load");
            insertQuery.bindValue(":func", "add");
            insertQuery.bindValue(":userId", 0 );
            insertQuery.bindValue(":keyId", 0 );
            insertQuery.bindValue(":deviceId", deviceQuery.value(0) );
            insertQuery.bindValue(":param",loadQuery.value(15).toString()  );
            insertQuery.bindValue(":param2",loadQuery.value(2).toString());
            insertQuery.bindValue(":param3",loadQuery.value(13).toString());
            insertQuery.exec();

        }
    }

}

void CGantnerTime::deviceConnectionMonitor(int id, bool status)
{


    QSqlQuery query("SELECT * FROM hr_device WHERE type='gantner_TimeTerminal' AND id=" + QString::number(id));

    if(query.next())
    {
        devices[id] = status;
    }
}

void CGantnerTime::deviceInputMonitor ( int , int , bool )
{

}

void CGantnerTime::checkDb()
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

                if(type == "user")
                {
                    QMap<QString, QString> p;
                    QString f = func == "add" ? "addUser" : "removeUser" ;
                    p["userId"] = userId;
                    p["userNo"] = userId;
                    p["displayName"] = param;
                    p["lang"] = param2;
                    p["fiuUse"] = "0";

                    if(param3 == "")
                        p["attendanceStatus"] = "2";
                    else
                        p["attendanceStatus"] = param3;

                    QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                    emit accessAction(xmlFunc);
                }

                if(type == "key")
                {
                    QMap<QString, QString> p;
                    QString f = func == "add" ? "addKey" : "removeKey" ;
                    p["userId"] = userId;
                    p["key"] = param;
                    p["keyType"] = "01";
                    QString xmlFunc = CXmlFactory::deviceAction( deviceId, f, p);
                    emit accessAction(xmlFunc);
                }

                if(type == "key_user")
                {
                    QMap<QString, QString> p2;
                    QString f2 = func == "add" ? "addUser" : "removeUser" ;
                    p2["userId"] = userId;
                    p2["userNo"] = userId;
                    p2["displayName"] = param;
                    p2["lang"] = param2;
                    p2["fiuUse"] = "0";
                    p2["attendanceStatus"] = "2";

                    QString xmlFunc2 = CXmlFactory::deviceAction( deviceId ,f2, p2);
                    emit accessAction(xmlFunc2);

                    QMap<QString, QString> p;
                    QString f = func == "add" ? "addKey" : "removeKey" ;
                    p["userId"] = userId;
                    p["key"] = param3;
                    p["keyType"] = "01";

                    QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                    emit accessAction(xmlFunc);

                }

                if(type == "reason")
                {
                    QMap<QString, QString> p;
                    QString f = func == "add" ? "addAbsentReason" : "removeAbsentReason" ;
                    p["reasonId"] = reasonId;
                    p["text"] = param;
                    p["status"] = param2;
                    p["group"] = param2 == "1" ? "IN" : "OUT";
                    QString xmlFunc = CXmlFactory::deviceAction( deviceId, f, p);
                    emit accessAction(xmlFunc);

                }

                if(type == "balances")
                {
                    QMap<QString, QString> p;
                    QString f = "addUserBalances";
                    p["userId"] = userId;
                    p["balances"] = param;
                    QString xmlFunc = CXmlFactory::deviceAction( deviceId, f, p);
                    emit accessAction(xmlFunc);

                }

                if(type == "balancesText")
                {
                    QMap<QString, QString> p;
                    QString f = "setBalanceText";
                    p["fieldNo"] = param;
                    p["text"] = param2;
                    QString xmlFunc = CXmlFactory::deviceAction( deviceId, f, p);
                    emit accessAction(xmlFunc);

                }

                if(type == "reinit")
                {
                    QMap<QString, QString> p;
                    QString f = "reinit";
                    QString xmlFunc = CXmlFactory::deviceAction( deviceId, f, p);
                    emit accessAction(xmlFunc);
                }

                if(type == "load") {
                    QMap<QString, QString> p;
                    QString f = func == "add" ? "addBDEData" : "removeBDEData";

                    if(func == "add") {
                        p["BDEfieldNo"] = param;
                        p["value"] = param2;
                        p["valueText"] = param3;
                    } else {
                        p["BDEfieldNo"] = param;
                        p["value"] = param2;
                    }
                    QString xmlFunc = CXmlFactory::deviceAction( deviceId, f, p);
                    emit accessAction(xmlFunc);
                }

                ids << id;
            }

            if(saas && ids.count()>0)
            {
                QtSoapMessage message;
                message.setMethod("callServiceComponent");

                QtSoapArray *array = new QtSoapArray(QtSoapQName("params"));

                array->insert(0, new QtSoapSimpleType(QtSoapQName("component"),"timuxadmin"));
                array->insert(1, new QtSoapSimpleType(QtSoapQName("class"),"timuxAdminDevice"));
                array->insert(2, new QtSoapSimpleType(QtSoapQName("function"),"syncStandalone"));
                array->insert(3, new QtSoapSimpleType(QtSoapQName("params"),ids.join(",")));

                message.addMethodArgument(array);

                soapClient.submitRequest(message, saas_path+"/index.php?soap=soapComponent&password=" + saas_password + "&username=" + saas_username);
            }
            else
            {
                QSqlQuery queryDel("DELETE FROM hr_gantner_standalone_action WHERE deviceId=" + QString::number(i.key()) );
                timerCheckDb->start(TIME_DB_CHECKING);
            }

        }
    }

    if(!saas)
        QSqlQuery queryOptimize("OPTIMIZE TABLE hr_gantner_standalone_action");
}

void CGantnerTime::initSAASMode()
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

        soapClientBalances.setHost(saas_host,saas_ssl);

        connect(&soapClientBalances, SIGNAL(responseReady()),this, SLOT(readSoapBalancesResponse()));
        connect(soapClientBalances.networkAccessManager(),SIGNAL(sslErrors( QNetworkReply *, const QList<QSslError> & )),
                this, SLOT(soapSSLErrors(QNetworkReply*,QList<QSslError>)));

        timerCheckBalances = new QTimer(this);
        connect(timerCheckBalances, SIGNAL(timeout()), this, SLOT(checkBalances()));
        timerCheckBalances->start(10000 * 6 * 60); // check every 1 hours
        checkBalances();
    }
    else
    {
        soapClientBalances.setHost(saas_host,saas_ssl);

        connect(&soapClientBalances, SIGNAL(responseReady()),this, SLOT(readSoapBalancesResponse()));
        connect(soapClientBalances.networkAccessManager(),SIGNAL(sslErrors( QNetworkReply *, const QList<QSslError> & )),
                this, SLOT(soapSSLErrors(QNetworkReply*,QList<QSslError>)));

        timerCheckBalances = new QTimer(this);
        connect(timerCheckBalances, SIGNAL(timeout()), this, SLOT(checkBalances()));
        timerCheckBalances->start(10000 * 6 * 60); // check every 1 hours
        checkBalances();
    }
}


void CGantnerTime::checkBalances(int id)
{
    if(devices.count() == 0) return;

    QtSoapMessage message;
    message.setMethod("callServiceComponent");

    QtSoapArray *array = new QtSoapArray(QtSoapQName("params"));

    array->insert(0, new QtSoapSimpleType(QtSoapQName("component"),"timuxadmin"));
    array->insert(1, new QtSoapSimpleType(QtSoapQName("class"),"timuxAdminDevice"));
    array->insert(2, new QtSoapSimpleType(QtSoapQName("function"),"syncBalances"));
    array->insert(3, new QtSoapSimpleType(QtSoapQName("id"),QString::number(id)));

    message.addMethodArgument(array);

    soapClientBalances.submitRequest(message, saas_path+"/index.php?soap=soapComponent&password=" + saas_password + "&username=" + saas_username);
}

void CGantnerTime::soapSSLErrors ( QNetworkReply * reply, const QList<QSslError> & errors )
{
    foreach(QSslError sslError, errors)
    {
        if(sslError.error() == QSslError::SelfSignedCertificate)
        {
            reply->ignoreSslErrors();
        }
    }
}

void CGantnerTime::readSoapBalancesResponse()
{

    // check if the response from the web service is ok
    const QtSoapMessage &response = soapClientBalances.getResponse();

    if (response.isFault()) {
        qDebug() << "(CGantnerTime) Not able to call the Horux GUI web service. (" << response.faultString().toString() << ":" << response.faultCode () << ")";
        return;
    }

    if(response.returnValue().toString().toInt() < 0)
    {
        qDebug() << response.returnValue().toString();
        return;
    }

    QSettings settings(QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat);
    settings.beginGroup("GantnerTimeTerminal");

    QString hoursText = settings.value("hoursText","heures").toString();
    if(!settings.contains("hoursText")) settings.setValue("hoursText", "heures");

    QString daysText = settings.value("daysText","jours").toString();
    if(!settings.contains("daysText")) settings.setValue("daysText", "jours");

    QStringList balances = response.returnValue().toString().split(";");

    foreach( QString balance, balances)
    {
        QStringList param = balance.split("/");

        if(param.count() != 3) continue;

        QString userId = param[0];
        QString overtime = param[1];
        QString holidays = param[2];
        QString balances = "";

        balances += holidays + " " + daysText + ";";
        balances +=  overtime + " " + hoursText + ";";

        // insert new balances values
        QSqlQuery deviceQuery("SELECT id_device FROM hr_gantner_TimeTerminal");

        while( deviceQuery.next())
        {

            QSqlQuery insertQuery;
            insertQuery.prepare("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param, :param2)");
            insertQuery.bindValue(":func", "add");
            insertQuery.bindValue(":type", "balances");
            insertQuery.bindValue(":userId", userId );
            insertQuery.bindValue(":keyId", 0 );
            insertQuery.bindValue(":deviceId", deviceQuery.value(0) );
            insertQuery.bindValue(":param", balances  );
            insertQuery.bindValue(":param2", ""  );
            insertQuery.exec();
        }
    }
}


void CGantnerTime::readSoapResponse()
{
    // check if the response from the web service is ok
    const QtSoapMessage &response = soapClient.getResponse();

    if (response.isFault()) {
        qDebug() << "(CGantnerTime) Not able to call the Horux GUI web service. (" << response.faultString().toString() << ":" << response.faultCode () << ")";
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
Q_EXPORT_PLUGIN2(gantnertime, CGantnerTime);
