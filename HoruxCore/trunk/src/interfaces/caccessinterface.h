
#ifndef CACCESSINTERFACE_H
#define CACCESSINTERFACE_H



/**
    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CAccessInterface
{

    public:

        virtual ~CAccessInterface() {}

        virtual bool isAccess ( QMap<QString, QVariant> params, bool emitAction, bool emitNotification ) = 0;

        /*!
          Return the meta object
        */
        virtual QObject *getMetaObject() = 0;

        void setAccessInterfaces ( QMap<QString, CAccessInterface*> ai ) {accessInterfaces = ai;}

    public slots:
        virtual void deviceEvent ( QString ) = 0;
        virtual void deviceConnectionMonitor ( int, bool ) = 0;
        virtual void deviceInputMonitor ( int , int , bool  ) = 0;


    signals:
        void accessAction ( QString xml );
        void notification(QMap<QString, QVariant>param);

    protected:
        QMap<QString, CAccessInterface*> accessInterfaces;

};

Q_DECLARE_INTERFACE ( CAccessInterface,
                      "com.letux.Horux.CAccessInterface/1.0" );

#endif
