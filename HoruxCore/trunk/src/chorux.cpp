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

#include "chorux.h"

#include "maiaXmlRpcServer.h"
#include "cxmlfactory.h"
#include "include.h"
#include <QFile>
#include <QtSql>

CHorux *CHorux::ptr_this = NULL;

CHorux::CHorux ( QObject *parent )
        : QObject ( parent )
{
    isStarted = false;
    ptr_xmlRpcServer = NULL;
    ptr_this = this;
    timerSoapInfo = NULL;
    timerSoapTracking = NULL;
    timerSoapSyncData = NULL;

    initSAASMode();
}


CHorux::~CHorux()
{

}

void CHorux::initSAASMode()
{
    isFullReloaded = false;

    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

    settings.beginGroup ( "Webservice" );

    if ( !settings.contains ( "saas" ) ) settings.setValue ( "saas", false );
    if ( !settings.contains ( "saas_username" ) ) settings.setValue ( "saas_username", "" );
    if ( !settings.contains ( "saas_password" ) ) settings.setValue ( "saas_password", "" );
    if ( !settings.contains ( "saas_path" ) ) settings.setValue ( "saas_path", "" );
    if ( !settings.contains ( "saas_host" ) ) settings.setValue ( "saas_host", "" );
    if ( !settings.contains ( "saas_ssl" ) ) settings.setValue ( "saas_ssl", true );
    if ( !settings.contains ( "saas_info_send_timer" ) ) settings.setValue ( "saas_info_send_timer", 5 );


    saas = settings.value ( "saas", "false" ).toBool();
    saas_host = settings.value ( "saas_host", "" ).toString();
    saas_ssl = settings.value ( "saas_ssl", true ).toBool();
    saas_username = settings.value ( "saas_username", "" ).toString();
    saas_password = settings.value ( "saas_password", "" ).toString();
    saas_path = settings.value ( "saas_path", "" ).toString();
    saas_info_send_timer = settings.value ( "saas_info_send_timer", 5 ).toInt();

    if(saas)
    {
        soapClient.setHost(saas_host,saas_ssl);

        connect(&soapClient, SIGNAL(responseReady()),this, SLOT(readSoapResponse()));
        connect(soapClient.networkAccessManager(),SIGNAL(sslErrors( QNetworkReply *, const QList<QSslError> & )),
                this, SLOT(soapSSLErrors(QNetworkReply*,QList<QSslError>)));

        timerSoapInfo = new QTimer(this);
        connect(timerSoapInfo, SIGNAL(timeout()), this, SLOT(getInfo()));

        timerSoapTracking = new QTimer(this);
        connect(timerSoapTracking, SIGNAL(timeout()), this, SLOT(sendTracking()));

        timerSoapSyncData = new QTimer(this);
        connect(timerSoapSyncData, SIGNAL(timeout()), this, SLOT(syncData()));
    }
}

