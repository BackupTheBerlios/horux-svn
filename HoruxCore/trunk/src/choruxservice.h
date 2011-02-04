
#ifndef CHORUXSERVICE_H
#define CHORUXSERVICE_H

#include <QtServiceBase>
#include "chorux.h"

class CHoruxService: public QtService<QCoreApplication>
{
public:
    CHoruxService(int argc, char **argv);

protected:
    void start();
    void stop();

private:
    CHorux *ptr_horux;
};

#endif // CHORUXSERVICE_H
