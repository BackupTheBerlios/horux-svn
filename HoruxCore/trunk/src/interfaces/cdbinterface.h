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
#ifndef CDBINTERFACE_H
#define CDBINTERFACE_H

#include <QMap>
#include <QVariant>

class QSqlQuery;


/**
Interface classes for the db plugin

    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CDbInterface
{
    public:

        virtual ~CDbInterface() {}

        /*!
          Open the database

          @param host Host of the database (default "localhost")
          @param db Name of the database
          @param username Name of the user having access to the database
          @param password Password of the user having access to the database
          @return return true if the db is well opened
        */
        virtual bool open ( const QString host,
                            const QString db,
                            const QString username,
                            const QString password ) = 0;

        /*!
          Load the database schema

          @param host Host of the database (default "localhost")
          @param db Name of the database
          @param username Name of the user having access to the database
          @param password Password of the user having access to the database
          @param queries Queries of the db schema
          @return return true if the db is well loaded
        */
        virtual bool loadSchema ( const QString host,
                            const QString db,
                            const QString username,
                            const QString password,
                            const QString queries) = 0;

        /*!
          Load the database data
          @param queries Queries of the data to be load
          @return return true if the db is well opened
        */
        virtual bool loadData ( const QString queries ) = 0;

        /*!
          Close the database
        */
        virtual void close() = 0;

        /*!
          Get the device list. This function return a qmap "id, plugin type"

          @return Return a QMap of the device
        */
        virtual QMap<int, QString> getDeviceList() = 0;

        /*!
          Get the id of the parent device
          
          @return Return the parent device id
        */
        virtual int getParentDevice ( int deviceId ) = 0;

        /*!
          Get the device configuration for a spefici device

          @param deviceId Id of the device
          @param database table for the device

          @return Return a list of parameters of the device config
        */
        virtual QMap<QString, QVariant> getDeviceConfiguration ( const int deviceId, QString type ) = 0;

        /*!
          Return the meta object
        */
        virtual QObject *getMetaObject() = 0;

        virtual QVariant getConfigParam ( QString paramName ) = 0;

        /*!
          Check the XMLRPC access authorization

          @param username Horux gui username
          @param password Horux gui password

          @return Return true if the user as access else false
        */
        virtual bool isXMLRPCAccess ( QString username, QString password ) = 0;

        /*!
          Count the number of notification configured according to the params

          @param params Notification parameters

          @return Return the number of notification according to the params
        */
        virtual int countNotification( QMap<QString, QVariant> params) = 0;


};

Q_DECLARE_INTERFACE ( CDbInterface,
                      "com.letux.Horux.CDbInterface/1.0" );

#endif
