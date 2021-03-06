#include <QtGui>
#include <QSslError>
#include <QNetworkReply>
#include <QCryptographicHash>
#include "horuxdesigner.h"

#include "carditemtext.h"
#include "carditem.h"
#include "confpage.h"
#include "printpreview.h"
#include "horuxdialog.h"
#include "databaseconnection.h"
#include "printselection.h"
#include "printhoruxuser.h"

const int InsertTextButton = 10;
const int InsertImageButton = 11;

HoruxDesigner *HoruxDesigner::pThis = NULL;


HoruxDesigner::HoruxDesigner(QWidget *parent)
    : QMainWindow(parent), ui(new Ui::HoruxDesigner)
{
    pThis = this;

    ui->setupUi(this);

    currentUser = 0;
    host = "";
    username = "";
    password = "";
    path = "";
    databaseName = "";
    ssl = false;
    engine = "";
    file = "";
    sql = "";
    primaryKeyColumn = 0;
    column1 = 1;
    column2 = 2;
    pictureColumn = 3;

    cardPage = NULL;
    textPage = NULL;
    pixmapPage = NULL;
    userCombo = NULL;
    scenePreview = NULL;

    sqlQuery = NULL;

    fileChanged = false;

    dbInformation = new QLabel("");
    statusBar()->addPermanentWidget(dbInformation);

    isSecure = new QLabel();
    statusBar()->addPermanentWidget(isSecure);
    isSecure->setToolTip(tr("The communication is not safe"));
    isSecure->setPixmap(QPixmap(":/images/decrypted.png"));

    printer = new QPrinter(QPrinter::HighResolution);

    currenFile.setFileName("");

    setWindowTitle("Horux Card Designer - new card");

    createToolBox();
    initScene();
    createAction();
    createToolBar();

    connect(ui->next, SIGNAL(clicked()), this, SLOT(nextRecord()));
    connect(ui->back, SIGNAL(clicked()), this, SLOT(backRecord()));    

    ui->cardMode->widget(1)->setDisabled(true);

    #if defined(Q_WS_WIN)
    m_pTwain = new QTwain(0);
    m_pPixmap = NULL;

    connect(ui->scanButton, SIGNAL(clicked()), this, SLOT(onAcquireButton()));
    connect(ui->source, SIGNAL(clicked()), this, SLOT(onSourceButton()));
    connect(ui->clear, SIGNAL(clicked()), this, SLOT(onClear()));
    connect(m_pTwain, SIGNAL(dibAcquired(CDIB*)),
                         this, SLOT(onDibAcquired(CDIB*)));

    #endif


    connect(ui->name, SIGNAL(textChanged(QString)), this, SLOT(onUserHoruxFieldChange()));
    connect(ui->firstName, SIGNAL(textChanged(QString)), this, SLOT(onUserHoruxFieldChange()));
    connect(ui->street, SIGNAL(textChanged(QString)), this, SLOT(onUserHoruxFieldChange()));
    connect(ui->city, SIGNAL(textChanged(QString)), this, SLOT(onUserHoruxFieldChange()));
    connect(ui->zip, SIGNAL(textChanged(QString)), this, SLOT(onUserHoruxFieldChange()));
    connect(ui->email, SIGNAL(textChanged(QString)), this, SLOT(onUserHoruxFieldChange()));
    connect(ui->phone, SIGNAL(textChanged(QString)), this, SLOT(onUserHoruxFieldChange()));
    connect(ui->birthday, SIGNAL(dateChanged(QDate)), this, SLOT(onUserHoruxFieldChange()));
    connect(ui->addUser, SIGNAL(clicked()), this, SLOT(onPrintHoruxUser()));
}

HoruxDesigner::~HoruxDesigner()
{
    if(sqlQuery)
        delete sqlQuery;
    delete ui;
}

void HoruxDesigner::showEvent(QShowEvent* thisEvent)
{
    // set the parent here to be sure to have a really
    // valid window as the twain parent!
    m_pTwain->setParent(this);
} // !showEvent()



bool HoruxDesigner::winEvent(MSG* pMsg, long* result)
{
    m_pTwain->processMessage(*pMsg);
    return false;
}

void HoruxDesigner::onAcquireButton()
{
    if (!m_pTwain->acquire())
    {
        qWarning("acquire() call not successful!");
    }
}

void HoruxDesigner::onSourceButton() {
    m_pTwain->selectSource();
}

void HoruxDesigner::onDibAcquired(CDIB* pDib)
{
    qDebug()<<"We have an image";

    if (m_pPixmap)
            delete m_pPixmap;

    m_pPixmap = QTwainInterface::convertToPixmap(pDib);


    ui->picture->setPixmap(m_pPixmap->scaledToWidth(100));

    pictureBuffer.close();

    m_pPixmap->save(&pictureBuffer,"JPG");

    updatePrintPreview();

    delete pDib;
}

void HoruxDesigner::onClear() {
    ui->name->setText("");
    ui->birthday->setDate(QDate(2000,1,1));
    ui->firstName->setText("");
    ui->street->setText("");
    ui->city->setText("");
    ui->zip->setText("");
    ui->phone->setText("");
    ui->email->setText("");

    QFile unknown(":/images/unknown.jpg");

    if(unknown.open(QIODevice::ReadOnly)) {
        QPixmap p(":/images/unknown.jpg");
        ui->picture->setPixmap(p);

        pictureBuffer.close();

        p.save(&pictureBuffer,"JPG");
    }

    updatePrintPreview();
}

void HoruxDesigner::onUserHoruxFieldChange() {

    QObject *p = sender();

    if(p == ui->name)
        userValue["name"] = ui->name->text();

    if(p == ui->firstName)
        userValue["firstname"] = ui->firstName->text();

    if(p == ui->birthday)
        userValue["birthday"] =  ui->birthday->text();

    if(p == ui->street)
        userValue["street_private"] = ui->street->text();

    if(p == ui->city)
        userValue["city_private"] = ui->city->text();

    if(p == ui->zip)
        userValue["zip_private"] =  ui->zip->text();

    if(p == ui->phone)
        userValue["phone_private"] =  ui->phone->text();

    if(p == ui->email)
        userValue["email_private"] =  ui->email->text();

    updatePrintPreview();
}

void HoruxDesigner::onPrintHoruxUser() {
    PrintHoruxUser dlg(this);

    connect(&dlg, SIGNAL(printCard()), this, SLOT(printPreview()));
    connect(this, SIGNAL(printCardOk()), &dlg, SLOT(rfidStep()));
    connect(&dlg, SIGNAL(newUserAdd()), this, SLOT(loadHoruxSoap()));

    dlg.setUserType(ui->userType->currentText());

    dlg.exec();
}

void HoruxDesigner::loadData()
{
    bool isDbType = false;

    if(engine == "NOT_USED") {
        dbInformation->setText(tr("No database used"));
    } else if(engine == "HORUX") {
        dbInformation->setText(tr("Connection to Horux database"));
        loadHoruxSoap();
        ui->cardMode->widget(1)->setDisabled(false);
    } else if(engine == "CSV") {
        dbInformation->setText(tr("Connection to CSV file: ") + file);
        loadCSVData();
    } else if(engine == "QMYSQL") {
        if(!QSqlDatabase::contains("horux"))
            dbase = QSqlDatabase::addDatabase("QMYSQL","horux");
        dbase.setHostName(host);
        dbase.setDatabaseName(databaseName);
        dbase.setUserName(username);
        dbase.setPassword(password);
        isDbType = true;
        dbInformation->setText(tr("Connection to MySql database: ") + databaseName);
    } else if(engine == "QSQLITE") {
        if(!QSqlDatabase::contains("horux"))
            dbase = QSqlDatabase::addDatabase("QSQLITE","horux");
        dbase.setDatabaseName(databaseName);
        isDbType = true;
        dbInformation->setText(tr("Connection to SQlite database: ") + databaseName );
    } else if(engine == "QPSQL") {
        if(!QSqlDatabase::contains("horux"))
            dbase = QSqlDatabase::addDatabase("QPSQL","horux");
        dbase.setHostName(host);
        dbase.setDatabaseName(databaseName);
        dbase.setUserName(username);
        dbase.setPassword(password);
        isDbType = true;
        dbInformation->setText(tr("Connection to PSQL database: ") + databaseName);
    } else if(engine == "QODBC") {
        if(!QSqlDatabase::contains("horux"))
            dbase = QSqlDatabase::addDatabase("QODBC","horux");
        dbase.setHostName(host);
        dbase.setDatabaseName(databaseName);
        dbase.setUserName(username);
        dbase.setPassword(password);
        isDbType = true;
        dbInformation->setText(tr("Connection to ODBC database: ") + databaseName);
    } else if(engine == "QOCI") {
        if(!QSqlDatabase::contains("horux"))
            dbase = QSqlDatabase::addDatabase("QOCI","horux");
        dbase.setHostName(host);
        dbase.setDatabaseName(databaseName);
        dbase.setUserName(username);
        dbase.setPassword(password);        
        isDbType = true;
        dbInformation->setText(tr("Connection to Oracle database: ") + databaseName);
    }

    if(isDbType)
    {
        if(!dbase.open()) {
            QMessageBox::warning(this,tr("Database connection error"),tr("Not able to connect to the database"));
            dbInformation->setText("Not able to connect to the database");
        } else {
            loadSQLData();
        }

    }
}