bool CHorux::startEngine()
{
    if ( isStarted )
        return true;

    qDebug ( "Start the engine" );

    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );
    settings.beginGroup ( "Webservice" );

    bool xmlrpc = settings.value ( "xmlrpc", "true" ).toBool();
    bool saas = settings.value ( "saas", "false" ).toBool();


    serverStarted = QDateTime::currentDateTime();

    //! 1, initialize the db engine
    if ( !CFactory::getDbHandling()->init() )
    {
        delete CFactory::getDbHandling();

        if(saas)
        {
            QMap<QString, QVariant> params;
            params["type"] = "ALARM";
            params["code"] = "1300";
            params["object"] = "0";

            // send a notifification that the database will be relaod
            CHorux::sendNotification(params);

            // get the tables used by each plugin to relaod only the necessary tables
            QMap<QString,QStringList> tables;
            QStringList tablesList;

            tables = CFactory::getLog()->getUsedTables();
            if(tables.contains("DbTableUsed"))
                tablesList << tables["DbTableUsed"];

            tables = CFactory::getAccessHandling()->getUsedTables();
            if(tables.contains("DbTableUsed"))
                tablesList << tables["DbTableUsed"];

            tables = CFactory::getAlarmHandling()->getUsedTables();
            if(tables.contains("DbTableUsed"))
                tablesList << tables["DbTableUsed"];

            tables = CFactory::getDeviceHandling()->getUsedTables();
            if(tables.contains("DbTableUsed"))
                tablesList << tables["DbTableUsed"];

            tables = CFactory::getDbHandling()->getUsedTables();
            if(tables.contains("DbTableUsed"))
                tablesList << tables["DbTableUsed"];

            // remove duplication table in the liste
            #if QT_VERSION >= 0x040500
                tablesList.removeDuplicates();
            #else
                QStringList tmpList;
                foreach(QString tName, tablesList)
                {
                    if(!tmpList.contains(tName))
                        tmpList << tName;
                }
                tablesList.clear();
                tablesList = tmpList;
            #endif
            // call the web service to obtain the database schema according to the table liste
            QtSoapMessage message;
            message.setMethod("reloadDatabaseSchema");

            QtSoapArray *array = new QtSoapArray(QtSoapQName("tables"));
            int i=0;
            foreach(QString t, tablesList)
            {
                array->insert(i, new QtSoapSimpleType(QtSoapQName("table"), t));
                i++;
            }
            message.addMethodArgument(array);

            soapClient.submitRequest(message, saas_path+"/index.php?soap=horux&password=" + saas_password + "&username=" + saas_username);
        }

        return false;
    }

    // the alarm handling monitor the device event
    connect ( CFactory::getDeviceHandling(),
              SIGNAL ( deviceEvent ( QString ) ),
              CFactory::getAlarmHandling(),
              SIGNAL ( alarmMonitor ( QString ) ) );

    // the alarm handling monitor the device connection
    connect ( CFactory::getDeviceHandling(),
              SIGNAL ( deviceConnection ( int, bool ) ),
              CFactory::getAlarmHandling(),
              SIGNAL ( deviceConnectionMonitor ( int, bool ) ) );

    // the alarm handling monitor the device input
    connect ( CFactory::getDeviceHandling(),
              SIGNAL ( deviceInputChange ( int, int, bool ) ),
              CFactory::getAlarmHandling(),
              SIGNAL ( deviceInputMonitor ( int, int, bool ) ) );

    // the alarm handling monitor the access alarm
    connect ( CFactory::getAccessHandling(),
              SIGNAL ( sendAlarm ( QString ) ),
              CFactory::getAlarmHandling(),
              SIGNAL ( alarmMonitor ( QString ) ) );


    // the device handling reemit the access action
    connect ( CFactory::getAccessHandling(),
              SIGNAL ( accessAction ( QString ) ),
              CFactory::getDeviceHandling(),
              SIGNAL ( deviceAction ( QString ) ) );

    // the device handling reemit the alarm action
    connect ( CFactory::getAlarmHandling(),
              SIGNAL ( alarmAction ( QString ) ),
              CFactory::getDeviceHandling(),
              SIGNAL ( deviceAction ( QString ) ) );


    // the access handling monitor the device event
    connect ( CFactory::getDeviceHandling(),
              SIGNAL ( deviceEvent ( QString ) ),
              CFactory::getAccessHandling(),
              SIGNAL ( deviceEvent ( QString ) ) );

    // the access handling monitor the device connection
    connect ( CFactory::getDeviceHandling(),
              SIGNAL ( deviceConnection ( int, bool ) ),
              CFactory::getAccessHandling(),
              SIGNAL ( deviceConnectionMonitor ( int, bool ) ) );

   // the access handling monitor the device connection
    connect ( CFactory::getDeviceHandling(),
              SIGNAL ( deviceInputChange ( int, int, bool ) ),
              CFactory::getAccessHandling(),
              SIGNAL ( deviceInputMonitor ( int, int, bool ) ) );

    // connect the alarms which could happen in this class
    connect ( this,
              SIGNAL ( sendAlarm(QString) ),
              CFactory::getAlarmHandling(),
              SIGNAL ( alarmMonitor(QString) ) );

    //! 2, initialize the log engine
    if ( !CFactory::getLog()->init() )
    {
        delete CFactory::getLog();
        return false;
    }

    //! 3, initialize the access engine
    if ( !CFactory::getAccessHandling()->init() )
    {
        delete CFactory::getAccessHandling();
        return false;
    }

    //! 4, initialize the alarm engine
    if ( !CFactory::getAlarmHandling()->init() )
    {
        delete CFactory::getAlarmHandling();
        return false;
    }

    //! 5, initialize the device engine
    if ( !CFactory::getDeviceHandling()->init() )
    {
        delete CFactory::getDeviceHandling();
        return false;
    }

    if(saas)
    {
        timerSoapInfo->start(1000 * 60 * saas_info_send_timer); //send the system info every 5 minutes
        //timerSoapTracking->start(1000 * 60 * saas_info_send_timer); //send the last tracking every 5 minutes
        timerSoapTracking->start(2000); //send the last tracking every 5 minutes
        //timerSoapSyncData->start(1000 * 60 * saas_info_send_timer ); //send the last tracking every 5 minutes
        timerSoapSyncData->start(2000 ); //send the last tracking every 5 minutes
    }

    if ( !ptr_xmlRpcServer && xmlrpc)
    {
        ptr_xmlRpcServer = new MaiaXmlRpcServer ( CFactory::getDbHandling()->plugin()->getConfigParam ( "xmlrpc_port" ).toInt(), this );

        if ( ptr_xmlRpcServer && ptr_xmlRpcServer->isListening() )
        {
            ptr_xmlRpcServer->addMethod ( "horux.getSystemInfo", this, "getInfo" );
            ptr_xmlRpcServer->addMethod ( "horux.startEngine", this, "startEngine" );
            ptr_xmlRpcServer->addMethod ( "horux.stopEngine", this, "stopEngine" );
            ptr_xmlRpcServer->addMethod ( "horux.isEngine", this, "isEngine" );
            ptr_xmlRpcServer->addMethod ( "horux.stopDevice", this, "stopDevice" );
            ptr_xmlRpcServer->addMethod ( "horux.startDevice", this, "startDevice" );
        }
        else
        {
            QString xml = CXmlFactory::systemAlarm("0","1200", "The Horux XMLRPC server cannot be started");
            emit sendAlarm(xml);
        }

    }

    // if we are in the saas mode, send update Horux gui with the system status
    if(saas)
    {
        getInfo();
    }

    isStarted = true;

    return true;
}

