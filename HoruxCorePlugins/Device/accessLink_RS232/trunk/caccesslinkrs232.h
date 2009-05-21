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
#ifndef CACCESSLINKRS232_H
#define CACCESSLINKRS232_H

#include <QObject>
#include <qextserialport.h>
#include "cdeviceinterface.h"
#include "cxmlfactory.h"

#define OUTPUT_OFF 0x00  //clear the output
#define OUTPUT_1  0x01  //door
#define OUTPUT_2  0x02  //green led
#define OUTPUT_3  0x04  //red led
#define OUTPUT_4  0x08  // orange led

#define INPUT_NUMBER 2 // the access link reader has 2 input

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CAccessLinkRS232 : public QObject, CDeviceInterface
{
  Q_OBJECT
  Q_INTERFACES ( CDeviceInterface )
  Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
  Q_CLASSINFO ( "Copyright", "Letux - 2008" );
  Q_CLASSINFO ( "Version", "0.0.1" );
  Q_CLASSINFO ( "PluginName", "accessLink_ReaderRS232" );
  Q_CLASSINFO ( "PluginType", "device" );
  Q_CLASSINFO ( "PluginDescription", "Handle a Access Link Reader rs232 / TCPIP over virtual Com" );


  enum COM_STATUS {FREE, BUSY};
  enum DOOR_LOCK_MODE {NONE, NO_TIMEOUT, TIMEOUT, TIMEOUT_IN};

public:

    CDeviceInterface *createInstance (QMap<QString, QVariant> config, QObject *parent=0 );

    CAccessLinkRS232( QObject *parent=0);

    void connectChild(CDeviceInterface *device);

    QVariant getParameter(QString paramName);
    
    void setParameter(QString paramName, QVariant value);

    bool open();
    
    void close();
  
    bool isOpened();

    QObject *getMetaObject() { return this;}

    QDomElement getDeviceInfo(QDomDocument xml_info );

public slots:

    void dispatchMessage(QByteArray ba);

    /*!
      Do something on the device (open door, set output, ...)
      Depend of the device
    */
    void deviceAction(QString xml);

signals:
    void deviceEvent(QString xmlEvent);
    void deviceInputChange(int deviceId, int in, bool status);
    void deviceConnection(int deviceId, bool isConnected);

protected:
    void getFirmware();
    void getSerialNumber();
    void getRdStatus();
    void getOutputStatus();
    void setAntenna(bool flag);
    void resetOutput();
    void checkReaderStatus(uchar status);
    void appendMessage(uchar *msg, int len);
    void timerEvent(QTimerEvent *e);
    void handleSn(QString sn);
    void logComm(uchar *ba, bool isReceive, int len);
    void hasMsg(); 

protected:

    static void s_openDoorLock(QObject *, QMap<QString, QVariant>);


protected:
    QextSerialPort *port;
    QString serial_port;
    int oTime1;
    int oTime2;
    int oTime3;
    int oTime4;
    int antipassback;
    int openModeTimeout;
    int openModeInput;

    uchar input;
    uchar output;

    COM_STATUS status;
    QList<QByteArray *> pendingMessage;
    int msgTimeoutTimer;

    DOOR_LOCK_MODE doorLockMode;

    QMap<QString, int> passBackTimer;

    int openModeTimeoutTimer;

    int readTimer;
    int checkConnectionTimer;

    QByteArray currentMessage;
    QByteArray msg;
    QList<QByteArray> keyDetection;

};

#endif
