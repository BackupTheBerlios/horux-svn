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
#include <QSettings>

#include "horuxsimul.h"
#include "deviceInterface.h"

#include "ui_horuxSimulAbout.h"
#include "ui_adddevice.h"

horuxSimul::horuxSimul()
{

	setupUi ( this );

	setAttribute ( Qt::WA_DeleteOnClose );

	site = NULL;
	currentFileName = "";
	treeWidget->setColumnHidden ( 1,true );

	horuxMenuRecentFile();
	updateRecentFileAction();

	statusBar()->showMessage ( tr ( "Ready" ) );
}

horuxSimul::~horuxSimul()
{

}

void horuxSimul::horuxMenuRecentFile()
{
	menuHorux->addSeparator();

	for ( int i=0; i<MaxRecentFiles; i++ )
	{
		recentFileActions[i] = new QAction ( this );
		recentFileActions[i]->setVisible ( false );
		connect ( recentFileActions[i], SIGNAL ( triggered() ), this, SLOT ( openRecentFile() ) );
		menuHorux->addAction ( recentFileActions[i] );
	}

}

void horuxSimul::setCurrentFile ( const QString &fileName )
{
	QString stripName = QFileInfo ( fileName ).fileName();

	if ( fileName.isEmpty() )
		setWindowTitle ( tr ( "Horux Simul" ) );
	else
		setWindowTitle ( tr ( "Horux Simul - %1" ).arg ( stripName ) );

	QSettings settings ( "Horux", "Simul" );
	QStringList files = settings.value ( "recentFileList" ).toStringList();
	files.removeAll ( fileName );
	files.prepend ( fileName );
	while ( files.size() > MaxRecentFiles )
		files.removeLast();

	settings.setValue ( "recentFileList", files );

	updateRecentFileAction();
}

void horuxSimul::updateRecentFileAction()
{
	QSettings settings ( "Horux", "Simul" );

	QStringList files = settings.value ( "recentFileList" ).toStringList();

	int numRecentFiles = qMin ( files.size(), ( int ) MaxRecentFiles );

	for ( int i=0; i<numRecentFiles; i++ )
	{
		QString stripName = QFileInfo ( files[i] ).fileName();
		QString text = tr ( "%1 %2" ).arg ( i+1 ).arg ( stripName );
		recentFileActions[i]->setText ( text );
		recentFileActions[i]->setData ( files[i] );
		recentFileActions[i]->setVisible ( true );
	}
}

void horuxSimul::on_actionOpen_site_triggered()
{
	QString fileName =
	    QFileDialog::getOpenFileName ( this, tr ( "Open Horux Simul File" ),
	                                   QDir::currentPath(),
	                                   tr ( "Horux Simul Files (*.xml)" ) );
	if ( fileName.isEmpty() )
		return;

	QFile file ( fileName );
	if ( !file.open ( QFile::ReadOnly | QFile::Text ) )
	{
		QMessageBox::warning ( this, tr ( "Horux Simul" ),
		                       tr ( "Cannot read file %1:\n%2." )
		                       .arg ( fileName )
		                       .arg ( file.errorString() ) );
		return;
	}

	site = new Site ( treeWidget, widget, this );
	connect ( site, SIGNAL ( message ( const QString & ) ), logText, SLOT ( insertHtml ( const QString & ) ) );
	connect ( site, SIGNAL ( siteChanged() ), this, SLOT ( siteChanged() ) );

	if ( site->loadSite ( &file ) )
	{
		currentFileName = fileName;

		statusBar()->showMessage ( tr ( "Site loaded" ), 2000 );

		actionOpen_site->setEnabled ( false );
		actionNew_site->setEnabled ( false );
		actionImport_site->setEnabled ( false );

		actionClose_site->setEnabled ( true );
		actionSave_site->setEnabled ( false );
		actionSave_as->setEnabled ( true );


		actionAdd->setEnabled ( true );
		actionDelete->setEnabled ( true );
		actionModify->setEnabled ( true );

		actionStart->setEnabled ( true );

		for ( int i=0; i<MaxRecentFiles; i++ )
		{
			recentFileActions[i]->setEnabled ( false );
		}


		setCurrentFile ( fileName );

	}
	else
	{
		delete site;
		site = NULL;
	}

	file.close();
}


