
#ifndef CFACTORY_H
#define CFACTORY_H

/**
This is the factory class

    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/


class CLog;
class CDbHandling;
class CAccessHandling;
class CDeviceHandling;
class CAlarmHandling;

class CFactory
{
    public:
        /*!
          Return the instance of CDbHandling
        */
        static CDbHandling *getDbHandling();

        /*!
          Return the instance of CLog
        */
        static CLog* getLog();

        /*!
          Return the instance of CAccessHandling
        */
        static CAccessHandling* getAccessHandling();

        /*!
          Return the instance of CDeviceHandling
        */
        static CDeviceHandling* getDeviceHandling();

        /*!
          Return the instance of CAlarmHandling
        */
        static CAlarmHandling* getAlarmHandling();

};

#endif
