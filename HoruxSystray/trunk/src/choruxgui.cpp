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
#include "choruxgui.h"
#include <QProcess>
#include <qextserialport.h>

#if defined(Q_OS_WIN)
    #include <windows.h>
    #include <winable.h>
    #include <winuser.h>
#elif defined(Q_WS_X11)
#endif


CHoruxGui::CHoruxGui(QWidget *parent)
 : QWidget(parent)
{
    setupUi(this);

    connect(techComboBox, SIGNAL(currentIndexChanged ( int )), this, SLOT(currentIndexChanged ( int )));


    trayIcon = new QSystemTrayIcon(this);
    trayIcon->setIcon(QIcon(":/images/logo.png"));

    trayIcon->show();


    quitAction = new QAction(tr("&Quit"), this);
    connect(quitAction, SIGNAL(triggered()), qApp, SLOT(quit()));

    settingsAction = new QAction(tr("&Settings..."), this);
    connect(settingsAction, SIGNAL(triggered()), this, SLOT(show()));

    /*testAction = new QAction(tr("Test"), this);
    connect(testAction, SIGNAL(triggered()), this, SLOT(test()));
    */

    trayIconMenu = new QMenu(this);
    //trayIconMenu->addAction(testAction);
    trayIconMenu->addAction(settingsAction);
    trayIconMenu->addSeparator();
    trayIconMenu->addAction(quitAction);

    trayIcon->setContextMenu(trayIconMenu);

    setWindowTitle(tr("Horux Gui Key Detection - Version 1.0.1"));

    process = NULL;

    QSettings settings ( "Horux", "HoruxGuiSys" );
    customPort->setText(settings.value("port", "").toString());
    techComboBox->setCurrentIndex(settings.value("tech", 0).toInt());
    fid->setText( settings.value("fid", "").toString());
    isBeep->setChecked( settings.value("beep", true).toBool());

    al_usb_reader = NULL;
    al_serial_reader = NULL;
    gat5250_serial_reader = NULL;

    openCom();

}


CHoruxGui::~CHoruxGui()
{
  if(gat5250_serial_reader)
      gat5250_serial_reader->close();

  if(al_serial_reader)
    al_serial_reader->close();

  if(al_usb_reader)
    al_usb_reader->close();
}

void CHoruxGui::timerEvent(QTimerEvent *e)
{
  QHashIterator<QString, int> i(antipassback);

  while (i.hasNext()) 
  {
    i.next();
    if(i.value() == e->timerId())
    {
      antipassback.remove(i.key());
      break;
    }
  }
}

void CHoruxGui::openCom()
{
  QSettings settings ( "Horux", "HoruxGuiSys" );

  QString portStr = settings.value("port", "").toString();
  int techno = settings.value("tech", 0).toInt();
  QString fid = settings.value("fid", "9999").toString();
  bool beep = settings.value("beep", true).toBool();

  switch( techno )
  {
      case 0: //GAT Writer 5250 B
          gat5250_serial_reader = new CGAT5250B(this);
          gat5250_serial_reader->isBeep(beep);

          connect(gat5250_serial_reader, SIGNAL(deviceError()), this, SLOT(deviceError()));
          connect(gat5250_serial_reader, SIGNAL(readError()), this, SLOT(readError()));
          connect(gat5250_serial_reader, SIGNAL(keyDetected(QByteArray)), this, SLOT(keyDetected(QByteArray)));
          gat5250_serial_reader->open();
          gat5250_serial_reader->setFID(fid);


          break;
      case 1: // Acces Link USB
          al_usb_reader = new CAccessLinkUsb(this);

          connect(al_usb_reader, SIGNAL(deviceError()), this, SLOT(deviceError()));
          connect(al_usb_reader, SIGNAL(readError()), this, SLOT(readError()));
          connect(al_usb_reader, SIGNAL(keyDetected(QByteArray)), this, SLOT(keyDetected(QByteArray)));

          al_usb_reader->start();
          break;
      case 2: // Acces Link Serial
          al_serial_reader = new CAccessLinkSerial(this);

          connect(al_serial_reader, SIGNAL(deviceError()), this, SLOT(deviceError()));
          connect(al_serial_reader, SIGNAL(readError()), this, SLOT(readError()));
          connect(al_serial_reader, SIGNAL(keyDetected(QByteArray)), this, SLOT(keyDetected(QByteArray)));

          al_serial_reader->start();
          break;
  }
}

void CHoruxGui::on_apply_clicked()
{
  QSettings settings ( "Horux", "HoruxGuiSys" );
  settings.setValue ( "port", customPort->text() );
  settings.setValue ( "tech", techComboBox->currentIndex() );
  settings.setValue ( "fid", fid->text() );
  settings.setValue ( "beep", isBeep->isChecked() );

  if(gat5250_serial_reader)
  {
      gat5250_serial_reader->close();
      delete gat5250_serial_reader;
      gat5250_serial_reader = NULL;
  }

  if(al_serial_reader)
  {
    al_serial_reader->close();
    delete al_serial_reader;
    al_serial_reader = NULL;
  }

  if(al_usb_reader)
  {
    al_usb_reader->close();
    delete al_usb_reader;
    al_usb_reader = NULL;
  }
  openCom();
}

