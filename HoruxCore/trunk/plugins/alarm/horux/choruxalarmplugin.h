/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License.     *
 *                                       *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/
#ifndef CHORUXALARMPLUGIN_H
#define CHORUXALARMPLUGIN_H

#include <QObject>
#include <QMap>
#include <QVariant>
#include "calarminterface.h"


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


//! System
#define ALARM_XML_RPC_SERVER                "1200" //! old 1050




/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CHoruxAlarmPlugin : public QObject, CAlarmInterface
{
    Q_OBJECT
    Q_INTERFACES(CAlarmInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2008" );
    Q_CLASSINFO ( "Version", "0.0.0.1" );
    Q_CLASSINFO ( "PluginName", "alarm_horux" );
    Q_CLASSINFO ( "PluginType", "alarm" );
    Q_CLASSINFO ( "PluginDescription", "Handle the alarm for Horux Core" );
public:

    void alarmMonitor(QString xml);
    void deviceConnectionMonitor(int deviceId, bool isConnected);
    void deviceInputMonitor(int deviceId, int in, bool status);
    QObject *getMetaObject() { return this;}

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