void horuxSimul::on_actionStart_triggered()
{
	if ( site )
	{
                //! start the site communication
		site->start();

                //! update the actions
		actionStart->setEnabled ( false );
		actionStop->setEnabled ( true );

		actionOpen_site->setEnabled ( false );
		actionNew_site->setEnabled ( false );
		actionImport_site->setEnabled ( false );

		actionClose_site->setEnabled ( false );
		//actionSave_site->setEnabled ( false );
		actionSave_as->setEnabled ( true );


		actionAdd->setEnabled ( false );
		actionDelete->setEnabled ( false );
		actionModify->setEnabled ( false );


		statusBar()->showMessage ( tr ( "Communication stared" ), 2000 );
	}
}

void horuxSimul::on_actionStop_triggered()
{
	if ( site )
	{
		site->stop();

		actionStart->setEnabled ( true );
		actionStop->setEnabled ( false );

		actionOpen_site->setEnabled ( false );
		actionNew_site->setEnabled ( false );
		actionImport_site->setEnabled ( false );

		actionClose_site->setEnabled ( true );
		//actionSave_site->setEnabled ( false );
		actionSave_as->setEnabled ( true );


		actionAdd->setEnabled ( true );
		actionDelete->setEnabled ( true );
		actionModify->setEnabled ( true );

		statusBar()->showMessage ( tr ( "Communication stopped" ), 2000 );

	}
}

void horuxSimul::on_actionClose_site_triggered()
{
        //! do we need to save the site
	maybeSave();

        //! close the current opened site
	if ( site )
	{
		if ( site->isStarted() ) site->stop();

		site->deleteLater();
                site = 0;
	}

	actionStart->setEnabled ( false );
	actionStop->setEnabled ( false );

	actionOpen_site->setEnabled ( true );
	actionNew_site->setEnabled ( true );
	actionImport_site->setEnabled ( true );

	actionClose_site->setEnabled ( false );
	actionSave_site->setEnabled ( false );
	actionSave_as->setEnabled ( false );


	actionAdd->setEnabled ( false );
	actionDelete->setEnabled ( false );
	actionModify->setEnabled ( false );

	treeWidget->clear();

	for ( int i=0; i<MaxRecentFiles; i++ )
		recentFileActions[i]->setEnabled ( true );

	setWindowTitle ( tr ( "Horux Simul" ) );
}

/*!
    \fn horuxSimul::loadPlugins()
*/
void horuxSimul::loadPlugins()
{
	pluginsDir = QDir ( qApp->applicationDirPath() );

	pluginsDir.cd ( "plugins" );

	foreach ( QString fileName, pluginsDir.entryList ( QDir::Files ) )
	{
		QPluginLoader loader ( pluginsDir.absoluteFilePath ( fileName ) );
		QObject *plugin = loader.instance();
		if ( plugin )
		{
			int index = plugin->metaObject()->indexOfClassInfo ( "PluginType" );
			if ( index != -1 )
			{
				QString type ( plugin->metaObject()->classInfo ( index ).value() );
				if ( type == "device" )
				{
					int index = plugin->metaObject()->indexOfClassInfo ( "PluginName" );
					if ( index != -1 )
						pluginFileNames += plugin->metaObject()->classInfo ( index ).value();
				}
			}
		}
	}
}

void horuxSimul::on_actionQuit_triggered()
{
	qApp->closeAllWindows();
}