void CHorux::stopDevice ( QString username, QString password, QString id )
{
    if ( !isStarted ) return;

    if ( !isInternal )
    {
        //! check user access
        if ( !CFactory::getDbHandling()->plugin()->isXMLRPCAccess ( username, password ) )
        {
            qWarning() << "XMLRPC request :" << username << "/" << password;
            return;
        }
    }

    if ( CFactory::getDeviceHandling()->isStarted() )
    {
       CFactory::getDeviceHandling()->stopDevice(id);
    }

}

void CHorux::startDevice ( QString username, QString password, QString id )
{
    if ( !isStarted ) return;

    if ( !isInternal )
    {
        //! check user access
        if ( !CFactory::getDbHandling()->plugin()->isXMLRPCAccess ( username, password ) )
        {
            qWarning() << "XMLRPC request :" << username << "/" << password;
            return;
        }
    }

    if ( CFactory::getDeviceHandling()->isStarted() )
    {
       CFactory::getDeviceHandling()->startDevice(id);
    }

}

void CHorux::stopEngine ( QString username, QString password )
{

    qDebug ( "Stop the engine" );


    if ( !isStarted ) return;

    if ( !isInternal )
    {
        //! check user access
        if ( !CFactory::getDbHandling()->plugin()->isXMLRPCAccess ( username, password ) )
        {
            qWarning() << "XMLRPC request :" << username << "/" << password;
            return;
        }
    }

    if(timerSoapInfo)
    {
        timerSoapInfo->stop();
    }

    if ( CFactory::getDeviceHandling()->isStarted() )
        delete CFactory::getDeviceHandling();

    if ( CFactory::getAccessHandling()->isStarted() )
        delete CFactory::getAccessHandling();

    if ( CFactory::getLog()->isStarted() )
        delete CFactory::getLog();

    if ( CFactory::getAlarmHandling()->isStarted() )
        delete CFactory::getAlarmHandling();

    if ( CFactory::getDbHandling()->isStarted() )
        delete CFactory::getDbHandling();

    isStarted = false;
}

