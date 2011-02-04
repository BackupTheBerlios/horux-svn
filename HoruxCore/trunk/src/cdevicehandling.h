
#ifndef CDEVICEHANDLING_H
#define CDEVICEHANDLING_H

#include <QObject>
#include <QMap>
#include <QtXml>

#include "cdeviceinterface.h"

/**
    This class will load and handled the device plugins

    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>

*/
class CDeviceHandling : public QObject
{
        Q_OBJECT
    public:
        /*!
          Create a instance (singelton)
          @return Return the instance
        */
        static CDeviceHandling *getInstance();

        /*!
           destructor
        */
        ~CDeviceHandling();

        /*!
           Call the init funtion for each plugins.
           @return Return true if all plugins are well initialized else false
        */
        bool init();

        /*!
          Get if the device handler is started
          @return Return true if the device handler is started else false
        */
        bool isStarted();

        /*!
          Construct an info XML element about the loaded plugins
          @param xml_info Root XML element
          @return return the device plugins info
        */
        QDomElement getInfo ( QDomDocument xml_info );

        /*!
          Construct an info XML element about the loaded devices
          @param xml_info Root XML element
          @return return the device info
        */
        QDomElement getDeviceInfo ( QDomDocument xml_info );

        /*!
          Return the list of the table used by each plugin in the database
        */
        QMap<QString,QStringList> getUsedTables();


       /*!
          Stop a device.
        */
        void stopDevice ( QString id );

        /*!
          Start a device.
        */
        void startDevice ( QString id );

    protected:
        /*!
          Constructor
        */
        CDeviceHandling ( QObject *parent = 0 );


        /*!
          Load all device plugins
          @return Return all plugins well loaded
        */
        QMap<QString, CDeviceInterface *> loadPlugin();

        /*!
          Create all devices according to the database
          @return Return true if all device are well created else false
        */
        bool createDevice();

        /*!
          If a device has a parent, the device is connected to the parent
          @return return true if the device is well connected to the parent else false
        */
        bool connectChild2Parent();

        /*!
          Start the communication with device
          @return return true if the device is well started else false
        */
        bool startDevice();


    private:
        //! Device handler instance (singleton)
        static CDeviceHandling* pThis;

        //!
        //! list of the devices (device id, device instance)
        QMap<int, CDeviceInterface *> devicesInterface;

        //! devices handler started
        bool started;



    signals:
        /*!
          Emit the signal when a event happens like a key detection
          @param xml Xml event
        */
        void deviceEvent ( QString xml );

        /*!
          Emit the signal when an input change
          @param deviceId Database device id
          @param in input number
          @param status true -> active input, false -> inactive input
        */
        void deviceInputChange ( int deviceId, int in, bool status );

        /*!
          Emit when the device is un/connected
          @param deviceId Database device id
          @param isConnected true when the device is conencted else false
        */
        void deviceConnection ( int deviceId, bool isConnected );

        /*!
          Emit the signal when an action must be applied on the device like an output activation
          @param xml Xml action
        */
        void deviceAction ( QString xml );
};

#endif
