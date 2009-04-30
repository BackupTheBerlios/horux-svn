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
