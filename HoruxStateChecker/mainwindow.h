#ifndef MAINWINDOW_H
#define MAINWINDOW_H

#include <QMainWindow>
#include <QNetworkReply>
#include <QtSoapHttpTransport>
#include <QVBoxLayout>
#include <QLabel>

namespace Ui {
   class MainWindow;
}

class MainWindow : public QMainWindow {
   Q_OBJECT
public:
   MainWindow(QWidget *parent = 0);
   ~MainWindow();

protected:
   void changeEvent(QEvent *e);
   void moveEvent(QMoveEvent *event);
protected slots:
   void sq(); // quit the program (call by the ctrl+alt+shift+q shortcut)
   void readSoapResponse(); // actualise the state when receiving soap responses
   void checkState(); // check the horux state, send soap requests (call by a timer (TMR_INTERVAL))
private:
   Ui::MainWindow *ui;

   int TMR_INTERVAL; // the interval we check the state
   int NBSEC_BEFORE_DECLARE_DOWN; // the nb of sec before we declare a controller down
   bool canGetStatus; // if we actually have an up to date status or not
   bool init; // if we are initialising the program or not
   int maxDeffect; // the max nb of deffect device by controller, used to create spacers...

   // values given by the config file
   QString LBL_DAILY_INFO; // the text we wrote for daily info
   QString LBL_DAILY_SUBSCRIPTION; // the text we wrote for daily subscriptions
   bool FULLSCREEN; // if we work in fullscreen mode or not
   bool DISPLAY_DAILY_SUBSCRIPTION; // if we have to show daily subscriptions or not
   QString WS_HOST; // the soap webservice's host
   QString WS_PATH; // the soap webservice's path
   QString WS_USR;  // the soap webservice's username
   QString WS_PWD;  // the soap webservice's password
   QString WS_STR;  // the soap webservice's string containing the optionals username and password
   bool WS_IS_SSL;  // if the soap webservice's use SSL or not

   QTimer *tmrCheckState; // the timer who check the state at TMR_INTERVAL
   QSize initSize; // the initial size of the window
   QRect* initGeometry; // the initial geometry of the window
   QtSoapHttpTransport transport; // use to call the soap WS
   QMap< QString, QList< QMap<QString, QString> > > status; // the status given by the last soap parseStatus request
   QList< QMap<QString, QString> > controller; // the horux controller we get by the soap getControllers request
   QList< QMap<QString, QString> > tracking; // the tracking we get by the last soap getDailyTracking request
   QList< QMap<QString, QString> > subscription; // the subscriptions we get by the last soap getDailySubscription request
   QList<QVBoxLayout*> site; // list of the site layout we have
   QMap<QString, QLabel*> labels; // list of the dynamic labels that we have
   QMap<QString, int> nbValidTracking; // the nb of valid tracking by controller name
   QMap<QString, int> dailySubscription; // the nb of daily subscription by name
   QMap<QString, QDateTime> lastUpdate; // the last update info by controller name
};

#endif // MAINWINDOW_H
