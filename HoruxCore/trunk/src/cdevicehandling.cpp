
#include <QtCore>
#include "cdevicehandling.h"
#include "include.h"
#include "cxmlfactory.h"
#include "chorux.h"

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
    started = false;
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

                    if(!CHorux::getUnloadPlugins().contains(pName)) {
                        loadedPlugins[pName] =  qobject_cast<CDeviceInterface *> ( plugin );
                    } else {
                        qDebug() << "Don't load the device plugin " << pName << ". Defined in horux.ini";
                    }
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
            QMap<QString, QVariant> config;

            //! get the device paramter from the database
            config = CFactory::getDbHandling()->plugin()->getDeviceConfiguration ( id, type );

            if(config["isActive"].toBool() == 1 ) {

                if(config["horuxControllerId"].toInt() == CHorux::getHoruxControllerId()) {

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
                } else {
                    qDebug() << "The device " << config["name"].toString() << "is not connected to this controller";
                }
            } else {
                qDebug() << "The device " << config["name"].toString() << "is unactivated";
            }
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

        int parentId = 0;

        parentId = CFactory::getDbHandling()->plugin()->getParentDevice ( i.key() );

        if ( parentId >0 )
        {
            if ( devicesInterface[parentId] ) {
                i.value()->setParent( devicesInterface[parentId] );
                devicesInterface[parentId]->connectChild ( i.value() );
             }
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
        int parentId = 0;

        parentId = CFactory::getDbHandling()->plugin()->getParentDevice ( i.key() );

        //! open the device only if the device is not handled by a parent device
        if ( !i.value()->isOpened() && parentId == 0 )
        {
            if ( !i.value()->open() )
            {
                QString xml = CXmlFactory::deviceEvent( i.value()->getParameter ( "id" ).toString().toLatin1(), "1016", "The communication with the device cannot be opened");
                emit deviceEvent(xml);
            }
        }
    }

    return true;
}


void CDeviceHandling::stopDevice ( QString id )
{
    QMapIterator<int, CDeviceInterface *> i ( devicesInterface );
    while ( i.hasNext() )
    {
        i.next();
        int parentId = 0;

        parentId = CFactory::getDbHandling()->plugin()->getParentDevice ( i.key() );

        //! open the device only if the device is not handled by a parent device
        if ( i.value()->isOpened() && parentId == 0 )
        {
            if(i.key() == id.toInt())
            {
                i.value()->close();
                return;
            }
        }
    }
}

void CDeviceHandling::startDevice ( QString id )
{
    QMapIterator<int, CDeviceInterface *> i ( devicesInterface );

    while ( i.hasNext() )
    {
        i.next();
        int parentId = 0;

        parentId = CFactory::getDbHandling()->plugin()->getParentDevice ( i.key() );

        //! open the device only if the device is not handled by a parent device
        if (i.key() == id.toInt() && !i.value()->isOpened() && parentId == 0)
        {
            QMap<QString, QVariant> config;
            QMap<int, QString> deviceList = CFactory::getDbHandling()->plugin()->getDeviceList();

            config = CFactory::getDbHandling()->plugin()->getDeviceConfiguration ( id.toInt(), deviceList[id.toInt()] );

            QMapIterator<QString, QVariant> j(config);
             while (j.hasNext()) {
                 j.next();
                 if( j.key() != "id")
                    i.value()->setParameter(j.key(),j.value() );
             }

            if ( !i.value()->open() )
            {
                QString xml = CXmlFactory::deviceEvent( i.value()->getParameter ( "id" ).toString().toLatin1(), "1016", "The communication with the device cannot be opened");
                emit deviceEvent(xml);
            }

            return;
        }
    }
}

QMap<QString,QStringList> CDeviceHandling::getUsedTables()
{
    QMap<QString,QStringList> returnList;

    QMap<QString, CDeviceInterface *> loadedPlugins = loadPlugin();

    QMapIterator<QString, CDeviceInterface *> i ( loadedPlugins );
    while ( i.hasNext() )
    {
        i.next();        

        int index = i.value()->getMetaObject()->metaObject()->indexOfClassInfo ( "DbTableUsed" );

        QString pName;
        int indexName = i.value()->getMetaObject()->metaObject()->indexOfClassInfo ( "PluginName" );
        pName = i.value()->getMetaObject()->metaObject()->classInfo ( indexName ).value();

        if(!CHorux::getUnloadPlugins().contains(pName)) {
            QString value = "";
            if ( index != -1 )
            {
                value = i.value()->getMetaObject()->metaObject()->classInfo ( index ).value();
                returnList["DbTableUsed"] << value.split(",");
            }

            index = i.value()->getMetaObject()->metaObject()->indexOfClassInfo ( "DbTrackingTable" );
            value = "";
            if ( index != -1 )
            {
                value = i.value()->getMetaObject()->metaObject()->classInfo ( index ).value();
                returnList["DbTrackingTable"] << value.split(",");
            }
        }
    }

    return returnList;
}

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
