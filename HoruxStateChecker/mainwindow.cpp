#include "mainwindow.h"
#include "ui_mainwindow.h"
#include <QLabel>
#include <QDebug>
#include <QKeyEvent>
#include <QShortcut>
#include <QDesktopWidget>
#include <QtSoapHttpTransport>
#include <QtSoapMessage>
#include <QSettings>

MainWindow::MainWindow(QWidget *parent) : QMainWindow(parent), ui(new Ui::MainWindow) {
   init = false;
   canGetStatus = false;

   // read the configuration file
   QSettings settings("config.ini",QSettings::IniFormat,this);
   // config
   settings.beginGroup("config");
   TMR_INTERVAL = settings.value("timerInterval").toInt();
   NBSEC_BEFORE_DECLARE_DOWN = settings.value("nbsecBeforeDeclareDown").toInt();
   DISPLAY_DAILY_SUBSCRIPTION = settings.value("displayDailySubscription").toBool();
   FULLSCREEN = settings.value("fullscreen").toBool();
   settings.endGroup();
   // texts
   settings.beginGroup("text");
   LBL_DAILY_INFO = QString::fromUtf8(settings.value("lblDailyInfo").toString().toAscii());
   LBL_DAILY_SUBSCRIPTION = QString::fromUtf8(settings.value("lblDailySubscription").toString().toAscii());
   settings.endGroup();
   //webservice
   settings.beginGroup("webservice");
   WS_HOST = settings.value("host").toString();
   WS_PATH= settings.value("path").toString();
   WS_USR = settings.value("user").toString();
   WS_PWD = settings.value("password").toString();
   WS_IS_SSL = settings.value("ssl").toBool();
   if (WS_USR != "")
      WS_STR += "&username="+WS_USR;
   if (WS_PWD != "")
      WS_STR += "&password="+WS_USR;
   settings.endGroup();

   // create the shortcut we use to quit the program
   QShortcut* sq = new QShortcut(QKeySequence(tr("Ctrl+Alt+Shift+Q")), this);
   connect(sq, SIGNAL(activated()), this, SLOT(sq()));

   // init the size, config and style of the window
   if (FULLSCREEN) {
      QDesktopWidget *desktop = QApplication::desktop();
      resize(desktop->width(), desktop->height());
      initSize = this->size();
      initGeometry = new QRect(0, 0, desktop->width(), desktop->height());
   }
   else {
      initSize.setHeight(0);
      initSize.setWidth(0);
      initGeometry = new QRect(0, 0, 0, 0);
   }
   setMinimumSize(initSize);
   setMaximumSize(initSize);
   setObjectName("mainWindow");
   if (FULLSCREEN)
      setWindowFlags(windowFlags() | Qt::WindowStaysOnTopHint | Qt::FramelessWindowHint);

   // set up the GUI
   ui->setupUi(this);

   // init the style of the window's content
   ui->logoFrm->setMinimumHeight(110);
   ui->logoFrm->setMaximumHeight(110);
   ui->generalInfoFrm->setMinimumHeight(60);
   ui->generalInfoFrm->setMaximumHeight(60);
   ui->siteInfoFrm->setMinimumHeight(60);
   ui->siteInfoFrm->setMaximumHeight(60);
   if (!DISPLAY_DAILY_SUBSCRIPTION)
      setStyleSheet(styleSheet()+"#generalInfoFrm{border: 0px; background: transparent;}");
   else
      setStyleSheet(styleSheet()+"#generalInfoFrm{border: 1px solid #d7d7d7; border-radius: 10px; background: white url(./gfx/subscription.png) no-repeat left center;}");
   setStyleSheet(styleSheet()+"#mainWindow {background: #eff2f3 url(./gfx/logoLetux.png) no-repeat left top;} #siteFrm{border: 1px solid #d7d7d7; border-radius: 10px; background: white;} #siteInfoFrm{border: 1px solid #d7d7d7; border-radius: 10px; background: white;} #logoFrm {margin-top:50px;border: 1px solid #d7d7d7; border-radius: 10px; background: white url(./gfx/logoHorux.png) no-repeat left center;}");

   // if needed init the daily subscription label
   if (DISPLAY_DAILY_SUBSCRIPTION) {
      QLabel* lblDailySubscription = new QLabel();
      lblDailySubscription->setObjectName("lblDailySubscription");
      labels["lblDailySubscription"] = lblDailySubscription;
      setStyleSheet(QString(styleSheet()+"QLabel#lblDailySubscription{ padding-left:50px;}"));
      ui->generalInfoLayout->addWidget(lblDailySubscription);
   }

   // create a spacer for the spacing frame
   QLabel* spacer = new QLabel();
   labels["spacer"] = spacer;
   ui->spacerLayout->addWidget(spacer);

   // init and param the soap WS
   connect(&transport, SIGNAL(responseReady()),this, SLOT(readSoapResponse()), Qt::UniqueConnection);
   if(WS_IS_SSL) {
      transport.setHost(WS_HOST, true);
      connect(transport.networkAccessManager(),SIGNAL(sslErrors( QNetworkReply *, const QList<QSslError> & )), this, SLOT(sslErrors(QNetworkReply*,QList<QSslError>)), Qt::UniqueConnection);
   }
   else {
      transport.setHost(WS_HOST);
   }

   // get the controllers
   QtSoapMessage message;
   message.setMethod(QtSoapQName("getControllers"));
   transport.submitRequest(message, WS_PATH+"/index.php?soap=horux"+WS_STR);

   // init and param the timer which will check the state
   tmrCheckState = new QTimer(this);
   connect(tmrCheckState, SIGNAL(timeout()), this, SLOT(checkState()));
   tmrCheckState->start(0);

   // processes pending events
   QApplication::processEvents();
}

