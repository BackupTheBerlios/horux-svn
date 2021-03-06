
#include "clog.h"
#include <QtCore>

#include "include.h"

CLog *CLog::pThis = NULL;

CLog *CLog::getInstance()
{
    if ( !pThis )
    {
        pThis = new CLog();
        return pThis;
    }
    else
    {
        return pThis;
    }
}

CLog::CLog ( QObject *parent )
        : QObject ( parent )
{
    started = false;
}


CLog::~CLog()
{
    qDebug ( "Log handling stopping..." );

    //! delete all plugins
    QMapIterator<QString, CLogInterface*> i ( logInterfaces );
    while ( i.hasNext() )
    {
        i.next();
        delete i.value();
    }

    logInterfaces.clear();

    pThis = NULL;

    qDebug ( "Log handling stopped" );
}


/*!
    \fn CLog::init()
 */
bool CLog::init()
{
    qDebug ( "Initialize the log engine" );

    //! load the existing log plugin
    if ( !loadPlugin() )
        return false;

    started = true;
    return true;
}

/*!
    \fn CLog::isStarted()
 */
bool CLog::isStarted()
{
    return started;
}

/*!
    \fn CLog:: loadPlugin()
 */
bool CLog::loadPlugin()
{
    QDir pluginDirectory ( QCoreApplication::instance()->applicationDirPath() + "/plugins/log/" );

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
                logInterfaces[pName] =  qobject_cast<CLogInterface *> ( plugin );

                QString logPath = CFactory::getDbHandling()->plugin()->getConfigParam ( "log_path" ).toString();

                logInterfaces[pName]->setLogPath ( logPath );
            }
            else
            {
                qWarning ( "Unknown plugin log name : %s", pName.toLatin1().constData() );
            }
        }
    }

    return true;

}

void CLog::debug ( QString msg )
{

    if ( CFactory::getDbHandling()->plugin() )
    {
        if ( !CFactory::getDbHandling()->plugin()->getConfigParam ( "debug_mode" ).toBool() )
            return;
    }

    QMapIterator<QString, CLogInterface*> i ( logInterfaces );
    while ( i.hasNext() )
    {
        i.next();
        i.value()->debug ( msg );
    }

}

void CLog::warning ( QString msg )
{
    QMapIterator<QString, CLogInterface*> i ( logInterfaces );
    while ( i.hasNext() )
    {
        i.next();
        i.value()->warning ( msg );
    }
}

void CLog::critical ( QString msg )
{
    QMapIterator<QString, CLogInterface*> i ( logInterfaces );
    while ( i.hasNext() )
    {
        i.next();
        i.value()->critical ( msg );
    }

}

void CLog::fatal ( QString msg )
{
    QMapIterator<QString, CLogInterface*> i ( logInterfaces );
    while ( i.hasNext() )
    {
        i.next();
        i.value()->fatal ( msg );
    }
}

QMap<QString,QStringList> CLog::getUsedTables()
{
    QMap<QString,QStringList> returnList;

    QMapIterator<QString, CLogInterface*> i ( logInterfaces );
    while ( i.hasNext() )
    {
        i.next();

        int index = i.value()->getMetaObject()->metaObject()->indexOfClassInfo ( "DbTableUsed" );
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

    return returnList;
}

QDomElement CLog::getInfo ( QDomDocument xml_info )
{
    QDomElement plugins = xml_info.createElement ( "plugins" );
    plugins.setAttribute ( "type", "log" );

    QMapIterator<QString, CLogInterface*> i ( logInterfaces );
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
