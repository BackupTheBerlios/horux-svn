/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
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

#include <QtGui>

#include "accessLinkReaderRS485Widget.h"

AccessLinkReaderRS485Widget::AccessLinkReaderRS485Widget ( QWidget *parent )
		: QWidget ( parent )
{
	setupUi ( this );
  
        dlg = new LoadTestingDlg(this);
        dlg->hide();

        connect(dlg, SIGNAL(sendTag(unsigned long)), this, SIGNAL(sendTag(unsigned long)));

	output1->setColor ( Qt::black );
	output2->setColor ( Qt::green );
	output3->setColor ( Qt::red );
	output4->setColor ( Qt::yellow );
	antenna->setColor ( Qt::black );
	modulation->setColor ( Qt::black );
}

void AccessLinkReaderRS485Widget::on_info_clicked()
{
	QMessageBox mbAbout ( this );
	mbAbout.setWindowTitle ( tr ( "Device info" ) );
	mbAbout.setText ( tr ( "This device is an RFID <b>Access Link</b> reader RS485 technology.<br/>This kind of reader must be connected to a <b>Access Link</b> Interface" ) );

	QPixmap icon ( ":images/accesslink.jpg" );
	mbAbout.setIconPixmap ( icon );

	mbAbout.exec();
}

void AccessLinkReaderRS485Widget::on_loadTest_clicked()
{
  dlg->show();
}