void MainWindow::checkState() {
   if (init)
      tmrCheckState->setInterval(TMR_INTERVAL);
   else
      init = true;

   // get the status
   QtSoapMessage msgParseStatus;
   msgParseStatus.setMethod(QtSoapQName("parseStatus"));
   msgParseStatus.addMethodArgument("xmlresp","","");
   transport.submitRequest(msgParseStatus, WS_PATH+"/index.php?soap=horux"+WS_STR);

   // get the daily trackings
   QtSoapMessage msgGetDailyTracking;
   msgGetDailyTracking.setMethod(QtSoapQName("getDailyTracking"));
   QDate date = QDate::currentDate();
   msgGetDailyTracking.addMethodArgument("date","",date.toString("yyyy-MM-dd"));
   transport.submitRequest(msgGetDailyTracking, WS_PATH+"/index.php?soap=horux"+WS_STR);

   // if needed get the daily subscriptions
   if (DISPLAY_DAILY_SUBSCRIPTION) {
      QtSoapMessage msgGetDailySubscription;
      msgGetDailySubscription.setMethod(QtSoapQName("getDailySubscription"));
      msgGetDailySubscription.addMethodArgument("date","",date.toString("yyyy-MM-dd"));
      transport.submitRequest(msgGetDailySubscription, WS_PATH+"/index.php?soap=ticketing"+WS_STR);
   }

   // check if the controllers are still alive and adapt the display if needed
   QMap<QString, QDateTime>::iterator i;
   for (i = lastUpdate.begin(); i != lastUpdate.end(); i++) {
      if (i.value().secsTo(QDateTime::currentDateTime()) > NBSEC_BEFORE_DECLARE_DOWN+(TMR_INTERVAL/1000)) {
         QImage image(tr("./gfx/down.png"));
         labels[i.key()+"img"]->setPixmap(QPixmap::fromImage(image));
         labels[i.key()+"DailyInfo"]->setText("");

         for (int j = 0; j < status["devices"].size(); j++) {
            for (int k = 0; k < site.size(); k++) {
               if (i.key() == status["devices"].at(j)["horuxController"] && labels[status["devices"].at(j)["name"]]) {
                  labels[status["devices"].at(j)["name"]]->clear();
                  labels[status["devices"].at(j)["name"]]->deleteLater();
                  site.at(k)->removeWidget(labels[status["devices"].at(j)["name"]]);
                  labels[status["devices"].at(j)["name"]] = NULL;
               }
            }
         }
      }
   }
}

