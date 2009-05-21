/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
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
#ifndef VELOPARK_H
#define VELOPARK_H

#include <QObject>
#include <QtSql>

#include "caccessinterface.h"
#include "cxmlfactory.h"

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CVeloPark : public QObject, CAccessInterface
{
    Q_OBJECT
    Q_INTERFACES(CAccessInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2008" );
    Q_CLASSINFO ( "Version", "0.0.1" );
    Q_CLASSINFO ( "PluginName", "velopark" );
    Q_CLASSINFO ( "PluginType", "access" );
    Q_CLASSINFO ( "PluginDescription", "Handle the access for the plugin velopark" );

public:
    void deviceEvent(QMap<QString, QVariant> params);
	bool isAccess(QMap<QString, QVariant> params, bool emitAction);
    QObject *getMetaObject() { return this;}

protected:
  	bool checkAccess(QMap<QString, QVariant> params, bool emitAction);
	bool checkSubDate(QDateTime start, QDateTime end);
	void acceptAccess(QMap<QString, QVariant> params, bool isOk);
    void displayMessage(QString type, QString deviceId);
    void setLightStatus(QString deviceId, QString key);

    void timerEvent(QTimerEvent *e);
signals:
  void accessAction(QString xml);
  void notification(QMap<QString, QVariant>param);

protected:
    QMap<int, int> displayTimeTimer; //! <Timer,displayId>

};

#endif
