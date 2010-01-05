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
#include "cdbhandling.h"
#include <QtCore>
#include <QSqlDatabase>

CDbHandling *CDbHandling::pThis = NULL;

CDbHandling *CDbHandling::getInstance()
{
    if ( !pThis )
    {
        pThis = new CDbHandling();
        return pThis;
    }
    else
    {
        return pThis;
    }
}

CDbHandling::CDbHandling ( QObject *parent )
        : QObject ( parent )
{
    dbInterface = NULL;
}


CDbHandling::~CDbHandling()
{
    qDebug ( "DB handling stopping..." );

    if ( dbInterface )
    {
        dbInterface->close();
        delete dbInterface;
        dbInterface = NULL;
    }

    QStringList dbNameList = QSqlDatabase::connectionNames();
    for ( int i=0; i<dbNameList.count(); i++ )
        QSqlDatabase::removeDatabase ( dbNameList[i] );

    pThis = NULL;

    qDebug ( "DB handling stopped..." );
}

/*!
    \fn CDbHandling::init()
 */
bool CDbHandling::init()
{
    qDebug ( "Initialize the db engine" );

    if ( loadPlugin() )
    {
        QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

        settings.beginGroup ( "SQL" );

        if ( !settings.contains ( "host" ) ) settings.setValue ( "host", "localhost" );
        if ( !settings.contains ( "db" ) ) settings.setValue ( "db", "horux" );
        if ( !settings.contains ( "username" ) ) settings.setValue ( "username", "root" );
        if ( !settings.contains ( "password" ) ) settings.setValue ( "password", "" );

        QString host = settings.value ( "host", "localhost" ).toString();
        QString db = settings.value ( "db", "horux" ).toString();
        QString username = settings.value ( "username", "root" ).toString();
        QString password = settings.value ( "password", "" ).toString();

        settings.endGroup();

        if ( dbInterface->open ( host, db, username, password ) )
        {
            started = true;

            return true;
        }
    }
    return false;
}

/*!
    \fn CDbHandling::isStarted()
 */
bool CDbHandling::isStarted()
{
    return started;
}


bool CDbHandling::loadPlugin()
{
    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );
    settings.beginGroup ( "SQL" );
    QString plugin = settings.value ( "plugins", "mysql" ).toString();
    if ( !settings.contains ( "plugins" ) ) settings.setValue ( "plugins", "mysql" );
    settings.endGroup();

    QDir pluginDirectory ( QCoreApplication::instance()->applicationDirPath() + "/plugins/db/" );


    QStringList filter;

#if defined(Q_OS_WIN)
    filter << plugin + "*";
#elif defined(Q_WS_X11) || defined(Q_WS_QWS)
    filter << "lib" + plugin + "*";
#endif

    if ( pluginDirectory.entryList ( filter ).count() >0 )
    {
        QString fileName = pluginDirectory.entryList ( filter ).first();

        QPluginLoader pluginLoader ( pluginDirectory.absoluteFilePath ( fileName ), this );
        QObject *plugin = pluginLoader.instance();

        if ( plugin )
        {
            dbInterface = qobject_cast<CDbInterface *> ( plugin );
            return true;
        }
    }

    return false;
}

/*!
    \fn CDbHandling::plugin()
 */
CDbInterface * CDbHandling::plugin()
{
    return dbInterface;
}


/*!
    \fn CDbHandling::getInfo(QDomDocument xml_info )
 */
QDomElement CDbHandling::getInfo ( QDomDocument xml_info )
{
    QDomElement plugins = xml_info.createElement ( "plugins" );
    plugins.setAttribute ( "type", "db" );

    QDomElement plugin = xml_info.createElement ( "plugin" );

    int index = dbInterface->getMetaObject()->metaObject()->indexOfClassInfo ( "PluginName" );
    QString value = "";
    if ( index != -1 )
        value = dbInterface->getMetaObject()->metaObject()->classInfo ( index ).value();

    QDomElement newElement = xml_info.createElement ( "name" );
    QDomText text =  xml_info.createTextNode ( value );
    newElement.appendChild ( text );
    plugin.appendChild ( newElement );


    index = dbInterface->getMetaObject()->metaObject()->indexOfClassInfo ( "PluginDescription" );
    value = "";
    if ( index != -1 )
        value = dbInterface->getMetaObject()->metaObject()->classInfo ( index ).value();

    newElement = xml_info.createElement ( "description" );
    text =  xml_info.createTextNode ( value );
    newElement.appendChild ( text );
    plugin.appendChild ( newElement );

    index = dbInterface->getMetaObject()->metaObject()->indexOfClassInfo ( "Version" );
    value = "";
    if ( index != -1 )
        value = dbInterface->getMetaObject()->metaObject()->classInfo ( index ).value();

    newElement = xml_info.createElement ( "version" );
    text =  xml_info.createTextNode ( value );
    newElement.appendChild ( text );
    plugin.appendChild ( newElement );

    index = dbInterface->getMetaObject()->metaObject()->indexOfClassInfo ( "Author" );
    value = "";
    if ( index != -1 )
        value = dbInterface->getMetaObject()->metaObject()->classInfo ( index ).value();

    newElement = xml_info.createElement ( "author" );
    text =  xml_info.createTextNode ( value );
    newElement.appendChild ( text );
    plugin.appendChild ( newElement );

    index = dbInterface->getMetaObject()->metaObject()->indexOfClassInfo ( "Copyright" );
    value = "";
    if ( index != -1 )
        value = dbInterface->getMetaObject()->metaObject()->classInfo ( index ).value();

    newElement = xml_info.createElement ( "copyright" );
    text =  xml_info.createTextNode ( value );
    newElement.appendChild ( text );
    plugin.appendChild ( newElement );

    plugins.appendChild ( plugin );

    return plugins;
}

bool CDbHandling::loadSchema(QString queries)
{
    qDebug ( "Load the db schema" );

    if ( loadPlugin() )
    {
        QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

        settings.beginGroup ( "SQL" );

        if ( !settings.contains ( "host" ) ) settings.setValue ( "host", "localhost" );
        if ( !settings.contains ( "db" ) ) settings.setValue ( "db", "horux" );
        if ( !settings.contains ( "username" ) ) settings.setValue ( "username", "root" );
        if ( !settings.contains ( "password" ) ) settings.setValue ( "password", "" );

        QString host = settings.value ( "host", "localhost" ).toString();
        QString db = settings.value ( "db", "horux" ).toString();
        QString username = settings.value ( "username", "root" ).toString();
        QString password = settings.value ( "password", "" ).toString();

        settings.endGroup();

        if ( dbInterface->loadSchema ( host, db, username, password, queries ) )
        {
            started = true;
            return true;
        }

    }
    return false;

}

bool CDbHandling::loadData(QString queries)
{
    qDebug ( "Load the db data" );
    if(started)
        return dbInterface->loadData ( queries );

    return false;
}
