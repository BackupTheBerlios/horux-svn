
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
    started = false;
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

                if(!CHorux::getUnloadPlugins().contains(pName)) {
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

                    connect ( this,
                              SIGNAL (deviceEvent(QString ) ),
                              plugin,
                              SLOT ( deviceEvent(QString ) ) );

                    connect ( this,
                              SIGNAL ( deviceConnectionMonitor(int,bool) ),
                              plugin,
                              SLOT ( deviceConnectionMonitor(int,bool) ) );

                    connect ( this,
                              SIGNAL ( deviceInputMonitor(int,int,bool) ),
                              plugin,
                              SLOT ( deviceInputMonitor(int,int,bool) ) );

                    accessInterfaces[pName] =  qobject_cast<CAccessInterface *> ( plugin );
                } else {
                    qDebug() << "Don't load the access plugin " << pName << ". Defined in horux.ini";
                }
            }
            else
            {
                qWarning ( "Unknown plugin access name : %s", pName.toLatin1().constData() );
            }
        }
    }

    QMapIterator<QString, CAccessInterface*> i ( accessInterfaces );
    while ( i.hasNext() )
    {
        i.next();
        i.value()->setAccessInterfaces ( accessInterfaces );
    }

    return true;

}

QMap<QString,QStringList> CAccessHandling::getUsedTables()
{
    QMap<QString,QStringList> returnList;

    QDir pluginDirectory ( QCoreApplication::instance()->applicationDirPath() + "/plugins/access/" );

    QStringList list = pluginDirectory.entryList();

    for ( int i = 0; i < list.size(); ++i )
    {

        QString fileName = list.at ( i );

        QPluginLoader pluginLoader ( pluginDirectory.absoluteFilePath ( fileName ),this );
        QObject *plugin = pluginLoader.instance();

        QString pName;

        if(plugin)
        {
            int index = plugin->metaObject()->indexOfClassInfo ( "DbTableUsed" );
            int indexName = plugin->metaObject()->indexOfClassInfo ( "PluginName" );
            pName = plugin->metaObject()->classInfo ( indexName ).value();

            if(!CHorux::getUnloadPlugins().contains(pName)) {
                QString value = "";
                if ( index != -1 )
                {
                    value = plugin->metaObject()->classInfo ( index ).value();
                    returnList["DbTableUsed"] << value.split(",");
                }

                index = plugin->metaObject()->indexOfClassInfo ( "DbTrackingTable" );
                value = "";
                if ( index != -1 )
                {
                    value = plugin->metaObject()->classInfo ( index ).value();
                    returnList["DbTrackingTable"] << value.split(",");
                }
            }
        }
    }

    return returnList;
}

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
