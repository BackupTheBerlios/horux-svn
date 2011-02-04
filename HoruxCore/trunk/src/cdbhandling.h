
#ifndef CDBHANDLING_H
#define CDBHANDLING_H

#include <QObject>
#include <QtXml>
#include "cdbinterface.h"

/**
     This class will load and handled the database plugin

    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CDbHandling : public QObject
{
        Q_OBJECT
    public:
        /*!
          Create a instance (singelton)
          @return Return the instance
        */
        static CDbHandling *getInstance();

        /*!
           destructor
        */
        ~CDbHandling();

        /*!
           Call the init funtion for if the plugin.
           @return Return true if the plugin are well initialized else false
        */
        bool init();

        /*!
          Get if the db handler is started
          @return Return true if the db handler is started else false
        */
        bool isStarted();

        /*!
          Return an instance on the db plugin
          @return return the instance of the db plugin insterface
        */
        CDbInterface * plugin();

        /*!
          Construct an info XML element about the loaded plugins
          @param xml_info Root XML element
          @return return the device plugins info
        */
        QDomElement getInfo ( QDomDocument xml_info );

        /*!
          Return the list of the table used by each plugin in the database
        */
        QMap<QString,QStringList> getUsedTables();


        bool loadSchema(QString queries);
        bool loadData(QString queries);

    protected:
        /*!
          Constructor
        */
        CDbHandling ( QObject *parent = 0 );

        /*!
          Load all db plugins
          @return Return true if all plugins are well loaded else false
        */
        bool loadPlugin();

    private:
        //! Db handler instance (singleton)
        static CDbHandling* pThis;

        //! intance of the db plugin interface
        CDbInterface *dbInterface;

    protected:
        //! devices handler started
        bool started;
};

#endif
