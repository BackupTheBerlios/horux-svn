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

//! adapter
#include "cunittest.h"

CUnitTest::CUnitTest(QObject *parent) : QObject(parent)
{
  _isConnected = false;
}

CDeviceInterface *CUnitTest::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
  CDeviceInterface *p = new CUnitTest ( parent );

  p->setParameter("name",config["name"]);  
  p->setParameter("_isLog",config["isLog"]);
  p->setParameter("accessPlugin",config["accessPlugin"]);
  p->setParameter("id",config["id_device"]); 


  return p;
}


void CUnitTest::deviceAction(QString xml)
{
    qDebug() << xml;
}

void CUnitTest::connectChild(CDeviceInterface *)
{

}

QVariant CUnitTest::getParameter(QString paramName)
{
  if(paramName == "name")
    return name;
  if(paramName == "id")
    return id;
  if(paramName == "_isLog")
    return _isLog;
  if(paramName == "accessPlugin")
    return accessPlugin;

  return "undefined";
}

void CUnitTest::setParameter(QString paramName, QVariant value)
{
  if(paramName == "name")
    name = value.toString();
  if(paramName == "id")
    id = value.toInt();
  if(paramName == "_isLog")
    _isLog = value.toBool();
  if(paramName == "accessPlugin")
    accessPlugin = value.toString();
}

bool CUnitTest::open()
{
  _isConnected = true;

  emit deviceConnection(id, true);

  return true;
}
    
void CUnitTest::close()
{
  _isConnected = false;

  emit deviceConnection(id, false);
}

bool CUnitTest::isOpened()
{
  return _isConnected;
}

void CUnitTest::dispatchMessage(QByteArray )
{
}


void CUnitTest::logComm(uchar *, bool , int )
{
}

QDomElement CUnitTest::getDeviceInfo(QDomDocument xml_info )
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

Q_EXPORT_PLUGIN2(unittest, CUnitTest);
