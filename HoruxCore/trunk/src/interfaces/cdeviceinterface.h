
#ifndef CDEVICEINTERFACE_H
#define CDEVICEINTERFACE_H

class QByteArray;
class QObject;

class QVariant;
#include <QtXml>
#include <QMap>


/**
    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CDeviceInterface
{
    public:

        //! Allow to create multiple instance
        /*!
          This function is a pure virtual function.
        */
        virtual CDeviceInterface * createInstance ( QMap<QString, QVariant> config, QObject *parent=0 ) = 0;

        virtual ~CDeviceInterface() { interfaces.clear(); }

        /*!
          Connect a child device to this device

          @param device Child instance of the device
        */
        virtual void connectChild ( CDeviceInterface *device ) = 0;

        virtual void setParent ( CDeviceInterface *device ) { deviceParent = device; }

        /*!
          Allow to obtain a custom value ot the device

          @return Return the param value
        */
        virtual QVariant getParameter ( QString paramName ) = 0;

        /*!
          Allow to set a custom value ot the device

          @return Return the param value
        */
        virtual void setParameter ( QString paramName, QVariant value ) = 0;

        /*!
          Open the connection with the physical device

          @return Return true if the communication is well started
        */
        virtual bool open() = 0;

        /*!
          Close the connection with the physical device
        */
        virtual void close() = 0;

        /*!
          Get if the device is opened

          @return Retunr true if the communication is opened
        */
        virtual bool isOpened() = 0;

        /*!
          Return the meta object
        */

        virtual QObject *getMetaObject() = 0;

        /*!
          Return the device information in XML format
        */
        virtual QDomElement getDeviceInfo ( QDomDocument xml_info ) = 0;

        /*!
            Return the name of the plugin which control the access. If empty, all access plugin could
            control it
        */
        QString getAccessPluginName() { return accessPlugin; }

    public slots:

        /*!
          Dispatch a message to the device
        */
        virtual void dispatchMessage ( QByteArray ba ) = 0;

        /*!
          Do something on the device (open door, set output, ...)
          Depend of the device
        */
        virtual void deviceAction ( QString xml ) = 0;

        /*!
            set the path for the log
        */
        void setLogPath ( QString path ) {logPath = path;}

    protected:
        void addFunction ( QString name, void ( *func ) ( QObject *, QMap<QString, QVariant> ) )
        {
            interfaces[name] = func;
        }

        void checkPermision ( QString file )
        {
            if ( QFile::exists ( file ) )
            {

                QFileInfo fi ( file );

                if ( !fi.permission ( QFile::ReadOwner |
                                      QFile::WriteOwner |
                                      QFile::ReadUser |
                                      QFile::WriteUser |
                                      QFile::ReadGroup |
                                      QFile::WriteGroup |
                                      QFile::ReadOther |
                                      QFile::WriteOther ) )
                {

                    QFile::setPermissions ( file,
                                            QFile::ReadOwner |
                                            QFile::WriteOwner |
                                            QFile::ReadUser |
                                            QFile::WriteUser |
                                            QFile::ReadGroup |
                                            QFile::WriteGroup |
                                            QFile::ReadOther |
                                            QFile::WriteOther );
                }
            }
        }

        virtual void logComm ( uchar *ba, bool isReceive, int len ) = 0;

    signals:
        void deviceEvent ( QString xmlChange );
        void deviceInputChange ( int deviceId, int in, bool status );
        void deviceConnection ( int deviceId, bool isConnected );

    protected:

        //! id of the device in the database. This id musst be unique
        int id;

        //! name of the device in the database
        QString name;

        //! do we have to log the communiaction
        bool _isLog;

        //! which access plugin must control it
        QString accessPlugin;

        //! is the device connected
        bool _isConnected;

        //! serial number of the device
        QString serialNumber;

        //! firmware version
        QString firmwareVersion;

        //! mapping on the interface function
        QMap<QString, void ( * ) ( QObject *, QMap<QString, QVariant> ) > interfaces;

        //! path where the log musst be logged
        QString logPath;

        //! device who handle an open free access
        bool isFreeAccess;

        CDeviceInterface *deviceParent;
};

Q_DECLARE_INTERFACE ( CDeviceInterface,
                      "com.letux.Horux.CDeviceInterface/1.0" );

#endif
