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
#include <QtCore>
#include "cdevicehandling.h"
#include "include.h"
#include "cxmlfactory.h"

CDeviceHandling *CDeviceHandling::pThis = NULL;

CDeviceHandling *CDeviceHandling::getInstance()
{
    if ( !pThis )
    {
        pThis = new CDeviceHandling();
        return pThis;
    }
    else
    {
        return pThis;
    }
}

CDeviceHandling::CDeviceHandling ( QObject *parent )
        : QObject ( parent )
{
}


CDeviceHandling::~CDeviceHandling()
{
    qDebug ( "Device handling stopping..." );


    QMapIterator<int, CDeviceInterface *> i ( devicesInterface );

    while ( i.hasNext() )
    {
        i.next();
        i.value()->close();
        delete i.value();
    }

    devicesInterface.clear();

    pThis = NULL;

    qDebug ( "Device handling stoped" );

}

/*!
    \fn CAccessHandling::init()
 */
bool CDeviceHandling::init()
{
    qDebug ( "Initialize the devices engine" );

    //! create the device instance
    if ( !createDevice() )
        return false;

    //! Connect the child device with the parent device
    if ( !connectChild2Parent() )
        return false;

    if ( !startDevice() )
        return false;

    started = true;
    return true;
}

/*!
    \fn CDeviceHandling::isStarted()
 */
bool CDeviceHandling::isStarted()
{
    return started;
}

QMap<QString, CDeviceInterface *> CDeviceHandling::loadPlugin()
{
    QMap<QString, CDeviceInterface *> loadedPlugins;

    QDir pluginDirectory ( QCoreApplication::instance()->applicationDirPath() + "/plugins/device/" );

    QStringList list = pluginDirectory.entryList();

    for ( int i = 0; i < list.size(); ++i )
    {

        QString fileName = list.at ( i );

        if(fileName != "." && fileName!="..")
        {
            QPluginLoader pluginLoader ( pluginDirectory.absoluteFilePath ( fileName ), this );
            QObject *plugin = pluginLoader.instance();
            if ( plugin )
            {
                int index = plugin->metaObject()->indexOfClassInfo ( "PluginName" );
                QString pName;
                if ( index != -1 )
                {
                    pName  =  plugin->metaObject()->classInfo ( index ).value() ;

                    loadedPlugins[pName] =  qobject_cast<CDeviceInterface *> ( plugin );
                }
                else
                {
                    qWarning ( "Unknown plugin device name: %s", pName.toLatin1().constData() );
                }
            }
            else
                qDebug() << pluginLoader.errorString ();
        }
    }

    return loadedPlugins;
}


/*!
    \fn CDeviceHandling::createDevice()
 */
bool CDeviceHandling::createDevice()
{
    QMap<QString, CDeviceInterface *> loadedPlugins = loadPlugin();

    QMap<int, QString> deviceList = CFactory::getDbHandling()->plugin()->getDeviceList();

    QMapIterator<int, QString> i ( deviceList );

    while ( i.hasNext() )
    {
        i.next();
        int id = i.key();
        QString type = i.value();

        if ( loadedPlugins[type] )
        {
            //! get the device paramter from the database
            QMap<QString, QVariant> config = CFactory::getDbHandling()->plugin()->getDeviceConfiguration ( id, type );

            CDeviceInterface *device = loadedPlugins[type]->createInstance ( config,this );

            devicesInterface[id] = device;

            QString logPath = CFactory::getDbHandling()->plugin()->getConfigParam ( "log_path" ).toString();

            devicesInterface[id]->setLogPath ( logPath );

            //! allow to push the device event to other sub system (alarm, access, etc)
            connect ( device->getMetaObject(),
                      SIGNAL ( deviceEvent ( QString ) ),
                      this,
                      SIGNAL ( deviceEvent ( QString ) )
                    );

            //! allow to push the device connection to other sub system (alarm, access, etc)
            connect ( device->getMetaObject(),
                      SIGNAL ( deviceConnection ( int, bool ) ),
                      this,
                      SIGNAL ( deviceConnection ( int, bool ) )
                    );

            //! allow to push the device input event to other sub system (alarm, access, etc)
            connect ( device->getMetaObject(),
                      SIGNAL ( deviceInputChange ( int, int, bool ) ),
                      this,
                      SIGNAL ( deviceInputChange ( int, int, bool ) )
                    );

            //! allow to push device action to all devices. The device must filter the signal
            connect ( this,
                      SIGNAL ( deviceAction ( QString ) ),
                      device->getMetaObject(),
                      SLOT ( deviceAction ( QString ) )
                    );

            //! each device can be informed when a event happen on an other device
            connect ( this,
                      SIGNAL ( deviceEvent ( QString ) ),
                      device->getMetaObject(),
                      SLOT ( deviceAction ( QString ) )
                    );
        }
        else
            qWarning ( "Cannot create the device with the plugin: %s", type.toLatin1().constData() );
    }

    QMapIterator<QString, CDeviceInterface *> i2 ( loadedPlugins );

    while ( i2.hasNext() )
    {
        i2.next();
        delete i2.value();
    }

    loadedPlugins.clear();

    return true;
}


/*!
    \fn CDeviceHandling::connectChild2Parent()
 */
bool CDeviceHandling::connectChild2Parent()
{
    QMapIterator<int, CDeviceInterface *> i ( devicesInterface );

    while ( i.hasNext() )
    {
        i.next();

        int parentId = CFactory::getDbHandling()->plugin()->getParentDevice ( i.key() );

        if ( parentId >0 )
        {
            if ( devicesInterface[parentId] )
                devicesInterface[parentId]->connectChild ( i.value() );
            else
                qWarning ( "The child %u cannot be connected to %u", i.key(),  parentId );
        }
    }

    return true;
}


/*!
    \fn CDeviceHandling::startDevice()
 */
bool CDeviceHandling::startDevice()
{
    QMapIterator<int, CDeviceInterface *> i ( devicesInterface );

    while ( i.hasNext() )
    {
        i.next();

        int parentId = CFactory::getDbHandling()->plugin()->getParentDevice ( i.key() );

        //! open the device only if the device is not handled by a parent device
        if ( !i.value()->isOpened() && parentId == 0 )
        {
            if ( !i.value()->open() )
            {
                QString xml = CXmlFactory::deviceEvent( i.value()->getParameter ( "id" ).toString().toLatin1(), "1016", "The communication with the device cannot be opened");
                
                CFactory::getAlarmHandling()->alarmMonitor(xml);
            }
        }
    }

    return true;
}


/*!
    \fn CDeviceHandling::getInfo(QDomDocument xml_info )
 */
QDomElement CDeviceHandling::getInfo ( QDomDocument xml_info )
{
    QDomElement plugins = xml_info.createElement ( "plugins" );
    plugins.setAttribute ( "type", "device" );

    QMapIterator<int, CDeviceInterface *> i ( devicesInterface );
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

QDomElement CDeviceHandling::getDeviceInfo ( QDomDocument xml_info )
{
    QDomElement devices = xml_info.createElement ( "devices" );

    QMapIterator<int, CDeviceInterface *> i ( devicesInterface );
    while ( i.hasNext() )
    {
        i.next();

        devices.appendChild ( i.value()->getDeviceInfo ( xml_info ) );

    }

    return devices;
}
