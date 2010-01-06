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
#ifndef CDBHANDLING_H
#define CDBHANDLING_H

#include <QObject>
#include <QtXml>
#include "cdbinterface.h"

/**
     This class will load and handled the database plugin

    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CDbHandling : public QObject
{
        Q_OBJECT
    public:
        /*!
          Create a instance (singelton)
          @return Return the instance
        */
        static CDbHandling *getInstance();

        /*!
           destructor
        */
        ~CDbHandling();

        /*!
           Call the init funtion for if the plugin.
           @return Return true if the plugin are well initialized else false
        */
        bool init();

        /*!
          Get if the db handler is started
          @return Return true if the db handler is started else false
        */
        bool isStarted();

        /*!
          Return an instance on the db plugin
          @return return the instance of the db plugin insterface
        */
        CDbInterface * plugin();

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


        bool loadSchema(QString queries);
        bool loadData(QString queries);

    protected:
        /*!
          Constructor
        */
        CDbHandling ( QObject *parent = 0 );

        /*!
          Load all db plugins
          @return Return true if all plugins are well loaded else false
        */
        bool loadPlugin();

    private:
        //! Db handler instance (singleton)
        static CDbHandling* pThis;

        //! intance of the db plugin interface
        CDbInterface *dbInterface;

    protected:
        //! devices handler started
        bool started;
};

#endif
