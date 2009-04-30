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
#ifndef CDEVICE_H
#define CDEVICE_H

#include <QThread>

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CDevice : public QThread
{
Q_OBJECT
public:
    CDevice(QObject *parent = 0);

    ~CDevice();

     virtual void close(bool isError=false) = 0;

  protected:
     virtual void handleMsg() = 0;
     virtual void handleKey() = 0;

  signals:
    void keyDetected(QByteArray key);
    void deviceError();
    void readError();

  protected:
    QByteArray msg;
    QList<QByteArray> keyDetection;
};

#endif
