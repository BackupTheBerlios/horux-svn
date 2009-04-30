/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License.     *
 *                                       *
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
#ifndef CSERVER_H
#define CSERVER_H

#include <QTcpServer>

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CServer : public QTcpServer
{
Q_OBJECT
public:
    static CServer* getInstance();
    ~CServer();
    bool start();


protected:
    CServer(QObject *parent = 0);

private:
    static CServer *pThis;
protected slots:
    void newInternfaceConnection();
signals:
    void newConnection(QTcpSocket *);
};

#endif
