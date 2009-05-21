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

#include "choruxservice.h"

CHoruxService::CHoruxService(int argc, char **argv) : QtService<QCoreApplication>(argc, argv, "Horux Core service")
{
    setServiceDescription("Horux Core service");
    setServiceFlags(QtServiceBase::Default);

    QCoreApplication::setOrganizationName ( "Letux" );
    QCoreApplication::setOrganizationDomain ( "letux.ch" );
    QCoreApplication::setApplicationName ( "HoruxCore" );

    chorux = NULL;
}

void CHoruxService::start()
{    
    QCoreApplication *app = application();

    app->addLibraryPath ( app->applicationDirPath() + "/plugins" );

    if(!chorux)
        chorux = new CHorux(app);

    //! start the horux engine
    if ( !chorux->startEngine() )
    {
        //! the function stopEngine could be called by XMLRPC, we set first the call to be internal called
        chorux->setInternalCall ( true );
        //! an error happens, stop the engine
        chorux->stopEngine ( "","" );
        chorux->setInternalCall ( false );
    }

}

void CHoruxService::stop()
{
    //! the function stopEngine could be called by XMLRPC, we set first the call to be internal called
    chorux->setInternalCall ( true );
    //! an error happens, stop the engine
    chorux->stopEngine ( "","" );
    chorux->setInternalCall ( false );

    delete chorux;
}

