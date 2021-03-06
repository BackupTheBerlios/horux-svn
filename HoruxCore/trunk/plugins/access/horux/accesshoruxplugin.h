
#ifndef ACCESSHORUXPLUGIN_H
#define ACCESSHORUXPLUGIN_H

#include <QObject>
#include <QtSql>

#include "caccessinterface.h"
#include "cxmlfactory.h"

//!access result
#define ACCESS_OK			"0"
#define KEY_BLOCKED			"1"
#define KEY_UNKNOW_BY_APP		"2"
#define KEY_NOT_ATTRIBUTED		"3"
#define USER_HAS_NO_GROUP		"4"
#define BLOCK_DURING_THE_WEEK_END       "5"
#define BLOCK_DURING_NON_WORK_DAY	"6"
#define VALIDITY_DATE_OUT		"7"
#define TIME_BLOCK			"8"
#define GROUP_HAS_NO_ACCESS_LEVEL	"9"
#define USER_BLOCKED			"11"

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class AccessHoruxPlugin : public QObject, CAccessInterface
{
    Q_OBJECT
    Q_INTERFACES(CAccessInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2008" );
    Q_CLASSINFO ( "Version", "1.0.6" );
    Q_CLASSINFO ( "PluginName", "access_horux" );
    Q_CLASSINFO ( "PluginType", "access" );
    Q_CLASSINFO ( "PluginDescription", "Handle the access for Horux Core" );
    Q_CLASSINFO ( "DbTableUsed", "hr_openTime_attribution,hr_openTime,hr_non_working_day,hr_openTime_time,hr_keys,hr_keys_attribution,hr_user_group_attribution,hr_user_group,hr_user,hr_user_group_access,hr_access_level,hr_non_working_day,hr_access_time,hr_tracking");
    Q_CLASSINFO ( "DbTrackingTable", "hr_tracking");

public:
    AccessHoruxPlugin(QObject *parent=0);
    QObject *getMetaObject() { return this;}
    bool isAccess(QMap<QString, QVariant> params, bool emitAction, bool emitNotification);

public slots:
    void deviceConnectionMonitor(int, bool);
    void deviceEvent(QString xml);
    void deviceInputMonitor ( int  , int , bool  ) { }

protected:
  bool checkAccess(QMap<QString, QVariant> params, bool emitAction, bool emitNotification);
  bool checkAccessLevel(QString groupId, QString deviceId, QString *reason);
  void insertTracking(QString userId, QString keyId, QString deviceId, QString parendId, QString reason, bool isAccess, QString serialNumber, bool emitAction, bool emitNotification);
  void timerEvent(QTimerEvent *e);
  void checkFreeAccess();

signals:
  void accessAction(QString xml);
  void notification(QMap<QString, QVariant>param);
  
protected:
  int timerFreeAccess;
  QStringList currentFreeAccess;

};

#endif
