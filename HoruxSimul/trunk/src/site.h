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
#ifndef SITE_H
#define SITE_H

#include <QObject>
#include <QDomDocument>
#include <QIODevice>
#include <QMessageBox>
#include <QTreeWidget>
#include <QHash>
#include <QIcon>

#include "deviceInterface.h"

//!  This class manage a site define by an xml file definition
/*!
  @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
  @version 0.1
  @date    2008

  history:
  28.02.2008  First implementation
*/

class Site : public QObject
{
		Q_OBJECT
	public:
		//! constructor
		/*!
		  Constructor of the site
		  \param tree QTreeWidget of the main window
		  \param parent QObject parent
		*/
		Site ( QTreeWidget *tree, QWidget *widget, QObject *parent = 0 );

		//! destructor.
		/*!
		  Destructor of the site
		*/
		~Site();

		//! Get the name of the current site
		/*!
		  \return Return the site name
		*/
		QString getName();

		//! Set the name of the current site
		/*!
		  \param name the site name
		*/
		void setName ( const QString name );

		//! Stop the communication
		/*!
		  disable all device and stop the communication with the horux server
		*/
		void stop();

		//! Start the communication
		/*!
		  Enable all device to be ready and start the communication with the horux server
		*/
		void start();

		//! Load an site xml file definition
		bool loadSite ( QIODevice *device );


		//! Get true if the communicaiton is stared else false
		/*!
		  \return Return the communication status
		*/
		bool isStarted();

		//! Save the current site value in the XML file description
		/*!
		  \return Return the save result
		*/
		bool save ( QString fileName );

		void deleteItem();

		void addItem ( QString name, QString plugin, QString parent );

	private slots:
		//! This slot is called when a message was received on a device
		/*!
		  \param msg Message received
		  \param len length of the message
		*/
		void logReceive ( unsigned char * msg, int len );

		//! This slot is called when a message was sended by a device
		/*!
		  \param msg Message received
		  \param len length of the message
		*/
		void logSend ( unsigned char * msg, int len );

		void currentItemChanged ( QTreeWidgetItem * current, QTreeWidgetItem * previous );

		void itemPressed ( QTreeWidgetItem * item, int column );

		void stopItem();

		void startItem();

	signals:
		void message ( const QString &msg );
		void siteChanged();

	private:
		//! site name
		QString name;

		//! Tree widget created according to the xml file definition
		QTreeWidget *mainTree;

		QWidget *mainWidget;
		//! XML document
		QDomDocument domDocument;

		QTreeWidgetItem *rootItem;

		//! hash table according to the tree widget and the XML file
		QHash<QTreeWidgetItem *, QDomElement> domElementForItem;

		//! hash  table of the device
		QHash<QString ,QObject *> devicePlugin;


		//! this icon represent the root item of the tree widget
		QIcon startIcon;

		//! this icon represent the root item of the tree widget
		QIcon stopIcon;

		//! this icon represent the root item of the tree widget
		QIcon siteIcon;

		//! this icon represent the device item of the tree widget
		QIcon deviceIcon;

		//! this icon represent the disable device item of the tree widget
		QIcon deviceIconDisabled;

		//! this icon is displayed when device parameters are missing
		QIcon deviceIconError;

		//! inform if the communicationis started
		bool started;

		//! This action appear in the menu context and allows to disable a device or a site
		QAction *stopAct;

		//! This action appear in the menu context and allows to enable a device or a site
		QAction *startAct;

		//! This action appear in the menu context and allows to save a device or a site
		QAction *saveAct;


	private:
		//! parse the xml file
		bool parseSiteElement ( const QDomElement &element );

		//! parse the xml device element
		bool parseDeviceElement ( QDomElement &element, QTreeWidgetItem *parentItem, QString parentId="" );

		//! create an item in the tree widget
		QTreeWidgetItem *createItem ( const QDomElement &element,
		                              QTreeWidgetItem *parentItem = 0 );

};

#endif