void horuxSimul::openRecentFile()
{
	QAction *action = qobject_cast<QAction *> ( sender() );
	if ( action )
	{
		QFile file ( action->data().toString() );
		if ( !file.open ( QFile::ReadOnly | QFile::Text ) )
		{
			QMessageBox::warning ( this, tr ( "Horux Simul" ),
			                       tr ( "Cannot read file %1:\n%2." )
			                       .arg ( action->data().toString() )
			                       .arg ( file.errorString() ) );
			return;
		}


		site = new Site ( treeWidget, widget, this );
		connect ( site, SIGNAL ( message ( const QString & ) ), logText, SLOT ( insertHtml ( const QString & ) ) );
		connect ( site, SIGNAL ( siteChanged() ), this, SLOT ( siteChanged() ) );

		if ( site->loadSite ( &file ) )
		{
			currentFileName = action->data().toString();

			statusBar()->showMessage ( tr ( "Site loaded" ), 2000 );

			actionOpen_site->setEnabled ( false );
			actionNew_site->setEnabled ( false );
			actionImport_site->setEnabled ( false );

			actionClose_site->setEnabled ( true );
			actionSave_site->setEnabled ( false );
			actionSave_as->setEnabled ( true );


			actionAdd->setEnabled ( true );
			actionDelete->setEnabled ( true );
			actionModify->setEnabled ( true );

			actionStart->setEnabled ( true );

			for ( int i=0; i<MaxRecentFiles; i++ )
			{
				recentFileActions[i]->setEnabled ( false );
			}

			setCurrentFile ( action->data().toString() );

		}
		else
		{
			delete site;
			site = NULL;
		}

		file.close();
	}
}

void horuxSimul::on_actionAbout_triggered()
{
	Ui::about ui;
	QDialog dlg;
	ui.setupUi ( &dlg );
	dlg.exec();
}

void horuxSimul::siteChanged()
{
	if ( !isWindowModified () )
	{
		actionSave_site->setEnabled ( true );

		setWindowTitle ( windowTitle() + "[*]" );

		setWindowModified ( true );
	}
}

void horuxSimul::closeEvent ( QCloseEvent *event )
{
	if ( maybeSave() )
	{
		if ( site )
		{
			if ( site->isStarted() )
				site->stop();
			site->deleteLater();
		}
		event->accept();
	}
	else
	{
		event->ignore();
	}
}

bool horuxSimul::maybeSave()
{
	if ( isWindowModified () )
	{
		QMessageBox::StandardButton ret;
		ret = QMessageBox::warning ( this, tr ( "Horux Simul" ),
		                             tr ( "The site has been modified.\n"
		                                  "Do you want to save your changes?" ),
		                             QMessageBox::Save | QMessageBox::Discard | QMessageBox::Cancel );
		if ( ret == QMessageBox::Save )
			return saveSite();
		else if ( ret == QMessageBox::Cancel )
			return false;
	}
	return true;
}

bool horuxSimul::saveSite()
{
	if ( site )
	{
		return site->save ( currentFileName );
	}
	return false;
}

void horuxSimul::on_actionSave_site_triggered()
{
	if ( site )
	{
		if ( site->save ( currentFileName ) )
		{
			setWindowModified ( false );
			actionSave_site->setEnabled ( false );

			on_actionClose_site_triggered();

			QFile file ( currentFileName );
			if ( !file.open ( QFile::ReadOnly | QFile::Text ) )
			{
				QMessageBox::warning ( this, tr ( "Horux Simul" ),
				                       tr ( "Cannot read file %1:\n%2." )
				                       .arg ( currentFileName )
				                       .arg ( file.errorString() ) );
				return;
			}

			
			site = new Site ( treeWidget, widget, this );
			connect ( site, SIGNAL ( message ( const QString & ) ), logText, SLOT ( insertHtml ( const QString & ) ) );
			connect ( site, SIGNAL ( siteChanged() ), this, SLOT ( siteChanged() ) );

			if ( site->loadSite ( &file ) )
			{
				statusBar()->showMessage ( tr ( "Site loaded" ), 2000 );

				actionOpen_site->setEnabled ( false );
				actionNew_site->setEnabled ( false );
				actionImport_site->setEnabled ( false );

				actionClose_site->setEnabled ( true );
				actionSave_site->setEnabled ( false );
				actionSave_as->setEnabled ( true );


				actionAdd->setEnabled ( true );
				actionDelete->setEnabled ( true );
				actionModify->setEnabled ( true );

				actionStart->setEnabled ( true );

				for ( int i=0; i<MaxRecentFiles; i++ )
				{
					recentFileActions[i]->setEnabled ( false );
				}

				setCurrentFile ( currentFileName );

			}
			else
			{
				delete site;
				site = NULL;
			}

			file.close();
		}
	}
}

