/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License.        *
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

/*!

Sous windows:
keybd_event

*/

#include <QtGui>
#include "choruxgui.h"

int main(int argc, char *argv[])
{
     QApplication app(argc, argv);

     QTranslator myappTranslator;
     QString lang = QLocale::system().name();
     if(lang.contains("fr"))
        lang = "fr_FR";
     myappTranslator.load(app.applicationDirPath() +  "/horux_" + lang +".qm");
     app.installTranslator(&myappTranslator);

     int counter = 5 * 60;   // test during 5 minutes

     while(!QSystemTrayIcon::isSystemTrayAvailable() && counter > 0)
     {
         QApplication::processEvents();

         #if defined(Q_OS_WIN)
             Sleep(1000);
        #elif defined(Q_WS_X11)
             sleep(1);
        #endif

       counter--;
     }

     if (!QSystemTrayIcon::isSystemTrayAvailable()) {
         QMessageBox::critical(0, QObject::tr("Systray"),
                               QObject::tr("I couldn't detect any system tray "
                                           "on this system."));
         return 1;
     }

     CHoruxGui hg;
     return app.exec();
}

