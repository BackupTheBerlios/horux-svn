
#include "cgantneraccess.h"
#include <QCoreApplication>
#include <QtCore>
#include <QtXml>
#include <QSslError>
#include <QNetworkReply>


CGantnerAccess::CGantnerAccess(QObject *parent) : QObject(parent)
{
    timerCheckDb = new QTimer(this);
    connect(timerCheckDb, SIGNAL(timeout()), this, SLOT(checkDb()));
    timerCheckDb->start(TIME_DB_CHECKING);

    initSAASMode();
}

bool CGantnerAccess::isAccess(QMap<QString, QVariant> params, bool , bool )
{
    QString date = params["date"].toString();
    QString time = params["time"].toString();
    QString key = params["key"].toString();
    QString code = params["code"].toString();

    QSqlQuery queryLasId("SELECT id FROM hr_tracking WHERE hr_tracking.key='" + key + "' ORDER BY id DESC LIMIT 0,1");

    queryLasId.next();

    QSqlQuery update("UPDATE hr_tracking SET `date`='" + date + "', `time`='" + time +  "' WHERE id=" + queryLasId.value(0).toString());

    //Pin code incorrect
    if(code == "5")
        QSqlQuery update("UPDATE hr_tracking SET `id_comment`='12', `is_access`=0 WHERE id=" + queryLasId.value(0).toString());

    //repeated access barrier
    if( code == "E")
        QSqlQuery update("UPDATE hr_tracking SET `id_comment`='13', `is_access`=0 WHERE id=" + queryLasId.value(0).toString());

    // access without door opening
    if( code == "G")
        QSqlQuery update("UPDATE hr_tracking SET `id_comment`='14', `is_access`=1 WHERE id=" + queryLasId.value(0).toString());

    // access with holdup pincode
    if( code == "H")
    {
        QSqlQuery update("UPDATE hr_tracking SET `id_comment`='15', `is_access`=1 WHERE id=" + queryLasId.value(0).toString());
    }

    // 1 access for 2 person
    if( code == "C")
        QSqlQuery update("UPDATE hr_tracking SET `id_comment`='16', `is_access`=1 WHERE id=" + queryLasId.value(0).toString());

    // Card number or card version invalid
    if(code == "4")
        QSqlQuery update("UPDATE hr_tracking SET `id_comment`='18', `is_access`=0 WHERE id=" + queryLasId.value(0).toString());


    return true;
}

void CGantnerAccess::deviceEvent(QString xml)
{
    QMap<QString, QVariant>params = CXmlFactory::deviceEvent(xml) ;

    QString event = params["event"].toString();

    if(event == "Gantner_AccessTerminal_accessDetected")
    {
        QSqlQuery checkKey("SELECT * FROM hr_keys WHERE serialNumber='" + params["key"].toString() + "'");
        if(checkKey.next())
        {

            //! be sure that the key must be checked by this plugin. This means that the user group must be set to be ckecked by this plugin
            QString sql="SELECT accessPlugin, uga.id_user FROM hr_keys AS k LEFT JOIN hr_keys_attribution AS ka ON ka.id_key=k.id LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=ka.id_user LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE serialNumber='" + params["key"].toString() + "' AND accessPlugin=''";
            QSqlQuery query(sql);

            if(!query.next())
                return;
        }
        if(accessInterfaces.contains("access_horux"))
        {
          bool emitNotification = true;

          if(params["code"].toString() == "E" || params["code"].toString() == "5" || params["code"].toString() == "4" )
          {
               emitNotification = false;
          }

          accessInterfaces["access_horux"]->isAccess(params, true, emitNotification);

          // call this function to update the date/time
          isAccess(params, true, true);
        }
        else
          qDebug("The access plugin gantner depend on the access plugin access_horux");
    }

    if(event == "Gantner_AccessTerminal_reloadAllData" )
        reloadAllData(params["deviceId"].toString());
}

