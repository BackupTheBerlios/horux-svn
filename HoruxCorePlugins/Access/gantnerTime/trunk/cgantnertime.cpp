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

CGantnerTime::CGantnerTime(QObject *parent) : QObject(parent)
{
    timerCheckDb = new QTimer(this);
    connect(timerCheckDb, SIGNAL(timeout()), this, SLOT(checkDb()));
    timerCheckDb->start(1000);
}

bool CGantnerTime::isAccess(QMap<QString, QVariant> params, bool)
{
    QString userId = params["userId"].toString();
    QString deviceId = params["deviceId"].toString();
    QString date = params["date"].toString();
    QString time = params["time"].toString();
    QString key = params["key"].toString();
    QString code = params["code"].toString();
    QString reason = params["reason"].toString() == "" ? "0" : params["reason"].toString();

    QSqlQuery querykey = "SELECT id FROM hr_keys WHERE serialNumber='" + key + "'";
    querykey.next();

    QSqlQuery query("INSERT INTO `hr_tracking` ( `id` , `id_user` , `id_key` , `time` , `date` , `id_entry` , `is_access` , `id_comment`, `key`, `extData` ) VALUES ('', '" +
                userId +
                "','" +
                querykey.value(0).toString() +
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

    QSqlQuery lastTracking = "SELECT id FROM `hr_tracking` WHERE id_entry=" + deviceId + " ORDER BY id DESC LIMIT 0,1";
    lastTracking.next();
    QString last = lastTracking.value(0).toString();

    QStringList timeSplit = time.split(":");

    QTime roundBooking ;
    QTime noRoundBooking(timeSplit.at(0).toInt(),timeSplit.at(1).toInt(),timeSplit.at(2).toInt()) ;

    QSqlQuery queryTimuxConfig("SELECT bookingRounding  FROM hr_timux_config");
    queryTimuxConfig.next();
    int round = queryTimuxConfig.value(0).toInt();

    if(round == 0)
    {
        roundBooking = noRoundBooking;
    }
    else
    {
        if(round == 1)
        {
           roundBooking.setHMS(noRoundBooking.hour(),noRoundBooking.minute()+1,0);
        }
        else
        {
            int m = noRoundBooking.minute();
            while(m % round != 0)
                m++;
            roundBooking.setHMS(noRoundBooking.hour(),m,0);
        }
    }

    QSqlQuery bookquery("INSERT INTO `hr_timux_booking` ( `tracking_id` , `action` , `actionReason`, `roundBooking` ) VALUES (" +
                last +
                "," +
                code +
                ",'" +
                reason +
                "','" +
                roundBooking.toString("hh:mm:ss") +
                "')"
                );

    return true;
}

void CGantnerTime::deviceEvent(QString xml)
{
    QMap<QString, QVariant>params = CXmlFactory::deviceEvent(xml) ;

    QString event = params["event"].toString();

    if(event == "bookingDetected")
        isAccess(params, true);

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
                insertQuery.prepare("INSERT INTO hr_gantner_standalone_action (`type`, `func`, `userId`,`keyId`, `deviceId`, `param`, `param2`) VALUES (:type,:func,:userId,:keyId,:deviceId, :param, :param2)");
                insertQuery.bindValue(":func", "add");
                insertQuery.bindValue(":type", "user");
                insertQuery.bindValue(":userId", userQuery.value(0) );
                insertQuery.bindValue(":keyId", 0 );
                insertQuery.bindValue(":deviceId", deviceId );
                insertQuery.bindValue(":param", userQuery.value(1)  );
                insertQuery.bindValue(":param2",userQuery.value(2) );
                insertQuery.exec();

                QSqlQuery keyQuery;
                keyQuery.prepare("SELECT k.id, serialNumber FROM hr_keys_attribution AS ka LEFT JOIN keys AS k ON k.id=ka.id_key WHERE id_user=:id AND k.isBlocked=0");
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
        QSqlQuery balancesTextQuery("SELECT * FROM hr_timux_timecode WHERE type='overtime' OR type='leave' ORDER BY type");
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
            insertQuery.bindValue(":param2",balancesTextQuery.value(1).toString()  );
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
        QSqlQuery balancesQuery("SELECT * FROM hr_timux_activity_counter AS ac LEFT JOIN hr_timux_timecode AS t ON t.id=ac.timecode_id WHERE year=0 AND month=0 AND ( type='overtime' OR type='leave')  ORDER BY ac.user_id, t.type");

        QSettings settings(QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat);
        settings.beginGroup("GantnerTimeTerminal");

        QString hoursText = settings.value("hoursText","heures").toString();
        if(!settings.contains("hoursText")) settings.setValue("hoursText", "heures");

        QString daysText = settings.value("daysText","jours").toString();
        if(!settings.contains("daysText")) settings.setValue("daysText", "jours");


        QString userId = "";
        QString balances = "";
        while( balancesQuery.next())
        {
            if(userId != balancesQuery.value(1).toString())
            {
                if(userId != "")
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

                balances = "";
                userId = balancesQuery.value(1).toString();
            }
            QString s;
            if(balancesQuery.value(16).toString() == "day")
                balances += s.sprintf("%.02f",balancesQuery.value(3).toDouble()) + " " + daysText + ";";
            else
                balances +=  s.sprintf("%.02f",balancesQuery.value(3).toDouble()*-1) + " " + hoursText + ";";

        }
    }

}

void CGantnerTime::deviceConnectionMonitor(int id, bool status)
{
    devices[id] = status;
}

void CGantnerTime::deviceInputMonitor ( int , int , bool )
{

}

void CGantnerTime::checkDb()
{
    QMapIterator<int, bool> i(devices);
    while (i.hasNext())
    {
        i.next();

        if(i.value())
        {
            QSqlQuery query("SELECT * FROM hr_gantner_standalone_action WHERE deviceId=" + QString::number(i.key()) + " ORDER BY id" );

            while(query.next())
            {
                QString type = query.value(1).toString();
                QString func = query.value(2).toString();
                QString deviceId = QString::number(i.key());
                QString userId = query.value(3).toString();
                QString keyId = query.value(4).toString();
                QString param = query.value(6).toString();
                QString param2 = query.value(7).toString();
                QString reasonId = query.value(8).toString();

                if(type == "user")
                {
                    QMap<QString, QString> p;
                    QString f = func == "add" ? "addUser" : "removeUser" ;
                    p["userId"] = userId;
                    p["userNo"] = userId;
                    p["displayName"] = param;
                    p["lang"] = param2;
                    p["fiuUse"] = "0";
                    p["attendanceStatus"] = "2";

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
                    QMap<QString, QString> p;
                    QString xmlFunc = CXmlFactory::deviceAction( deviceId ,"", p);
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

            }

            QSqlQuery queryDel("DELETE FROM hr_gantner_standalone_action WHERE deviceId=" + QString::number(i.key()) );

        }
    }

}

//! Q_EXPORT_PLUGIN2(TARGET, CLASSNAME);
Q_EXPORT_PLUGIN2(gantnertime, CGantnerTime);
