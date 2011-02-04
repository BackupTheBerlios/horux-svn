
#ifndef CHTMLLOGPLUGIN_H
#define CHTMLLOGPLUGIN_H

#include <QObject>
#include "cloginterface.h"

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CHtmlLogPlugin : public QObject, CLogInterface
{
    Q_OBJECT
    Q_INTERFACES(CLogInterface)
    Q_CLASSINFO ( "Author", "Jean-Luc Gyger" );
    Q_CLASSINFO ( "Copyright", "Letux - 2008" );
    Q_CLASSINFO ( "Version", "1.0.6" );
    Q_CLASSINFO ( "PluginName", "htmllog_horux" );
    Q_CLASSINFO ( "PluginType", "log" );
    Q_CLASSINFO ( "PluginDescription", "Log all the message in html for Horux Core" );
public:
    void debug(QString msg);
    void warning(QString msg);
    void critical(QString msg);
    void fatal(QString msg);
    QObject *getMetaObject() { return this;}

protected:
		void checkPermision(QString file);

};

#endif