void CGantnerAccess::reloadAllData(QString deviceId)
{
    qDebug() << "Reload day plan for the access level";

    // ACCESS LEVEL
    QSqlQuery accessLevel("SELECT al.*, uga.* FROM hr_access_level AS al LEFT JOIN hr_user_group_access AS uga ON uga.id_access_level=al.id WHERE uga.id_device=" + deviceId + "  GROUP BY al.id");

    int scheduleNumber = 1;
    int dayPlanNumber = 1;
    QStringList daysPlan;

    QList<s_schedule*>schedules;

    while(accessLevel.next())
    {
        QString accessLevelId = accessLevel.value(0).toString();
        bool isFullAccess = accessLevel.value(2).toBool();
        bool isNonWorkingDay = accessLevel.value(3).toBool();
        bool isMondayDefault = accessLevel.value(4).toBool();

        QString dayPlan = "";

        QString horuxDay = "";
        QMap<QString, int>days;

        days["lundi"] = 0;
        days["mardi"] = 0;
        days["mercredi"] = 0;
        days["jeudi"] = 0;
        days["vendredi"] = 0;
        days["samedi"] = 0;
        days["dimanche"] = 0;
        days["special1"] = 0;
        days["special2"] = 0;
        days["special3"] = 0;
        days["special4"] = 0;
        days["special5"] = 0;


        if(isFullAccess)
        {
            dayPlan = "0000235900"; // last two 00 -> flags

            // create a day plan for a full access if it is currently not defined
            if(!daysPlan.contains(dayPlan.leftJustified(100,'0')))
            {
                daysPlan << dayPlan.leftJustified(100,'0');

                dayPlan = dayPlan.leftJustified(100,'0');

                QMap<QString, QString> p;
                QString f = "Gantner_AccessTerminal_setDayPlan" ;
                p["dayPlanNumber"] = QString::number(dayPlanNumber);

                p["fromTime1"] = dayPlan.mid(0,4);
                p["toTime1"] =  dayPlan.mid(4,4);
                p["flags1"] = dayPlan.mid(8,2);

                p["fromTime2"] = dayPlan.mid(10,4);
                p["toTime2"] =  dayPlan.mid(14,4);
                p["flags2"] = dayPlan.mid(18,2);

                p["fromTime3"] = dayPlan.mid(20,4);
                p["toTime3"] =  dayPlan.mid(24,4);
                p["flags3"] = dayPlan.mid(28,2);

                p["fromTime4"] = dayPlan.mid(30,4);
                p["toTime4"] =  dayPlan.mid(34,4);
                p["flags4"] = dayPlan.mid(38,2);

                p["fromTime5"] = dayPlan.mid(40,4);
                p["toTime5"] =  dayPlan.mid(44,4);
                p["flags5"] = dayPlan.mid(48,2);

                p["fromTime6"] = dayPlan.mid(50,4);
                p["toTime6"] =  dayPlan.mid(54,4);
                p["flags6"] = dayPlan.mid(58,2);

                p["fromTime7"] = dayPlan.mid(60,4);
                p["toTime7"] =  dayPlan.mid(64,4);
                p["flags7"] = dayPlan.mid(68,2);

                p["fromTime8"] = dayPlan.mid(70,4);
                p["toTime8"] =  dayPlan.mid(74,4);
                p["flags8"] = dayPlan.mid(78,2);

                p["fromTime9"] = dayPlan.mid(80,4);
                p["toTime9"] =  dayPlan.mid(84,4);
                p["flags9"] = dayPlan.mid(88,2);

                p["fromTime10"] = dayPlan.mid(90,4);
                p["toTime10"] =  dayPlan.mid(94,4);
                p["flags10"] = dayPlan.mid(98,2);


                QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                emit accessAction(xmlFunc);

                dayPlanNumber++;
            }

            for(int i=1; i<=daysPlan.count(); i++)
            {
                if(daysPlan.contains(dayPlan.leftJustified(100,'0')))
                {
                    days["lundi"] = i;
                    days["mardi"] = i;
                    days["mercredi"] = i;
                    days["jeudi"] = i;
                    days["vendredi"] = i;
                    days["samedi"] = i;
                    days["dimanche"] = i;
                    days["special1"] = i;
                    days["special2"] = i;
                    days["special3"] = i;
                    days["special4"] = i;
                    days["special5"] = i;

                    i=daysPlan.count()+1;
                }
            }


        }
        else
        {
            QSqlQuery accessTime("SELECT * FROM hr_access_time WHERE id_access_level=" + accessLevelId + " ORDER BY day,`from`");
            while(accessTime.next())
            {
                if(horuxDay != accessTime.value(2).toString() && dayPlan!="")
                {
                    if(!daysPlan.contains(dayPlan.leftJustified(100,'0')))
                    {
                        daysPlan << dayPlan.leftJustified(100,'0');

                        dayPlan = dayPlan.leftJustified(100,'0');

                        QMap<QString, QString> p;
                        QString f = "Gantner_AccessTerminal_setDayPlan" ;
                        p["dayPlanNumber"] = QString::number(dayPlanNumber);

                        p["fromTime1"] = dayPlan.mid(0,4);
                        p["toTime1"] =  dayPlan.mid(4,4);
                        p["flags1"] = dayPlan.mid(8,2);

                        p["fromTime2"] = dayPlan.mid(10,4);
                        p["toTime2"] =  dayPlan.mid(14,4);
                        p["flags2"] = dayPlan.mid(18,2);

                        p["fromTime3"] = dayPlan.mid(20,4);
                        p["toTime3"] =  dayPlan.mid(24,4);
                        p["flags3"] = dayPlan.mid(28,2);

                        p["fromTime4"] = dayPlan.mid(30,4);
                        p["toTime4"] =  dayPlan.mid(34,4);
                        p["flags4"] = dayPlan.mid(38,2);

                        p["fromTime5"] = dayPlan.mid(40,4);
                        p["toTime5"] =  dayPlan.mid(44,4);
                        p["flags5"] = dayPlan.mid(48,2);

                        p["fromTime6"] = dayPlan.mid(50,4);
                        p["toTime6"] =  dayPlan.mid(54,4);
                        p["flags6"] = dayPlan.mid(58,2);

                        p["fromTime7"] = dayPlan.mid(60,4);
                        p["toTime7"] =  dayPlan.mid(64,4);
                        p["flags7"] = dayPlan.mid(68,2);

                        p["fromTime8"] = dayPlan.mid(70,4);
                        p["toTime8"] =  dayPlan.mid(74,4);
                        p["flags8"] = dayPlan.mid(78,2);

                        p["fromTime9"] = dayPlan.mid(80,4);
                        p["toTime9"] =  dayPlan.mid(84,4);
                        p["flags9"] = dayPlan.mid(88,2);

                        p["fromTime10"] = dayPlan.mid(90,4);
                        p["toTime10"] =  dayPlan.mid(94,4);
                        p["flags10"] = dayPlan.mid(98,2);


                        QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                        emit accessAction(xmlFunc);

                        dayPlanNumber++;
                    }

                    for(int i=1; i<=daysPlan.count(); i++)
                    {
                        if(daysPlan.contains(dayPlan.leftJustified(100,'0')))
                            days[horuxDay] = i;
                    }

                    dayPlan = "";
                }

                horuxDay = accessTime.value(2).toString();
                int horuxFrom = accessTime.value(3).toInt();
                int horuxUntil =  accessTime.value(4).toInt();
                int pinCodeNecessary = accessTime.value(5).toBool() ? 1 : 0;
                int specialRelayPlan = accessTime.value(6).toBool() ? 2 : 0;
                int exitingOnly = accessTime.value(7).toBool() ? 4 : 0;

                int flag = pinCodeNecessary | specialRelayPlan | exitingOnly;

                QString gantnerFrom = QString::number( horuxFrom / 60 ).rightJustified(2,'0') +  QString::number( horuxFrom % 60 ).rightJustified(2,'0');
                QString gantnerUntil = QString::number( horuxUntil / 60 ).rightJustified(2,'0') +  QString::number( horuxUntil % 60 ).rightJustified(2,'0');

                dayPlan += gantnerFrom + gantnerUntil + QString::number( flag ).rightJustified(2,'0'); // 00 -> flags
            }

            if(dayPlan!="")
            {
                if(!daysPlan.contains(dayPlan.leftJustified(100,'0')))
                {
                    daysPlan << dayPlan.leftJustified(100,'0');

                    dayPlan = dayPlan.leftJustified(100,'0');
                    QMap<QString, QString> p;
                    QString f = "Gantner_AccessTerminal_setDayPlan" ;
                    p["dayPlanNumber"] = QString::number(dayPlanNumber);

                    p["fromTime1"] = dayPlan.mid(0,4);
                    p["toTime1"] =  dayPlan.mid(4,4);
                    p["flags1"] = dayPlan.mid(8,2);

                    p["fromTime2"] = dayPlan.mid(10,4);
                    p["toTime2"] =  dayPlan.mid(14,4);
                    p["flags2"] = dayPlan.mid(18,2);

                    p["fromTime3"] = dayPlan.mid(20,4);
                    p["toTime3"] =  dayPlan.mid(24,4);
                    p["flags3"] = dayPlan.mid(28,2);

                    p["fromTime4"] = dayPlan.mid(30,4);
                    p["toTime4"] =  dayPlan.mid(34,4);
                    p["flags4"] = dayPlan.mid(38,2);

                    p["fromTime5"] = dayPlan.mid(40,4);
                    p["toTime5"] =  dayPlan.mid(44,4);
                    p["flags5"] = dayPlan.mid(48,2);

                    p["fromTime6"] = dayPlan.mid(50,4);
                    p["toTime6"] =  dayPlan.mid(54,4);
                    p["flags6"] = dayPlan.mid(58,2);

                    p["fromTime7"] = dayPlan.mid(60,4);
                    p["toTime7"] =  dayPlan.mid(64,4);
                    p["flags7"] = dayPlan.mid(68,2);

                    p["fromTime8"] = dayPlan.mid(70,4);
                    p["toTime8"] =  dayPlan.mid(74,4);
                    p["flags8"] = dayPlan.mid(78,2);

                    p["fromTime9"] = dayPlan.mid(80,4);
                    p["toTime9"] =  dayPlan.mid(84,4);
                    p["flags9"] = dayPlan.mid(88,2);

                    p["fromTime10"] = dayPlan.mid(90,4);
                    p["toTime10"] =  dayPlan.mid(94,4);
                    p["flags10"] = dayPlan.mid(98,2);


                    QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                    emit accessAction(xmlFunc);

                    dayPlanNumber++;

                }

                for(int i=1; i<=daysPlan.count(); i++)
                {
                    if(daysPlan.contains(dayPlan.leftJustified(100,'0')))
                        days[horuxDay] = i;
                }

            }

            if(isMondayDefault)
            {
                days["mardi"] = days["lundi"];
                days["mercredi"] = days["lundi"];
                days["jeudi"] = days["lundi"];
                days["vendredi"] = days["lundi"];
                days["samedi"] = days["lundi"];
                days["dimanche"] = days["lundi"];

                if(isNonWorkingDay)
                {
                    days["special1"] = days["lundi"];
                    days["special2"] = days["lundi"];
                    days["special3"] = days["lundi"];
                    days["special4"] = days["lundi"];
                    days["special5"] = days["lundi"];
                }
            }
        }

        s_schedule *schedule = new s_schedule;

        //only 98 scheduler possible
        schedule->number = accessLevelId.toInt() % 98;
        schedule->accessLevelId = accessLevelId.toInt();
        schedule->days = days;

        schedules.append(schedule);

        scheduleNumber++;
    }

    // OPEN TIME
    QSqlQuery otQuery("SELECT * FROM hr_openTime_attribution AS ota LEFT JOIN hr_openTime AS ot ON ot.id=ota.id_openTime  WHERE id_device=" + deviceId + " ORDER BY ot.id LIMIT 0,1 ");
    if(otQuery.next())
    {
        QString openTimelId = otQuery.value(3).toString();
        bool isNonWorkingDay = otQuery.value(5).toBool();
        bool isMondayDefault = otQuery.value(6).toBool();

        QString dayPlan = "";

        QString horuxDay = "";
        QMap<QString, int>days;

        days["lundi"] = 0;
        days["mardi"] = 0;
        days["mercredi"] = 0;
        days["jeudi"] = 0;
        days["vendredi"] = 0;
        days["samedi"] = 0;
        days["dimanche"] = 0;
        days["special1"] = 0;
        days["special2"] = 0;
        days["special3"] = 0;
        days["special4"] = 0;
        days["special5"] = 0;


        QSqlQuery openTime("SELECT * FROM hr_openTime_time WHERE id_openTime =" + openTimelId + " ORDER BY day,`from`");
        while(openTime.next())
        {
            if(horuxDay != openTime.value(2).toString() && dayPlan!="")
            {
                if(!daysPlan.contains(dayPlan.leftJustified(100,'0')))
                {
                    daysPlan << dayPlan.leftJustified(100,'0');

                    dayPlan = dayPlan.leftJustified(100,'0');

                    QMap<QString, QString> p;
                    QString f = "Gantner_AccessTerminal_setDayPlan" ;
                    p["dayPlanNumber"] = QString::number(dayPlanNumber);

                    p["fromTime1"] = dayPlan.mid(0,4);
                    p["toTime1"] =  dayPlan.mid(4,4);
                    p["flags1"] = dayPlan.mid(8,2);

                    p["fromTime2"] = dayPlan.mid(10,4);
                    p["toTime2"] =  dayPlan.mid(14,4);
                    p["flags2"] = dayPlan.mid(18,2);

                    p["fromTime3"] = dayPlan.mid(20,4);
                    p["toTime3"] =  dayPlan.mid(24,4);
                    p["flags3"] = dayPlan.mid(28,2);

                    p["fromTime4"] = dayPlan.mid(30,4);
                    p["toTime4"] =  dayPlan.mid(34,4);
                    p["flags4"] = dayPlan.mid(38,2);

                    p["fromTime5"] = dayPlan.mid(40,4);
                    p["toTime5"] =  dayPlan.mid(44,4);
                    p["flags5"] = dayPlan.mid(48,2);

                    p["fromTime6"] = dayPlan.mid(50,4);
                    p["toTime6"] =  dayPlan.mid(54,4);
                    p["flags6"] = dayPlan.mid(58,2);

                    p["fromTime7"] = dayPlan.mid(60,4);
                    p["toTime7"] =  dayPlan.mid(64,4);
                    p["flags7"] = dayPlan.mid(68,2);

                    p["fromTime8"] = dayPlan.mid(70,4);
                    p["toTime8"] =  dayPlan.mid(74,4);
                    p["flags8"] = dayPlan.mid(78,2);

                    p["fromTime9"] = dayPlan.mid(80,4);
                    p["toTime9"] =  dayPlan.mid(84,4);
                    p["flags9"] = dayPlan.mid(88,2);

                    p["fromTime10"] = dayPlan.mid(90,4);
                    p["toTime10"] =  dayPlan.mid(94,4);
                    p["flags10"] = dayPlan.mid(98,2);


                    QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                    emit accessAction(xmlFunc);

                    dayPlanNumber++;
                }

                for(int i=1; i<=daysPlan.count(); i++)
                {
                    if(daysPlan.contains(dayPlan.leftJustified(100,'0')))
                        days[horuxDay] = i;
                }

                dayPlan = "";
            }

            horuxDay = openTime.value(2).toString();
            int horuxFrom = openTime.value(3).toInt();
            int horuxUntil =  openTime.value(4).toInt();
            int unlocking = openTime.value(5).toBool() ? 1 : 0;
            int supOpenTooLongAlarm = openTime.value(6).toBool() ? 2 : 0;
            int supWithoutPermAlarm = openTime.value(7).toBool() ? 4 : 0;
            int checkOnlyCompanyID = openTime.value(8).toBool() ? 8 : 0;
            int specialRelayPlan = openTime.value(9).toBool() ? 16 : 0;

            int flag = unlocking | supOpenTooLongAlarm | supWithoutPermAlarm | checkOnlyCompanyID | specialRelayPlan;

            QString gantnerFrom = QString::number( horuxFrom / 60 ).rightJustified(2,'0') +  QString::number( horuxFrom % 60 ).rightJustified(2,'0');
            QString gantnerUntil = QString::number( horuxUntil / 60 ).rightJustified(2,'0') +  QString::number( horuxUntil % 60 ).rightJustified(2,'0');

            dayPlan += gantnerFrom + gantnerUntil + QString::number( flag ).rightJustified(2,'0'); // 00 -> flags
        }

        if(dayPlan!="")
        {
            if(!daysPlan.contains(dayPlan.leftJustified(100,'0')))
            {
                daysPlan << dayPlan.leftJustified(100,'0');

                dayPlan = dayPlan.leftJustified(100,'0');
                QMap<QString, QString> p;
                QString f = "Gantner_AccessTerminal_setDayPlan" ;
                p["dayPlanNumber"] = QString::number(dayPlanNumber);

                p["fromTime1"] = dayPlan.mid(0,4);
                p["toTime1"] =  dayPlan.mid(4,4);
                p["flags1"] = dayPlan.mid(8,2);

                p["fromTime2"] = dayPlan.mid(10,4);
                p["toTime2"] =  dayPlan.mid(14,4);
                p["flags2"] = dayPlan.mid(18,2);

                p["fromTime3"] = dayPlan.mid(20,4);
                p["toTime3"] =  dayPlan.mid(24,4);
                p["flags3"] = dayPlan.mid(28,2);

                p["fromTime4"] = dayPlan.mid(30,4);
                p["toTime4"] =  dayPlan.mid(34,4);
                p["flags4"] = dayPlan.mid(38,2);

                p["fromTime5"] = dayPlan.mid(40,4);
                p["toTime5"] =  dayPlan.mid(44,4);
                p["flags5"] = dayPlan.mid(48,2);

                p["fromTime6"] = dayPlan.mid(50,4);
                p["toTime6"] =  dayPlan.mid(54,4);
                p["flags6"] = dayPlan.mid(58,2);

                p["fromTime7"] = dayPlan.mid(60,4);
                p["toTime7"] =  dayPlan.mid(64,4);
                p["flags7"] = dayPlan.mid(68,2);

                p["fromTime8"] = dayPlan.mid(70,4);
                p["toTime8"] =  dayPlan.mid(74,4);
                p["flags8"] = dayPlan.mid(78,2);

                p["fromTime9"] = dayPlan.mid(80,4);
                p["toTime9"] =  dayPlan.mid(84,4);
                p["flags9"] = dayPlan.mid(88,2);

                p["fromTime10"] = dayPlan.mid(90,4);
                p["toTime10"] =  dayPlan.mid(94,4);
                p["flags10"] = dayPlan.mid(98,2);


                QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                emit accessAction(xmlFunc);

                dayPlanNumber++;

            }

            for(int i=1; i<=daysPlan.count(); i++)
            {
                if(daysPlan.contains(dayPlan.leftJustified(100,'0')))
                    days[horuxDay] = i;
            }
        }


        if(isMondayDefault)
        {
            days["mardi"] = days["lundi"];
            days["mercredi"] = days["lundi"];
            days["jeudi"] = days["lundi"];
            days["vendredi"] = days["lundi"];
            days["samedi"] = days["lundi"];
            days["dimanche"] = days["lundi"];

            if(isNonWorkingDay)
            {
                days["special1"] = days["lundi"];
                days["special2"] = days["lundi"];
                days["special3"] = days["lundi"];
                days["special4"] = days["lundi"];
                days["special5"] = days["lundi"];
            }
        }


        s_schedule *schedule = new s_schedule;
        schedule->number = 99;
        schedule->accessLevelId = openTimelId.toInt();
        schedule->days = days;

        schedules.append(schedule);

        scheduleNumber++;

    }


    // Send the scheduler
    foreach(s_schedule *schedule, schedules)
    {
        QMap<QString, QString> p;

        QString f = "Gantner_AccessTerminal_setSchedule" ;
        p["scheduleNumber"] = QString::number(schedule->number);

        p["monday"] = QString::number(schedule->days["lundi"]);
        p["tuesday"] =  QString::number(schedule->days["mardi"]);
        p["wednesday"] = QString::number(schedule->days["mercredi"]);
        p["thursday"] = QString::number(schedule->days["jeudi"]);
        p["friday"] = QString::number(schedule->days["vendredi"]);

        p["saturday"] = QString::number(schedule->days["samedi"]);
        p["sunday"] = QString::number(schedule->days["dimanche"]);

        p["special1"] = QString::number(schedule->days["special1"]);
        p["special2"] = QString::number(schedule->days["special2"]);
        p["special3"] = QString::number(schedule->days["special3"]);
        p["special4"] = QString::number(schedule->days["special4"]);
        p["special5"] = QString::number(schedule->days["special5"]);


        QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
        emit accessAction(xmlFunc);
    }


    // special day
    for(int m=1; m<=12; m++)
    {
        QMap<QString, QString> p;
        p.clear();
        QString f = "Gantner_AccessTerminal_setSpecialDay" ;
        p["month"] = QString::number(m);

        for(int d=1; d<=31; d++)
        {
            QDate date(QDate::currentDate().year(),m,d);

            if(date.isValid())
            {
                QSqlQuery query("SELECT * FROM hr_non_working_day WHERE `from`<='" + date.toString("yyyy-MM-dd") + "' AND `until`>='" + date.toString("yyyy-MM-dd") + "'");

                if(query.next())
                {
                    p["specialDay"] += "7";
                }
                else
                    p["specialDay"] += "0";
            }
            else
                p["specialDay"] += "0";

        }

        QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
        emit accessAction(xmlFunc);
    }

    // relay plan CURRENTLY NOT USED
    QMap<QString, QString> p;
    QString f = "Gantner_AccessTerminal_setRelayPlan" ;
    p["relayPlanNumber"] = "1";
    p["relays"] = "0";
    QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
    emit accessAction(xmlFunc);

    // actualizing
    p.clear();
    f = "Gantner_AccessTerminal_settingActualizing" ;
    xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
    emit accessAction(xmlFunc);


    QSqlQuery queryUser("SELECT u.id, uga.id_group, CONCAT(u.name,' ' ,u.firstname) AS fullname, k.serialNumber, u.pin_code,u.validity_date, u.masterAuthorization FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id LEFT JOIN hr_keys AS k ON k.id=ka.id_key LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=u.id LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE ug.accessPlugin='' AND u.name!='??' AND u.isBlocked=0 AND k.serialNumber IS NOT NULL");

    while(queryUser.next())
    {
        QString userId = queryUser.value(0).toString();
        QString groupId = queryUser.value(1).toString();
        QString fullName = queryUser.value(2).toString();
        QString cardNumber = queryUser.value(3).toString();
        QString pinCode = queryUser.value(4).toString();
        QDate valididyDate = queryUser.value(5).toDate();
        bool masterAuthorization = queryUser.value(6).toBool();

        QSqlQuery queryHasAccessDevice("SELECT * FROM hr_user_group_access WHERE id_device=" + deviceId + " AND id_group=" + groupId);

        if(queryHasAccessDevice.next())
        {
            QMap<QString, QString> p;
            p.clear();
            QString f = "Gantner_AccessTerminal_loadPersonnel" ;

            p["personnelNumber"] = userId;
            p["cardNumber"] = cardNumber;
            p["cardVersion"] = "0";
            p["fullname"] = fullName.left(16);
            if(masterAuthorization)
                p["masterAuhtorization"] = "1";
            else
                p["masterAuhtorization"] = "0";

            p["PINCode"] = pinCode;

            QString scheduleNumber = "00";
            foreach(s_schedule *schedule, schedules)
            {
                if( schedule->accessLevelId == queryHasAccessDevice.value(3).toInt())
                    scheduleNumber = QString::number( schedule->number ).rightJustified(2,'0');
            }

            p["scheduleNumber"] = scheduleNumber;
            p["regularRelayPlanNumber"] = "00";
            p["specialRelayPlanNumber"] = "00";
            p["validityDate"] = valididyDate.toString("yyMMdd") + "0000";

            if("" == valididyDate.toString("yyMMdd") )
                p["validityDateOption"] = "0";
            else
                p["validityDateOption"] = "1";

            QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
            emit accessAction(xmlFunc);
        }
    }


    foreach(s_schedule *value, schedules)
    {
        if(value)
            delete value;
    }

    schedules.clear();

}

