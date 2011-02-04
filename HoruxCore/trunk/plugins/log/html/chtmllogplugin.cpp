#include "chtmllogplugin.h"
#include <QtCore>

void CHtmlLogPlugin::debug(QString msg)
{
  QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

  checkPermision(path + "debug.html");

  QFile file(path + "debug.html");

  if (!file.open(QIODevice::Append | QIODevice::Text))
   return;

  QTextStream out(&file);

  out << "<span class=\"date\">" << date << "</span>" << "<span class=\"debug\">" << msg << "</span>" << "<br/>\n";

  file.close();
}

void CHtmlLogPlugin::warning(QString msg)
{
  QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

  checkPermision(path + "warning.html");

  QFile file(path + "warning.html");
  if (!file.open(QIODevice::Append | QIODevice::Text))
   return;

  QTextStream out(&file);

  out << "<span class=\"date\">" << date << "</span>" << "<span class=\"warning\">" << msg << "</span>" << "<br/>\n";

  file.close();
}

void CHtmlLogPlugin::critical(QString msg)
{
  QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

	checkPermision(path + "critical.html");

  QFile file(path + "critical.html");
  if (!file.open(QIODevice::Append | QIODevice::Text))
   return;

  QTextStream out(&file);

  out << "<span class=\"date\">" << date << "</span>" << "<span class=\"critical\">" << msg << "</span>" << "<br/>\n";

  file.close();
}

void CHtmlLogPlugin::fatal(QString msg)
{
  QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

	checkPermision(path + "fatal.html");

  QFile file(path + "fatal.html");
  if (!file.open(QIODevice::Append | QIODevice::Text))
   return;

  QTextStream out(&file);

  out << "<span class=\"date\">" << date << "</span>" << "<span class=\"fatal\">" << msg << "</span>" << "<br/>\n";

  file.close();
}

void CHtmlLogPlugin::checkPermision(QString file)
{
	if(QFile::exists(file))
	{

		QFileInfo fi(file);

		if(!fi.permission(QFile::ReadOwner | 
												QFile::WriteOwner | 
												QFile::ReadUser | 
												QFile::WriteUser | 
												QFile::ReadGroup | 
												QFile::WriteGroup |
												QFile::ReadOther |
												QFile::WriteOther))
		{

			QFile::setPermissions(file, 
													QFile::ReadOwner | 
													QFile::WriteOwner | 
													QFile::ReadUser | 
													QFile::WriteUser | 
													QFile::ReadGroup | 
													QFile::WriteGroup |
													QFile::ReadOther |
													QFile::WriteOther);
		}
	}
}

Q_EXPORT_PLUGIN2(horuxhtmllogplugin, CHtmlLogPlugin);
