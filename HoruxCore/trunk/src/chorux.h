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
#include <QtSoapHttpTransport>
#include <QObject>

class MaiaXmlRpcServer;
class MaiaXmlRpcClient;

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

        /*!
          Get e list of pluging which should not be load by Horux
          @return Return a string list of the plugins
        */
        static QStringList getUnloadPlugins();

        /*!
          Get the id for this Horux Controller
          @return Return the id of this Horux conctroller
        */
        static int getHoruxControllerId();

        /*!
          Get if this Horux Controller is a master/slave controller
          @return Return true if is is a master else false
        */
        static bool isMasterHoruxController();

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

        /*!
          Stop a device. Can be called by XMLRPC but with username and password auhtentication
        */
        void stopDevice ( QString username, QString password, QString id );

        /*!
          Start a device. Can be called by XMLRPC but with username and password auhtentication
        */
        void startDevice ( QString username, QString password, QString id );

        /*!
          This funciton is called by the slave Horux Controller to informe the master about its status
        */
        void setSlaveHoruxControllerInfo(int controllerId, QString xml);

    private slots:
        /*!
          Read the soap response from Horux Gui
        */
        void readSoapResponse();

        /*!
          Read the SSL error when doing a SOAP transaction
        */
        void soapSSLErrors ( QNetworkReply * reply, const QList<QSslError> & errors );

        /*!
          Send the last tracking in the saas mode
        */
        void sendTracking();

        /*!
          get the db modificaiton from Horux gui in the saas mode
        */
        void syncData();


        void xmlRpcClientResponse(QVariant &);

        void xmlRpcClientFault(int, const QString &);

    private:
        void initSAASMode();

    protected:

        //! call th slot internaly or not
        bool isInternal;

        //! horux engine started
        bool isStarted;

        //! date and time when the server was stared
        QDateTime serverStarted;

        //! xmlrpc server
        MaiaXmlRpcServer *ptr_xmlRpcServer;
        MaiaXmlRpcClient *ptr_xmlRpcClient;

        //! soap client
        QtSoapHttpTransport soapClient;
        QTimer *timerSoapInfo;
        QTimer *timerSoapTracking;
        QTimer *timerSoapSyncData;

        //! saas param
        bool saas;
        QString saas_host;
        bool saas_ssl;
        QString saas_username;
        QString saas_password;
        QString saas_path;
        int saas_info_send_timer;
        bool isFullReloaded;
        QString saas_syncMode;

        //! pointer of the instance
        static CHorux *ptr_this;

        //! Horux controller info
        QMap<int, QString>horuxControllerInfo;

    signals:
        /*!
          Emit when a alarm musst be catch by the alarm plugin
          @param xml Xml alarm
        */
        void sendAlarm ( QString xml );
};

#endif