void CGantnerAccess::deviceConnectionMonitor(int id, bool status)
{
    QSqlQuery query("SELECT * FROM hr_device WHERE type='gantner_AccessTerminal' AND id=" + QString::number(id));

    if(query.next())
        devices[id] = status;
}

void CGantnerAccess::deviceInputMonitor ( int , int , bool )
{

}

void CGantnerAccess::checkDb()
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

                if(type == "reinit")
                {
                    QMap<QString, QString> p;

                    QString xmlFunc = CXmlFactory::deviceAction( deviceId ,"Gantner_AccessTerminal_reinit", p);
                    emit accessAction(xmlFunc);
                    ids << id;

                }

                if(type == "user" || type == "key")
                {

                    if(func == "add")
                    {

                        QSqlQuery queryUser("SELECT u.id, uga.id_group, CONCAT(u.name,' ' ,u.firstname) AS fullname, k.serialNumber, u.pin_code,u.validity_date, u.masterAuthorization FROM hr_user AS u LEFT JOIN hr_keys_attribution AS ka ON ka.id_user=u.id LEFT JOIN hr_keys AS k ON k.id=ka.id_key LEFT JOIN hr_user_group_attribution AS uga ON uga.id_user=u.id  LEFT JOIN hr_user_group AS ug ON ug.id=uga.id_group WHERE ug.accessPlugin='' AND u.name!='??' AND u.isBlocked=0  AND u.id=" + userId );

                        while(queryUser.next())
                        {
                            QString userId = queryUser.value(0).toString();
                            QString groupId = queryUser.value(1).toString();
                            QString fullName = queryUser.value(2).toString();
                            QString cardNumber = queryUser.value(3).toString();
                            QString pinCode = queryUser.value(4).toString();
                            QDate valididyDate = queryUser.value(5).toDate();
                            bool masterAuthorization = queryUser.value(6).toBool();

                            QSqlQuery queryHasAccessDevice("SELECT * FROM hr_user_group_access WHERE id_device=" + deviceId + " AND id_group=" + groupId);

                            if(queryHasAccessDevice.next())
                            {
                                QMap<QString, QString> p;
                                p.clear();
                                QString f = "Gantner_AccessTerminal_loadPersonnel" ;

                                p["personnelNumber"] = userId;
                                p["cardNumber"] = cardNumber;
                                p["cardVersion"] = "0";
                                p["fullname"] = fullName.left(16);
                                if(masterAuthorization)
                                    p["masterAuhtorization"] = "1";
                                else
                                    p["masterAuhtorization"] = "0";

                                p["PINCode"] = pinCode;

                                p["scheduleNumber"] = QString::number(queryHasAccessDevice.value(3).toInt() % 98);
                                p["regularRelayPlanNumber"] = "00";
                                p["specialRelayPlanNumber"] = "00";
                                p["validityDate"] = valididyDate.toString("yyMMdd") + "0000";

                                if("" == valididyDate.toString("yyMMdd") )
                                    p["validityDateOption"] = "0";
                                else
                                    p["validityDateOption"] = "1";

                                QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);


                                emit accessAction(xmlFunc);

                                ids << id;
                            }
                        }
                    }
                }

                if(func == "sub")
                {
                    QMap<QString, QString> p;
                    p.clear();
                    QString f = "Gantner_AccessTerminal_unloadPersonnel";

                    p["cardNumber"] = param;

                    QString xmlFunc = CXmlFactory::deviceAction( deviceId ,f, p);
                    emit accessAction(xmlFunc);

                    ids << id;
                }


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
                for(int i=0; i<ids.size(); i++)
                {
                    QSqlQuery queryDel("DELETE FROM hr_gantner_standalone_action WHERE id=" + ids.at(i) );
                }

                QSqlQuery queryOptimize("OPTIMIZE TABLE hr_gantner_standalone_action");

                timerCheckDb->start(TIME_DB_CHECKING);
            }

        }
    }


}

void CGantnerAccess::initSAASMode()
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


void CGantnerAccess::soapSSLErrors ( QNetworkReply * reply, const QList<QSslError> & errors )
{
    foreach(QSslError sslError, errors)
    {
        if(sslError.error() == QSslError::SelfSignedCertificate)
        {
            reply->ignoreSslErrors();
        }
    }
}


void CGantnerAccess::readSoapResponse()
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
Q_EXPORT_PLUGIN2(gantneraccess, CGantnerAccess);
