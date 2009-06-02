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
#include "cplayerlist.h"

#include <QPalette>
#include <QGraphicsProxyWidget>
#include <QDateTime>
#include <QFile>
#include <QSettings>
#include <QCoreApplication>
#include <QMessageBox>

CPlayerList::CPlayerList(QObject * parent)
 : QObject(parent)
{
  isStart = false; 

  QSettings settings(QCoreApplication::applicationDirPath () + "/config.ini",
                    QSettings::IniFormat);

  if(!settings.contains("media_path"))
    settings.setValue("media_path","");

  if(!settings.contains("mysql_host"))
    settings.setValue("mysql_host","localhost");

  if(!settings.contains("mysql_db"))
    settings.setValue("mysql_db","horux");

  if(!settings.contains("mysql_username"))
    settings.setValue("mysql_username","root");

  if(!settings.contains("mysql_password"))
    settings.setValue("mysql_password","");

  if(!settings.contains("xmlrpc"))
    settings.setValue("xmlrpc",7000);


  if(!settings.contains("device_id"))
    settings.setValue("device_id",0);

  if(!settings.contains("displayDefaultImage"))
    settings.setValue("displayDefaultImage",true);

  dbase = QSqlDatabase::addDatabase("QMYSQL");
  dbase.setHostName(settings.value("mysql_host","localhost").toString());
  dbase.setDatabaseName(settings.value("mysql_db","horux").toString());
  dbase.setUserName(settings.value("mysql_username","root").toString());
  dbase.setPassword(settings.value("mysql_password","").toString());
  bool result = dbase.open();

  if(!result)
  {
    exit(0);
  }

  xmlRpcServer = new MaiaXmlRpcServer(settings.value("xmlrpc",7000).toInt(), this);

  xmlRpcServer->addMethod("horuxInfoDisplay.userDetected", this, "userDetected");
  xmlRpcServer->addMethod("horuxInfoDisplay.reload", this, "reload");
  xmlRpcServer->addMethod("horuxInfoDisplay.start", this, "start");
  xmlRpcServer->addMethod("horuxInfoDisplay.stop", this, "stop");
  xmlRpcServer->addMethod("horuxInfoDisplay.getMediaList", this, "getMediaList");


  deviceId = settings.value("device_id","0").toString();

  if(deviceId == "0")
  {
    QMessageBox::information(NULL, "Error", "Set the device id parameter in config.ini and restart the application");
    exit(1);
    return;
  }

  scene = NULL;
  splash = NULL;
  myview = NULL;
  group = NULL;

  start();
}

CPlayerList::~CPlayerList()
{
}

QString CPlayerList::getMediaList()
{

  QStringList sl;

  QSettings settings(QCoreApplication::applicationDirPath () + "/config.ini",
                    QSettings::IniFormat);


  QString mediaPath = settings.value("media_path","").toString();


  QDir dir(mediaPath);
  dir.setFilter(QDir::Files | QDir::Hidden | QDir::NoSymLinks);
  dir.setSorting(QDir::Size | QDir::Reversed);

  QFileInfoList list = dir.entryInfoList();
  for (int i = 0; i < list.size(); ++i) {
      QFileInfo fileInfo = list.at(i);
      sl << fileInfo.fileName();
  } 

  return sl.join(",");
}

void CPlayerList::start()
{

  QSettings settings(QCoreApplication::applicationDirPath () + "/config.ini",
                    QSettings::IniFormat);

  scene = new QGraphicsScene(desk.screenGeometry(), this);
  scene->setBackgroundBrush(Qt::black);


  if( settings.value("displayDefaultImage",true).toBool() )
  {
    CImage *image = new CImage(QPixmap(":/images/horux.png"));
    image->setDuration(5000);
    mediaList << image;

    connect(image, SIGNAL(finished()), this, SLOT(playNext()));
  }

  splash = new SplashItem();
  splash->setZValue(5);
  splash->setPos(100, scene->sceneRect().top());
  scene->addItem(splash);

  myview = new QGraphicsView(scene);
  myview->setHorizontalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
  myview->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
  myview->setRenderHints(QPainter::Antialiasing | QPainter::SmoothPixmapTransform);
  myview->setFrameShadow(QFrame::Plain);
  myview->setFrameShape(QFrame::NoFrame);
  myview->showFullScreen();
  myview->setCursor( QCursor( Qt::BlankCursor ) );



  QString mediaPath = settings.value("media_path","").toString();

  QSqlQuery query("SELECT * FROM hr_horux_media_media WHERE published=1 AND id_device=" + deviceId + " ORDER by `order`");

  while(query.next())
  {
    QString type = query.value(2).toString();
    QString path = mediaPath + query.value(3).toString();
    int time = query.value(4).toInt();
    QFile file(path);
    if(file.exists())
    {
      if(type == "IMAGE")
      {
        CImage *image = new CImage(QPixmap(path));
        image->setDuration(time * 1000);
        mediaList << image;
      
        connect(image, SIGNAL(finished()), this, SLOT(playNext()));
      }
      
      if(type == "MOVIE")
      {
        VideoWidget *video = new VideoWidget();
      
        video->setDuration(time * 1000);
        video->setMedia(path);

        mediaList << video;
  
        connect(video, SIGNAL(finished()), this, SLOT(playNext()));
      }
    }
  }


  if(mediaList.count() == 0)
  {
    CImage *image = new CImage(QPixmap(":/images/horux.png"));
    image->setDuration(5000);
    mediaList << image;

    connect(image, SIGNAL(finished()), this, SLOT(playNext()));
  }

  group = new QtStateGroup(scene);

  createTransition();

  stateList.at(0)->activate();

  isStart = true;

  if(mediaList.size() > 0)
  {
    index = 0;
    playNext();
  }
}