void CHoruxGui::on_save_clicked()
{
  QSettings settings ( "Horux", "HoruxGuiSys" );
  settings.setValue ( "port", customPort->text() );
  settings.setValue ( "tech", techComboBox->currentText() );
  settings.setValue ( "fid", fid->text() );
  settings.setValue ( "beep", isBeep->isChecked() );

  if(gat5250_serial_reader)
  {
      gat5250_serial_reader->close();
      delete gat5250_serial_reader;
      gat5250_serial_reader = NULL;
  }

  if(al_serial_reader)
  {
    al_serial_reader->close();
    delete al_serial_reader;
    al_serial_reader = NULL;
  }

  if(al_usb_reader)
  {
    al_usb_reader->close();
    delete al_usb_reader;
    al_usb_reader = NULL;
  }

  openCom();
  
  hide();
}


 void CHoruxGui::closeEvent(QCloseEvent *event)
 {
     if (trayIcon->isVisible()) {
         hide();
         event->ignore();
     }
 }

void CHoruxGui::test()
{
	sendKey("0000000000000001");
}

void CHoruxGui::sendKey(QString key)
{

	#if defined(Q_OS_WIN)
		//Ctrl down
		keybd_event(0x11 ,0,0,0);
		//Alt
		keybd_event(0x12 ,0,0,0);
		//3 down
		keybd_event(0x33 ,0,0,0);

        keybd_event(0x11,0,KEYEVENTF_KEYUP,0);
        keybd_event(0x12,0,KEYEVENTF_KEYUP,0);
        keybd_event(0x33,0,KEYEVENTF_KEYUP,0);

		//Ctrl down
		keybd_event(0x11 ,0,0,0);
		//Alt
		keybd_event(0x12 ,0,0,0);
		//3 down
		keybd_event(0x33 ,0,0,0);

        keybd_event(0x11,0,KEYEVENTF_KEYUP,0);
        keybd_event(0x12,0,KEYEVENTF_KEYUP,0);
        keybd_event(0x33,0,KEYEVENTF_KEYUP,0);

		for(int i=0; i<key.length(); i++)
                {
                                keybd_event(key.at(i).toLatin1() ,0,0,0);
				keybd_event(key.at(i).toLatin1(),0,KEYEVENTF_KEYUP,0);
		}

		//Ctrl down
		keybd_event(0x11 ,0,0,0);
		//Alt
		keybd_event(0x12 ,0,0,0);
		//3 down
		keybd_event(0x33 ,0,0,0);

        keybd_event(0x11,0,KEYEVENTF_KEYUP,0);
        keybd_event(0x12,0,KEYEVENTF_KEYUP,0);
        keybd_event(0x33,0,KEYEVENTF_KEYUP,0);

		//Ctrl down
		keybd_event(0x11 ,0,0,0);
		//Alt
		keybd_event(0x12 ,0,0,0);
		//3 down
		keybd_event(0x33 ,0,0,0);

        keybd_event(0x11,0,KEYEVENTF_KEYUP,0);
        keybd_event(0x12,0,KEYEVENTF_KEYUP,0);
        keybd_event(0x33,0,KEYEVENTF_KEYUP,0);

	#elif defined(Q_WS_X11)
		QString program = "xvkbd";
		QStringList arguments;
	
		//! add extra char to be detected ba javascript
		QString keyToSend = key;
	
		arguments << "-text" << ("##" + keyToSend + "##");
	
		if(process)
			delete process;
	
		process = new QProcess(this);
		process->start(program, arguments);
	#endif
}

void CHoruxGui::currentIndexChanged ( int index )
{
    if(index == 0)
    {
        fid->setEnabled(true);
    }
    else
    {
        fid->setEnabled(false);
    }

    if(index == 2 || index == 0)
    {
        customPort->setEnabled(true);
    }
    else
    {
        customPort->setEnabled(false);
    }
}

void CHoruxGui::deviceError()
{
  QCoreApplication::processEvents();
  trayIcon->showMessage(tr("Device error"), tr("The device cannot be opened"), QSystemTrayIcon::Information, 5000);
}

void CHoruxGui::readError()
{
  QCoreApplication::processEvents();
  trayIcon->showMessage(tr("Device error"), tr("Cannot read data from the device"));
}

void CHoruxGui::keyDetected(QByteArray key)
{
  QSettings settings ( "Horux", "HoruxGuiSys" );

  int techno = settings.value("tech", 0).toInt();

  QString s, s1;

  if(techno == 1 || techno == 2)
  {
      for(int i=0; i<key.length(); i++)
        s += s1.sprintf("%02X", (uchar)key[i]);

      s = s.rightJustified(16,'0');
  }
  else
      s = key;

  if(!antipassback.contains(s))
  {
    antipassback[s] = startTimer(500);
    s.toUpper();
    sendKey(s);
  }
}
