
#ifndef GANTNERACCESS_H
#define GANTNERACCESS_H

#include <QObject>
#include <QtSql>
#include <QtSoapHttpTransport>

#include "caccessinterface.h"
#include "cxmlfactory.h"

#define TIME_DB_CHECKING 5000

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CGantnerAccess : public QObject, CAccessInterface
{
    Q_OBJECT
    Q_INTERFACES(CAccessInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2010" );
    Q_CLASSINFO ( "Version", "1.0.0" );
    Q_CLASSINFO ( "PluginName", "gantneraccess" );
    Q_CLASSINFO ( "PluginType", "access" );
    Q_CLASSINFO ( "PluginDescription", "Handle the GAT Terminal 3100 AK" );
    Q_CLASSINFO ( "DbTableUsed", "");
    Q_CLASSINFO ( "DbTrackingTable", "hr_tracking");


    struct s_schedule
    {
        int number;
        int accessLevelId;
        QMap<QString, int> days;

    };

public:
    CGantnerAccess(QObject *parent=NULL);
    void deviceEvent(QMap<QString, QVariant> params);
    bool isAccess(QMap<QString, QVariant> params, bool emitAction, bool emitNotification);
    QObject *getMetaObject() { return this;}

public slots:
    void deviceEvent(QString xml);
    void deviceConnectionMonitor(int, bool);
    void deviceInputMonitor ( int , int , bool );

protected:
    void reloadAllData(QString deviceId);

    void initSAASMode();

protected slots:
    void checkDb();

    /*!
      Read the soap response from Horux Gui
    */
    void readSoapResponse();

    /*!
      Read the SSL error when doing a SOAP transaction
    */
    void soapSSLErrors ( QNetworkReply * reply, const QList<QSslError> & errors );

signals:
  void accessAction(QString xml);
  void notification(QMap<QString, QVariant>param);

private:
    QTimer *timerCheckDb;
    QMap<int, bool> devices;
    QtSoapHttpTransport soapClient;


    //! saas param
    bool saas;
    QString saas_host;
    bool saas_ssl;
    QString saas_username;
    QString saas_password;
    QString saas_path;

};

#endif
