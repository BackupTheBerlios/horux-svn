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
    Q_CLASSINFO ( "Version", "0.0.1" );
    Q_CLASSINFO ( "PluginName", "access_horux" );
    Q_CLASSINFO ( "PluginType", "access" );
    Q_CLASSINFO ( "PluginDescription", "Handle the access for Horux Core" );

public:
    AccessHoruxPlugin(QObject *parent=0);
    void deviceEvent(QMap<QString, QVariant> params);
    QObject *getMetaObject() { return this;}
    bool isAccess(QMap<QString, QVariant> params, bool emitAction);
    void deviceConnectionMonitor(int, bool);

protected:
  bool checkAccess(QMap<QString, QVariant> params, bool emitAction);
  bool checkAccessLevel(QString groupId, QString deviceId, QString *reason);
  void insertTracking(QString userId, QString keyId, QString entryId, QString reason, bool isAccess, QString serialNumber, bool emitAction);
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
