
#include "include.h"


/*!
    \fn CFactory::getDbHandling()
 */
CDbHandling *CFactory::getDbHandling()
{
    return CDbHandling::getInstance();
}


/*!
    \fn CFactory::getLog()
 */
CLog* CFactory::getLog()
{
    return CLog::getInstance();
}

/*!
    \fn CFactory::getAccessHandling()
 */
CAccessHandling* CFactory::getAccessHandling()
{
    return CAccessHandling::getInstance();
}

/*!
    \fn CFactory::getAccessHandling()
 */
CDeviceHandling* CFactory::getDeviceHandling()
{
    return CDeviceHandling::getInstance();
}

/*!
    \fn CFactory::getAlarmHandling()
 */
CAlarmHandling* CFactory::getAlarmHandling()
{
    return CAlarmHandling::getInstance();
}