void HoruxDesigner::loadHoruxSoap()
{
    connect(&transport, SIGNAL(responseReady()),this, SLOT(readSoapResponse()), Qt::UniqueConnection);

    pictureBuffer.open(QBuffer::ReadWrite);

    QtSoapMessage message;
    message.setMethod("getAllUser");


    if(ssl)
    {
        isSecure->setToolTip(tr("The communication is safe by SSL"));
        isSecure->setPixmap(QPixmap(":/images/encrypted.png"));
        transport.setHost(host, true);
        connect(transport.networkAccessManager(),SIGNAL(sslErrors( QNetworkReply *, const QList<QSslError> & )),
                this, SLOT(sslErrors(QNetworkReply*,QList<QSslError>)), Qt::UniqueConnection);
    }
    else
    {
        isSecure->setToolTip(tr("The communication is not safe"));
        isSecure->setPixmap(QPixmap(":/images/decrypted.png"));
        transport.setHost(host);
    }

    transport.submitRequest(message, path+"/index.php?soap=horux&password=" + password + "&username=" + username);
    QApplication::processEvents();

    QtSoapMessage message2;
    message2.setMethod("getUserGroup");
    transport.submitRequest(message2, path+"/index.php?soap=horux&password=" + password + "&username=" + username);
    QApplication::processEvents();

}


void HoruxDesigner::loadSQLData() {

    if(dbase.isOpen()) {


        bool isNew = false;

        if(userCombo == NULL)
        {
            isNew = true;
            userCombo = new QComboBox();
        }
        else
        {
            userCombo->deleteLater();
            userCombo = new QComboBox();
        }

        if(sqlQuery == NULL) {
            sqlQuery = new QSqlQuery(dbase);
        }

        sqlQuery->prepare(sql);

        if(!sqlQuery->exec()) {
            QMessageBox::information(this,tr("Database sql error"),sqlQuery->lastError().text());
        } else {
              while(sqlQuery->next()) {
                if(column2>0)
                    userCombo->addItem(sqlQuery->value(column1).toString()+ " " + sqlQuery->value(column2).toString (), sqlQuery->value(primaryKeyColumn).toInt ());
                else
                    userCombo->addItem(sqlQuery->value(column1).toString(), sqlQuery->value(primaryKeyColumn).toInt ());
            }
        }


        ui->step->setText("1/" + QString::number(userCombo->count()));

        if(isNew)
            ui->toolBar->addSeparator();
        ui->toolBar->addWidget(userCombo);
        connect( userCombo, SIGNAL(currentIndexChanged(int)), this, SLOT(userChanged(int)));

        if(userCombo->count()>0)
            userChanged(0);

    }
}

void HoruxDesigner::loadCSVData() {

    bool isNew = false;

    if(userCombo == NULL)
    {
        isNew = true;
        userCombo = new QComboBox();
    }
    else
    {
        userCombo->deleteLater();
        userCombo = new QComboBox();
    }

    QFile f(file);
    if ( f.open(QIODevice::ReadOnly) ) { // file opened successfully
        QTextStream t( &f ); // use a text stream
        bool isFirstLine = true;
        int i=0;
        while ( !t.atEnd()  ) { // until end of file...
            QString line = t.readLine(); // line of text excluding '\n'

            if(line != "") {
                QStringList list = line.split(",");
                if(isFirstLine) {
                    isFirstLine = false;
                    QStringList listSimplified;
                    for(int i=0; i<list.count(); i++) {
                       listSimplified.append(list.at(i).simplified());
                    }
                    header = listSimplified;
                } else {
                    if(column2 < header.count() && column1 < header.count() && primaryKeyColumn<header.count()) {
                        if(column2>0)
                            userCombo->addItem(list.at(column1).simplified () + " " + list.at(column2).simplified (), list.at(primaryKeyColumn).simplified ());
                        else
                            userCombo->addItem(list.at(column1).simplified (), list.at(primaryKeyColumn).simplified());
                        userData[list.at(primaryKeyColumn).simplified().toInt()] = list;

                        i++;
                    }
                }
            }            
        }

    } else {
        QMessageBox::warning(this,tr("CSV file error"),tr("Not able to open the file"));

    }

    ui->step->setText("1/" + QString::number(userCombo->count()));

    if(isNew)
        ui->toolBar->addSeparator();
    ui->toolBar->addWidget(userCombo);
    connect( userCombo, SIGNAL(currentIndexChanged(int)), this, SLOT(userChanged(int)));

    if(userCombo->count()>0)
        userChanged(0);

}

void HoruxDesigner::sslErrors ( QNetworkReply * reply, const QList<QSslError> & errors )
{
    foreach(QSslError sslError, errors)
    {
        if(sslError.error() == QSslError::SelfSignedCertificate)
        {
            reply->ignoreSslErrors();
        }
    }
}

void HoruxDesigner::sslErrors ( const QList<QSslError> & errors )
{
    QNetworkReply *reply = qobject_cast<QNetworkReply *>(sender());

    foreach(QSslError sslError, errors)
    {
        if(sslError.error() == QSslError::SelfSignedCertificate)
        {
            reply->ignoreSslErrors();
        }
        else
            qDebug() << sslError;
    }
}

void HoruxDesigner::createToolBar()
{
    fontCombo = new QFontComboBox();
    fontSizeCombo = new QComboBox();
    fontSizeCombo->setEditable(true);
    for (int i = 8; i < 80; i = i + 2)
        fontSizeCombo->addItem(QString().setNum(i));

    QIntValidator *validator = new QIntValidator(2, 64, this);
    fontSizeCombo->setValidator(validator);
    connect(fontSizeCombo, SIGNAL(currentIndexChanged(const QString &)),
            this, SLOT(fontSizeChanged(const QString &)));


    connect(fontCombo, SIGNAL(currentFontChanged(const QFont &)),
            this, SLOT(currentFontChanged(const QFont &)));

    fontSizeCombo->setCurrentIndex(20);


    sceneScaleCombo = new QComboBox;
    QStringList scales;
    scales << tr("25%") << tr("50%") << tr("75%") << tr("100%") << tr("125%") << tr("150%")<< tr("200%")<< tr("250%");
    sceneScaleCombo->addItems(scales);
    sceneScaleCombo->setCurrentIndex(0);
    connect(sceneScaleCombo, SIGNAL(currentIndexChanged(const QString &)),
            this, SLOT(sceneScaleChanged(const QString &)));

    ui->toolBar->addWidget(fontCombo);
    ui->toolBar->addWidget(fontSizeCombo);
    ui->toolBar->addSeparator();
    ui->toolBar->addWidget(sceneScaleCombo);

    sceneScaleChanged("25%");

}

