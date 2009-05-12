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

#ifndef CXMLFACTORY_H
#define CXMLFACTORY_H

#include <QtCore>
#include <QtXml>

class CXmlFactory
{
public:
    /*!
        Create the xml structure to send a system alarm
        @id Id of the destination object
        @e Event number
        @m Message text
     */
    static QString systemAlarm(QString id, QString e, QString m);

    /*!
        Create the xml structure to send a device event
        @id Id of the destination object
        @e Event number
        @m Message text
     */
    static QString deviceEvent(QString id, QString e, QString m);

    /*!
        Create the xml structure to send a access alarm
        @id Id of the destination object
        @e Event number
        @m Message text
     */
    static QString accessAlarm(QString id, QString e, QString m);

    /*!
        Create the xml structure to send a device action
        @id Id of the destination object
        @f Function name
        @p list of the parameters QMap<[Param name], [Param value]>
     */
    static QString deviceAction(QString id, QString f, QMap<QString, QString>p);

    /*!
        Create the xml structure to send a key detection
        @id Id of the destination object
        @pn Name of the access plugin
        @k Value of the key
     */
    static QString keyDetection(QString id, QString pn, QString k);

};

#endif // CXMLFACTORY_H
