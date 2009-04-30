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

#include <QDir>
#include <QtGui>
#include <QUiLoader>
#include "site.h"


Site::Site ( QTreeWidget *tree, QWidget *widget, QObject *parent )
		: QObject ( parent )
{
	mainTree = tree;
	mainTree->setColumnHidden ( 1,true );
	mainWidget = widget;

	startIcon.addFile ( ":/images/player_play.png" );
	stopIcon.addFile ( ":/images/player_stop.png" );


	siteIcon.addFile ( ":/images/icon-16-site.png" );
	deviceIcon.addFile ( ":/images/icon-16-hardware.png" );
	deviceIconDisabled.addFile ( ":/images/icon-16-hardware-disable.png" );
	deviceIconError.addFile ( ":/images/error.png" );

	connect ( mainTree,
	          SIGNAL ( currentItemChanged ( QTreeWidgetItem *, QTreeWidgetItem * ) ),
	          SLOT ( currentItemChanged ( QTreeWidgetItem *, QTreeWidgetItem * ) ) );

	connect ( mainTree,
	          SIGNAL ( itemPressed ( QTreeWidgetItem * , int ) ),
	          SLOT ( itemPressed ( QTreeWidgetItem * , int ) ) );


	started = false;

	stopAct = new QAction ( tr ( "Power off" ), this );
	stopAct->setStatusTip ( tr ( "Stop the communication" ) );
	stopAct->setIcon ( stopIcon );
	connect ( stopAct, SIGNAL ( triggered() ), this, SLOT ( stopItem() ) );

	startAct = new QAction ( tr ( "Power on" ), this );
	startAct->setStatusTip ( tr ( "Start the communication" ) );
	startAct->setIcon ( startIcon );
	connect ( startAct, SIGNAL ( triggered() ), this, SLOT ( startItem() ) );

}


Site::~Site()
{

	QHashIterator<QString, QObject *> i ( devicePlugin );
	while ( i.hasNext() )
	{
		i.next();
		QObject *obj = i.value();
		if ( obj )
		{
			( qobject_cast<DeviceInterface *> ( obj ) )->setEnabled ( false );
			obj->deleteLater();

			QWidget *wid = ( qobject_cast<DeviceInterface *> ( obj ) )->getWidget();
			if ( wid )
			{
				wid->hide();
			}

		}
	}

	domElementForItem.clear();
	devicePlugin.clear();

	QPluginLoader loader ( this );
	loader.unload();

}


QString Site::getName()
{
	return name;
}


void Site::setName ( const QString name )
{
	this->name = name;
}


void Site::start()
{
	started = true;
	QHashIterator<QString, QObject *> i ( devicePlugin );
	while ( i.hasNext() )
	{
		i.next();
		QObject *obj = i.value();
		if ( obj )
		{
			( qobject_cast<DeviceInterface *> ( obj ) )->setEnabled ( true );
			QString id = i.key();

			QHashIterator<QTreeWidgetItem *, QDomElement> i2 ( domElementForItem );
			while ( i2.hasNext() )
			{
				i2.next();
				QDomElement element = i2.value();
				if ( element.attribute ( "id" ) == id )
				{
					i2.key()->setIcon ( 0,deviceIcon );
				}
			}
		}
	}
}


void Site::stop()
{
	started = false;

	QHashIterator<QString, QObject *> i ( devicePlugin );
	while ( i.hasNext() )
	{
		i.next();
		QObject *obj = i.value();
		if ( obj )
		{
			( qobject_cast<DeviceInterface *> ( obj ) )->setEnabled ( false );
			QString id = i.key();

			QHashIterator<QTreeWidgetItem *, QDomElement> i2 ( domElementForItem );
			while ( i2.hasNext() )
			{
				i2.next();
				QDomElement element = i2.value();
				if ( element.attribute ( "id" ) == id )
				{
					i2.key()->setIcon ( 0,deviceIconDisabled );
				}
			}

		}
	}

}

bool Site::loadSite ( QIODevice *device )
{
	QString errorStr;
	int errorLine;
	int errorColumn;

	if ( !domDocument.setContent ( device, true, &errorStr, &errorLine,
	                               &errorColumn ) )
	{
		QMessageBox::information ( NULL, tr ( "Horux Simul" ),
		                           tr ( "Parse error at line %1, column %2:\n%3" )
		                           .arg ( errorLine )
		                           .arg ( errorColumn )
		                           .arg ( errorStr ) );
		return false;
	}

	QDomElement root = domDocument.documentElement();

	if ( root.tagName() != "horuxSimul" )
	{
		QMessageBox::information ( NULL, tr ( "Horux Simul" ),
		                           tr ( "The file is not an Horux Simul file." ) );
		return false;
	}
	else if ( root.hasAttribute ( "version" )
	          && root.attribute ( "version" ) != "1.0" )
	{
		QMessageBox::information ( NULL, tr ( "Horux Simul" ),
		                           tr ( "The file is not an Horux Simul version 1.0 "
		                                "file." ) );
		return false;
	}

	QDomElement child = root.firstChildElement ( "site" );

	parseSiteElement ( child );

	return true;
}


