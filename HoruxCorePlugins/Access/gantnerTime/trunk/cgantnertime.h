
#ifndef GANTNERTIME_H
#define GANTNERTIME_H

#include <QObject>
#include <QtSql>
#include <QtSoapHttpTransport>

#include "caccessinterface.h"
#include "cxmlfactory.h"

#define TIME_DB_CHECKING 10000

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CGantnerTime : public QObject, CAccessInterface
{
    Q_OBJECT
    Q_INTERFACES(CAccessInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2010" );
    Q_CLASSINFO ( "Version", "0.0.2" );
    Q_CLASSINFO ( "PluginName", "gantnertime" );
    Q_CLASSINFO ( "PluginType", "access" );
    Q_CLASSINFO ( "PluginDescription", "Handle the Gantner time unit GAT p.time ST X80 / ST X81 / ST X90" );
    Q_CLASSINFO ( "DbTableUsed", "hr_keys,hr_tracking,hr_timux_config,hr_timux_booking,hr_user,hr_user_group,hr_user_group_attribution,hr_user_group_access,hr_device,hr_gantner_standalone_action,hr_keys_attribution,hr_gantner_TimeTerminal,hr_timux_timecode,hr_timux_booking_bde");
    Q_CLASSINFO ( "DbTrackingTable", "hr_timux_booking,hr_tracking,hr_timux_booking_bde");

public:
    CGantnerTime(QObject *parent=NULL);
    void deviceEvent(QMap<QString, QVariant> params);
    bool isAccess(QMap<QString, QVariant> params, bool emitAction, bool emitNotification);
    QObject *getMetaObject() { return this;}

public slots:
    void deviceEvent(QString xml);
    void deviceConnectionMonitor(int, bool);
    void deviceInputMonitor ( int , int , bool );

protected:
    void reloadAllData();
    void initSAASMode();
    void setUserPresence(QString userId, bool isPresent);

protected slots:
    void checkDb();
    void checkBalances(int id=0);

    /*!
      Read the soap response from Horux Gui
    */
    void readSoapResponse();
    void readSoapBalancesResponse();
    void readSoapInputBDEResponse();

    /*!
      Read the SSL error when doing a SOAP transaction
    */
    void soapSSLErrors ( QNetworkReply * reply, const QList<QSslError> & errors );

signals:
  void accessAction(QString xml);
  void notification(QMap<QString, QVariant>param);

private:
  QTimer *timerCheckDb;
  QTimer *timerCheckBalances;
  QMap<int, bool> devices;
  QtSoapHttpTransport soapClient;
  QtSoapHttpTransport soapClientBalances;
  QtSoapHttpTransport soapClientInputBDE;

    //! saas param
    bool saas;
    QString saas_host;
    bool saas_ssl;
    QString saas_username;
    QString saas_password;
    QString saas_path;

};

#endif