void horuxSimul::on_actionSave_as_triggered()
{
	QString fileName = QFileDialog::getSaveFileName ( this, tr ( "Save Horux Simul File" ),
	                   QDir::currentPath(),
	                   tr ( "Horux Simul Files (*.xml)" ) );
	if ( fileName.isEmpty() )
		return;

	if ( !fileName.contains ( ".xml" ) ) fileName += ".xml";

	if ( site )
		site->save ( fileName );
}

void horuxSimul::on_actionDelete_triggered()
{
	site->deleteItem();

}

void horuxSimul::on_actionAdd_triggered()
{
	Ui::addDevice ui;
	QDialog dlg(this);
	ui.setupUi ( &dlg );

	ui.pluginList->addItems ( pluginFileNames );

	QTreeWidgetItem *item = treeWidget->topLevelItem ( 0 )->clone();
	ui.treeWidget->addTopLevelItem ( item ) ;

	ui.treeWidget->setColumnHidden ( 1,true );

	if ( dlg.exec() )
	{
		QString name = ui.name->text();
		QString plugin = ui.pluginList->currentText();

		if ( !ui.treeWidget->currentItem() )
		{
			QMessageBox::warning ( this, tr ( "Horux Simul" ),
			                       tr ( "No parent was selected" ) );
			return;
		}

		QString parent = ui.treeWidget->currentItem()->text ( 1 );

		if ( name == "" )
		{
			QMessageBox::warning ( this, tr ( "Horux Simul" ),
			                       tr ( "A name for this device is missing" ) );
		}
		else
			if ( site )
				site->addItem ( name, plugin, parent );
	}
}

void horuxSimul::on_actionNew_site_triggered()
{
	bool ok;
	QString text = QInputDialog::getText ( this, tr ( "New site" ),
	                                       tr ( "Name:" ), QLineEdit::Normal,
	                                       "Site name", &ok );

	if ( ok && !text.isEmpty() )
	{
		QString fileName = QFileDialog::getSaveFileName ( this, tr ( "Save Horux Simul File" ),
		                   QDir::currentPath(),
		                   tr ( "Horux Simul Files (*.xml)" ) );
		if ( fileName.isEmpty() )
			return;

		if ( !fileName.contains ( ".xml" ) ) fileName += ".xml";

		QDomDocument doc;

		QDomElement root = doc.createElement ( "horuxSimul" );
		root.setAttribute ( "version", "1.0" );

		doc.appendChild ( root );

		QDomElement siteNode = doc.createElement ( "site" );
		siteNode.setAttribute ( "name", text );

		root.appendChild ( siteNode );

		QFile file1 ( fileName );

		if ( !file1.open ( QIODevice::WriteOnly ) )
			return;

		QTextStream out ( &file1 );

		doc.save ( out, 4 );

		file1.close();

		QFile file ( fileName );
		if ( !file.open ( QFile::ReadOnly | QFile::Text ) )
		{
			QMessageBox::warning ( this, tr ( "Horux Simul" ),
			                       tr ( "Cannot read file %1:\n%2." )
			                       .arg ( fileName )
			                       .arg ( file.errorString() ) );
			return;
		}

		site = new Site ( treeWidget, widget, this );
		connect ( site, SIGNAL ( message ( const QString & ) ), logText, SLOT ( insertHtml ( const QString & ) ) );
		connect ( site, SIGNAL ( siteChanged() ), this, SLOT ( siteChanged() ) );

		if ( site->loadSite ( &file ) )
		{
			currentFileName = fileName;

			statusBar()->showMessage ( tr ( "Site loaded" ), 2000 );

			actionOpen_site->setEnabled ( false );
			actionNew_site->setEnabled ( false );
			actionImport_site->setEnabled ( false );

			actionClose_site->setEnabled ( true );
			actionSave_site->setEnabled ( false );
			actionSave_as->setEnabled ( true );


			actionAdd->setEnabled ( true );
			actionDelete->setEnabled ( true );
			actionModify->setEnabled ( true );

			actionStart->setEnabled ( true );

			for ( int i=0; i<MaxRecentFiles; i++ )
			{
				recentFileActions[i]->setEnabled ( false );
			}


			setCurrentFile ( fileName );

		}
		else
		{
			delete site;
			site = NULL;
		}

		file.close();
	}
}