bool Site::parseSiteElement ( const QDomElement &element )
{
	QTreeWidgetItem *item = createItem ( element, NULL );
	rootItem = item;

	name = element.attribute ( "name" );

	if ( name.isEmpty() )
		name = QObject::tr ( "Unknown" );

	item->setText ( 0, name );
	item->setText ( 1, "0" );
	item->setIcon ( 0, siteIcon );

	QDomElement child = element.firstChildElement ( "devices" );

	if ( !child.isNull() )
	{
		child = child.firstChildElement ( "device" );
		if ( !parseDeviceElement ( child,item ) ) return false;
	}

	mainTree->expandItem ( item );

	return true;
}


bool Site::parseDeviceElement ( QDomElement &element, QTreeWidgetItem *parentItem, QString parentId )
{
	QDir pluginsDir = QDir ( qApp->applicationDirPath() );
	pluginsDir.cd ( "plugins" );

	while ( !element.isNull() )
	{

		if ( element.hasAttribute ( "id" ) && element.hasAttribute ( "plugin" ) )
		{
			QString id = element.attribute ( "id" );
			QString plugin = element.attribute ( "plugin" );
			QString widget = element.attribute ( "widget" );
			QString name =  element.attribute ( "name" );

#if defined(Q_OS_WIN)
			QPluginLoader loader ( pluginsDir.absoluteFilePath ( plugin + ".dll" ) );
#elif defined(Q_WS_X11)
			QPluginLoader loader ( pluginsDir.absoluteFilePath ( "lib" + plugin + ".so" ) );
#endif

			QObject *plg = loader.instance();

			if ( plg )
			{

				QObject *obj = ( qobject_cast<DeviceInterface *> ( plg ) )->createInstance ( this );

				if ( obj )
				{
					( qobject_cast<DeviceInterface *> ( obj ) )->setId ( id.toInt() );
					( qobject_cast<DeviceInterface *> ( obj ) )->setName ( name );
					( qobject_cast<DeviceInterface *> ( obj ) )->init();
					( qobject_cast<DeviceInterface *> ( obj ) )->setWidget ( mainWidget );
					( qobject_cast<DeviceInterface *> ( obj ) )->getWidget()->hide();

					/*QUiLoader loader;
					loader.addPluginPath ( qApp->applicationDirPath() + "/plugins" );

					QStringList availableWidgets = loader.availableWidgets();

					if ( availableWidgets.contains ( widget ) )
					{
						QWidget *w = loader.createWidget ( widget, mainWidget );
						w->hide();
						if ( w )
							( qobject_cast<DeviceInterface *> ( obj ) )->setWidget ( w );
					}*/

					bool initRes = ( qobject_cast<DeviceInterface *> ( obj ) )->initParam ( element );

					devicePlugin[id] = obj;

					connect ( obj, SIGNAL ( receiveMessage ( unsigned char *, int ) ), SLOT ( logReceive ( unsigned char *, int ) ) );

					connect ( obj, SIGNAL ( sendMessage ( unsigned char *, int ) ), SLOT ( logSend ( unsigned char *, int ) ) );

					connect ( obj, SIGNAL ( deviceChanged() ), SIGNAL ( siteChanged() ) );

					if ( parentId != "" )
						( qobject_cast<DeviceInterface *> ( devicePlugin[parentId] ) )->addSubDevice ( obj );

					QTreeWidgetItem *childItem = createItem ( element, parentItem );

					childItem->setText ( 0, name );
					childItem->setText ( 1, id );

					if ( initRes )
						childItem->setIcon ( 0, deviceIconDisabled );
					else
						childItem->setIcon ( 0, deviceIconError );

					QDomElement child = element.firstChildElement ( "device" );
					if ( !parseDeviceElement ( child, childItem, id ) ) return false;

					mainTree->expandItem ( childItem );

				}
			}
			else
				qDebug ( loader.errorString ().toLatin1() );


		}
		else
		{
			QMessageBox::information ( NULL, tr ( "Horux Simul" ),
			                           tr ( "The file is not an Horux Simul version 1.0 "
			                                "file." ) );
			return false;
		}

		element = element.nextSiblingElement();
	}

	return true;
}