QString CHorux::isEngine()
{
    return isStarted ? "ok" : "ko";
}

QString CHorux::getInfo( )
{
    QDomDocument xml_info;
    QDomElement root = xml_info.createElement ( "infoSystem" );

    QDomElement newElement = xml_info.createElement ( "appVersion" );
    QDomText text =  xml_info.createTextNode ( APPD_VERSION );
    newElement.appendChild ( text );
    root.appendChild ( newElement );

    newElement = xml_info.createElement ( "serverLive" );
    text =  xml_info.createTextNode ( serverStarted.toString ( "hh:mm:ss / dd.MM.yyyy" ) );
    newElement.appendChild ( text );
    root.appendChild ( newElement );

    newElement = xml_info.createElement ( "lastUpdate" );
    text =  xml_info.createTextNode ( QDateTime::currentDateTime().toString ( "hh:mm:ss / dd.MM.yyyy" ) );
    newElement.appendChild ( text );
    root.appendChild ( newElement );

    if ( isStarted )
    {
        QDomElement devicesPl =  CFactory::getDeviceHandling()->getInfo ( xml_info );
        QDomElement devices =  CFactory::getDeviceHandling()->getDeviceInfo ( xml_info );

        QDomElement logPl =  CFactory::getLog()->getInfo ( xml_info );
        QDomElement accessPl =  CFactory::getAccessHandling()->getInfo ( xml_info );
        QDomElement alarmPl =  CFactory::getAlarmHandling()->getInfo ( xml_info );
        QDomElement dbPl =  CFactory::getDbHandling()->getInfo ( xml_info );

        root.appendChild ( dbPl );
        root.appendChild ( alarmPl );
        root.appendChild ( accessPl );
        root.appendChild ( logPl );
        root.appendChild ( devicesPl );

        root.appendChild ( devices );
    }

    xml_info.appendChild ( root );

    QDomNode xmlNode =  xml_info.createProcessingInstruction ( "xml", "version=\"1.0\" encoding=\"ISO-8859-1\"" );
    xml_info.insertBefore ( xmlNode, xml_info.firstChild() );

    if(saas)
    {
        QtSoapMessage message;
        message.setMethod("updateSystemStatus");
        message.addMethodArgument("status","",xml_info.toString());

        soapClient.submitRequest(message, saas_path+"/index.php?soap=horux&password=" + saas_password + "&username=" + saas_username);

    }

    return  xml_info.toString() ;
}

void CHorux::sendNotification(QMap<QString, QVariant> params)
{
    QtSoapMessage message;
    message.setMethod("sendMail");

    QtSoapStruct *param = new QtSoapStruct(QtSoapQName("param"));
    QMapIterator<QString, QVariant> i(params);
    while (i.hasNext())
    {
        i.next();
        QString key = i.key();
        QString value = i.value().toString();

        QtSoapStruct *item = new QtSoapStruct(QtSoapQName("item"));
        item->insert(new QtSoapSimpleType(QtSoapQName("key"), key));
        item->insert(new QtSoapSimpleType(QtSoapQName("value"), value));

        param->insert(item);

    }
    message.addMethodArgument(param);

    ptr_this->soapClient.submitRequest(message, ptr_this->saas_path+"/index.php?soap=notification&password=" + ptr_this->saas_password + "&username=" + ptr_this->saas_username);
}

