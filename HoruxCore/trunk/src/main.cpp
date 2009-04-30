/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License.     *
 *                                       *
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


#include <QCoreApplication>
#include <QDateTime>
#include "chorux.h"
#include "include.h"

#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
#include <fcntl.h>
#include <sys/wait.h> 
#include <sys/times.h>
#endif 
 

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

#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)

#define RUNNING_DIR "/tmp"
#define LOCK_FILE "appd.lock"

using namespace std;

/*!
Could by call by SIGTERM and SIGINT to exit the application
*/
static void sighandler ( int )
{
    printf ( "exit\n" );
    exit ( 0 );
}

/*!
Create horuxd as a UNIX deamon
This function is only called in a UNIX plateform
*/
void deamonize()
{
    int i,lfp;
    char str[10];

    if ( getppid() == 1 )
    {
        printf ( "daemon\n" );
        return;
    }
    i = fork();
    if ( i<0 ) exit ( 1 ); //fork error
    if ( i>0 ) exit ( 0 ); //parent exist
    setsid();//obtain a new process group

    for ( i = getdtablesize(); i>=0; i-- ) close ( i );

    i = open ( "/dev/null", O_RDWR );
    dup ( i );
    dup ( i );
    umask ( 027 );
    chdir ( RUNNING_DIR );

    lfp = open ( LOCK_FILE,O_RDWR|O_CREAT,0640 );
    if ( lfp < 0 ) exit ( 0 );
    if ( lockf ( lfp,F_TLOCK,0 ) <0 ) exit ( 0 );

    sprintf ( str,"%d", getpid() );
    write ( lfp, str,strlen ( str ) );

    signal ( SIGTERM, sighandler );
    signal ( SIGINT,  sighandler );

    printf ( "horuxd daemon created\n" );

}
#endif


int main ( int argc, char *argv[] )
{
#if defined(Q_OS_WIN)
#elif defined(Q_WS_X11)
#if defined(H_PRODUCTION)
    // deamonize();
#endif
#endif

    QCoreApplication app ( argc, argv );

    //! add the directories plugins in the Qt library path
    app.addLibraryPath ( app.applicationDirPath() + "/plugins" );

    QCoreApplication::setOrganizationName ( "Letux" );
    QCoreApplication::setOrganizationDomain ( "letux.ch" );
    QCoreApplication::setApplicationName ( "HoruxCore" );

    //! install a qt message handler
    qInstallMsgHandler ( myMessageOutput );

    CHorux chorux ( &app );

    //! start the horux engine
    if ( !chorux.startEngine() )
    {
        //! the function stopEngine could be called by XMLRPC, we set first the call to be internal called
        chorux.setInternalCall ( true );
        //! an error happens, stop the engine
        chorux.stopEngine ( "","" );
        chorux.setInternalCall ( false );
    }

    //! start the application event loop
    int res = app.exec();

    //! the application was kill, stop the engine
    chorux.setInternalCall ( true );
    chorux.stopEngine ( "","" );
    chorux.setInternalCall ( false );

    //! reset the qt message handler
    qInstallMsgHandler ( 0 );


    return res;
}