QTreeWidgetItem *Site::createItem ( const QDomElement &element,
                                    QTreeWidgetItem *parentItem )
{
	QTreeWidgetItem *item;
	if ( parentItem )
	{
		item = new QTreeWidgetItem ( parentItem );
	}
	else
	{
		item = new QTreeWidgetItem ( mainTree );
	}
	domElementForItem.insert ( item, element );
	return item;
}


void Site::logReceive ( unsigned char *msg, int len )
{
	QObject *obj = sender();

        if( !( qobject_cast<DeviceInterface *> ( obj ) )->getIsLog() ) return;

        QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

	QString s1 = "<b>"+ date + " " + ( qobject_cast<DeviceInterface *> ( obj ) )->getName() + "</b>:<span style=\"color:blue\">", s2;

	for ( int i=0; i<len; i++ )
	{
		s1+= s2.sprintf ( "%02X ", ( unsigned char ) msg[i] );
	}
	s1+="</span><br>";

	emit message ( s1 );
}

void Site::logSend ( unsigned char *msg, int len )
{
	QObject *obj = sender();

        if( !( qobject_cast<DeviceInterface *> ( obj ) )->getIsLog() ) return;


        QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

	QString s1 = "<b>"+ date + " " + ( qobject_cast<DeviceInterface *> ( obj ) )->getName() + "</b>:<span style=\"color:green\">", s2;

	for ( int i=0; i<len; i++ )
	{
		s1+= s2.sprintf ( "%02X ", ( unsigned char ) msg[i] );
	}
	s1+="</span><br>";

	emit message ( s1 );
}


void Site::currentItemChanged ( QTreeWidgetItem * current, QTreeWidgetItem *last )
{
	if ( !current ) return;

	if ( last )
	{
		QDomElement element = domElementForItem[last];
		if ( !element.isNull() )
		{
			QString id = element.attribute ( "id" );
			QObject *obj = devicePlugin[id];
			if ( obj )
			{
				QWidget *wid = ( qobject_cast<DeviceInterface *> ( obj ) )->getWidget();
				if ( wid )
				{
					wid->hide();
				}
			}
		}

	}

	QDomElement element = domElementForItem[current];

	if ( !element.isNull() )
	{
		QString id = element.attribute ( "id" );

		QObject *obj = devicePlugin[id];
		if ( obj )
		{
			QWidget *wid = ( qobject_cast<DeviceInterface *> ( obj ) )->getWidget();
			if ( wid )
			{
				wid->show();
			}
		}
	}
}

bool Site::isStarted()
{
	return started;
}


void Site::itemPressed ( QTreeWidgetItem * item, int )
{
	Qt::MouseButtons buttons = QApplication::mouseButtons();

	if ( buttons == Qt::RightButton )
	{

		QDomElement element = domElementForItem[item];
		QString id = element.attribute ( "id" );
		QObject *obj = devicePlugin[id];

		if ( obj )
		{
			bool isEnabled = ( qobject_cast<DeviceInterface *> ( obj ) )->getParameter("enabled").toBool();
			if ( isEnabled )
			{
				startAct->setEnabled ( false );
				stopAct->setEnabled ( true );
			}
			else
			{
				startAct->setEnabled ( true );
				stopAct->setEnabled ( false );
			}

			QMenu menu ( mainTree );
			menu.addAction ( startAct );
			menu.addAction ( stopAct );
			menu.exec ( QCursor::pos() );
		}
	}
}

void Site::stopItem()
{

	QTreeWidgetItem * item = mainTree->currentItem ();

	if ( !item ) return;

	QDomElement element = domElementForItem[item];

	QString id = element.attribute ( "id" );

	QObject *obj = devicePlugin[id];

	if ( obj )
	{
		( qobject_cast<DeviceInterface *> ( obj ) )->setEnabled ( false );
		item->setIcon ( 0,deviceIconDisabled );
	}

}

void Site::startItem()
{
	QTreeWidgetItem * item = mainTree->currentItem ();

	if ( !item ) return;

	QDomElement element = domElementForItem[item];

	QString id = element.attribute ( "id" );

	QObject *obj = devicePlugin[id];

	if ( obj )
	{
		( qobject_cast<DeviceInterface *> ( obj ) )->setEnabled ( true );
		item->setIcon ( 0,deviceIcon );
	}
}

