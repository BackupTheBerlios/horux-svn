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

#include "cxmlfactory.h"
#include <QtXml>

QString CXmlFactory::systemAlarm(QString id, QString e, QString m)
{
    QDomDocument doc;

    QDomElement systemAlarm = doc.createElement("systemAlarm");
    systemAlarm.setAttribute("id", id);

    QDomElement event = doc.createElement("event");
    QDomText e_dt = doc.createTextNode(e);
    event.appendChild(e_dt);

    systemAlarm.appendChild(event);

    QDomElement params = doc.createElement("params");
    systemAlarm.appendChild(params);

    QDomElement param = doc.createElement("param");
    params.appendChild(param);

    QDomElement name = doc.createElement("name");
    QDomText n_dt = doc.createTextNode("message");
    name.appendChild(n_dt);

    param.appendChild(name);

    QDomElement value = doc.createElement("value");
    QDomText v_dt = doc.createTextNode(m);
    value.appendChild(v_dt);

    param.appendChild(value);

    doc.appendChild(systemAlarm);
    return doc.toString();
}

QString CXmlFactory::deviceEvent(QString id, QString e, QString m)
{
    QDomDocument doc;

    QDomElement deviceEvent = doc.createElement("deviceEvent");
    deviceEvent.setAttribute("id", id);

    QDomElement event = doc.createElement("event");
    QDomText e_dt = doc.createTextNode(e);
    event.appendChild(e_dt);

    deviceEvent.appendChild(event);

    QDomElement params = doc.createElement("params");
    deviceEvent.appendChild(params);

    QDomElement param = doc.createElement("param");
    params.appendChild(param);

    QDomElement name = doc.createElement("name");
    QDomText n_dt = doc.createTextNode("message");
    name.appendChild(n_dt);

    param.appendChild(name);

    QDomElement value = doc.createElement("value");
    QDomText v_dt = doc.createTextNode(m);
    value.appendChild(v_dt);

    param.appendChild(value);

    doc.appendChild(deviceEvent);
    return doc.toString();
}

QString CXmlFactory::keyDetection(QString id, QString pn, QString k)
{
    QDomDocument doc;

    QDomElement deviceEvent = doc.createElement("deviceEvent");
    deviceEvent.setAttribute("id", id);

    QDomElement event = doc.createElement("event");
    QDomText e_dt = doc.createTextNode("keyDetected");
    event.appendChild(e_dt);

    deviceEvent.appendChild(event);

    QDomElement params = doc.createElement("params");
    deviceEvent.appendChild(params);

    QDomElement param = doc.createElement("param");
    params.appendChild(param);

    QDomElement name = doc.createElement("name");
    QDomText n_dt = doc.createTextNode("AccessPluginName");
    name.appendChild(n_dt);
    param.appendChild(name);

    QDomElement value = doc.createElement("value");
    QDomText v_dt = doc.createTextNode(pn);
    value.appendChild(v_dt);
    param.appendChild(value);

    QDomElement param2 = doc.createElement("param");
    params.appendChild(param2);

    QDomElement name2 = doc.createElement("name");
    QDomText n2_dt = doc.createTextNode("key");
    name2.appendChild(n2_dt);
    param2.appendChild(name2);

    QDomElement value2 = doc.createElement("value");
    QDomText v2_dt = doc.createTextNode(k);
    value2.appendChild(v2_dt);
    param2.appendChild(value2);


    doc.appendChild(deviceEvent);
    return doc.toString();
}

QString CXmlFactory::accessAlarm(QString id, QString e, QString m)
{
    QDomDocument doc;

    QDomElement accessAlarm = doc.createElement("accessAlarm");
    accessAlarm.setAttribute("id", id);

    QDomElement event = doc.createElement("event");
    QDomText e_dt = doc.createTextNode(e);
    event.appendChild(e_dt);

    accessAlarm.appendChild(event);

    QDomElement params = doc.createElement("params");
    accessAlarm.appendChild(params);

    QDomElement param = doc.createElement("param");
    params.appendChild(param);

    QDomElement name = doc.createElement("name");
    QDomText n_dt = doc.createTextNode("message");
    name.appendChild(n_dt);

    param.appendChild(name);

    QDomElement value = doc.createElement("value");
    QDomText v_dt = doc.createTextNode(m);
    value.appendChild(v_dt);

    param.appendChild(value);

    doc.appendChild(accessAlarm);
    return doc.toString();
}

QString CXmlFactory::deviceAction(QString id, QString f, QMap<QString, QString>p)
{
    QDomDocument doc;

    QDomElement deviceAction = doc.createElement("deviceAction");
    deviceAction.setAttribute("id", id);

    QDomElement action = doc.createElement("action");
    deviceAction.appendChild(action);

    QDomElement function = doc.createElement("function");
    QDomText f_dt = doc.createTextNode(f);
    function.appendChild(f_dt);
    action.appendChild(function);

    QDomElement params = doc.createElement("params");
    deviceAction.appendChild(params);

    QMapIterator<QString, QString> i(p);
    while (i.hasNext())
    {
        i.next();
        QDomElement param = doc.createElement("param");
        params.appendChild(param);

        QDomElement name = doc.createElement("name");
        QDomText n_dt = doc.createTextNode( i.key() );
        name.appendChild(n_dt);

        param.appendChild(name);

        QDomElement value = doc.createElement("value");
        QDomText v_dt = doc.createTextNode( i.value());
        value.appendChild(v_dt);
        param.appendChild(value);
     }


    doc.appendChild(deviceAction);
    return doc.toString();

}
