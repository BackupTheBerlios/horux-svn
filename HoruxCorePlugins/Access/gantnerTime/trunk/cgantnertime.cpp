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
    if(queryTimuxConfig.next())
    {
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
        QSqlQuery balancesTextQuery("SELECT * FROM hr_timux_timecode WHERE (type='overtime' OR type='leave')  ORDER BY type");
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
                balances +=  s.sprintf("%.02f",balancesQuery.value(3).toDouble()) + " " + hoursText + ";";
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
        timerCheckBalances->start(1000 * 60 * 60 * 12); // check every 12 hours
        checkBalances();
    }
}


void CGantnerTime::checkBalances()
{
    QtSoapMessage message;
    message.setMethod("callServiceComponent");

    QtSoapArray *array = new QtSoapArray(QtSoapQName("params"));

    array->insert(0, new QtSoapSimpleType(QtSoapQName("component"),"timuxadmin"));
    array->insert(1, new QtSoapSimpleType(QtSoapQName("class"),"timuxAdminDevice"));
    array->insert(2, new QtSoapSimpleType(QtSoapQName("function"),"syncBalances"));

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
        qDebug() << "Not able to call the Horux GUI web service. (" << response.method().name().name() << ")";
        return;
    }

    if(response.returnValue().toString().toInt() < 0)
    {
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

        // Reload all absent reason
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
        qDebug() << "Not able to call the Horux GUI web service. (" << response.method().name().name() << ")";
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