void CHorux::readSoapResponse()
{
    // check if the response from the web service is ok
    const QtSoapMessage &response = soapClient.getResponse();
    if (response.isFault()) {
        qDebug() << "Not able to call the Horux GUI web service. (" << response.method().name().name() << ")";
        return;
    }

    // response when loading the database schema
    if( response.method().name().name() == "reloadDatabaseSchemaResponse")
    {
        qDebug() << "Schema reloading...";
        const QtSoapType &returnValue = response.returnValue();
        QString queries = returnValue.toString();

        if ( CFactory::getDbHandling()->loadSchema(queries) )
        {
            QtSoapMessage message;
            message.setMethod("reloadDatabaseData");

            QtSoapMessage message2;
            message2.setMethod("createTrigger");

            QMap<QString,QStringList> tables;
            QStringList tablesList;
            QStringList tablesRmList;

            tables = CFactory::getLog()->getUsedTables();
            if(tables.contains("DbTableUsed"))
                tablesList << tables["DbTableUsed"];
            if(tables.contains("DbTrackingTable"))
                tablesRmList << tables["DbTrackingTable"];

            tables = CFactory::getAccessHandling()->getUsedTables();

            if(tables.contains("DbTableUsed"))
                tablesList << tables["DbTableUsed"];
            if(tables.contains("DbTrackingTable"))
                tablesRmList << tables["DbTrackingTable"];

            tables = CFactory::getAlarmHandling()->getUsedTables();
            if(tables.contains("DbTableUsed"))
                tablesList << tables["DbTableUsed"];
            if(tables.contains("DbTrackingTable"))
                tablesRmList << tables["DbTrackingTable"];

            tables = CFactory::getDeviceHandling()->getUsedTables();
            if(tables.contains("DbTableUsed"))
                tablesList << tables["DbTableUsed"];
            if(tables.contains("DbTrackingTable"))
                tablesRmList << tables["DbTrackingTable"];

            tables = CFactory::getDbHandling()->getUsedTables();
            if(tables.contains("DbTableUsed"))
                tablesList << tables["DbTableUsed"];
            if(tables.contains("DbTrackingTable"))
                tablesRmList << tables["DbTrackingTable"];

            #if QT_VERSION >= 0x040500
                tablesList.removeDuplicates();
                tablesRmList.removeDuplicates();
            #else

                QStringList tmpList;
                foreach(QString tName, tablesList)
                {
                    if(!tmpList.contains(tName))
                        tmpList << tName;
                }
                tablesList.clear();
                tablesList = tmpList;

                QStringList tmpList2;
                foreach(QString tName, tablesRmList)
                {
                    if(!tmpList2.contains(tName))
                        tmpList2 << tName;
                }
                tablesRmList.clear();
                tablesRmList = tmpList2;

            #endif

            for(int i=0; i<tablesRmList.count(); i++)
            {
                if(tablesList.contains(tablesRmList.at(i)))
                {
                    tablesList.removeOne(tablesRmList.at(i));
                }
            }

            QtSoapArray *array = new QtSoapArray(QtSoapQName("tables"));
            int i=0;
            foreach(QString t, tablesList)
            {
                array->insert(i, new QtSoapSimpleType(QtSoapQName("table"), t));
                i++;
            }
            message.addMethodArgument(array);

            QtSoapArray *array2 = new QtSoapArray(QtSoapQName("tables"));
            i=0;
            foreach(QString t, tablesList)
            {
                array2->insert(i, new QtSoapSimpleType(QtSoapQName("table"), t));
                i++;
            }
            message2.addMethodArgument(array2);

            soapClient.submitRequest(message, saas_path+"/index.php?soap=horux&password=" + saas_password + "&username=" + saas_username);

            soapClient.submitRequest(message2, saas_path+"/index.php?soap=horux&password=" + saas_password + "&username=" + saas_username);
        }

        return;
    }

    // response when loading the database data
    if( response.method().name().name() == "createTriggerResponse")
    {
        qDebug() << "Trigger created : " << response.returnValue().toString();
        if(isFullReloaded)
        {
            QMap<QString, QVariant> params;
            params["type"] = "ALARM";
            params["code"] = "1301";
            params["object"] = "0";

            CHorux::sendNotification(params);

            delete CFactory::getDbHandling();
            startEngine();
        }
        else
            isFullReloaded = true;

    }

    // response when loading the database data
    if( response.method().name().name() == "reloadDatabaseDataResponse")
    {

        const QtSoapType &returnValue = response.returnValue();
        QString queries = returnValue.toString();

        if ( CFactory::getDbHandling()->loadData(queries))
        {
            qDebug() << "Data was well relaoded";

            if(isFullReloaded)
            {
                QMap<QString, QVariant> params;
                params["type"] = "ALARM";
                params["code"] = "1301";
                params["object"] = "0";

                CHorux::sendNotification(params);

                delete CFactory::getDbHandling();
                startEngine();
            }
            else
                isFullReloaded = true;
        }           

        return;
    }

    // response when notify an alarm or else
    if( response.method().name().name() == "sendMailResponse")
    {
        return;
    }

    // response when updating the info system
    if( response.method().name().name() == "updateSystemStatusResponse")
    {
        return;
    }

    // response when updating the tracking data
    if( response.method().name().name() == "syncTrackingTableResponse")
    {
        qDebug() << response.returnValue().toString();

        QStringList ids = response.returnValue().toString().split(",");

        foreach(QString id, ids)
        {
            QStringList i = id.split(":");

            if(i.count() == 2)
            {
                if(i.at(0) == "hr_alarms" || i.at(0) == "hr_tracking")
                    QSqlQuery queryDelete("DELETE FROM " + i.at(0) + " WHERE id=" + i.at(1));
                else
                    QSqlQuery queryDelete("DELETE FROM " + i.at(0) + " WHERE tracking_id=" + i.at(1));
            }
        }

        return;
    }

    if( response.method().name().name() == "syncDatabaseDataResponse")
    {
        QString xml = response.returnValue().value().toString();

        QStringList ids;

        QDomDocument doc("trigger");
        if(doc.setContent(xml))
        {
            QDomElement docElem = doc.documentElement();

            QDomNode n = docElem.firstChild();
            while(!n.isNull())
            {
                 QDomElement trigger = n.toElement();
                 if(trigger.tagName() == "Trigger")
                 {
                     QDomNode n2 = trigger.firstChild();
                     while(!n2.isNull())
                     {
                        QDomElement table = n2.toElement();

                        if(table.tagName() == "table")
                        {
                            QString name = table.attribute("name","");
                            QString key = table.attribute("key","");
                            QString action = table.attribute("action","");

                            QDomElement value = table.firstChild().toElement();

                            QString newValues = value.text();

                            if(action == "DELETE")
                            {
                                qDebug() << "DELETE FROM " + name + " WHERE " + key;
                                QSqlQuery queryDelete;
                                if(queryDelete.exec("DELETE FROM " + name + " WHERE " + key))
                                {
                                    ids << trigger.attribute("id","");
                                }
                                else
                                {
                                     qDebug() <<   queryDelete.lastError().text();
                                }
                            }

                            if(action == "INSERT")
                            {
                                QSqlQuery queryField( "SHOW COLUMNS FROM " + name);
                                QStringList fields;
                                while( queryField.next() )
                                {
                                    fields << queryField.value(0).toString();
                                }

                                fields.join(",");

                                qDebug() << "INSERT INTO " + name + " ( `" + fields.join("`,`") + "` ) VALUES (" + newValues + " )";

                                QSqlQuery queryInsert;
                                if(queryInsert.exec("INSERT INTO " + name + " ( `" + fields.join("`,`") + "` ) VALUES (" + newValues + " )"))
                                    ids << trigger.attribute("id","");
                                else
                                {
                                    if(queryInsert.lastError().number() == 1062)
                                    {
                                        qDebug() << "Not inserted, duplicate entry";
                                        ids << trigger.attribute("id","");
                                    }
                                    else
                                    {
                                        qDebug() <<   queryInsert.lastError().text();
                                        qDebug() <<   queryInsert.lastError().number();
                                    }
                                }
                            }

                            if(action == "UPDATE")
                            {
                                QStringList values = newValues.split(",");
                                QSqlQuery queryField( "SHOW COLUMNS FROM " + name);

                                QStringList fields;
                                int i=0;
                                while( queryField.next() )
                                {
                                    if(values.count() > i)
                                        fields << "`" + queryField.value(0).toString()+"`=" + values.at(i);
                                    else
                                        fields << "`" + queryField.value(0).toString()+"`=''";
                                    i++;
                                }

                                qDebug() << "UPDATE " + name + " SET " + fields.join(",") + " WHERE " + key;

                                QSqlQuery queryUpdate;
                                if(queryUpdate.exec("UPDATE " + name + " SET " + fields.join(",") + " WHERE " + key))
                                {
                                    ids << trigger.attribute("id","");
                                }
                                else
                                {
                                     qDebug() <<   queryUpdate.lastError().text();
                                }

                            }
                        }
                        n2 = n2.nextSibling();
                      }
                 }
                 n = n.nextSibling();
             }            
        }

        if( ids.count() > 0 )
        {

            QtSoapMessage message;
            message.setMethod("syncDatabaseDataDone");

            message.addMethodArgument("ids", "", ids.join(","));

            soapClient.submitRequest(message, saas_path+"/index.php?soap=horux&password=" + saas_password + "&username=" + saas_username);
        }

        return;
    }

    if( response.method().name().name() == "syncDatabaseDataDoneResponse")
    {
        return;
    }
}