void MainWindow::readSoapResponse() {
   const QtSoapMessage &response = transport.getResponse();

   // if we get a fault response, adapt the display
   if (response.isFault()) {
      canGetStatus = false;

      for (int i = 0; i < site.size(); i++) {
         QImage image(tr("./gfx/down.png"));
         labels[site.at(i)->objectName()+"img"]->setPixmap(QPixmap::fromImage(image));
      }

      QMap<QString, int>::iterator i;
      for (i = nbValidTracking.begin(); i != nbValidTracking.end(); i++) {
         labels[i.key()+"DailyInfo"]->setText("");
      }
      if (DISPLAY_DAILY_SUBSCRIPTION)
         labels["lblDailySubscription"]->setText("");

      for (int i = 0; i < status["devices"].size(); i++) {
         for (int j = 0; j < site.size(); j++) {
            if (site.at(j)->objectName() == status["devices"].at(i)["horuxController"] && labels[status["devices"].at(i)["name"]]) {
               labels[status["devices"].at(i)["name"]]->clear();
               labels[status["devices"].at(i)["name"]]->deleteLater();
               site.at(j)->removeWidget(labels[status["devices"].at(i)["name"]]);
               labels[status["devices"].at(i)["name"]] = NULL;
            }
         }
      }

      return;
   }

   // if we get new status
   if( response.method().name().name() == "parseStatusResponse") {
      const QtSoapType &returnValue = response.returnValue();
      canGetStatus = true;

      status.clear();

      // foreach returned value, convert to "status"
      for(int i=0; i<returnValue.count(); i++ ) {
         const QtSoapType &record =  returnValue[i]["value"];

         QString key;
         QList< QMap<QString, QString> > item;

         for (int j=0;j<record.count();j++) {
            QMap<QString, QString> value;

            for (int k=0;k<record[0].count();k++) {
               key = record[j][k]["key"].toString();
               value[key] = record[j][k]["value"].toString();
            }

            item.append(value);
         }

         status[returnValue[i]["key"].toString()] = item;
      }

      // remove old spacers and init the devices states...
      QMap<QString, bool> devicesOk; // if the devices are all up or not, by controller name
      QMap<QString, int> nbDeffect; // the nb of deffect device by controller
      for (int i = 0; i < site.size(); i++) {
         devicesOk[site.at(i)->objectName()] = true; // by default the device are ok
         for (int j = -1; j < maxDeffect; j++) {
            if (labels[site.at(i)->objectName()+"Spacer"+QString::number(j)]) {
               site.at(i)->removeWidget(labels[site.at(i)->objectName()+"Spacer"+QString::number(j)]);
               labels[site.at(i)->objectName()+"Spacer"+QString::number(j)]->clear();
               labels[site.at(i)->objectName()+"Spacer"+QString::number(j)]->deleteLater();
               labels.remove(site.at(i)->objectName()+"Spacer"+QString::number(j));
            }
         }
      }

      // (TODO: simplify and optimize...)
      // for each device, check if it is connected and adapt the display
      for (int i = 0; i < status["devices"].size(); i++) {
         // for each controller check their controlled devices
         for (int j = 0; j < site.size(); j++) {
            if (site.at(j)->objectName() == status["devices"].at(i)["horuxController"]) {
               QLabel* device;
               // if the device isn't connected create the corresponding label else remove the old deffect label if needed
               if (status["devices"].at(i)["isConnected"] != "1") {
                  if (!labels[status["devices"].at(i)["name"]]) {
                     device = new QLabel(status["devices"].at(i)["name"]);
                     device->setObjectName(status["devices"].at(i)["name"]);
                     device->setMaximumHeight(25);
                     site.at(j)->addWidget(device);
                  }
                  else {
                     device = labels[status["devices"].at(i)["name"]];
                  }
               }
               QString imgPath;
               if (status["devices"].at(i)["isConnected"] == "0") {
                  imgPath = "./gfx/downSmall.png";
                  setStyleSheet(QString(styleSheet()+"QLabel#"+status["devices"].at(i)["name"]+" { padding-left:16px; background: transparent url("+imgPath+") no-repeat left center;}"));
                  labels[status["devices"].at(i)["name"]] = device;
                  devicesOk[site.at(j)->objectName()] = false;

                  if (!nbDeffect[status["devices"].at(i)["horuxController"]])
                     nbDeffect[status["devices"].at(i)["horuxController"]] = 0;
                  nbDeffect[status["devices"].at(i)["horuxController"]] += 1;
               }
               else if (status["devices"].at(i)["isConnected"] == "1" && labels[status["devices"].at(i)["name"]]) {
                  if (labels[status["devices"].at(i)["name"]]) {
                     labels[status["devices"].at(i)["name"]]->clear();
                     labels[status["devices"].at(i)["name"]]->deleteLater();
                     site.at(j)->removeWidget(labels[status["devices"].at(i)["name"]]);
                     labels[status["devices"].at(i)["name"]] = NULL;
                  }
               }
               else if (status["devices"].at(i)["isConnected"] != "1")
                  devicesOk[site.at(j)->objectName()] = false;

               // if old the devices of the controller, it's up
               if (devicesOk[site.at(j)->objectName()]) {
                  QImage image(tr("./gfx/up.png"));
                  labels[status["devices"].at(i)["horuxController"]+"img"]->setPixmap(QPixmap::fromImage(image));
               }
               // else, we have alert
               else {
                  QImage image(tr("./gfx/alert.png"));
                  labels[status["devices"].at(i)["horuxController"]+"img"]->setPixmap(QPixmap::fromImage(image));
               }
            }

            // determine the max nb of alert a controller have
            maxDeffect = 0;
            QMap<QString, int>::iterator k;
            for (k = nbDeffect.begin(); k != nbDeffect.end(); k++) {
               if (k.value() > maxDeffect)
                  maxDeffect = k.value();
            }
         }
      }

      // add spacers
      for (int i = 0; i < site.size(); i++) {
         for (int j = -1; j < maxDeffect-nbDeffect[site.at(i)->objectName()]; j++) {
            QLabel* spacer = new QLabel();
            spacer->setObjectName(site.at(i)->objectName()+"Spacer"+QString::number(j));
            labels[site.at(i)->objectName()+"Spacer"+QString::number(j)] = spacer;
            site.at(i)->addWidget(spacer);
         }
      }

      // memorize the time of the last update of the horux controller
      for (int i = 0; i < status["horuxRepeaterData"].size(); i++) {
         lastUpdate[status["horuxRepeaterData"].at(i)["name"]] = QDateTime::fromString(status["horuxRepeaterData"].at(i)["lastUpdate"], "hh:mm:ss / dd.MM.yyyy");
      }
   }
   // if we get the list of the controllers
   if(response.method().name().name() == "getControllersResponse") {
      const QtSoapType &returnValue = response.returnValue();
      controller.clear();

      // convert the received data to "controller"
      for(int i=0; i<returnValue.count(); i++ ) {
         QMap<QString, QString> value;
         for(int j=0; j<returnValue[i].count(); j++ ) {
            value[returnValue[i][j]["key"].toString()] =  returnValue[i][j]["value"].toString();
         }
         controller.append(value);
      }

      // for each controller, display its data
      for (int i = 0; i < controller.size(); i++) {
         int pos = site.size();

         site.append(new QVBoxLayout());

         QLabel* siteName = new QLabel("<html><h2>"+controller.at(i)["name"]+"</h2></html>");
         siteName->setObjectName(controller.at(i)["name"]);
         siteName->setMaximumHeight(20);
         siteName->setMinimumHeight(20);
         labels[controller.at(i)["name"]] = siteName;

         QLabel* labelImage = new QLabel();
         QImage image(tr("./gfx/down.png"));
         labelImage->setPixmap(QPixmap::fromImage(image));
         labelImage->setObjectName(controller.at(i)["name"]+"img");
         labelImage->setMaximumHeight(128);
         labels[controller.at(i)["name"]+"img"] = labelImage;

         ui->stateLayout->addLayout(site.at(pos));

         site.at(pos)->addWidget(labelImage);
         site.at(pos)->addWidget(siteName);
         site.at(pos)->setObjectName(controller.at(i)["name"]);

         QLabel* dailyInfo = new QLabel();
         dailyInfo->setObjectName(controller.at(i)["name"]+"DailyInfo");
         dailyInfo->setMaximumHeight(50);
         //setStyleSheet(QString(styleSheet()+"QLabel#"+controller.at(i)["name"]+"DailyInfo"+" { padding-left:50px;}"));
         labels[controller.at(i)["name"]+"DailyInfo"] = dailyInfo;
         ui->siteInfoLayout->addWidget(dailyInfo);
      }
   }

   // if we get a new list of daily tracking
   if( response.method().name().name() == "getDailyTrackingResponse" && canGetStatus) {
      const QtSoapType &returnValue = response.returnValue();
      tracking.clear();

      // convert the received data to "tracking"
      for(int i=0; i<returnValue.count(); i++ ) {
         QMap<QString, QString> value;
         for(int j=0; j<returnValue[i].count(); j++ ) {
            value[returnValue[i][j]["key"].toString()] =  returnValue[i][j]["value"].toString();
         }
         tracking.append(value);
      }

      // determine the number of valid daily tracking
      nbValidTracking.clear();
      for (int i = 0; i < tracking.size(); i++) {
         if (tracking.at(i)["is_access"] == "1") {
            for (int j = 0; j < status["devices"].size(); j++) {
               if (status["devices"].at(j)["id"] == tracking.at(i)["id_entry"]) {
                  if (!nbValidTracking[status["devices"].at(j)["horuxController"]])
                     nbValidTracking[status["devices"].at(j)["horuxController"]] = 0;
                  nbValidTracking[status["devices"].at(j)["horuxController"]] += 1;
                  break;
               }
            }
         }
      }

      // for each valid tracking, display it
      QMap<QString, int>::iterator i;
      for (i = nbValidTracking.begin(); i != nbValidTracking.end(); i++) {
         labels[i.key()+"DailyInfo"]->setText(LBL_DAILY_INFO+" : "+QString::number(i.value()));
      }
   }

   // if we get a new list of daily subscription and we have to display it
   if(DISPLAY_DAILY_SUBSCRIPTION && response.method().name().name() == "getDailySubscriptionResponse" && canGetStatus) {
      const QtSoapType &returnValue = response.returnValue();
      subscription.clear();

      // convert the received data to "subscription"
      for(int i=0; i<returnValue.count(); i++ ) {
         QMap<QString, QString> value;
         for(int j=0; j<returnValue[i].count(); j++ ) {
            value[returnValue[i][j]["key"].toString()] =  returnValue[i][j]["value"].toString();
         }
         subscription.append(value);
      }

      // determine the number of daily subscription by subscription name
      dailySubscription.clear();
      for (int i = 0; i < subscription.size(); i++) {
         if (!dailySubscription[subscription.at(i)["name"]])
            dailySubscription[subscription.at(i)["name"]] = 0;
         dailySubscription[subscription.at(i)["name"]] += 1;
      }

      // for each valid tracking, display it
      QMap<QString, int>::iterator i;
      QString t = "";
      int nbSub = -1;
      for (i = dailySubscription.begin(); i != dailySubscription.end(); i++) {
         t += "<br />" + i.key() + " : " + QString::number(i.value());
         nbSub++;
      }
      if (t != "") {
         labels["lblDailySubscription"]->setText(LBL_DAILY_SUBSCRIPTION + t + "</html>");
         ui->generalInfoFrm->setMinimumHeight(60+(nbSub*10));
         ui->generalInfoFrm->setMaximumHeight(60+(nbSub*10));
      }
   }
}

MainWindow::~MainWindow() {
   delete ui;
}

void MainWindow::changeEvent(QEvent *e) {
   QMainWindow::changeEvent(e);
   switch (e->type()) {
   case QEvent::LanguageChange:
      ui->retranslateUi(this);
      break;
   default:
      break;
   }
}

// quit the program
void MainWindow::sq() {
   exit(0);
}

// ignore close event
void QWidget::closeEvent(QCloseEvent *event) {
   event->ignore();
}

// keep the same position
void MainWindow::moveEvent(QMoveEvent *event) {
   if (event->pos() != QPoint(0,0))
      this->move(0, 0);
}
