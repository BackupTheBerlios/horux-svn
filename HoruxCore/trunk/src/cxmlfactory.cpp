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

QString CXmlFactory::deviceEvent(QString id, QString e, QMap<QString, QString>p)
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

    doc.appendChild(deviceEvent);
    return doc.toString();
}

QString CXmlFactory::keyDetection(QString id, QString id_parent, QString pn, QString k)
{
    QDomDocument doc;

    QDomElement deviceEvent = doc.createElement("deviceEvent");
    deviceEvent.setAttribute("id", id);
    deviceEvent.setAttribute("id_parent", id_parent);

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
    action.appendChild(params);

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

QMap<QString, MapParam> CXmlFactory::deviceAction(QString xml, int id, int parent_id)
{
  QMap<QString, MapParam> funcList;

  QDomDocument doc;
  doc.setContent(xml);

  QDomElement root = doc.documentElement();

  QDomNode node = root.firstChild();

  if(root.tagName() != "deviceAction")
  {
    return funcList;
  }

  if(root.attribute("id").toInt() != id && root.attribute("id").toInt() != parent_id)
    return funcList;

  QDomNode actionNode = root.firstChild();

  while(!actionNode.isNull())
  {
    QDomElement action = actionNode.toElement();

    if(action.tagName() == "action")
    {
      QString funcName;
      QMap<QString, QVariant>funcParam;

      QDomNode functionNode = action.firstChild();
      while(!functionNode.isNull())
      {
        QDomElement function = functionNode.toElement();

        // On récupère le nom de la fonction qui devra être exécutée
        if(function.tagName() == "function")
          funcName = function.text();

        // On récupère les paramètres pour la fonction
        if(function.tagName() == "params")
        {
          QDomNode paramsNode = function.firstChild();
          while(!paramsNode.isNull())
          {
            QDomElement params = paramsNode.toElement();

            if(params.tagName() == "param")
            {
              QString pName;
              QVariant pValue;
              QDomNode p = params.firstChild();
              if(p.toElement().tagName() == "name")
              {
                pName = p.toElement().text();
                p = p.nextSibling();
                if(p.toElement().tagName() == "value")
                {
                  pValue = p.toElement().text();
                  funcParam[pName] = pValue;
                }
              }
            }

            paramsNode = paramsNode.nextSibling();
          }

        }

        functionNode = functionNode.nextSibling();

      }

      funcList[funcName] = funcParam;

    }

    actionNode = actionNode.nextSibling();
  }

  return funcList;
}

QMap<QString, QVariant> CXmlFactory::deviceEvent(QString xml)
{
    QMap<QString, QVariant>funcParams;

    QDomDocument doc;
    doc.setContent ( xml );

    QDomElement root = doc.documentElement();

    QDomNode node = root.firstChild();

    //! check if it is a device event
    if ( root.tagName() != "deviceEvent" )
    {
        return funcParams;
    }

    QString deviceId = root.attribute ( "id" );
    QString deviceParentId = root.attribute ( "id_parent" );

    QDomNode eventNode = root.firstChild();

    QDomElement event = eventNode.toElement();

    //! check if the request contain the tag "event"
    if ( event.tagName() == "event" )
    {

        funcParams["event"] = event.text();

        //! check if the request is not empty
        if ( event.text() != "" )
        {

            eventNode = eventNode.nextSibling();

            QDomElement params = eventNode.toElement();

            QDomNode paramsNode = params.firstChild();
            while ( !paramsNode.isNull() )
            {
                QDomElement params = paramsNode.toElement();

                if ( params.tagName() == "param" )
                {
                    QString pName;
                    QVariant pValue;
                    QDomNode p = params.firstChild();
                    if ( p.toElement().tagName() == "name" )
                    {
                        pName = p.toElement().text();
                        p = p.nextSibling();
                        if ( p.toElement().tagName() == "value" )
                        {
                            pValue = p.toElement().text();
                            funcParams[pName] = pValue;

                        }
                    }
                }

                paramsNode = paramsNode.nextSibling();
            }

            funcParams["deviceId"] = deviceId;
            funcParams["deviceParentId"] = deviceParentId;
        }
        else
            return funcParams;
    }
    else
        return funcParams;

  return funcParams;
}