void HoruxDesigner::createAction()
{
    connect(ui->actionItalic, SIGNAL(triggered()),
            this, SLOT(handleFontChange()));

    connect(ui->actionBold, SIGNAL(triggered()),
            this, SLOT(handleFontChange()));

    connect(ui->actionUnderline, SIGNAL(triggered()),
            this, SLOT(handleFontChange()));

    connect(ui->actionDelete, SIGNAL(triggered()),
            this, SLOT(deleteItem()));

    connect(ui->actionBring_to_front, SIGNAL(triggered()),
            this, SLOT(bringToFront()));

    connect(ui->actionSend_to_back, SIGNAL(triggered()),
            this, SLOT(sendToBack()));

    connect(ui->actionNew, SIGNAL(triggered()),
            this, SLOT(newCard()));

    connect(ui->actionPrint_preview, SIGNAL(triggered()),
            this, SLOT(printPreview()));
    ui->actionPrint_preview->setEnabled(false);

    connect(ui->actionPrint, SIGNAL(triggered()),
            this, SLOT(print()));
    ui->actionPrint->setEnabled(false);

    connect(ui->actionPrint_selection, SIGNAL(triggered()),
            this, SLOT(printSelection()));
    ui->actionPrint_selection->setEnabled(false);

    connect(ui->actionPrint_setup, SIGNAL(triggered()),
            this, SLOT(printSetup()));

    connect(ui->actionExit, SIGNAL(triggered()),
            this, SLOT(exit()));

    connect(ui->actionSave, SIGNAL(triggered()),
            this, SLOT(save()));

    connect(ui->actionSave_as, SIGNAL(triggered()),
            this, SLOT(saveAs()));

    connect(ui->actionDatabase, SIGNAL(triggered()),
            this, SLOT(setDatabase()));

    connect(ui->actionOpen, SIGNAL(triggered()),
            this, SLOT(open()));

    connect(ui->actionAbout, SIGNAL(triggered()),
            this, SLOT(about()));

    connect(ui->actionAbout_Qt, SIGNAL(triggered()), qApp, SLOT(aboutQt()));

    // Recent files
    for (int i = 0; i < MaxRecentFiles; ++i) {
        recentFileActs[i] = new QAction(this);
        recentFileActs[i]->setVisible(false);
        connect(recentFileActs[i], SIGNAL(triggered()),
                this, SLOT(openRecentFile()));
    }

    for (int i = 0; i < MaxRecentFiles; ++i)
    {
        ui->menuRecent_files->addAction(recentFileActs[i]);

    }

    updateRecentFileActions();
}

void HoruxDesigner::initScene()
{
    scene = new CardScene(this);
    connect(scene, SIGNAL(itemInserted(QGraphicsItem *)),
            this, SLOT(itemInserted(QGraphicsItem *)));

    connect(scene, SIGNAL(textInserted(QGraphicsTextItem *)),
            this, SLOT(textInserted(QGraphicsTextItem *)));

    connect(scene, SIGNAL(itemSelected(QGraphicsItem *)),
            this, SLOT(itemSelected(QGraphicsItem *)));

    connect(scene, SIGNAL( selectionChanged()),
            this, SLOT(selectionChanged()));

    connect(scene, SIGNAL( itemMoved(QGraphicsItem *, QPointF)),
            this, SLOT(itemMoved(QGraphicsItem *, QPointF)));

    connect(scene, SIGNAL(itemChange()), this, SLOT(fileChange()));

    connect(scene, SIGNAL( mouseRelease() ),
            this, SLOT( mouseRelease() ));

    ui->graphicsView->setScene(scene);
    ui->graphicsView->setRenderHint(QPainter::Antialiasing);

    selectionChanged();
}

void HoruxDesigner::about()
{
    QMessageBox::about(this, tr("About Horux Card Designer"),tr("<h2>Horux Card Designer</h2><h3>") + HoruxDesigner::getVersion() + tr("</h3>Copyright 2010 Letux S&agrave;rl<br/>A Free Software released under the GNU/GPL License (GPL3)"));
}

void HoruxDesigner::readSoapResponse()
{

    const QtSoapMessage &response = transport.getResponse();
    if (response.isFault()) {
        QMessageBox::warning(this,tr("Horux webservice error"),tr("Not able to call the Horux GUI web service."));
        return;
    }

    if( response.method().name().name() == "getAllUserResponse") {

        bool isNew = false;

        if(userCombo == NULL)
        {
            isNew = true;
            userCombo = new QComboBox();
        }
        else
        {
            userCombo->deleteLater();
            userCombo = new QComboBox();
        }

        const QtSoapType &returnValue = response.returnValue();



        for(int i=0; i<returnValue.count(); i++ )
        {
            const QtSoapType &record =  returnValue[i];

            const QtSoapType &field_id =  record[0];
            const QtSoapType &field_name =  record[1];
            const QtSoapType &field_firstname =  record[2];
            const QtSoapType &field_picture =  record[4];
            QStringList data;

            for(int j=0; j<record.count(); j++) {
                const QtSoapType &field = record[j];
                data.append(field["value"].toString());

                if(!header.contains(field["key"].toString()))
                    header.append(field["key"].toString());
            }

            userData[field_id["value"].toInt()] = data;
            userCombo->addItem(field_name["value"].toString() + " " + field_firstname["value"].toString(), field_id["value"].toInt());

            connect(&pictureHttp, SIGNAL(finished(QNetworkReply*)), this, SLOT(httpRequestDone(QNetworkReply*)));

            if(ssl)
            {
                connect( &pictureHttp, SIGNAL( sslErrors( QNetworkReply *, const QList<QSslError> & ) ), this, SLOT( sslErrors(QNetworkReply *, const QList<QSslError> &)) ,Qt::UniqueConnection);
            }

            if(field_picture["value"].toString() != "") {

                if( !userPicture.contains( field_id["value"].toInt() ) ) {

                    userPicture[field_id["value"].toInt()] = new QBuffer;

                    QString pathPicture = currenFile.fileName().left(currenFile.fileName().length()-4) ;
                    QDir dir(pathPicture);

                    if(!dir.exists())
                    {
                        dir.mkdir(pathPicture );
                    }

                    if(!QFile::exists(pathPicture + "/" + field_picture["value"].toString())) {

                        QNetworkRequest request;
                        request.setUrl(QUrl( "http://" +  host + path + "/pictures/" + field_picture["value"].toString()));
                        qDebug() << "http://" +  host + path + "/pictures/" + field_picture["value"].toString();
                        request.setRawHeader("User-Agent", "Horux Card Designer");
                        QNetworkReply *reply = pictureHttp.get(request);
                        userPictureReply[reply] = field_id["value"].toInt();

                        connect(reply , SIGNAL(sslErrors(QList<QSslError>)),
                                this, SLOT(sslErrors(QList<QSslError>)));
                    } else {
                        QFile p( pathPicture + "/" + field_picture["value"].toString() );
                        p.open(QIODevice::ReadOnly);
                        userPicture[field_id["value"].toInt()]->setData(p.readAll());
                        p.close();
                    }

                }
            }

        }

        ui->step->setText("1/" + QString::number(userCombo->count()));

        if(isNew)
            ui->toolBar->addSeparator();
        ui->toolBar->addWidget(userCombo);
        connect( userCombo, SIGNAL(currentIndexChanged(int)), this, SLOT(userChanged(int)));

        if(userCombo->count()>0)
            userChanged(0);
    }

    if( response.method().name().name() == "getUserGroupResponse") {

        ui->userType->clear();

        const QtSoapType &returnValue = response.returnValue();

        for(int i=0; i<returnValue.count(); i++ )
        {
            const QtSoapType &record =  returnValue[i];

            const QtSoapType &field_id =  record[0];
            const QtSoapType &field_name =  record[1];

            ui->userType->addItem(field_name["value"].toString(), field_id["value"].toInt());

        }

        ui->addUser->setEnabled(true);
    }
}

