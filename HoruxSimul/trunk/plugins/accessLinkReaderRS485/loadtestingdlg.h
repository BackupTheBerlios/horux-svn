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
#ifndef LOADTESTINGDLG_H
#define LOADTESTINGDLG_H

#include <QtGui>
#include <QDialog>

#include "ui_uiloadtesting.h"

class LoadTestingDlg: public QDialog, public Ui::loadTest
{
  Q_OBJECT

  public:
    LoadTestingDlg ( QWidget * parent = 0, Qt::WindowFlags f = 0 );

  protected slots:
    void on_addKey_clicked();
    void on_start_clicked();
    void on_stop_clicked();

  signals:
    void sendTag(unsigned long n);

  protected:
    void timerEvent(QTimerEvent *e);

  protected:
    int t;

};

#endif
