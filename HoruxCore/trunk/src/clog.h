
#ifndef CLOG_H
#define CLOG_H
#include <QtXml>

#include <QObject>
#include <QMap>
#include "cloginterface.h"
/**
    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>

    This class load the plugins who will log the Qt message:
    - Debug
    - Warning
    - Critical
    - fatal
*/
class CLog : public QObject
{
        Q_OBJECT
    public:
        /*!
          Create a instance (singelton)
          @return Return the instance
        */
        static CLog *getInstance();

        /*!
           destructor
        */
        ~CLog();


        /*!
           Call the init funtion for each plugins.
           @return Return true if all plugins are well initialized else false
        */
        bool init();

        /*!
          Get if the log handler is started
          @return Return true if the log handler is started else false
        */
        bool isStarted();

        /*!
          Called when the function qDebug is called. The handler dispatch to all plugins
          @param msg debug message
        */
        void debug ( QString msg );

        /*!
          Called when the function qWarning is called. The handler dispatch to all plugins
          @param msg warning message
        */
        void warning ( QString msg );

        /*!
          Called when the function qCritical is called. The handler dispatch to all plugins
          @param msg critical message
        */
        void critical ( QString msg );

        /*!
          Called when the function qFatal is called. The handler dispatch to all plugins
          @param msg fatal message
        */
        void fatal ( QString msg );

        /*!
          Construct an info XML element about the loaded plugins
          @param xml_info Root XML element
          @return return the log plugins info
        */
        QDomElement getInfo ( QDomDocument xml_info );

        /*!
          Return the list of the table used by each plugin in the database
        */
        QMap<QString,QStringList> getUsedTables();

    protected:
        /*!
          Constructor
        */
        CLog ( QObject *parent = 0 );

        /*!
          Load all log plugins
          @return Return true if all plugins are well loaded else false
        */
        bool loadPlugin();

    private:
        //! Log handler instance (singleton)
        static CLog* pThis;

        //! log handler started
        bool started;

        //! list of the log plugins (plugin name, plugin instance)
        QMap<QString, CLogInterface*> logInterfaces;

};

#endif
