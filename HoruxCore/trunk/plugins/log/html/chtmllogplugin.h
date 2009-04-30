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
#ifndef CHTMLLOGPLUGIN_H
#define CHTMLLOGPLUGIN_H

#include <QObject>
#include "cloginterface.h"

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CHtmlLogPlugin : public QObject, CLogInterface
{
    Q_OBJECT
    Q_INTERFACES(CLogInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2008" );
    Q_CLASSINFO ( "Version", "0.0.0.1" );
    Q_CLASSINFO ( "PluginName", "htmllog_horux" );
    Q_CLASSINFO ( "PluginType", "log" );
    Q_CLASSINFO ( "PluginDescription", "Log all the message in html for Horux Core" );
public:
    void debug(QString msg);
    void warning(QString msg);
    void critical(QString msg);
    void fatal(QString msg);
    QObject *getMetaObject() { return this;}

protected:
		void checkPermision(QString file);

};

#endif
