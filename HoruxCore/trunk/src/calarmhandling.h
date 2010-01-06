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
#ifndef CALARMHANDLING_H
#define CALARMHANDLING_H

#include <QObject>
#include <QMap>
#include <QtXml>
#include "calarminterface.h"

/**
    This class will load and handled the alarm plugins

    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CAlarmHandling : public QObject
{
        Q_OBJECT
    public:
        /*!
          Create a instance (singelton)
          @return Return the instance
        */
        static CAlarmHandling *getInstance();

        /*!
           destructor
        */
        ~CAlarmHandling();

        /*!
           Call the init funtion for each plugins.
           @return Return true if all plugins are well initialized else false
        */
        bool init();

        /*!
          Get if the alarm handler is started
          @return Return true if the alarm handler is started else false
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
        CAlarmHandling ( QObject *parent = 0 );

        /*!
          Load all log plugins
          @return Return true if all plugins are well loaded else false
        */
        bool loadPlugin();

    private:
        //! Alarm handler instance (singleton)
        static CAlarmHandling* pThis;

        //! alarm handler started
        bool started;

        //! list of the alarm plugins (plugin name, plugin instance)
        QMap<QString, CAlarmInterface*> alarmInterfaces;

    protected slots:
        void notification(QMap<QString, QVariant>param);

    signals:
        /*!
          Emit when a action musst be execute follow a alarm (ex:open door)
          @param xml Xml action
        */
        void alarmAction ( QString xml );

        /*!
          Emit when an alarm happens
          @param xml xml alarm definition
        */
        void alarmMonitor ( QString xml );

        /*!
          Emit when a device is un/connected
          @param deviceId  Id of the device in the database
          @param isConnected true if connected else false
        */
        void deviceConnectionMonitor ( int deviceId, bool isConnected );

        /*!
          Emit when an device input changed
          @param device Id of the device in the database
          @param in input number
          @param status input status
        */
        void deviceInputMonitor ( int deviceId, int in, bool status );
};

#endif
