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
#ifndef CMEDIA_H
#define CMEDIA_H

#include <QGraphicsWidget>
#include <QTimerEvent>

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CMedia : public QGraphicsWidget
{
public:
  enum MTYPE
  {
    IMAGE,
    MOVIE
  };

Q_OBJECT
public:
    CMedia( QGraphicsItem * parent = 0, Qt::WindowFlags wFlags = 0);

    ~CMedia();
    virtual void startDisplay(){}
    virtual void stopDisplay(){}
    
    void setMedia(QString f);

    void setDuration(int d);
    MTYPE getType() { return type;}

    virtual QGraphicsWidget *getItem() {return this;}
  
protected:
    void timerEvent(QTimerEvent *e);


signals:
    void finished();

protected:
    int endTimer;
    int duration;
    QString fileMedia;
    MTYPE type;
};

#endif
