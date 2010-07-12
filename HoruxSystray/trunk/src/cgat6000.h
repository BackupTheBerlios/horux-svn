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

#ifndef CGAT6000_H
#define CGAT6000_H

#include "cdevice.h"

#if defined(Q_OS_WIN)
    #include <QAxObject>
#elif defined(Q_WS_X11)
    #include <qextserialport.h>
    #include <QTimer>
#endif

class CGAT6000 : public CDevice
{
    Q_OBJECT
public:
    CGAT6000(QObject * parent = 0);

    ~CGAT6000();

     void open();
     void run();
     void close(bool isError=false);
     void setFID(QString _fid);
     void isBeep(bool flag);

protected:
    virtual void handleMsg();
    virtual void handleKey();


/*signals:
    void keyDetected(QByteArray key);
    void deviceError();
    void readError();*/

protected slots:
    void readyRead();
    void setGreenLED();
    void setRedLED();
    void setBeep();
    void smIdle();
    void readUniqueSerialNumber();
    void readCardNumberNumber();


private:
    #if defined(Q_OS_WIN)
        QAxObject *gat;
    #elif defined(Q_WS_X11)
        QextSerialPort *port;
        QTimer *readPort;
        QList<QByteArray>msgList;
    #endif

    QString key;
    bool stop;
    QString fid;
    bool isStarted;
    bool beep;
};

#endif // CGAT5250B_H
