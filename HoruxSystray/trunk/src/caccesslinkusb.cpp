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
#include "caccesslinkusb.h"
#include <QtCore>

CAccessLinkUsb::CAccessLinkUsb(QObject * parent) : CAccessLinkDevice(parent)
{
  isOpend = false;

  #if defined(Q_OS_WIN)
    m_handlePort = NULL;
    m_readDataEvent = CreateEvent(NULL, TRUE, FALSE, NULL);
  #elif defined(Q_WS_X11)
      hid_return ret;
      ret = hid_init();
      if (ret != HID_RET_SUCCESS) {
        emit deviceError();
        return;
      }
      hid = NULL;
  #endif
}

void CAccessLinkUsb::run()
{
  #if defined(Q_OS_WIN)
    if(!FindTheHID())
    {
     close(true);
     emit deviceError();
     return;
    }
    
    isOpend = true;

    unsigned char buffer[9];
    DWORD numberOfBytesRead = 0;

    OVERLAPPED  overlapped;

    overlapped.Offset       = 0;
    overlapped.OffsetHigh   = 0;
    overlapped.hEvent       = m_readDataEvent;

    QByteArray tmp;

    tmp.clear();

    while(isOpend)
    {
        memset(buffer,0,9);

        if(!ReadFile(m_handlePort, buffer, 9, &numberOfBytesRead,&overlapped))
        {
            DWORD error = GetLastError();
            if ( error == ERROR_IO_PENDING)
            {
                GetOverlappedResult(m_handlePort, &overlapped, &numberOfBytesRead, true);
            }
        }

        if(numberOfBytesRead > 0)
        {
          int nbreByte = buffer[1];

          for(int i=0; i<nbreByte; i++)
          {
            msg.append(buffer[i+2]);
          }

          handleMsg();

          if(msg.length() > 10)
          {
            msg.clear();
            keyDetection.clear();
          }
        }

        Sleep(10);
    }

  #elif defined(Q_WS_X11)
      int iface_num = 0;
      hid_return ret;

      unsigned short vendor_id  = 0x0925;
      unsigned short product_id = 0x1235;

      HIDInterfaceMatcher matcher = { vendor_id, product_id, NULL, NULL, 0 };

      hid = hid_new_HIDInterface();
      if (hid == 0) {
        close(true);
        emit deviceError();
        return;
      }


      ret = hid_force_open(hid, iface_num, &matcher,3);
      if (ret != HID_RET_SUCCESS) {
        close(true);
        emit deviceError();
        return;
      }

      isOpend = true;

      char packet[8];

      while(isOpend)
      {
        ret = hid_interrupt_read(hid, 1, packet, 8, 10);

        if(ret == 0)
        {

          int nbreByte = packet[1];

          for(int i=0; i<nbreByte; i++)
          {
            msg.append(packet[i+2]);
          }

          if(msg.length() <=20 && ( (uchar)msg[0] == 0x09 || (uchar)msg[0] == 0x0A) )
            handleMsg();
          else
          {
            msg.clear();
            keyDetection.clear();
          }
        }

        QThread::msleep(10);
      }
   #endif

}

void CAccessLinkUsb::close(bool isError)
{
  isOpend = false;
  #if defined(Q_OS_WIN)
    SetEvent(m_readDataEvent);
    
    if(m_handlePort)
    {
        if(!isError)
        {
          terminate();
          wait();
        }
    }


    if(m_handlePort)
    {
        CloseHandle(m_handlePort);
        m_handlePort = NULL;
    }

    if(m_readDataEvent)
    {
        CloseHandle(m_readDataEvent);
        m_readDataEvent = NULL;
    }
  #elif defined(Q_WS_X11)

      if(!hid) return;


      hid_return ret;
      ret = hid_close(hid);

      hid_delete_HIDInterface(&hid);

      ret = hid_cleanup();

      hid = NULL;

      if(!isError)
        wait();

   #endif
}

#if defined(Q_OS_WIN)
bool CAccessLinkUsb::FindTheHID()
{
    PSP_DEVICE_INTERFACE_DETAIL_DATA	detailData;
    SP_DEVICE_INTERFACE_DATA		devInfoData;
    bool					LastDevice = FALSE;
    int					MemberIndex = 0;
    bool					MyDeviceDetected = FALSE;
    LONG					Result;

    std::vector<BYTE> buffer;

    Length = 0;
    detailData = NULL;

    GUID GUID_CLASS_COMPORT =
    { 0x4D1E55B2L, 0xF16F, 0x11CF, {
        0x88, 0xCB, 0x00, 0x11, 0x11, 0x00, 0x00, 0x30 } };

    hDevInfo=SetupDiGetClassDevs
            (&GUID_CLASS_COMPORT,
            NULL,
            NULL,
            DIGCF_PRESENT|DIGCF_INTERFACEDEVICE);

    devInfoData.cbSize = sizeof(devInfoData);

    MemberIndex = 0;
    LastDevice = FALSE;

    do
    {
        MyDeviceDetected=FALSE;

        Result=SetupDiEnumDeviceInterfaces
                (hDevInfo,
                0,
                &GUID_CLASS_COMPORT,
                MemberIndex,
                &devInfoData);

        if (Result != 0)
        {
            Result = SetupDiGetDeviceInterfaceDetail
                    (hDevInfo,
                    &devInfoData,
                    NULL,
                    0,
                    &Length,
                    NULL);

            //Allocate memory for the hDevInfo structure, using the returned Length.
            detailData = (PSP_DEVICE_INTERFACE_DETAIL_DATA)malloc(Length);

            //Set cbSize in the detailData structure.
            detailData -> cbSize = sizeof(SP_DEVICE_INTERFACE_DETAIL_DATA);

            //Call the function again, this time passing it the returned buffer size.
            Result = SetupDiGetDeviceInterfaceDetail
                    (hDevInfo,
                    &devInfoData,
                    detailData,
                    Length,
                    &Required,
                    NULL);

            QString s = detailData->DevicePath;

            MyDeviceDetected = FALSE;

            if(s.contains("Vid_0925",Qt::CaseInsensitive) && s.contains("Pid_1235",Qt::CaseInsensitive))
            {
                m_handlePort = CreateFile
                                     (detailData->DevicePath,
                                 GENERIC_READ|GENERIC_WRITE,
                                     FILE_SHARE_READ|FILE_SHARE_WRITE,
                                     NULL,
                                     OPEN_EXISTING,
                                     FILE_FLAG_OVERLAPPED,
                                     NULL);

                MyDeviceDetected = TRUE;
                LastDevice=TRUE;
            }
            else
            {
                CloseHandle(m_handlePort);
                                m_handlePort = NULL;
            }

            //Free the memory used by the detailData structure (no longer needed).
            free(detailData);
        }  //if (Result != 0)
        else
            //SetupDiEnumDeviceInterfaces returned 0, so there are no more devices to check.
            LastDevice=TRUE;

    //If we haven't found the device yet, and haven't tried every available device,
    //try the next one.
    MemberIndex = MemberIndex + 1;

    } //do
    while ((LastDevice == FALSE) );


    SetupDiDestroyDeviceInfoList(hDevInfo);

    return MyDeviceDetected;
}
#endif