bool Site::save ( QString fileName )
{
	QDomDocument doc;

	QDomElement root = doc.createElement ( "horuxSimul" );
	root.setAttribute ( "version", "1.0" );

	doc.appendChild ( root );

	QDomElement siteNode = doc.createElement ( "site" );
	siteNode.setAttribute ( "name", name );

	root.appendChild ( siteNode );

	QDomElement devices = doc.createElement ( "devices" );
	siteNode.appendChild ( devices );


	QHashIterator<QTreeWidgetItem *, QDomElement> i ( domElementForItem );
	while ( i.hasNext() )
	{
		i.next();
		QTreeWidgetItem *t = i.key();
		if ( t->parent() == rootItem )
		{
			QObject *obj = devicePlugin[i.value().attribute ( "id" ) ];

			QDomElement element = ( qobject_cast<DeviceInterface *> ( obj ) )->getXml();
			devices.appendChild ( element );
		}
	}

	QFile file ( fileName );

	if ( !file.open ( QIODevice::WriteOnly ) )
		return false;

	QTextStream out ( &file );

	doc.save ( out, 4 );

	return true;
}

void Site::deleteItem()
{
	QTreeWidgetItem *current = mainTree->currentItem();

	if ( current == rootItem )
	{
		QMessageBox::warning ( NULL, tr ( "Horux Simul" ),
		                       tr ( "Cannot delete the site" ) );
		return;
	}

	QMessageBox::StandardButton ret;
	ret = QMessageBox::warning ( NULL, tr ( "Horux Simul" ),
	                             tr ( "Are you sure?" ),
	                             QMessageBox::Yes | QMessageBox::Cancel );
	if ( ret == QMessageBox::Yes )
	{
		QDomElement element = domElementForItem[current];

		QObject *obj = devicePlugin[element.attribute ( "id" ) ];
		( qobject_cast<DeviceInterface *> ( obj ) )->getWidget()->hide();
		obj->deleteLater();

		devicePlugin.remove ( element.attribute ( "id" ) );
		domElementForItem.remove ( current );

		delete current;

		emit siteChanged();
	}
}

void Site::addItem ( QString name, QString plugin, QString parent )
{
	QDir pluginsDir = QDir ( qApp->applicationDirPath() );
	pluginsDir.cd ( "plugins" );


	int id = 1;

	while ( devicePlugin.contains ( QString::number ( id ) ) )
	{
		id++;
	}


	QString idStr = QString::number ( id );

	QDomDocument doc;

	QDomElement device = doc.createElement ( "device" );

	device.setAttribute ( "id", id );
	device.setAttribute ( "plugin",plugin );
	device.setAttribute ( "name",name );


	QTreeWidgetItem *parentItem = NULL;

	QHashIterator<QTreeWidgetItem *, QDomElement> i ( domElementForItem );
	while ( i.hasNext() )
	{
		i.next();
		QDomElement element = i.value();
		if ( element.attribute ( "id" ) == parent )
		{
			parentItem = i.key();
			break;
		}
	}

	if ( parentItem == 0 )
		parentItem = rootItem;

#if defined(Q_OS_WIN)
	QPluginLoader loader ( pluginsDir.absoluteFilePath ( plugin + ".dll" ) );
#elif defined(Q_WS_X11)
	QPluginLoader loader ( pluginsDir.absoluteFilePath ( "lib" + plugin + ".so" ) );
#endif

	QObject *plg = loader.instance();

	if ( plg )
	{

		QObject *obj = ( qobject_cast<DeviceInterface *> ( plg ) )->createInstance ( this );

		if ( obj )
		{
			( qobject_cast<DeviceInterface *> ( obj ) )->setId ( id );
			( qobject_cast<DeviceInterface *> ( obj ) )->setName ( name );
			( qobject_cast<DeviceInterface *> ( obj ) )->init();
			( qobject_cast<DeviceInterface *> ( obj ) )->setWidget ( mainWidget );


			bool initRes = ( qobject_cast<DeviceInterface *> ( obj ) )->initParam ( device );

			devicePlugin[QString::number ( id ) ] = obj;

			connect ( obj, SIGNAL ( receiveMessage ( unsigned char *, int ) ), SLOT ( logReceive ( unsigned char *, int ) ) );

			connect ( obj, SIGNAL ( sendMessage ( unsigned char *, int ) ), SLOT ( logSend ( unsigned char *, int ) ) );

			connect ( obj, SIGNAL ( deviceChanged() ), SIGNAL ( siteChanged() ) );

			if ( parent != "0" )
				( qobject_cast<DeviceInterface *> ( devicePlugin[parent] ) )->addSubDevice ( obj );

			QTreeWidgetItem *childItem = createItem ( device, parentItem );

			childItem->setText ( 0, name );
			childItem->setText ( 1, QString::number ( id ) );

			if ( initRes )
				childItem->setIcon ( 0, deviceIconDisabled );
			else
				childItem->setIcon ( 0, deviceIconError );

			mainTree->expandItem ( childItem );

			emit siteChanged();

		}
	}
	else
		qDebug ( loader.errorString ().toLatin1() );

}
