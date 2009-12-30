#include <QtGui/QApplication>
#include "horuxdesigner.h"
#include <QSplashScreen>

#if defined(Q_WS_WIN)
#include <windows.h>
#endif

int main(int argc, char *argv[])
{
    Q_INIT_RESOURCE(ressource);
    QApplication a(argc, argv);

    QPixmap pixmap(":/images/splash.jpg");
    QSplashScreen splash(pixmap);
    splash.show();
    splash.showMessage(QObject::tr("Loading..."),Qt::AlignLeft, Qt::white);
    QApplication::processEvents();

#if defined(Q_WS_WIN)
    Sleep(2000);
#else
    sleep(2);
#endif

    HoruxDesigner w;
    w.loadHoruxSoap(&splash);
    splash.hide();
    w.show();
    return a.exec();
}
