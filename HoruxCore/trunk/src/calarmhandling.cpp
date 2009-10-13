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
#include "calarmhandling.h"
#include <QtCore>
#include "chorux.h"

CAlarmHandling *CAlarmHandling::pThis = NULL;

CAlarmHandling *CAlarmHandling::getInstance()
{
    if ( !pThis )
    {
        pThis = new CAlarmHandling();
        return pThis;
    }
    else
    {
        return pThis;
    }
}

CAlarmHandling::CAlarmHandling ( QObject *parent )
        : QObject ( parent )
{

}


CAlarmHandling::~CAlarmHandling()
{
    qDebug ( "Alarm handlind stopping..." );

    QMapIterator<QString, CAlarmInterface*> i ( alarmInterfaces );
    while ( i.hasNext() )
    {
        i.next();
        delete i.value();
    }

    alarmInterfaces.clear();

    pThis = NULL;

    qDebug ( "Alarm handling stopped..." );
}


/*!
    \fn CAlarmHandling::init()
 */
bool CAlarmHandling::init()
{
    qDebug ( "Initialize the alarm engine" );

    //! load the existing alarm plugin
    if ( !loadPlugin() )
        return false;

    started = true;
    return true;
}

/*!
    \fn CLog::isStarted()
 */
bool CAlarmHandling::isStarted()
{
    return started;
}

/*!
    \fn CAlarmHandling:: loadPlugin()
 */
bool CAlarmHandling::loadPlugin()
{
    QDir pluginDirectory ( QCoreApplication::instance()->applicationDirPath() + "/plugins/alarm/" );

    QStringList list = pluginDirectory.entryList();

    for ( int i = 0; i < list.size(); ++i )
    {

        QString fileName = list.at ( i );

        QPluginLoader pluginLoader ( pluginDirectory.absoluteFilePath ( fileName ), this );
        QObject *plugin = pluginLoader.instance();

        if ( plugin )
        {
            int index = plugin->metaObject()->indexOfClassInfo ( "PluginName" );
            QString pName;
            if ( index != -1 )
            {
                pName = plugin->metaObject()->classInfo ( index ).value();

                connect ( plugin,
                          SIGNAL ( alarmAction ( QString ) ),
                          this,
                          SIGNAL ( alarmAction ( QString ) ) );

                connect ( plugin,
                          SIGNAL ( notification ( QMap<QString, QVariant> ) ),
                          this,
                          SLOT ( notification ( QMap<QString, QVariant> ) ) );

                connect ( this,
                          SIGNAL ( alarmMonitor(QString) ),
                          plugin,
                          SLOT ( alarmMonitor(QString) ) );

                connect ( this,
                          SIGNAL ( deviceConnectionMonitor(int,bool) ),
                          plugin,
                          SLOT ( deviceConnectionMonitor(int,bool) ) );

                connect ( this,
                          SIGNAL ( deviceInputMonitor(int,int,bool) ),
                          plugin,
                          SLOT ( deviceInputMonitor(int,int,bool) ) );

                alarmInterfaces[pName] =  qobject_cast<CAlarmInterface *> ( plugin );
            }
            else
            {
                qWarning ( "Unknown plugin alarm name : %s", pName.toLatin1().constData() );
            }
        }
    }

    return true;

}

/*!
    \fn CAlarmHandling::getInfo(QDomDocument xml_info )
 */
QDomElement CAlarmHandling::getInfo ( QDomDocument xml_info )
{
    QDomElement plugins = xml_info.createElement ( "plugins" );
    plugins.setAttribute ( "type", "alarm" );

    QMapIterator<QString, CAlarmInterface*> i ( alarmInterfaces );
    while ( i.hasNext() )
    {
        i.next();

        QDomElement plugin = xml_info.createElement ( "plugin" );

        int index = i.value()->getMetaObject()->metaObject()->indexOfClassInfo ( "PluginName" );
        QString value = "";
        if ( index != -1 )
            value = i.value()->getMetaObject()->metaObject()->classInfo ( index ).value();

        QDomElement newElement = xml_info.createElement ( "name" );
        QDomText text =  xml_info.createTextNode ( value );
        newElement.appendChild ( text );
        plugin.appendChild ( newElement );


        index = i.value()->getMetaObject()->metaObject()->indexOfClassInfo ( "PluginDescription" );
        value = "";
        if ( index != -1 )
            value = i.value()->getMetaObject()->metaObject()->classInfo ( index ).value();

        newElement = xml_info.createElement ( "description" );
        text =  xml_info.createTextNode ( value );
        newElement.appendChild ( text );
        plugin.appendChild ( newElement );

        index = i.value()->getMetaObject()->metaObject()->indexOfClassInfo ( "Version" );
        value = "";
        if ( index != -1 )
            value = i.value()->getMetaObject()->metaObject()->classInfo ( index ).value();

        newElement = xml_info.createElement ( "version" );
        text =  xml_info.createTextNode ( value );
        newElement.appendChild ( text );
        plugin.appendChild ( newElement );

        index = i.value()->getMetaObject()->metaObject()->indexOfClassInfo ( "Author" );
        value = "";
        if ( index != -1 )
            value = i.value()->getMetaObject()->metaObject()->classInfo ( index ).value();

        newElement = xml_info.createElement ( "author" );
        text =  xml_info.createTextNode ( value );
        newElement.appendChild ( text );
        plugin.appendChild ( newElement );

        index = i.value()->getMetaObject()->metaObject()->indexOfClassInfo ( "Copyright" );
        value = "";
        if ( index != -1 )
            value = i.value()->getMetaObject()->metaObject()->classInfo ( index ).value();

        newElement = xml_info.createElement ( "copyright" );
        text =  xml_info.createTextNode ( value );
        newElement.appendChild ( text );
        plugin.appendChild ( newElement );

        plugins.appendChild ( plugin );
    }
    return plugins;
}

void CAlarmHandling::notification(QMap<QString, QVariant>param)
{
    CHorux::sendNotification(param);
}