void HoruxDesigner::userChanged(int index)
{    

    if(userCombo)
    {
        int userId = userCombo->itemData(index).toInt();

        currentUser = userId;

        if(engine == "HORUX") {

            int i = 0;
            foreach(QString s, header) {
               if(userData[userId].size()-1>=i)
                    userValue[s] = userData[userId].at(i);
               i++;
            }

            ui->name->setText(userValue["name"]);
            ui->firstName->setText(userValue["firstname"]);
            ui->street->setText(userValue["street_private"]);
            ui->zip->setText(userValue["zip_private"]);
            ui->city->setText(userValue["city_private"]);
            ui->email->setText(userValue["email_private"]);
            ui->phone->setText(userValue["phone_private"]);
            ui->birthday->setDate(QDate( userValue["birthday"].section("-",0,0).toInt() ,
                                         userValue["birthday"].section("-",1,1).toInt(),
                                         userValue["birthday"].section("-",2,2).toInt()));

            ui->userType->setCurrentIndex(ui->userType->findData(userValue["ugroup"]));


            if(userData.count() > 0 && userCombo && userCombo->count() > userCombo->currentIndex()+1 ) {
                ui->next->setEnabled(true);
            }

            userValue["__countIndex"] = QString::number(userCombo->currentIndex());

            if(userValue["picture"] != "")
            {
                pictureBuffer.close();
                pictureBuffer.setData(QByteArray());

                if(userPicture.contains(userId)) {
                    pictureBuffer.close();
                    pictureBuffer.setData(userPicture[userId]->buffer());
                }

                QPixmap p;
                p.loadFromData(userPicture[userId]->buffer());
                p = p.scaledToWidth(100);
                ui->picture->setPixmap(p);

                updatePrintPreview();

            }
            else
            {
                statusBar()->clearMessage();
                pictureBuffer.close();
                pictureBuffer.setData(QByteArray());
                pictureBuffer.open(QBuffer::ReadWrite);
                updatePrintPreview();
            }

        }

        if(engine == "CSV") {

            int i = 0;
            foreach(QString s, header) {
               userValue[s] = userData[userId].at(i);
               i++;
            }

            if(userData.count() > 0 && userCombo && userCombo->count() > userCombo->currentIndex()+1 ) {
                ui->next->setEnabled(true);
            }

            userValue["__countIndex"] = QString::number(userCombo->currentIndex());

            if(pictureColumn>=0) {
                pictureBuffer.close();
                pictureBuffer.setData(QByteArray());

                QString picturePath = userData[userId].at(pictureColumn).simplified();

                if(picturePath != "") {
                    QFile file(picturePath);

                    if(file.open(QIODevice::ReadOnly)) {
                         pictureBuffer.setData(file.readAll());
                    }
                }

                pictureBuffer.open(QBuffer::ReadWrite);

            }


            updatePrintPreview();
        }

        if(dbase.isOpen()) {
            sqlQuery->first();

            for(int i=0; i<index; i++) {
                sqlQuery->next();
            }

            QSqlRecord record = sqlQuery->record();

            for(int i=0; i<record.count(); i++) {
               userValue[record.fieldName(i)] = record.value(i).toString();
            }

            if(userCombo && userCombo->count() > userCombo->currentIndex()+1 ) {
                ui->next->setEnabled(true);
            }

            userValue["__countIndex"] = QString::number(userCombo->currentIndex());

            if(pictureColumn>=0) {
                pictureBuffer.close();
                pictureBuffer.setData(QByteArray());

                QString picturePath = record.value(pictureColumn).toString();

                if(picturePath != "") {
                    QFile file(picturePath);

                    if(file.open(QIODevice::ReadOnly)) {
                         pictureBuffer.setData(file.readAll());

                    }
                }

                pictureBuffer.open(QBuffer::ReadWrite);

            }

            updatePrintPreview();
        }
    }

    ui->step->setText(QString::number(index+1) + "/" + QString::number(userCombo->count()));


    if(index == 0)
        ui->back->setEnabled(false);
    else
        ui->back->setEnabled(true);

    if(index+1 == userCombo->count())
        ui->next->setEnabled(false);
    else
        ui->next->setEnabled(true);
}


void HoruxDesigner::httpRequestDone ( QNetworkReply* reply )
{
    statusBar()->clearMessage();


    if(reply->error() != QNetworkReply::NoError)
    {
        //QMessageBox::information(this, tr("Picture error"),tr("Not able to load the user picture"));
    }
    else {
        QByteArray b = reply->readAll();
        if(b.size() > 0 ) {
            int userId = userPictureReply[reply];
            userPicture[userId]->setData(b);

            QString pathPicture = currenFile.fileName().left(currenFile.fileName().length()-4) ;
            QFile p( pathPicture + "/" + userData[userId].at(4) );
            p.open(QIODevice::WriteOnly);
            p.write(b);
            p.close();

            updatePrintPreview();
            QApplication::processEvents();
        }

    }

    reply->deleteLater();
}

void HoruxDesigner::setCurrentFile(const QString &fileName)
{
    currenFile.setFileName(fileName);
    if (fileName.isEmpty())
        setWindowTitle(tr("Horux Card Designer - new card"));
    else
        setWindowTitle(tr("Horux Card Designer - %2").arg(strippedName(fileName)));

    QSettings settings("Letux", "HoruxCardDesigner",this);
    QStringList files = settings.value("recentFileList").toStringList();
    files.removeAll(fileName);
    files.prepend(fileName);
    while (files.size() > MaxRecentFiles)
        files.removeLast();

    settings.setValue("recentFileList", files);

    foreach (QWidget *widget, QApplication::topLevelWidgets()) {
        HoruxDesigner *mainWin = qobject_cast<HoruxDesigner *>(widget);
        if (mainWin)
            mainWin->updateRecentFileActions();
    }
}

void HoruxDesigner::updateRecentFileActions()
{
    QSettings settings("Letux", "HoruxCardDesigner",this);
    QStringList files = settings.value("recentFileList").toStringList();

    int numRecentFiles = qMin(files.size(), (int)MaxRecentFiles);

    for (int i = 0; i < numRecentFiles; ++i) {
        QString text = tr("&%1 %2").arg(i + 1).arg(strippedName(files[i]));
        recentFileActs[i]->setText(text);
        recentFileActs[i]->setData(files[i]);
        recentFileActs[i]->setVisible(true);
    }
    for (int j = numRecentFiles; j < MaxRecentFiles; ++j)
        recentFileActs[j]->setVisible(false);

    //separatorAct->setVisible(numRecentFiles > 0);
}

QString HoruxDesigner::strippedName(const QString &fullFileName)
{
    return QFileInfo(fullFileName).fileName();
}

void HoruxDesigner::openRecentFile()
{
    QAction *action = qobject_cast<QAction *>(sender());
    if (action)
    {
        if(dbase.isOpen())
            dbase.close();

        userData.clear();
        header.clear();
        userPicture.clear();
        userPictureReply.clear();

        ui->actionPrint_selection->setEnabled(true);
        ui->actionPrint_preview->setEnabled(true);
        ui->actionPrint->setEnabled(true);

        newCard();
        currenFile.setFileName(action->data().toString());

        QString xml;
        currenFile.open(QIODevice::ReadOnly);
        xml = currenFile.readAll();


        QDomDocument doc;
        doc.setContent(xml, false);
        QDomElement root = doc.documentElement();

        if( root.tagName() != "HoruxCardDesigner")
        {
            return;
        }

        QDomNode node = root.firstChild();

        while(!node.isNull())
        {
            if(node.toElement().tagName() == "PrintedUser")
            {
               QString pu = node.toElement().text();
               printedUser = pu.split(",");
            }

            if(node.toElement().tagName() == "Database") {
                QDomNode database = node.firstChild();

                while(!database.isNull()) {
                    if(database.toElement().tagName() == "host") {
                        host = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "username") {
                        username = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "password") {
                        password = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "path") {
                        path = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "databaseName") {
                        databaseName = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "ssl") {
                        ssl = database.toElement().text().toInt();
                    }
                    if(database.toElement().tagName() == "engine") {
                        engine = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "file") {
                        file = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "sql") {
                        sql = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "primaryKeyColumn") {
                        primaryKeyColumn = database.toElement().text().toInt();
                    }
                    if(database.toElement().tagName() == "column1") {
                        column1 = database.toElement().text().toInt();
                    }
                    if(database.toElement().tagName() == "column2") {
                        column2 = database.toElement().text().toInt();
                    }
                    if(database.toElement().tagName() == "pictureColumn") {
                        pictureColumn = database.toElement().text().toInt();
                    }

                    database = database.nextSibling();
                }
            }

            node = node.nextSibling();
        }

        loadData();

        scene->loadScene(xml);
        currenFile.close();
        selectionChanged();
        setWindowTitle("Horux Card Designer - " + currenFile.fileName());
        updatePrintPreview();
    }
}

