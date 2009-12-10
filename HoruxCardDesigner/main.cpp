#include <QtGui/QApplication>
#include "horuxdesigner.h"

int main(int argc, char *argv[])
{
    Q_INIT_RESOURCE(ressource);

    QApplication a(argc, argv);
    HoruxDesigner w;
    w.show();
    return a.exec();
}
