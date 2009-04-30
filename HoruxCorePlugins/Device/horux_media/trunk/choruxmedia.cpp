/***************************************************************************
 *   Copyright (C) 2008 by LETUX					   *
 *   info@letux.ch   							   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License, or     *
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
#include <QtCore>
#include <QtSql> 

//! adapter
#include "choruxmedia.h"

CHoruxMedia::CHoruxMedia(QObject *parent) : QObject(parent)
{
  _isConnected = false;
}

CDeviceInterface *CHoruxMedia::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
  CDeviceInterface *p = new CHoruxMedia ( parent );

  p->setParameter("name",config["name"]);  
  p->setParameter("_isLog",config["isLog"]);
  p->setParameter("accessPlugin",config["accessPlugin"]);
  p->setParameter("id",config["id_device"]); 
 
  p->setParameter("ip",config["ip"]);  
  p->setParameter("port",config["port"]);  
  p->setParameter("id_action_device",config["id_action_device"]);  
  

  return p;
}


void CHoruxMedia::deviceAction(QString xml)
{
  QDomDocument doc;
  doc.setContent(xml);

  QDomElement root = doc.documentElement();

  QDomNode node = root.firstChild();

  if(root.tagName() != "deviceEvent")
  {
    return;
  } 
  if(root.attribute("id").toInt() != id_action_device)
    return;

  QMap<QString, QVariant>funcParam;

  QDomNode eventNode = root.firstChild();

  QDomElement event = eventNode.toElement();

  if(event.tagName() == "event") 
  {
    if(event.text() == "keyDetected")
    {

      eventNode = eventNode.nextSibling(); 
    
      QDomElement params = eventNode.toElement();

      QDomNode paramsNode = params.firstChild();
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
     
      displayMessage(funcParam["key"].toString());
    }
  }

}

void CHoruxMedia::connectChild(CDeviceInterface *)
{

}

QVariant CHoruxMedia::getParameter(QString paramName)
{
  if(paramName == "name")
    return name;
  if(paramName == "id")
    return id;
  if(paramName == "_isLog")
    return _isLog;
  if(paramName == "accessPlugin")
    return accessPlugin;

  if(paramName == "ip")
    return ip;
  if(paramName == "port")
    return port;
  if(paramName == "id_action_device")
    return id_action_device;


  return "undefined";
}

void CHoruxMedia::setParameter(QString paramName, QVariant value) 
{
  if(paramName == "name")
    name = value.toString();
  if(paramName == "id")
    id = value.toInt();
  if(paramName == "_isLog")
    _isLog = value.toBool();
  if(paramName == "accessPlugin")
    accessPlugin = value.toString();

  if(paramName == "ip")
    ip = value.toString();
  if(paramName == "port")
    port = value.toInt();
  if(paramName == "id_action_device")
    id_action_device = value.toInt();
}

bool CHoruxMedia::open()
{
  _isConnected = true;

  emit deviceConnection(id, true);
  return true;
}
    
void CHoruxMedia::close()
{
  _isConnected = false;

   //! Signal emit pour signaler ��Horux Core que le p�rip�rique est d�connect�
  emit deviceConnection(id, false);
}

bool CHoruxMedia::isOpened()
{
  return _isConnected;
}

void CHoruxMedia::dispatchMessage(QByteArray ba)
{
}


void CHoruxMedia::logComm(uchar *ba, bool isReceive, int len)
{
}

QDomElement CHoruxMedia::getDeviceInfo(QDomDocument xml_info )
{
  QDomElement device = xml_info.createElement( "device");
  device.setAttribute("id", QString::number(id));

  QDomElement newElement = xml_info.createElement( "name");
  QDomText text =  xml_info.createTextNode(name);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "isConnected");
  text =  xml_info.createTextNode(QString::number(_isConnected));
  newElement.appendChild(text);
  device.appendChild(newElement);

  return device;

}

void CHoruxMedia::displayMessage(QString key)
{
  QSqlQuery query("SELECT ka.id_user FROM hr_keys AS k LEFT JOIN hr_keys_attribution AS ka ON ka.id_key=k.id WHERE k.serialNumber='" + key + "'");

  QString userId = "0";

  if(query.next())
    userId = query.value(0).toString();

  rpc = new MaiaXmlRpcClient(QUrl("http://" + ip + ":" + QString::number(port) + "/RPC2"), this);

  QVariantList args;
  args << userId;
  rpc->call("horuxMedia.userDetected", args,
                          this, SLOT(xmlrpcResponse(QVariant &)),
                          this, SLOT(xmlrpcFault(int, const QString &)));

}


void CHoruxMedia::xmlrpcResponse(QVariant &arg) {
}

void CHoruxMedia::xmlrpcFault(int error, const QString &message) {
		qDebug() << "EEE:" << error << "-" << message;
}

Q_EXPORT_PLUGIN2(horuxmedia, CHoruxMedia);
