
#include "choruxservice.h"
#include "include.h"

/*! 
  Own Qt message handler
  All message are redirect to the log plugins

  @param type type of the message (debug, warning, critical, fatal)
  @param msg message  
*/
void myMessageOutput ( QtMsgType type, const char *msg )
{
    switch ( type )
    {
        case QtDebugMsg:
            fprintf ( stderr, "Debug: %s\n", msg );
            CFactory::getLog()->debug ( msg );
            break;
        case QtWarningMsg:
            fprintf ( stderr, "Warning: %s\n", msg );
            CFactory::getLog()->warning ( msg );
            break;
        case QtCriticalMsg:
            fprintf ( stderr, "Critical: %s\n", msg );
            CFactory::getLog()->critical ( msg );
            break;
        case QtFatalMsg:
            fprintf ( stderr, "Fatal: %s\n", msg );
            CFactory::getLog()->fatal ( msg );
            break;
    }
}


int main ( int argc, char *argv[] )
{

    qInstallMsgHandler ( myMessageOutput );


#if !defined(Q_WS_WIN)
    // QtService stores service settings in SystemScope, which normally require root privileges.
    // To allow testing Horux Core as non-root, we change the directory of the SystemScope settings file.
    QSettings::setPath(QSettings::NativeFormat, QSettings::SystemScope, QDir::tempPath());
#endif
    CHoruxService service(argc, argv);      
    return service.exec();
}

