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
#include "caccesslinkserial.h"
#include <QSettings>

CAccessLinkSerial::CAccessLinkSerial(QObject * parent) : CAccessLinkDevice(parent) 
{
  QSettings settings ( "Horux", "HoruxGuiSys" );

  QString portStr = settings.value("port", "").toString();
  int techno = settings.value("tech", 0).toInt();

  port = new QextSerialPort(portStr);

  port->setBaudRate(BAUD9600 );   
  port->setFlowControl(FLOW_OFF);
  port->setParity(PAR_NONE);    
  port->setDataBits(DATA_8);   
  port->setStopBits(STOP_2);
  port->setTimeout(0,1);

  if(!port->open(QIODevice::ReadWrite))
  {
    delete port;
    port = NULL;
    emit deviceError();
  }
}


CAccessLinkSerial::~CAccessLinkSerial()
{
  if(port)
  {
    port->close();
    delete port;
    port = NULL;
  }
}


void CAccessLinkSerial::run()
{
  while(port->isOpen())
  {
    if(port->bytesAvailable () > 0)
    {
      msg += port->readAll();

      handleMsg();

      if(msg.length() > 10)
      {
        msg.clear();
        keyDetection.clear();
      }
    }
    QThread::msleep(10);
  }
}

void CAccessLinkSerial::close(bool isError)
{
  if(port)
  {
    port->close();
    
    wait();

    delete port;
    port = NULL;
  }
}
