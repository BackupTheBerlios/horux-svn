
#ifndef CALARMINTERFACE_H
#define CALARMINTERFACE_H



/**
    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CAlarmInterface
{
    public:
        virtual ~CAlarmInterface() {}

        /*!
          Return the meta object
        */
        virtual QObject *getMetaObject() = 0;

    public slots:
        virtual void alarmMonitor ( QString xml ) = 0;
        virtual void deviceConnectionMonitor ( int deviceId, bool isConnected ) = 0;
        virtual void deviceInputMonitor ( int deviceId, int in, bool status ) = 0;


    signals:
        /*!
          Emit when a action musst be execute follow a alarm (ex:open door)
          @param xml Xml action
        */
        void alarmAction ( QString xml );
        void notification(QMap<QString, QVariant>);

};


Q_DECLARE_INTERFACE ( CAlarmInterface,
                      "com.letux.Horux.CAlarmInterface/1.0" );

#endif
