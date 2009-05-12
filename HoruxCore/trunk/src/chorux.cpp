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
#include "maiaXmlRpcClient.h"
#include "cxmlfactory.h"
#include "include.h"
#include <QFile>


CHorux *CHorux::pThis = NULL;

CHorux::CHorux ( QObject *parent )
        : QObject ( parent )
{
    isStarted = false;
    xmlRpcServer = NULL;
    pThis = this;

    //! set the notification service
    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

    settings.beginGroup ( "Notification" );

    if ( !settings.contains ( "host" ) ) settings.setValue ( "host", "localhost" );
    if ( !settings.contains ( "port" ) ) settings.setValue ( "port", "80" );
    if ( !settings.contains ( "webservice" ) ) settings.setValue ( "webservice", "" );

    QString host = settings.value ( "host", "localhost" ).toString();
    QString port = settings.value ( "port", "80" ).toString();
    QString webservice = settings.value ( "webservice", "" ).toString();

    rpc = new MaiaXmlRpcClient(QUrl("http://" + host + ":" + port + "/" + webservice), this);


    settings.endGroup();

}


CHorux::~CHorux()
{

}


bool CHorux::startEngine()
{
    if ( isStarted )
        return true;

    qDebug ( "Start the engine" );

    serverStarted = QDateTime::currentDateTime();

    // the alarm handling monitor the device event
    connect ( CFactory::getDeviceHandling(),
              SIGNAL ( deviceEvent ( QString ) ),
              CFactory::getAlarmHandling(),
              SLOT ( alarmMonitor ( QString ) ) );

    // the alarm handling monitor the device connection
    connect ( CFactory::getDeviceHandling(),
              SIGNAL ( deviceConnection ( int, bool ) ),
              CFactory::getAlarmHandling(),
              SLOT ( deviceConnectionMonitor ( int, bool ) ) );

    // the alarm handling monitor the device input
    connect ( CFactory::getDeviceHandling(),
              SIGNAL ( deviceInputChange ( int, int, bool ) ),
              CFactory::getAlarmHandling(),
              SLOT ( deviceInputMonitor ( int, int, bool ) ) );

    // the alarm handling monitor the access alarm
    connect ( CFactory::getAccessHandling(),
              SIGNAL ( sendAlarm ( QString ) ),
              CFactory::getAlarmHandling(),
              SLOT ( alarmMonitor ( QString ) ) );


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
              SLOT ( deviceEvent ( QString ) ) );

    // the access handling monitor the device connection
    connect ( CFactory::getDeviceHandling(),
              SIGNAL ( deviceConnection ( int, bool ) ),
              CFactory::getAccessHandling(),
              SLOT ( deviceConnectionMonitor ( int, bool ) ) );


    //! Secondary, initialize the db engine
    if ( !CFactory::getDbHandling()->init() )
        return false;

    //! First, initialize the log engine
    if ( !CFactory::getLog()->init() )
        return false;

    //! Third, initialize the access engine
    if ( !CFactory::getAccessHandling()->init() )
        return false;

    //! First, initialize the alarm engine
    if ( !CFactory::getAlarmHandling()->init() )
        return false;

    //! Third, initialize the device engine
    if ( !CFactory::getDeviceHandling()->init() )
        return false;

    if ( !xmlRpcServer )
    {
        xmlRpcServer = new MaiaXmlRpcServer ( CFactory::getDbHandling()->plugin()->getConfigParam ( "xmlrpc_port" ).toInt(), this );

        if ( xmlRpcServer && xmlRpcServer->isListening() )
        {
            xmlRpcServer->addMethod ( "horux.getSystemInfo", this, "getInfo" );
            xmlRpcServer->addMethod ( "horux.startEngine", this, "startEngine" );
            xmlRpcServer->addMethod ( "horux.stopEngine", this, "stopEngine" );
            xmlRpcServer->addMethod ( "horux.isEngine", this, "isEngine" );
        }
        else
        {
            QString xml = CXmlFactory::systemAlarm("0","1200", "The Horux XMLRPC server cannot be started");
            CFactory::getAlarmHandling()->alarmMonitor(xml);
        }

    }
    isStarted = true;

    return true;
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

    return  xml_info.toString() ;
}

void CHorux::sendNotification(QMap<QString, QVariant> params)
{
    // check if we have at least one notification accoding to the type
    if(CFactory::getDbHandling()->plugin()->countNotification(params) == 0) return;

    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

    settings.beginGroup ( "SQL" );

    params["username"] = settings.value ( "username", "root" ).toString();
    params["password"] = settings.value ( "password", "" ).toString();

    settings.endGroup();


    QVariantList args;
    args << params;

    pThis->rpc->call("notification", args,
                pThis, SLOT(notificationResponse(QVariant &)),
                pThis, SLOT(notificatioFault(int, const QString &)));

}

void CHorux::notificationResponse(QVariant &) {

}

void CHorux::notificatioFault(int error, const QString &message) {
    qDebug() << error << " : " << message;
}
