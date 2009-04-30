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

#include <QSplashScreen>
#include <QPixmap>
#include <QApplication>
#include "horuxsimul.h"

int main(int argc, char *argv[])
{
      Q_INIT_RESOURCE(application);
      QApplication app(argc, argv);
    
      
      QSplashScreen *splash = new QSplashScreen ;
      splash->setPixmap(QPixmap(":/images/splash.png"));
      splash->show();

      Qt::Alignment topRight = Qt::AlignRight | Qt::AlignTop; 

      splash->showMessage(QObject::tr("Setting up the main window..."), 
                            topRight, Qt::black);

      app.processEvents();


      horuxSimul * mw = new horuxSimul();

      splash->showMessage(QObject::tr("Loading plugins..."), 
                            topRight, Qt::black);

      app.processEvents();
#if defined(Q_OS_WIN)
      _sleep(1000);
#elif defined(Q_WS_X11)
      sleep(1);
#endif      

      mw->loadPlugins();

      splash->showMessage(QObject::tr("Ok"), 
                            topRight, Qt::black);

      app.processEvents();
#if defined(Q_OS_WIN)
      _sleep(1000);
#elif defined(Q_WS_X11)
      sleep(1);
#endif      

      delete splash;

      mw->show();

      return app.exec();
}