void HoruxDesigner::open()
{
    QString fileName = QFileDialog::getOpenFileName(this,
                                                    tr("Open an Horux Card Designer file"),
                                                    "",
                                                    tr("Horux Card Designer (*.xml)"));
    if (!fileName.isEmpty())
    {

        if(dbase.isOpen())
            dbase.close();
        userData.clear();
        header.clear();
        userPicture.clear();
        userPictureReply.clear();

        ui->actionPrint_selection->setEnabled(true);
        ui->actionPrint_preview->setEnabled(true);
        ui->actionPrint->setEnabled(true);


        setCurrentFile(fileName);

        newCard();

        currenFile.setFileName(fileName);

        QString xml;
        currenFile.open(QIODevice::ReadOnly);
        QTextStream data(&currenFile);
        data.setCodec("ISO 8859-1");
        xml = data.readAll();

        QDomDocument doc;
        doc.setContent(xml, false);
        QDomElement root = doc.documentElement();

        if( root.tagName() != "HoruxCardDesigner")
        {
            return;
        }

        QDomNode node = root.firstChild();

        while(!node.isNull())
        {
            if(node.toElement().tagName() == "PrintedUser")
            {
               QString pu = node.toElement().text();
               printedUser = pu.split(",");
            }

            if(node.toElement().tagName() == "Database") {
                QDomNode database = node.firstChild();

                while(!database.isNull()) {
                    if(database.toElement().tagName() == "host") {
                        host = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "username") {
                        username = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "password") {
                        password = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "path") {
                        path = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "databaseName") {
                        databaseName = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "ssl") {
                        ssl = database.toElement().text().toInt();
                    }
                    if(database.toElement().tagName() == "engine") {
                        engine = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "file") {
                        file = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "sql") {
                        sql = database.toElement().text();
                    }
                    if(database.toElement().tagName() == "primaryKeyColumn") {
                        primaryKeyColumn = database.toElement().text().toInt();
                    }
                    if(database.toElement().tagName() == "column1") {
                        column1 = database.toElement().text().toInt();
                    }
                    if(database.toElement().tagName() == "column2") {
                        column2 = database.toElement().text().toInt();
                    }
                    if(database.toElement().tagName() == "pictureColumn") {
                        pictureColumn = database.toElement().text().toInt();
                    }

                    database = database.nextSibling();
                }
            }


            node = node.nextSibling();
        }

        loadData();

        scene->loadScene(xml);
        currenFile.close();
        selectionChanged();
        setWindowTitle("Horux Card Designer - " + currenFile.fileName());

        updatePrintPreview();
    }
}

void HoruxDesigner::setDatabase()
{
    DatabaseConnection dlg(this);


    dlg.setHost(host);
    dlg.setUsername(username);
    dlg.setPassword(password);
    dlg.setPath(path);
    dlg.setDatabase(databaseName);
    dlg.setSSL(ssl);
    dlg.setEngine(engine);
    dlg.setFile(file);
    dlg.setPrimaryKey(primaryKeyColumn);
    dlg.setColumn1(column1);
    dlg.setColumn2(column2);
    dlg.setPictureColumn(pictureColumn);
    dlg.setSqlRequest(sql);

    if(dlg.exec() == QDialog::Accepted)
    {
        isSecure->setToolTip(tr("The communication is not safe"));
        isSecure->setPixmap(QPixmap(":/images/decrypted.png"));

        if( host != dlg.getHost() ||
            username != dlg.getUsername() ||
            password != dlg.getPassword() ||
            path != dlg.getPath() ||
            databaseName != dlg.getDatabase() ||
            ssl != dlg.getSSL() ||
            file != dlg.getFile() ||
            engine != dlg.getEngine() ||
            primaryKeyColumn != dlg.getPrimaryKey() ||
            column1 != dlg.getColumn1() ||
            column2 != dlg.getColumn2() ||
            pictureColumn != dlg.getPictureColumn() ||
            sql != dlg.getSqlRequest()
            )
        {
            fileChange();
        }

        host = dlg.getHost();
        username = dlg.getUsername();
        password = dlg.getPassword();
        path = dlg.getPath();
        databaseName = dlg.getDatabase();
        ssl = dlg.getSSL();
        file = dlg.getFile();
        engine = dlg.getEngine();
        primaryKeyColumn = dlg.getPrimaryKey();
        column1 = dlg.getColumn1();
        column2 = dlg.getColumn2();
        pictureColumn = dlg.getPictureColumn();
        sql = dlg.getSqlRequest();

        loadData();
    }
}

void HoruxDesigner::save()
{
    if(currenFile.fileName() == "")
    {
        saveAs();
        return;
    }

    fileChanged = false;

    currenFile.open(QIODevice::WriteOnly);
    QTextStream data(&currenFile);
    data.setCodec("ISO 8859-1");

    QDomDocument xml;
    QDomElement root = xml.createElement ( "HoruxCardDesigner" );

    // Save the printed card
    QDomElement newElement = xml.createElement( "PrintedUser");
    QDomText text =  xml.createTextNode( printedUser.join(",") );
    newElement.appendChild(text);
    root.appendChild(newElement);

    //save the db connection
    QDomElement database = xml.createElement( "Database");

    newElement = xml.createElement( "host");
    text =  xml.createTextNode( host );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "username");
    text =  xml.createTextNode( username );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "password");
    text =  xml.createTextNode( password );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "path");
    text =  xml.createTextNode( path );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "databaseName");
    text =  xml.createTextNode( databaseName );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "ssl");
    text =  xml.createTextNode( QString::number(ssl) );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "engine");
    text =  xml.createTextNode( engine );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "file");
    text =  xml.createTextNode( file );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "sql");
    text =  xml.createTextNode( sql );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "primaryKeyColumn");
    text =  xml.createTextNode( QString::number(primaryKeyColumn) );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "column1");
    text =  xml.createTextNode( QString::number(column1) );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "column2");
    text =  xml.createTextNode( QString::number(column2) );
    newElement.appendChild(text);
    database.appendChild(newElement);

    newElement = xml.createElement( "pictureColumn");
    text =  xml.createTextNode( QString::number(pictureColumn) );
    newElement.appendChild(text);
    database.appendChild(newElement);


    database.appendChild(newElement);

    root.appendChild(database);



    //save the scene
    QDomElement card = qgraphicsitem_cast<CardItem*>(scene->getCardItem())->getXmlItem(xml);

    root.appendChild ( card );


    foreach (QGraphicsItem *item, scene->getCardItem()->childItems())
    {
        switch(item->type())
        {
        case QGraphicsItem::UserType + 3:
            card.appendChild(qgraphicsitem_cast<CardTextItem*>(item)->getXmlItem(xml));
            break;
        case QGraphicsItem::UserType + 4:
            card.appendChild(qgraphicsitem_cast<PixmapItem*>(item)->getXmlItem(xml));
            break;
        }
    }

    xml.appendChild ( root );

    QDomNode xmlNode =  xml.createProcessingInstruction ( "xml", "version=\"1.0\" encoding=\"ISO 8859-1\"" );
    xml.insertBefore ( xmlNode, xml.firstChild() );

    data << xml.toString() ;


    currenFile.close();

    setWindowTitle("Horux Card Designer - " + currenFile.fileName());

    ui->actionSave->setEnabled(false);
}

void HoruxDesigner::saveAs()
{
    QString name = QFileDialog::getSaveFileName(this,tr("Save the card"),"",tr("Horux Card Designer (*.xml)"));

    if(name != "")
    {
        currenFile.setFileName( name );
        save();
    }
}


void HoruxDesigner::exit()
{
    if(fileChanged) {
        if(QMessageBox::information(this,tr("File changed"),tr("The file was modified, do you want to save it before to close Horux Card Designer?"),QMessageBox::Save,QMessageBox::No) == QMessageBox::Save) {
            save();            
        }
    }

    fileChanged = false;

    close ();
}


void HoruxDesigner::printSetup()
{
    QPageSetupDialog dlg(printer, this);

    dlg.exec();
}

