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
#include "cimage.h"
#include <QDesktopWidget>

CImage::CImage(QGraphicsItem * parent, Qt::WindowFlags wFlags)
 : CMedia(parent, wFlags)
{
  type = IMAGE;
}

CImage::CImage(const QPixmap &pix, QGraphicsItem * parent, Qt::WindowFlags wFlags)
  : CMedia(parent, wFlags), p(pix ), currentRotation(0.0)
{
  QDesktopWidget desk;
  QRect rect = desk.screenGeometry();
  p = p.scaled(rect.size(), Qt::IgnoreAspectRatio);
  type = IMAGE;

}

CImage::~CImage()
{
}


void CImage::paint(QPainter *painter, const QStyleOptionGraphicsItem *, QWidget *w)
{
    painter->drawPixmap(QPointF(), p);
}

void CImage::startDisplay()
{
  if(duration > 0)
  {
    endTimer = startTimer(duration);
  }
  else
    endTimer = startTimer(30000);
  
}

void CImage::stopDisplay()
{
  if(endTimer)
  {
    killTimer(endTimer);
    endTimer = 0;
  }

}

