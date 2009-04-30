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
#include "videowidget.h"
#include <QDesktopWidget>
#include <QGraphicsGridLayout>
#include <QTimer>

VideoWidget::VideoWidget(QGraphicsItem * parent, Qt::WindowFlags wFlags)
 : CMedia(parent,wFlags)
{
  type = MOVIE;

  media = new Phonon::MediaObject;
  vwidget = new Phonon::VideoWidget;

  vwidget->setScaleMode(Phonon::VideoWidget::FitInView);

  Phonon::createPath(media, vwidget);
  connect(media, SIGNAL(finished ()), this, SIGNAL(finished()));
  
  videoProxy = new QGraphicsProxyWidget(this);
  videoProxy->setWidget(vwidget);
}


VideoWidget::~VideoWidget()
{
  if(media)
    delete media;
  if(vwidget)
    delete vwidget;
  /*if(audioOutput)
    delete audioOutput;*/

} 



QGraphicsWidget *VideoWidget::getItem()
{
  return videoProxy;
}

void VideoWidget::paint(QPainter *painter, const QStyleOptionGraphicsItem *, QWidget *)
{
}

void VideoWidget::startDisplay()
{
  media->setCurrentSource(Phonon::MediaSource(fileMedia));
  media->play();

  if(duration > 0)
  {
    endTimer = startTimer(duration);
  }
}

void VideoWidget::stopDisplay()
{
  if(endTimer)
  {
    killTimer(endTimer);
    endTimer = 0;
  }
  media->stop();
}
