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
#include "caccesslinkdevice.h"
#include <QtCore>

CAccessLinkDevice::CAccessLinkDevice(QObject *parent)
 : CDevice(parent)
{
}


CAccessLinkDevice::~CAccessLinkDevice()
{
}

void CAccessLinkDevice::handleMsg()
{
  uchar len = (uchar)msg[0];

  //! check if we have enough data for at least one message
  if( msg.length() < len) return;

  uchar cmd = (uchar)msg[1];

  //! be sure that the message is tag detection or tag standart read
  if(cmd != 0x90 && cmd != 0x92)
  {
    msg.remove(0,len);
    return;
  }

  uchar tagStatus = (uchar)msg[2] & 0x07;

  //! wait, no information, timeout
  if(tagStatus == 0 || tagStatus == 0x04 || tagStatus == 0x05)
  {
    msg.remove(0,len);
    return;
  }

  //! more data will comming
  if(tagStatus == 0x01 || tagStatus == 0x02)
  {
    if(cmd == 0x90)
      keyDetection << msg.left(len);
    msg.remove(0,len);
    handleMsg();
    return;
  }

  //! all data are received
  if(tagStatus == 0x03 || tagStatus == 0x07)
  {
    if(cmd == 0x90)
      keyDetection << msg.left(len);
    msg.remove(0,len);

    handleKey();

    return;
  }
}

void CAccessLinkDevice::handleKey()
{
  QByteArray key;
  //! read only tag or read/write tag without tag identification
  if(keyDetection.count() == 1)
  {
    QByteArray sn = keyDetection.at(0);

    //! this is not possible, the sn is missing
    if(sn[3] == (char)0x21)
    {
      keyDetection.clear();
      return;
    }

    //! read only tag
    if(sn[0] == (char)10)
    {
      key = sn.mid(4,5);
    }

    //! read write tag without tag identification
    if(sn[0] == (char)9)
    {
      key = sn.mid(4,4);
    }


  }

  //! read/write tag with tag identification
  if(keyDetection.count() == 2)
  {
     QByteArray sn = keyDetection.at(0).mid(4,4);
     QByteArray tid = keyDetection.at(1).mid(4,4);

     key = tid + sn;
  }

  keyDetection.clear();

  if(key.length() > 0)
    emit keyDetected(key);
}

