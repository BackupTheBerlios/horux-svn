/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
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
#ifndef CPLAYERLIST_H
#define CPLAYERLIST_H

#include <QObject>
#include <QList>
#include <QGraphicsProxyWidget>
#include <QGraphicsScene>
#include <QGraphicsView>
#include <QtSql>
#include <QDesktopWidget>

#include "videowidget.h"
#include "cimage.h"
#include "maiaXmlRpcServer.h"
#include "splashitem.h"


#if defined(QT_EXPERIMENTAL_SOLUTION)
# include <common.h>
# include <qstate.h>
# include <qstategroup.h>
# include <qtransition.h>
# include <qanimationgroup.h>
# include <qanimation.h>
#endif

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CPlayerList : public QObject
{
Q_OBJECT
public:
    CPlayerList(QObject * parent = 0);

    ~CPlayerList();
    
    

protected slots:
    //! XMLRPC access
    QString getMediaList();
    void start();
    void stop();
    void reload();
    void userDetected(QString userId);
    //! ------

    void playNext();

protected:
    void createTransition();

protected:
    QList<CMedia *> mediaList;
    int index;
    QGraphicsView *myview;
    QGraphicsScene *scene;
    QtStateGroup *group;
    QList<QtState *> stateList;
    QList<QtTransition *> transitionList;
    QSqlDatabase dbase;
    MaiaXmlRpcServer *xmlRpcServer;
    SplashItem *splash;
    QString deviceId;
    QDesktopWidget desk;
    bool isStart;
};

#endif


