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
#ifndef CIMAGE_H
#define CIMAGE_H

#include "cmedia.h"
#include <QPixmap>
#include <QPainter>


/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CImage : public CMedia
{
Q_OBJECT

public:
    CImage(QGraphicsItem * parent = 0, Qt::WindowFlags wFlags = 0);
    CImage(const QPixmap &pix, QGraphicsItem * parent = 0, Qt::WindowFlags wFlags = 0);

    ~CImage();

    virtual void startDisplay();
    virtual void stopDisplay();

    void paint(QPainter *painter, const QStyleOptionGraphicsItem *, QWidget *);

protected:
    QSizeF sizeHint(Qt::SizeHint, const QSizeF & = QSizeF());

private:
    QPixmap p;
    double currentRotation;

};

#endif
