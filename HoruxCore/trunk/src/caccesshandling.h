
#ifndef CACCESSHANDLING_H
#define CACCESSHANDLING_H

#include <QObject>
#include <QMap>
#include <QtXml>
#include "caccessinterface.h"

/**
    This class will load and handled the access plugins

    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CAccessHandling : public QObject
{
        Q_OBJECT
    public:
        /*!
          Create a instance (singelton)
          @return Return the instance
        */
        static CAccessHandling *getInstance();

        /*!
           destructor
        */
        ~CAccessHandling();

        /*!
           Call the init funtion for each plugins.
           @return Return true if all plugins are well initialized else false
        */
        bool init();

        /*!
          Get if the access handler is started
          @return Return true if the access handler is started else false
        */
        bool isStarted();

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


    protected:
        /*!
          Constructor
        */
        CAccessHandling ( QObject *parent = 0 );

        /*!
          Load all log plugins
          @return Return true if all plugins are well loaded else false
        */
        bool loadPlugin();

    private:
        //! Access handler instance (singleton)
        static CAccessHandling* pThis;

        //! access handler started
        bool started;

        //! list of the alarm plugins (plugin name, plugin instance)
        QMap<QString, CAccessInterface*> accessInterfaces;

    public slots:

        /*!
            This slot is called when a notification must be sent.
            The notification call a web service of Horux Gui to send
            an email, sms, etc
            @param param Parameter of the notification
        */
        void notification(QMap<QString, QVariant>param);    



    signals:
        /*!
          Emit when a alarm musst be catch (ex:key stolen)
          @param xml Xml alarm
        */
        void sendAlarm ( QString xml );

        /*!
          Emit when a action musst be execute (ex:open door)
          @param xml Xml action
        */
        void accessAction ( QString xml );

        /*!
          Emit when a event happens on the device like keyDetection
          @param xml Xml event
        */
        void deviceEvent ( QString xml );

        /*!
          Emit when a device is un/connected
          @param deviceId  Id of the device in the database
          @param isConnected true if connected else false
        */
        void deviceConnectionMonitor ( int, bool );

        /*!
          Emit when an device input changed
          @param device Id of the device in the database
          @param in input number
          @param status input status
        */
        void deviceInputMonitor ( int deviceId, int in, bool status );

};

#endif
