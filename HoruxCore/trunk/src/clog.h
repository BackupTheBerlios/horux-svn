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
#ifndef CLOG_H
#define CLOG_H
#include <QtXml>

#include <QObject>
#include <QMap>
#include "cloginterface.h"
/**
    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>

    This class load the plugins who will log the Qt message:
    - Debug
    - Warning
    - Critical
    - fatal
*/
class CLog : public QObject
{
        Q_OBJECT
    public:
        /*!
          Create a instance (singelton)
          @return Return the instance
        */
        static CLog *getInstance();

        /*!
           destructor
        */
        ~CLog();


        /*!
           Call the init funtion for each plugins.
           @return Return true if all plugins are well initialized else false
        */
        bool init();

        /*!
          Get if the log handler is started
          @return Return true if the log handler is started else false
        */
        bool isStarted();

        /*!
          Called when the function qDebug is called. The handler dispatch to all plugins
          @param msg debug message
        */
        void debug ( QString msg );

        /*!
          Called when the function qWarning is called. The handler dispatch to all plugins
          @param msg warning message
        */
        void warning ( QString msg );

        /*!
          Called when the function qCritical is called. The handler dispatch to all plugins
          @param msg critical message
        */
        void critical ( QString msg );

        /*!
          Called when the function qFatal is called. The handler dispatch to all plugins
          @param msg fatal message
        */
        void fatal ( QString msg );

        /*!
          Construct an info XML element about the loaded plugins
          @param xml_info Root XML element
          @return return the log plugins info
        */
        QDomElement getInfo ( QDomDocument xml_info );


    protected:
        /*!
          Constructor
        */
        CLog ( QObject *parent = 0 );

        /*!
          Load all log plugins
          @return Return true if all plugins are well loaded else false
        */
        bool loadPlugin();

    private:
        //! Log handler instance (singleton)
        static CLog* pThis;

        //! log handler started
        bool started;

        //! list of the log plugins (plugin name, plugin instance)
        QMap<QString, CLogInterface*> logInterfaces;

};

#endif
