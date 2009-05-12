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
#ifndef CLOGINTERFACE_H
#define CLOGINTERFACE_H


/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CLogInterface{
public:
    virtual ~CLogInterface() {};

    virtual void debug(QString msg) = 0;
    virtual void warning(QString msg) = 0;
    virtual void critical(QString msg) = 0;
    virtual void fatal(QString msg) = 0;
    void setLogPath(QString path) {this->path = path;}

    /*!
      Return the meta object
    */

    virtual QObject *getMetaObject() = 0;

protected:
    QString path;
};


Q_DECLARE_INTERFACE(CLogInterface,
                     "com.letux.Horux.CLogInterface/1.0");

#endif
