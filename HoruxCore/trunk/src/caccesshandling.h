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
          Slot called when a event happens on the device like keyDetection
          @param xml Xml event
        */
        void deviceEvent ( QString xml );

        /*!
          This slot is called when a device is un/connected
          @param deviceId  Id of the device in the database
          @param isConnected true if connected else false
        */
        void deviceConnectionMonitor ( int, bool );

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
};

#endif
