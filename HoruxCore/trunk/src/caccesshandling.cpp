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
#include "caccesshandling.h"
#include <QtCore>
#include "chorux.h"

CAccessHandling *CAccessHandling::pThis = NULL;

CAccessHandling *CAccessHandling::getInstance()
{
    if ( !pThis )
    {
        pThis = new CAccessHandling();
        return pThis;
    }
    else
    {
        return pThis;
    }
}

CAccessHandling::CAccessHandling ( QObject *parent )
        : QObject ( parent )
{
}


CAccessHandling::~CAccessHandling()
{
    qDebug ( "Access handling stopping..." );


    QMapIterator<QString, CAccessInterface*> i ( accessInterfaces );
    while ( i.hasNext() )
    {
        i.next();
        delete i.value();
    }

    accessInterfaces.clear();

    pThis = NULL;

    qDebug ( "Access handling stopped" );

}

/*!
    \fn CAccessHandling::init()
 */
bool CAccessHandling::init()
{
    qDebug ( "Initialize the access engine" );

    //! load the existing access plugin
    if ( !loadPlugin() )
        return false;

    started = true;

    return true;
}



/*!
    \fn CAccessHandling::isStarted()
 */
bool CAccessHandling::isStarted()
{
    return started;
}

/*!
    \fn CAccessHandling:: loadPlugin()
 */
bool CAccessHandling::loadPlugin()
{
    QDir pluginDirectory ( QCoreApplication::instance()->applicationDirPath() + "/plugins/access/" );

    QStringList list = pluginDirectory.entryList();

    for ( int i = 0; i < list.size(); ++i )
    {

        QString fileName = list.at ( i );

        QPluginLoader pluginLoader ( pluginDirectory.absoluteFilePath ( fileName ),this );
        QObject *plugin = pluginLoader.instance();

        if ( plugin )
        {
            int index = plugin->metaObject()->indexOfClassInfo ( "PluginName" );
            QString pName;
            if ( index != -1 )
            {
                pName = plugin->metaObject()->classInfo ( index ).value();

                connect ( plugin,
                          SIGNAL ( accessAction ( QString ) ),
                          this,
                          SIGNAL ( sendAlarm ( QString ) ) );

                connect ( plugin,
                          SIGNAL ( accessAction ( QString ) ),
                          this,
                          SIGNAL ( accessAction ( QString ) ) );

                connect ( plugin,
                          SIGNAL ( notification ( QMap<QString, QVariant> ) ),
                          this,
                          SLOT ( notification ( QMap<QString, QVariant> ) ) );

                accessInterfaces[pName] =  qobject_cast<CAccessInterface *> ( plugin );

                qobject_cast<CAccessInterface *> ( plugin )->setAccessInterfaces ( accessInterfaces );
            }
            else
            {
                qWarning ( "Unknown plugin access name : " + pName.toLatin1() );
            }
        }
    }

    return true;

}

void CAccessHandling::deviceEvent ( QString xml )
{
    QDomDocument doc;
    doc.setContent ( xml );

    QDomElement root = doc.documentElement();

    QDomNode node = root.firstChild();

    //! check if it is a device event
    if ( root.tagName() != "deviceEvent" )
    {
        return;
    }

    QString deviceId = root.attribute ( "id" );

    QMap<QString, QVariant>funcParam;

    QDomNode eventNode = root.firstChild();

    QDomElement event = eventNode.toElement();

    //! check if the request contain the tag "event"
    if ( event.tagName() == "event" )
    {

        //! check if the request contain the tag "keyDetected"
        if ( event.text() == "keyDetected" )
        {

            eventNode = eventNode.nextSibling();

            QDomElement params = eventNode.toElement();

            QDomNode paramsNode = params.firstChild();
            while ( !paramsNode.isNull() )
            {
                QDomElement params = paramsNode.toElement();

                if ( params.tagName() == "param" )
                {
                    QString pName;
                    QVariant pValue;
                    QDomNode p = params.firstChild();
                    if ( p.toElement().tagName() == "name" )
                    {
                        pName = p.toElement().text();
                        p = p.nextSibling();
                        if ( p.toElement().tagName() == "value" )
                        {
                            pValue = p.toElement().text();
                            funcParam[pName] = pValue;

                        }
                    }
                }

                paramsNode = paramsNode.nextSibling();
            }

            funcParam["deviceId"] = deviceId;
        }
        else
            return;
    }
    else
        return;

    //! ask the access plugin
    QMapIterator<QString, CAccessInterface*> i ( accessInterfaces );
    while ( i.hasNext() )
    {
        i.next();
        bool mustBeCheck = false;

        //! Check if the device must be checked by a specfic access plugin
        QString pName = "";
        int index = i.value()->getMetaObject()->metaObject()->indexOfClassInfo ( "PluginName" );

        if ( index != -1 )
            pName = i.value()->getMetaObject()->metaObject()->classInfo ( index ).value();

        //! if specified in the xml deviceEvent
        if ( funcParam.contains ( "AccessPluginName" ) )
        {
            if ( funcParam["AccessPluginName"] == "" || funcParam["AccessPluginName"] == pName )
                mustBeCheck = true;
        }
        else
            mustBeCheck = true;


        if ( mustBeCheck )
        {
            if ( i.value() )
                i.value()->deviceEvent ( funcParam );
        }
    }

}


void CAccessHandling::deviceConnectionMonitor ( int deviceId, bool status )
{
    //! ask the access plugin
    QMapIterator<QString, CAccessInterface*> i ( accessInterfaces );
    while ( i.hasNext() )
    {
        i.next();
        i.value()->deviceConnectionMonitor ( deviceId, status );
    }
}

/*!
    \fn CAccessHandling::getInfo(QDomDocument xml_info )
 */
QDomElement CAccessHandling::getInfo ( QDomDocument xml_info )
{
    QDomElement plugins = xml_info.createElement ( "plugins" );
    plugins.setAttribute ( "type", "access" );

    QMapIterator<QString, CAccessInterface*> i ( accessInterfaces );
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

void CAccessHandling::notification(QMap<QString, QVariant>param)
{
    CHorux::sendNotification(param);
}
