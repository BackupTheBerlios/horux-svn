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
#ifndef CHORUXGUI_H
#define CHORUXGUI_H

#include <QtGui>
#include <QSystemTrayIcon>

#include "ui_settings.h"


#include "caccesslinkserial.h"
#include "caccesslinkusb.h"
#include "cgat5250b.h"

class QextSerialPort;

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CHoruxGui : public QWidget, public Ui::Settings
{
Q_OBJECT
public:
    CHoruxGui(QWidget *parent = 0);

    ~CHoruxGui();

 protected:
     void closeEvent(QCloseEvent *event);
     void openCom();

     void timerEvent(QTimerEvent *e);

protected slots:
	void test();
	void sendKey(QString key);
	void on_apply_clicked();
	void on_save_clicked();

        void deviceError();
        void readError();
        void keyDetected(QByteArray key);

protected:
	QSystemTrayIcon *trayIcon;

	QAction *quitAction;
	QAction *settingsAction;
	QAction *testAction;
	QMenu *trayIconMenu;

	QProcess *process;

        CAccessLinkUsb *al_usb_reader;
        CAccessLinkSerial *al_serial_reader;
        CGAT5250B *gat5250_serial_reader;

        QHash<QString, int> antipassback;
};

#endif
