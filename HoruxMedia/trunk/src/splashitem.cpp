/****************************************************************************
**
** Copyright (C) 2008 Nokia Corporation and/or its subsidiary(-ies).
** Contact: Qt Software Information (qt-info@nokia.com)
**
** This file is part of the example classes of the Qt Toolkit.
**
** Commercial Usage
** Licensees holding valid Qt Commercial licenses may use this file in
** accordance with the Qt Commercial License Agreement provided with the
** Software or, alternatively, in accordance with the terms contained in
** a written agreement between you and Nokia.
**
**
** GNU General Public License Usage
** Alternatively, this file may be used under the terms of the GNU
** General Public License versions 2.0 or 3.0 as published by the Free
** Software Foundation and appearing in the file LICENSE.GPL included in
** the packaging of this file.  Please review the following information
** to ensure GNU General Public Licensing requirements will be met:
** http://www.fsf.org/licensing/licenses/info/GPLv2.html and
** http://www.gnu.org/copyleft/gpl.html.  In addition, as a special
** exception, Nokia gives you certain additional rights. These rights
** are described in the Nokia Qt GPL Exception version 1.3, included in
** the file GPL_EXCEPTION.txt in this package.
**
** Qt for Windows(R) Licensees
** As a special exception, Nokia, as the sole copyright holder for Qt
** Designer, grants users of the Qt/Eclipse Integration plug-in the
** right for the Qt/Eclipse Integration to link to functionality
** provided by Qt Designer and its related libraries.
**
** If you are unsure which license is appropriate for your use, please
** contact the sales department at qt-sales@nokia.com.
**
****************************************************************************/

#include "splashitem.h"

#include <QtGui/QtGui>
#include <QDesktopWidget>

SplashItem::SplashItem(QGraphicsItem *parent)
    : QGraphicsWidget(parent)
{

    QDesktopWidget desk;
    QRect rect = desk.screenGeometry();

    opacity = 1.0;

    
    timeLineHide = new QTimeLine(350);
    timeLineHide->setCurveShape(QTimeLine::EaseInCurve);
    connect(timeLineHide, SIGNAL(valueChanged(qreal)), this, SLOT(setValueHide(qreal)));

    timeLineShow = new QTimeLine(350);
    timeLineShow->setCurveShape(QTimeLine::EaseOutCurve);
    connect(timeLineShow, SIGNAL(valueChanged(qreal)), this, SLOT(setValueShow(qreal)));

    text = "";
    resize(rect.width()-200, 175);

    connect(&timerDisplay, SIGNAL(timeout()), timeLineHide, SLOT(start()));

    connect(&timerInfoDisplay, SIGNAL(timeout()), this, SLOT(displayInfoMessage()));


    timerInfoDisplay.start(1000);

    timeLineHide->start();
}

void SplashItem::setText(QString t, int time)
{
  text = t;
  
  timerDisplay.stop();

  if(t == "")
  {
    timeLineHide->start();
    return;
  }

  if(time > 0)
    timerDisplay.start(time*1000);

  if (timeLineShow->state() == QTimeLine::NotRunning)
    timeLineShow->start();
}

void SplashItem::displayInfoMessage()
{
  if (!timerDisplay.isActive())
  {

    QSqlQuery messageQuery("SELECT * FROM hr_horux_media_message WHERE type='INFO' AND startDisplay<=NOW() AND stopDisplay>=NOW()");
  
    QString message = "";
  
    while(messageQuery.next())
    {
      message += messageQuery.value(2).toString().toLatin1();
      message += "\n\n";
    }

    if(message != text)
      setText(message, 0);
  }
}

void SplashItem::paint(QPainter *painter, const QStyleOptionGraphicsItem *, QWidget *)
{
    QDesktopWidget desk;
    QRect rect1 = desk.screenGeometry();

    painter->setOpacity(opacity);
    //painter->setPen(QPen(Qt::black, 1));
    painter->setBrush(QColor(245, 245, 255, 220));
    painter->setClipRect(rect());

    painter->drawRoundRect(3, -50 + 3, rect1.width()-200 - 6, 250 - 6);

    QRectF textRect = rect().adjusted(10, 10, -10, -10);
    int flags = Qt::AlignTop | Qt::AlignLeft | Qt::TextWordWrap;

    QFont font;
    font.setPixelSize(25);
    painter->setPen(Qt::black);
    painter->setFont(font);

    painter->drawText(textRect, flags, text);
}

void SplashItem::setValueHide(qreal value)
{
    opacity = 1 - value;
    setPos(x(), scene()->sceneRect().top() - rect().height() * value);
    if (value == 1)
    {
        timeLineHide->stop();
        timerDisplay.stop();
        text = "";
    }
}

void SplashItem::setValueShow(qreal value)
{
    opacity = value;
    setPos(x(), scene()->sceneRect().top() - rect().height() + rect().height() * ( value ));
    if (value == 1)
    {
        timeLineShow->stop();
    }
}