void HoruxDesigner::printPreview()
{
    scene->clearSelection ();

    QPointF cardPos = scene->getCardItem()->pos();

    scene->getCardItem()->setPrintingMode(true, pictureBuffer, userValue);
    scene->getCardItem()->setPos(0,0);

    sceneScaleChanged("25%");
    QRectF cardRect = scene->getCardItem()->boundingRect();
    cardRect.setHeight(cardRect.height() * 0.25);
    cardRect.setWidth(cardRect.width() * 0.25);

    QPixmap pixmap(cardRect.size().toSize());
    pixmap.fill( Qt::white );
    QPainter painter(&pixmap);
    painter.setRenderHint(QPainter::Antialiasing, true);
    painter.setRenderHint(QPainter::SmoothPixmapTransform, true);
    ui->graphicsView->render(&painter, QRectF(0,0,pixmap.size().width(),pixmap.size().height()), cardRect.toRect(), Qt::KeepAspectRatio );
    painter.end();

    PrintPreview dlg(pixmap, this);

    scene->getCardItem()->setPrintingMode( false, pictureBuffer, userValue );
    scene->getCardItem()->setPos(cardPos);
    sceneScaleChanged(sceneScaleCombo->currentText());



    if (dlg.exec() != QDialog::Rejected )
    {
        print();
        emit printCardOk();
    }


}

void HoruxDesigner::print()
{
    if(engine == "") return;


    printer->setOrientation(( QPrinter::Orientation)scene->getCardItem()->getFormat());

    printer->setPaperSize(scene->getCardItem()->getSizeMm(),QPrinter::Millimeter);
    printer->setPageMargins(0,0,0,0,QPrinter::Millimeter);

    if (QPrintDialog(printer).exec() == QDialog::Accepted)
    {

        scene->clearSelection ();

        QPointF cardPos = scene->getCardItem()->pos();

        if(userPicture.contains(currentUser)) {
            pictureBuffer.setData(userPicture[currentUser]->buffer());
        }


        scene->getCardItem()->setPrintingMode( true, pictureBuffer, userValue );
        scene->getCardItem()->setPos(0,0);

        QRectF cardRect = scene->getCardItem()->boundingRect();

        QPixmap screenshot(printer->pageRect().size());
        screenshot.fill( Qt::white );
        QPainter painterPix(&screenshot);

        scene->render(&painterPix, printer->pageRect(), cardRect.toRect(), Qt::KeepAspectRatio);

        QPainter painter(printer);
        painter.setRenderHint(QPainter::Antialiasing, true);
        painter.setRenderHint(QPainter::SmoothPixmapTransform, true);
        painter.begin(printer);
        painter.drawPixmap(0,0,screenshot);
        painter.end();

        /*QPainter painter(printer);
        painter.setRenderHint(QPainter::Antialiasing, true);
        painter.setRenderHint(QPainter::SmoothPixmapTransform, true);
        scene->render(&painter, printer->pageRect(), cardRect.toRect(), Qt::KeepAspectRatio);

        QApplication::processEvents();*/

        scene->getCardItem()->setPrintingMode( false, pictureBuffer, userValue );
        scene->getCardItem()->setPos(cardPos);

        // increment all counter in the card scene
        scene->getCardItem()->incrementCounter();

        int userId = userCombo->itemData(userCombo->currentIndex()).toInt();

        if(!printedUser.contains(QString::number(userId)))
            printedUser.append(QString::number(userId));

        fileChange();


        updatePrintPreview();
    }
}

void HoruxDesigner::printSelection() {

    PrintSelection dlg;

    if(engine == "") return;

    dlg.setPrintedUser(printedUser);

    if(engine == "CSV") {
        dlg.setCSVData(header,userData);
    }
    else
        if(engine == "HORUX")
            dlg.setHoruxData(header, userData);
        else
            if(engine != "NOT_USED")
                dlg.setSQLData(sqlQuery, primaryKeyColumn);

    if(dlg.exec() == QDialog::Accepted) {

        QStringList printCardFor = dlg.getCheckedUser(primaryKeyColumn);

        scene->clearSelection ();

        printer->setOrientation(( QPrinter::Orientation)scene->getCardItem()->getFormat());

        printer->setPaperSize(scene->getCardItem()->getSizeMm(),QPrinter::Millimeter);
        printer->setPageMargins(0,0,0,0,QPrinter::Millimeter);

        QPointF cardPos = scene->getCardItem()->pos();

        if( printCardFor.count() >> 0 &&  QPrintDialog(printer).exec() == QDialog::Accepted)
        {
            foreach(QString userID, printCardFor) {

                int index = userCombo->findData(userID.toInt());

                userCombo->setCurrentIndex(index);

                QApplication::processEvents();
                QApplication::flush();

                if(userPicture.contains(currentUser)) {
                    pictureBuffer.setData(userPicture[currentUser]->buffer());
                }


                scene->getCardItem()->setPrintingMode( true, pictureBuffer, userValue );
                scene->getCardItem()->setPos(0,0);

                QRectF cardRect = scene->getCardItem()->boundingRect();

                QPixmap screenshot(printer->pageRect().size());
                screenshot.fill( Qt::white );
                QPainter painterPix(&screenshot);

                scene->render(&painterPix, printer->pageRect(), cardRect.toRect(), Qt::KeepAspectRatio);

                QPainter painter(printer);
                painter.setRenderHint(QPainter::Antialiasing, true);
                painter.setRenderHint(QPainter::SmoothPixmapTransform, true);
                painter.begin(printer);
                painter.drawPixmap(0,0,screenshot);
                painter.end();

                // increment all counter in the card scene
                scene->getCardItem()->incrementCounter();

                // add the user as a printed user
                if(!printedUser.contains(userID))
                    printedUser.append(userID);

                fileChange();
            }

            scene->getCardItem()->setPrintingMode( false, pictureBuffer, userValue );
            scene->getCardItem()->setPos(cardPos);

            // increment all counter in the card scene
            scene->getCardItem()->incrementCounter();

            int userId = userCombo->itemData(userCombo->currentIndex()).toInt();

            if(!printedUser.contains(QString::number(userId)))
                printedUser.append(QString::number(userId));

            fileChange();


            updatePrintPreview();
        }
    }
}

void HoruxDesigner::newCard()
{
    host = "";
    username = "";
    password = "";
    path = "";
    databaseName = "";
    ssl = false;
    engine = "";
    file = "";
    sql = "";
    primaryKeyColumn = 0;
    column1 = 1;
    column2 = 2;
    pictureColumn = 3;

    foreach (QGraphicsItem *item, scene->items()) {
        if (item->type() !=  QGraphicsItem::UserType+1) {
            scene->removeItem(item);
        }
    }

    scene->reset();

}

void HoruxDesigner::deleteItem()
{
    foreach (QGraphicsItem *item, scene->selectedItems()) {
        if (item->type() !=  QGraphicsItem::UserType+1) {

            if(item->type() == QGraphicsItem::UserType + 3  && qgraphicsitem_cast<CardTextItem *>(item)->textInteractionFlags() == Qt::TextEditorInteraction) // text
            {
                // do nothing
            }
            else {
                scene->removeItem(item);
                fileChange();
            }
        }
    }
}

void HoruxDesigner::bringToFront()
{
    if (scene->selectedItems().isEmpty())
        return;

    QGraphicsItem *selectedItem = scene->selectedItems().first();
    QList<QGraphicsItem *> overlapItems = selectedItem->collidingItems();

    qreal zValue = selectedItem->zValue();

    foreach (QGraphicsItem *item, overlapItems) {
        if (item->zValue() >= zValue )
        {
            zValue = item->zValue() + 0.1;
        }
    }
    selectedItem->setZValue(zValue);
}

void HoruxDesigner::sendToBack()
{
    if (scene->selectedItems().isEmpty())
        return;

    QGraphicsItem *selectedItem = scene->selectedItems().first();
    QList<QGraphicsItem *> overlapItems = selectedItem->collidingItems();

    qreal zValue = selectedItem->zValue();

    foreach (QGraphicsItem *item, overlapItems) {
        if (item->zValue() <= zValue)
        {
            zValue = item->zValue() - 0.1;
        }
    }
    selectedItem->setZValue(zValue);
}


void HoruxDesigner::currentFontChanged(const QFont &)
{
    handleFontChange();
}

void HoruxDesigner::fontSizeChanged(const QString &)
{
    handleFontChange();
}


void HoruxDesigner::handleFontChange()
{
    QFont font = fontCombo->currentFont();
    font.setPointSize(fontSizeCombo->currentText().toInt());
    font.setWeight(ui->actionBold->isChecked() ? QFont::Bold : QFont::Normal);
    font.setItalic(ui->actionItalic->isChecked());
    font.setUnderline(ui->actionUnderline->isChecked());

    scene->setFont(font);
}