void CPlayerList::stop()
{
  isStart = false;

  for(int i=0; i<mediaList.count(); i++)
  {
    mediaList.at(i)->stopDisplay();
    delete mediaList.at(i);
  }

  if(scene)
  {
    delete scene;
  }

  if(myview)
  {
    delete myview;
  }

  mediaList.clear();
  stateList.clear();


  scene = NULL;
  splash = NULL;
  myview = NULL;
  group = NULL;
}

void CPlayerList::reload()
{
  stop();
  start();
}

void CPlayerList::userDetected(QString userId)
{ 
  QSqlQuery userQuery("SELECT * FROM hr_user WHERE id="+userId);

  QString name = "";
  QString firstName = "";
  QString time = QTime::currentTime().toString(Qt::ISODate);
  QString date = QDate::currentDate().toString("d.M.yyyy");
  QString day = QDate::currentDate().toString("dddd");

  if(userQuery.next())
  {
    QString name = userQuery.value(1).toString();
    QString firstName = userQuery.value(2).toString();

    QSqlQuery messageQuery("SELECT * FROM hr_horux_media_message WHERE type='ALL' OR id_user="+userId + " ORDER BY ID");
    QString message = "";
    while(messageQuery.next())
    {
      message += messageQuery.value(2).toString().toLatin1();

      message.replace(QString("{day}"), day);
      message.replace(QString("{date}"), date);
      message.replace(QString("{time}"), time);
      message.replace(QString("{name}"), name);
      message.replace(QString("{firstName}"), firstName);

      message += "\n\n";

    }

      splash->setText(message);


  }
  else
  {
      QSqlQuery messageQuery("SELECT * FROM hr_horux_media_message WHERE type='UNKNOWN'");
      if(messageQuery.next())
      {
        QString message = messageQuery.value(2).toString().toLatin1();
        message.replace(QString("{day}"), day);
        message.replace(QString("{time}"), time);
        message.replace(QString("{date}"), date);
        splash->setText(message);
      }
  }

  
}


void CPlayerList::createTransition()
{
  QDesktopWidget desk;
  QRect rect = desk.screenGeometry();

  for(int i=0; i<mediaList.count(); i++)
  {

    scene->addItem(mediaList.at(i)->getItem());

    QtState *state = new QtState(scene);

    stateList << state;
    
    group->addState(state);
  
    state->setGeometry(mediaList.at(i)->getItem(), QRectF(rect));
    state->setOpacity(mediaList.at(i)->getItem(), double(1.0));


    for(int j=0; j<i; j++)
    {
      state->setGeometry(mediaList.at(j)->getItem(), QRectF(-(rect.width()),0,rect.width(),rect.height()));
      state->setOpacity(mediaList.at(j)->getItem(), double(0.0));
    }

    for(int j=i+1; j<mediaList.count(); j++)
    {
      state->setGeometry(mediaList.at(j)->getItem(), QRectF(-(rect.width()),0,rect.width(),rect.height()));
      state->setOpacity(mediaList.at(j)->getItem(), double(0.0));
    }
  }

  if(stateList.count() > 1)
  {
    for(int i=0; i<stateList.count(); i++)
    {
      QtTransition *t = NULL;

      if(i == stateList.count()-1)
      {
        t = new QtTransition(stateList.at(i),stateList.at(0), scene);
        t->add(new QtAnimation(mediaList.at(i)->getItem(), "geometry"));
        t->add(new QtAnimation(mediaList.at(0)->getItem(), "geometry"));
      }
      else
      {
        t = new QtTransition(stateList.at(i),stateList.at(i+1),scene);
        t->add(new QtAnimation(mediaList.at(i)->getItem(), "geometry"));
        t->add(new QtAnimation(mediaList.at(i+1)->getItem(), "geometry"));
      }

      transitionList << t;
    }
  }
}

void CPlayerList::playNext()
{

  if(!isStart) return;

  mediaList.at(index)->startDisplay();
  stateList.at(index)->activate();


  if(index == mediaList.size()-1)
    index = 0;
  else
    index++;
}
