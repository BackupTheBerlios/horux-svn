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
#ifndef CACCESSLINKUSB_H
#define CACCESSLINKUSB_H

#include <QThread>

#if defined(Q_OS_WIN)
    #include <windows.h>
    #include <Setupapi.h>
    #include <ddk\Hidclass.h>

#elif defined(Q_WS_X11)
  #define HAVE_STDBOOL_H
  #include <hid.h>
#endif

#include "caccesslinkdevice.h"

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CAccessLinkUsb : public CAccessLinkDevice
{
  Q_OBJECT
 public:
    CAccessLinkUsb(QObject * parent = 0);

     void run();
     void close(bool isError=false);

  protected:
    #if defined(Q_OS_WIN)
        bool FindTheHID();
    #endif

  private:
    #if defined(Q_OS_WIN)
     HANDLE m_handlePort;
     HANDLE m_readDataEvent;
     ULONG Length;
     HANDLE hDevInfo;
     ULONG Required;
    #elif defined(Q_WS_X11)
        HIDInterface* hid;
    #endif
    
    bool isOpend;

};

#endif