void HoruxDesigner::sceneScaleChanged(const QString &scale)
{
    double newScale = scale.left(scale.indexOf(tr("%"))).toDouble() / 100.0;
    QMatrix oldMatrix = ui->graphicsView->matrix();
    ui->graphicsView->resetMatrix();
    ui->graphicsView->translate(oldMatrix.dx(), oldMatrix.dy());
    ui->graphicsView->scale(newScale, newScale);


}

void HoruxDesigner::setParamView(QGraphicsItem *item)
{
    switch(item->type())
    {
    case QGraphicsItem::UserType+1: //card
        {
            CardItem *card = qgraphicsitem_cast<CardItem *>(item);
            if(card)
            {
                if(!cardPage)
                {
                    cardPage = new CardPage(ui->widget);
                    connect(cardPage->sizeCb, SIGNAL(currentIndexChanged ( int )), card, SLOT(setSize(int)));
                    connect(cardPage->orientation, SIGNAL(currentIndexChanged ( int )), card, SLOT(setFormat(int)));
                    connect(cardPage->bkgColor, SIGNAL(textChanged(const QString & )), card, SLOT(setBkgColor(const QString &)));
                    connect(cardPage->bkgPicture, SIGNAL(textChanged(const QString & )), card, SLOT(setBkgPixmap(QString)));

                    connect(cardPage->gridDraw, SIGNAL(currentIndexChanged ( int )), card, SLOT(viewGrid(int)));
                    connect(cardPage->gridSize, SIGNAL(valueChanged  ( int )), card, SLOT(setGridSize(int)));
                    connect(cardPage->gridAlign, SIGNAL(currentIndexChanged ( int )), card, SLOT(alignGrid(int)));
                    connect(cardPage->locked, SIGNAL(stateChanged(int)),  card, SLOT(setLocked(int)) );

                    connect(card, SIGNAL(itemChange()), this, SLOT(fileChange()));
                }

                cardPage->sizeCb->setCurrentIndex(card->getSize());
                cardPage->orientation->setCurrentIndex(card->getFormat());

                if (card->bkgColor.isValid()) {
                    cardPage->color = card->bkgColor;
                    cardPage->bkgColor->setText(card->bkgColor.name());
                    cardPage->bkgColor->setStyleSheet("background-color: " + card->bkgColor.name() + ";");
                }

                cardPage->locked->setChecked(card->isLocked);

                cardPage->bkgPicture->setText(card->bkgFile);
                cardPage->gridAlign->setCurrentIndex((int)card->isGridAlign);
                cardPage->gridDraw->setCurrentIndex((int)card->isGrid);
                cardPage->gridSize->setValue(card->gridSize);

                if(textPage)
                    textPage->hide();

                if(pixmapPage)
                    pixmapPage->hide();


                cardPage->show();
            }
        }
        break;
    case QGraphicsItem::UserType+3: //text
        {
            CardTextItem *textItem = qgraphicsitem_cast<CardTextItem *>(item);

            if(textItem)
            {
                if(textPage)
                {
                    delete textPage;
                    textPage = NULL;
                }

                if(!textPage)
                {
                    textPage = new TextPage(ui->widget);

                    connect(textPage->name, SIGNAL(textChanged ( const QString & )), textItem, SLOT(setName(const QString &)));
                    connect(textPage, SIGNAL(changeFont(const QFont &)), textItem, SLOT(fontChanged(const QFont &)));
                    connect(textPage, SIGNAL(changeColor ( const QColor & )), textItem, SLOT(colorChanged(const QColor &)));
                    connect(textPage->rotation, SIGNAL(valueChanged(QString)), textItem, SLOT(rotationChanged(const QString &)));
                    connect(textPage->source, SIGNAL(currentIndexChanged ( int )), textItem, SLOT(sourceChanged(int)));
                    connect(textPage->top, SIGNAL(valueChanged(QString)), textItem, SLOT(topChanged(const QString &)));
                    connect(textPage->left, SIGNAL(valueChanged(QString)), textItem, SLOT(leftChanged(const QString &)));
                    connect(textPage->alignment, SIGNAL(currentIndexChanged ( int )), textItem, SLOT(alignmentChanged(int)));
                    connect(textPage, SIGNAL(changePrintCounter(int,int,int)), textItem, SLOT(setPrintCounter(int,int,int)));
                    connect(textPage, SIGNAL(changeFormat(int,int, int, QString, QString)),  textItem, SLOT(setFormat(int,int,int,QString, QString)));
                    connect(textPage->locked, SIGNAL(stateChanged(int)),  textItem, SLOT(setLocked(int)) );

                    connect(textItem, SIGNAL(itemChange()), this, SLOT(fileChange()));

                    textPage->setFormat(textItem->format,textItem->format_digit,textItem->format_decimal,textItem->format_date, textItem->format_sourceDate);

                    textPage->name->setText(textItem->name);

                    textPage->font = textItem->font();
                    textPage->fontText->setText(textItem->font().family());

                    if (textItem->defaultTextColor().isValid()) {
                        textPage->color = textItem->defaultTextColor();
                        textPage->colorText->setText(textItem->defaultTextColor().name());
                        textPage->colorText->setStyleSheet("background-color: " + textItem->defaultTextColor().name() + ";");
                    }

                    textPage->widthRect->setText(QString::number(textItem->boundingRect().width()));
                    textPage->heightRect->setText(QString::number(textItem->boundingRect().height()));

                    textPage->rotation->setValue(textItem->rotation);
                    textPage->top->setValue(textItem->pos().y());
                    textPage->left->setValue(textItem->pos().x());
                    textPage->alignment->setCurrentIndex(textItem->alignment);

                    textPage->locked->setChecked(textItem->isLocked);
                    ui->actionDelete->setEnabled(!textItem->isLocked);


                    textPage->source->setCurrentIndex( textItem->source );
                    textPage->setSource(textItem->source);

                    textPage->connectDataSource();
                    textPage->setPrintCounter(textItem->initialValue, textItem->increment, textItem->digits);
                }

                if(cardPage)
                    cardPage->hide();
                if(pixmapPage)
                    pixmapPage->hide();


                textPage->show();

            }
        }
        break;
    case QGraphicsItem::UserType+4: //Pixmap
        {
            PixmapItem *pixmapItem = qgraphicsitem_cast<PixmapItem *>(item);

            if(pixmapItem)
            {
                if(pixmapPage)
                {
                    delete pixmapPage;
                    pixmapPage = NULL;
                }

                if(!pixmapPage)
                {
                    pixmapPage = new PixmapPage(ui->widget);

                    pixmapPage->name->setText(pixmapItem->name);
                    pixmapPage->file->setText(pixmapItem->file);
                    pixmapPage->widthEdit->setValue(pixmapItem->boundingRect().width());

                    connect(pixmapPage->name, SIGNAL(textChanged ( const QString & )), pixmapItem, SLOT(setName(const QString &)));
                    connect(pixmapPage->file, SIGNAL(textChanged(const QString & )), pixmapItem, SLOT(setPixmapFile(QString)));
                    connect(pixmapPage->source, SIGNAL(currentIndexChanged ( int )), pixmapItem, SLOT(sourceChanged(int)));
                    connect(pixmapPage, SIGNAL(newPicture(QByteArray)), pixmapItem, SLOT(setHoruxPixmap(QByteArray )));
                    connect(pixmapPage->widthEdit, SIGNAL(valueChanged ( const QString & )), pixmapItem, SLOT(setWidth(const QString &)));

                    connect(pixmapPage->top, SIGNAL(valueChanged(QString)), pixmapItem, SLOT(topChanged(const QString &)));
                    connect(pixmapPage->left, SIGNAL(valueChanged(QString)), pixmapItem, SLOT(leftChanged(const QString &)));

                    connect(pixmapPage->locked, SIGNAL(stateChanged(int)),  pixmapItem, SLOT(setLocked(int)) );

                    connect(pixmapItem, SIGNAL(itemChange()), this, SLOT(fileChange()));

                    pixmapPage->top->setValue(pixmapItem->pos().y());
                    pixmapPage->left->setValue(pixmapItem->pos().x());

                    pixmapPage->locked->setChecked(pixmapItem->isLocked);
                    ui->actionDelete->setEnabled(!pixmapItem->isLocked);

                    pixmapPage->source->setCurrentIndex( pixmapItem->source );
                    pixmapPage->connectDataSource();
                }

                if(textPage)
                    textPage->hide();
                if(cardPage)
                    cardPage->hide();


                pixmapPage->show();

            }

        }
        break;
    }
}

