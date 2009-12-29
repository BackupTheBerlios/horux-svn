#include <QtGui/QApplication>
#include "horuxdesigner.h"
#include <QSplashScreen>

int main(int argc, char *argv[])
{
    Q_INIT_RESOURCE(ressource);
    QApplication a(argc, argv);

    QPixmap pixmap(":/images/splash.jpg");
    QSplashScreen splash(pixmap);
    splash.show();

    sleep(2);

    HoruxDesigner w;
    splash.finish(&w);
    w.show();
    return a.exec();
}
