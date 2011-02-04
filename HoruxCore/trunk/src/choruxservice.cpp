
#include "choruxservice.h"

CHoruxService::CHoruxService(int argc, char **argv) : QtService<QCoreApplication>(argc, argv, "Horux Core service")
{
    setServiceDescription("Horux Core service");
    setServiceFlags(QtServiceBase::Default);

    QCoreApplication::setOrganizationName ( "Letux Sàrl" );
    QCoreApplication::setOrganizationDomain ( "letux.ch" );
    QCoreApplication::setApplicationName ( "HoruxCore" );

    ptr_horux = NULL;
}

void CHoruxService::start()
{    
    QCoreApplication *app = application();

    app->addLibraryPath ( app->applicationDirPath() + "/plugins" );

    if(!ptr_horux)
        ptr_horux = new CHorux(app);

    //! start the horux engine
    if ( !ptr_horux->startEngine() )
    {
        //! the function stopEngine could be called by XMLRPC, we set first the call to be internal called
        ptr_horux->setInternalCall ( true );
        //! an error happens, stop the engine
        ptr_horux->stopEngine ( "","" );
        ptr_horux->setInternalCall ( false );
    }
}

void CHoruxService::stop()
{
    if(ptr_horux)
    {
        //! the function stopEngine could be called by XMLRPC, we set first the call to be internal called
        ptr_horux->setInternalCall ( true );
        //! an error happens, stop the engine
        ptr_horux->stopEngine ( "","" );
        ptr_horux->setInternalCall ( false );

        delete ptr_horux;
    }
}
