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

#include "loadtestingdlg.h"

LoadTestingDlg::LoadTestingDlg ( QWidget * parent , Qt::WindowFlags f  ) : QDialog(parent, f)
{
  setupUi(this);

  QValidator *validator = new QIntValidator(0, 999999999, this);
  key->setValidator(validator);  

  stop->setEnabled(false);

  t = 0;
}


void LoadTestingDlg::on_addKey_clicked()
{
  if(key->text() != "")
  {
    keysList->addItem (key->text());
  }
}

void LoadTestingDlg::on_start_clicked()
{
  stop->setEnabled(true);
  start->setEnabled(false);
  addKey->setEnabled(false);
  clearList->setEnabled(false);
  
  t = startTimer(sendTimer->value());
}

void LoadTestingDlg::on_stop_clicked()
{
  stop->setEnabled(false);
  start->setEnabled(true);
  addKey->setEnabled(true);
  clearList->setEnabled(true);

  killTimer(t);
  t = 0;
}

void LoadTestingDlg::timerEvent(QTimerEvent *e)
{
  int n = keysList->count();

  int randomIndex = qrand() % n;

  unsigned long key = keysList->item(randomIndex)->text().toULong();

  if(key == 0)
  {
    key = qrand();

    if(key == 0) key++;
  }

  emit sendTag(key);
}