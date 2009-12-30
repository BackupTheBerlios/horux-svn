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
    splash.showMessage(QObject::tr("Loading..."),Qt::AlignLeft, Qt::white);
    QApplication::processEvents();
    sleep(2);

    HoruxDesigner w;
    w.loadHoruxSoap(&splash);
    splash.hide();
    w.show();
    return a.exec();
}