void HoruxDesigner::resizeEvent ( QResizeEvent * )
{
    QPointF p = scene->getCardItem()->pos();
    QRectF r = scene->getCardItem()->boundingRect();

    r.setWidth( r.width() + p.x() + 50 );
    r.setHeight( r.height() + p.y() + 50 );

    scene->setSceneRect(r);
}

void HoruxDesigner::createToolBox()
{
    ui->toolbox->removeItem(0);

    buttonGroup = new QButtonGroup;
    buttonGroup->setExclusive(false);
    connect(buttonGroup, SIGNAL(buttonClicked(int)),
            this, SLOT(buttonGroupClicked(int)));

    QGridLayout *layout = new QGridLayout;

    //Text
    QToolButton *textButton = new QToolButton;
    textButton->setCheckable(true);
    buttonGroup->addButton(textButton, InsertTextButton);

    textButton->setIcon(QIcon(QPixmap(":/images/textpointer.png")
                              .scaled(30, 30)));
    textButton->setIconSize(QSize(50, 50));
    QGridLayout *textLayout = new QGridLayout;
    textLayout->addWidget(textButton, 0, 0, Qt::AlignHCenter);
    textLayout->addWidget(new QLabel(tr("Text")), 1, 0, Qt::AlignCenter);
    QWidget *textWidget = new QWidget;
    textWidget->setLayout(textLayout);
    layout->addWidget(textWidget, 1, 1);


    //Image
    QToolButton *imageButton = new QToolButton;
    imageButton->setCheckable(true);
    buttonGroup->addButton(imageButton, InsertImageButton);

    imageButton->setIcon(QIcon(QPixmap(":/images/gadu.png")));
    imageButton->setIconSize(QSize(50, 50));
    QGridLayout *imageLayout = new QGridLayout;
    imageLayout->addWidget(imageButton, 0, 0, Qt::AlignHCenter);
    imageLayout->addWidget(new QLabel(tr("Picture")), 1, 0, Qt::AlignCenter);
    QWidget *imageWidget = new QWidget;
    imageWidget->setLayout(imageLayout);
    layout->addWidget(imageWidget, 1, 2);



    layout->setRowStretch(3, 10);
    layout->setColumnStretch(2, 10);

    QWidget *itemWidget = new QWidget;
    itemWidget->setLayout(layout);


    ui->toolbox->setSizePolicy(QSizePolicy(QSizePolicy::Maximum, QSizePolicy::Ignored));
    ui->toolbox->setMinimumWidth(itemWidget->sizeHint().width());
    ui->toolbox->insertItem(1, itemWidget, tr("Object"));
}

void HoruxDesigner::buttonGroupClicked(int id)
{
    QList<QAbstractButton *> buttons = buttonGroup->buttons();
    foreach (QAbstractButton *button, buttons) {
        if (buttonGroup->button(id) != button)
            button->setChecked(false);
    }
    if (id == InsertTextButton) {
        scene->setMode(CardScene::InsertText);
    }

    if(id == InsertImageButton) {
        scene->setMode(CardScene::InsertPicture);
    }
}

void HoruxDesigner::itemInserted(QGraphicsItem *)
{
    scene->setMode(CardScene::MoveItem);
    buttonGroup->button(InsertImageButton)->setChecked(false);
}

void HoruxDesigner::textInserted(QGraphicsTextItem *)
{
    buttonGroup->button(InsertTextButton)->setChecked(false);
    scene->setMode(CardScene::MoveItem);


}

void HoruxDesigner::itemSelected(QGraphicsItem *)
{
    /* CardTextItem *textItem =
        qgraphicsitem_cast<CardTextItem *>(item);*/
}

void HoruxDesigner::selectionChanged()
{
    if (scene->selectedItems().isEmpty() || scene->selectedItems().count() > 1 )
    {
        setParamView(scene->getCardItem());

        return;
    }


    setParamView(scene->selectedItems().at(0));

}

void HoruxDesigner::itemMoved(QGraphicsItem *item, QPointF pos)
{
    if(item && scene->selectedItems().count() > 0)
    {
        if(item->type() == QGraphicsItem::UserType + 3)
        {
            if(textPage)
            {
                textPage->top->setValue(item->pos().y());
                textPage->left->setValue(item->pos().x());
            }
        }
        if(item->type() == QGraphicsItem::UserType + 4)
        {
            if(pixmapPage)
            {
                pixmapPage->top->setValue(item->pos().y());
                pixmapPage->left->setValue(item->pos().x());
            }
        }

        if(pos.y() != 0 && pos.x() != 0) {
            if(item->pos() != pos)
                fileChange();
        }
    }


}

void HoruxDesigner::updatePrintPreview()
{

    QList<QGraphicsItem *> listSelectedItems = scene->selectedItems();

    scene->clearSelection ();

    QPointF cardPos = scene->getCardItem()->pos();

    if(userPicture.contains(currentUser)) {
        pictureBuffer.setData(userPicture[currentUser]->buffer());
    }

    scene->getCardItem()->setPrintingMode(true, pictureBuffer, userValue);
    scene->getCardItem()->setPos(0,0);
    sceneScaleChanged("25%");       

    QRectF cardRect = scene->getCardItem()->boundingRect();

    cardRect.setHeight(cardRect.height() *  0.25);
    cardRect.setWidth(cardRect.width() *  0.25);

    QPixmap pixmap(cardRect.size().toSize());
    pixmap.fill( Qt::white );
    QPainter painter(&pixmap);

    if(!painter.isActive()) return;

    painter.setRenderHint(QPainter::Antialiasing, true);
    painter.setRenderHint(QPainter::SmoothPixmapTransform, true);

    ui->graphicsView->render(&painter, QRectF(0,0,pixmap.size().width(),pixmap.size().height()), cardRect.toRect(), Qt::KeepAspectRatio );
    painter.end();

    if(scenePreview == NULL) {
        scenePreview = new QGraphicsScene(this);
        scenePreview->clear();
        scenePreview->setBackgroundBrush(Qt::gray);
        ui->graphicViewPreview->setScene(scenePreview);
    } else {
        scenePreview->clear();
    }

    scenePreview->addPixmap (pixmap);

    scene->getCardItem()->setPrintingMode( false, pictureBuffer, userValue );
    scene->getCardItem()->setPos(cardPos);
    sceneScaleChanged(sceneScaleCombo->currentText());

    foreach(QGraphicsItem *item, listSelectedItems) {
        item->setSelected(true);
    }
}

void HoruxDesigner::nextRecord() {
    int index = userCombo->currentIndex();

    if(userCombo->count() > index+1) {
        userCombo->setCurrentIndex(index+1);
        ui->back->setEnabled(true);

        if(index+2 == userCombo->count())
           ui->next->setEnabled(false);

        ui->step->setText(QString::number(userCombo->currentIndex()+1) + "/" + QString::number(userCombo->count()));
    }

}

void HoruxDesigner::backRecord() {
    int index = userCombo->currentIndex();

    if(index-1 >= 0) {
        userCombo->setCurrentIndex(index-1);

        if(index-1 == 0)
            ui->back->setEnabled(false);
        ui->step->setText(QString::number(userCombo->currentIndex()+1) + "/" + QString::number(userCombo->count()));
    }
}

void HoruxDesigner::mouseRelease() {
    updatePrintPreview();


}

void HoruxDesigner::fileChange() {
    setWindowTitle("Horux Card Designer - " + currenFile.fileName() + " *");
    ui->actionSave->setEnabled(true);
    fileChanged = true;
}

void HoruxDesigner::closeEvent ( QCloseEvent * event ) {
    if(fileChanged) {
        if(QMessageBox::information(this,tr("File changed"),tr("The file was modified, do you want to save it before to close Horux Card Designer?"),QMessageBox::Save,QMessageBox::No) == QMessageBox::Save) {
            save();
        }
    }

    fileChanged = false;

    QMainWindow::closeEvent(event);
}