void CHorux::soapSSLErrors ( QNetworkReply * reply, const QList<QSslError> & errors )
{
    foreach(QSslError sslError, errors)
    {
        if(sslError.error() == QSslError::SelfSignedCertificate)
        {
            reply->ignoreSslErrors();
        }
    }
}

void CHorux::sendTracking()
{
    QMap<QString,QStringList> tables;
    QStringList trackingTablesList;

    tables = CFactory::getLog()->getUsedTables();
    if(tables.contains("DbTrackingTable"))
        trackingTablesList << tables["DbTrackingTable"];

    tables = CFactory::getAccessHandling()->getUsedTables();
    if(tables.contains("DbTrackingTable"))
        trackingTablesList << tables["DbTrackingTable"];

    tables = CFactory::getAlarmHandling()->getUsedTables();
    if(tables.contains("DbTrackingTable"))
        trackingTablesList << tables["DbTrackingTable"];

    tables = CFactory::getDeviceHandling()->getUsedTables();
    if(tables.contains("DbTrackingTable"))
        trackingTablesList << tables["DbTrackingTable"];

    tables = CFactory::getDbHandling()->getUsedTables();
    if(tables.contains("DbTrackingTable"))
        trackingTablesList << tables["DbTrackingTable"];

    #if QT_VERSION >= 0x040500
        trackingTablesList.removeDuplicates();
    #else
        QStringList tmpList;
        foreach(QString tName, trackingTablesList)
        {
            if(!tmpList.contains(tName))
                tmpList << tName;
        }
        trackingTablesList.clear();
        trackingTablesList = tmpList;
    #endif

    QDomDocument xml_dump;
    QDomElement root = xml_dump.createElement ( "trackingDump" );

    QDomElement tablesElement = xml_dump.createElement ( "tables" );
    root.appendChild ( tablesElement );

    foreach(QString table, trackingTablesList)
    {
        QDomElement tableElement = xml_dump.createElement ( table );
        tablesElement.appendChild ( tableElement );

        QSqlQuery query("SELECT * FROM " + table);

        QStringList fields;

        for(int i=0; i< query.record().count(); i++)
            fields << query.record().fieldName(i);

        QDomElement recordsElement = xml_dump.createElement ( "records" );
        tableElement.appendChild ( recordsElement );

        while(query.next())
        {
            QDomElement recordElement = xml_dump.createElement ( "record" );
            recordsElement.appendChild ( recordElement );

            foreach(QString field, fields)
            {
                QDomElement fieldElement = xml_dump.createElement ( field );

                QDomText text =  xml_dump.createTextNode ( query.value(query.record().indexOf(field)).toString()  );
                fieldElement.appendChild ( text );

                recordElement.appendChild ( fieldElement );
            }
        }
    }

    xml_dump.appendChild ( root );

    QDomNode xmlNode =  xml_dump.createProcessingInstruction ( "xml", "version=\"1.0\" encoding=\"utf-8\"" );
    xml_dump.insertBefore ( xmlNode, xml_dump.firstChild() );

    QtSoapMessage message;
    message.setMethod("syncTrackingTable");

    message.addMethodArgument("xml", "", xml_dump.toString());

    soapClient.submitRequest(message, saas_path+"/index.php?soap=horux&password=" + saas_password + "&username=" + saas_username);
}

void CHorux::syncData()
{
    QtSoapMessage message;
    message.setMethod("syncDatabaseData");

    soapClient.submitRequest(message, saas_path+"/index.php?soap=horux&password=" + saas_password + "&username=" + saas_username);
}
