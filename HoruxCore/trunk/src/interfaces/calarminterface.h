/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
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
#ifndef CALARMINTERFACE_H
#define CALARMINTERFACE_H

/**
    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CAlarmInterface
{
    public:
        virtual ~CAlarmInterface() {};

        virtual void alarmMonitor ( QString xml ) = 0;
        virtual void deviceConnectionMonitor ( int deviceId, bool isConnected ) = 0;
        virtual void deviceInputMonitor ( int deviceId, int in, bool status ) = 0;

        /*!
          Return the meta object
        */

        virtual QObject *getMetaObject() = 0;

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
