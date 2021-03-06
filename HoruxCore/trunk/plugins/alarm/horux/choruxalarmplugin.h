
#ifndef CHORUXALARMPLUGIN_H
#define CHORUXALARMPLUGIN_H

#include <QObject>
#include <QMap>
#include <QVariant>
#include "calarminterface.h"
#include "cxmlfactory.h"

//! device
#define ALARM_DEVICE_ANTI_VANDALE_ON		"1001" //! 
#define ALARM_DEVICE_ANTI_VANDALE_OFF	    "1002" //! 
#define ALARM_DEVICE_OPEN		            "1003" //! old 1031
#define ALARM_DEVICE_CLOSE		            "1004" //! old 1032
#define ALARM_DOOR_AJAR_ON                  "1005" //! old 1033
#define ALARM_DOOR_AJAR_OFF                 "1006" //! old 1034
#define ALARM_DOOR_FORCED                   "1007" //! old 1035
#define ALARM_TOO_MANY_PIN                  "1008" //! old 1036
#define ALARM_DEVICE_TEMPERATUR             "1009" //! old 1060
#define ALARM_DEVICE_EEPROM_DB_FULL         "1010" //! old 1071
#define ALARM_DEVICE_EEPROM_DB_WARNING      "1011" //! old 1072
#define ALARM_DEVICE_EEPROM_DB_INSERT       "1012" //! old 1073
#define ALARM_DEVICE_EEPROM_DB_REMOVE       "1013" //! old 1074
#define ALARM_DEVICE_ANTENNA_ON             "1014" //! old 1201
#define ALARM_DEVICE_ANTENNA_OFF            "1015" //! old 1202

#define ALARM_DEVICE_CONNECTION_ERROR       "1016" //! 
#define ALARM_DEVICE_CMD_ERROR              "1017" //! 


//! access
#define ALARM_KEY_BLOCKED		            "1100" //! old 1080
#define ALARM_PERSON_BLOCKED		        "1101" //! old 1100
#define ALARM_PINCODE_HOLDUP                    "1102"

//! System
#define ALARM_XML_RPC_SERVER                "1200" //! old 1050

//! DB
#define ALARM_RELAOD_DB                    "1300"
#define ALARM_RELAOD_DB_OK                 "1301"




/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CHoruxAlarmPlugin : public QObject, CAlarmInterface
{
    Q_OBJECT
    Q_INTERFACES(CAlarmInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2008" );
    Q_CLASSINFO ( "Version", "1.0.6" );
    Q_CLASSINFO ( "PluginName", "alarm_horux" );
    Q_CLASSINFO ( "PluginType", "alarm" );
    Q_CLASSINFO ( "PluginDescription", "Handle the alarm for Horux Core" );
    Q_CLASSINFO ( "DbTableUsed", "hr_alarms");
    Q_CLASSINFO ( "DbTrackingTable", "hr_alarms");

public:
    QObject *getMetaObject() { return this;}

public slots:
    void alarmMonitor(QString xml);
    void deviceConnectionMonitor(int deviceId, bool isConnected);
    void deviceInputMonitor(int deviceId, int in, bool status);


protected:
    void handleHalarm(QString name, QMap<QString, QVariant>params);

signals:
    /*!
      Emit when a action musst be execute follow a alarm (ex:open door)
      @param xml Xml action
    */
    void alarmAction(QString xml);

    void notification(QMap<QString, QVariant>);
};

#endif

