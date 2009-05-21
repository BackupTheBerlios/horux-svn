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
#ifndef CHORUX_H
#define CHORUX_H
#include <QtXml>
#include <QObject>

class MaiaXmlRpcServer;
class CNotification;

/**
    Main class of the Horux applications

    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CHorux : public QObject
{
        Q_OBJECT
    public:
        /*!
          Constuctor
        */
        CHorux ( QObject *parent = 0 );

        /*!
          destructor
        */
        ~CHorux();

        /*!
          Set if the slot are called by XMLRPC request or internaly
          @param flag true when internaly else false
        */
        void setInternalCall ( bool flag ) { isInternal=flag; }

        /*!
            Send a notification (email, sms, etc)
            This function is static. The notification is sended by 
            calling a web service from Horux Gui

            @param params Parameters of the notification
        */
        static void sendNotification(QMap<QString, QVariant> params);


    public slots:
        /*!
          Start the engine. Can be called by XMLRPC
          @return return true when ok else false
        */
        bool startEngine();

        /*!
          Stop the engine. Can be called by XMLRPC but with username and password auhtentication
          @return return true when ok else false
        */
        void stopEngine ( QString username, QString password );

        /*!
          Allow to know if the engine is started. Can be called by XMLRPC
          @return return "ok" when started else "ko"
        */
        QString isEngine();

        /*!
          Construct an info XML output about the system
          @return return the xml info
        */
        QString getInfo( );


    protected:
        //! call th slot internaly or not
        bool isInternal;

        //! horux engine started
        bool isStarted;

        //! date and time when the server was stared
        QDateTime serverStarted;

        //! xmlrpc server
        MaiaXmlRpcServer *ptr_xmlRpcServer;

        //! Notification
        CNotification *notification;

        //! pointer of the instance
        static CHorux *ptr_this;
};

#endif
