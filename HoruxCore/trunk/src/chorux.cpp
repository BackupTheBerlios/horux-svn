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

CHorux *CHorux::ptr_this = NULL;

CHorux::CHorux ( QObject *parent )
        : QObject ( parent )
{
    isStarted = false;
    ptr_xmlRpcServer = NULL;
    ptr_this = this;
    timerSoapInfo = NULL;
    saasRequest = NONE;

    initSAASMode();
}


CHorux::~CHorux()
{

}

void CHorux::initSAASMode()
{
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

    if(saas)
    {
        timerSoapInfo->start(1000 * 60 * saas_info_send_timer); //send the system info every 5 minutes
    }

    serverStarted = QDateTime::currentDateTime();

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

    //! 1, initialize the db engine
    if ( !CFactory::getDbHandling()->init() )
    {
        delete CFactory::getDbHandling();

        if(saas)
        {
            QtSoapMessage message;
            message.setMethod("reloadDatabaseSchema");
            saasRequest = RELOAD_SCHEMA;

            soapClient.submitRequest(message, saas_path+"/index.php?soap=horux&password=" + saas_password + "&username=" + saas_username);
        }

        return false;
    }

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

    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

    settings.beginGroup ( "Webservice" );

    if ( !settings.contains ( "saas" ) ) settings.setValue ( "saas", false );

    bool saas = settings.value ( "saas", "false" ).toBool();

    if(saas)
    {
        QtSoapMessage message;
        message.setMethod("updateSystemStatus");
        message.addMethodArgument("status","",xml_info.toString());

        saasRequest = UPDATE_INFO;

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

    ptr_this->saasRequest = NOTIFICATION;

    ptr_this->soapClient.submitRequest(message, ptr_this->saas_path+"/index.php?soap=notification&password=" + ptr_this->saas_password + "&username=" + ptr_this->saas_username);
}

void CHorux::readSoapResponse()
{
    const QtSoapMessage &response = soapClient.getResponse();
    if (response.isFault()) {
        qDebug() << "Not able to call the Horux GUI web service.";
        return;
    }

    qDebug() << "SOAP OK";

    switch(saasRequest)
    {
       case NONE:
        break;
       case UPDATE_INFO:
        saasRequest = NONE;
        break;
       case RELOAD_SCHEMA:
        {
            qDebug() << "reload schema";
            const QtSoapType &returnValue = response.returnValue();
            QString queries = returnValue.toString();

            if ( CFactory::getDbHandling()->loadSchema(queries) )
            {
                QtSoapMessage message;
                message.setMethod("reloadDatabaseData");
                message.addMethodArgument("tables","","ALL");
                saasRequest = RELAOD_DATA;

                soapClient.submitRequest(message, saas_path+"/index.php?soap=horux&password=" + saas_password + "&username=" + saas_username);

            }
        }
        break;
       case RELAOD_DATA:
        {
            qDebug() << "reload data";
            const QtSoapType &returnValue = response.returnValue();
            QString queries = returnValue.toString();

            if ( CFactory::getDbHandling()->loadData(queries) )
            {
                saasRequest = NONE;
                delete CFactory::getDbHandling();
                startEngine();
            }
        }
        break;
       case SYNC_DATA:
        saasRequest = NONE;
        break;
       case NOTIFICATION:
        saasRequest = NONE;
        break;

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
