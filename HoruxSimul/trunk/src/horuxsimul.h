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


#ifndef HORUXSIMUL_H
#define HORUXSIMUL_H

#include <QMainWindow>
#include <QDir>
#include "ui_HSMainWindow.h"
#include "site.h"

//!  This is the main window of the HoruxSumul application
/*!
  @author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
  @version 0.1
  @date    2008

  history:
  28.02.2008  First implementation
*/

class horuxSimul:public QMainWindow, public Ui::MainWindow
{
		Q_OBJECT

	public:
		horuxSimul();
		~horuxSimul();

		//! load the plugins
		/*!
		  Juste check the right plugin and fill the plugin list name
		*/
		void loadPlugins();

	protected slots:
		//! Open a site
		void on_actionOpen_site_triggered();

		//! start a site
		void on_actionStart_triggered();

		//! stop a site
		void on_actionStop_triggered();

		//! close the current site
		void on_actionClose_site_triggered();

		//! quit the application
		void on_actionQuit_triggered();

		//! about the application
		void on_actionAbout_triggered();

		//! save the application
		void on_actionSave_site_triggered();

		//! save as the site
		void on_actionSave_as_triggered();

		//! delete a device
		void on_actionDelete_triggered();

		//! add a device
		void on_actionAdd_triggered();

		//! creat a new site
		void on_actionNew_site_triggered();

		void openRecentFile();

		void siteChanged();

	protected:
		void closeEvent ( QCloseEvent *event );

	private:
		//! current site
		Site *site;

		//! directory where the plugin are
		QDir pluginsDir;

		//! plugin name list
		QStringList pluginFileNames;


		enum {MaxRecentFiles = 5};
		QAction *recentFileActions[MaxRecentFiles];

		QString currentFileName;

	private:
		void updateRecentFileAction();

		void setCurrentFile ( const QString &fileName );

		bool maybeSave();

		bool saveSite();

		void horuxMenuRecentFile();
};

#endif
