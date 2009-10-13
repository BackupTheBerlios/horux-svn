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
#ifndef CACCESSINTERFACE_H
#define CACCESSINTERFACE_H



/**
    @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CAccessInterface
{

    public:

        virtual ~CAccessInterface() {}

        virtual void deviceEvent ( QMap<QString, QVariant> params ) = 0;

        virtual bool isAccess ( QMap<QString, QVariant> params, bool emitAction ) = 0;

        virtual void deviceConnectionMonitor ( int, bool ) {}

        virtual void deviceInputMonitor ( int , int , bool  ) {}


        /*!
          Return the meta object
        */

        virtual QObject *getMetaObject() = 0;

        void setAccessInterfaces ( QMap<QString, CAccessInterface*> ai ) {accessInterfaces = ai;}

    signals:
        void accessAction ( QString xml );
        void notification(QMap<QString, QVariant>param);

    protected:
        QMap<QString, CAccessInterface*> accessInterfaces;

};

Q_DECLARE_INTERFACE ( CAccessInterface,
                      "com.letux.Horux.CAccessInterface/1.0" );

#endif
