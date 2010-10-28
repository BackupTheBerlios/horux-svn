#include <QtGui>
#include <QSslError>
#include <QNetworkReply>
#include "horuxdesigner.h"
#include "ui_horuxdesigner.h"
#include "carditemtext.h"
#include "carditem.h"
#include "confpage.h"
#include "printpreview.h"
#include "horuxdialog.h"
#include "databaseconnection.h"

const int InsertTextButton = 10;
const int InsertImageButton = 11;

HoruxDesigner::HoruxDesigner(QWidget *parent)
    : QMainWindow(parent), ui(new Ui::HoruxDesigner)
{
    ui->setupUi(this);

    cardPage = NULL;
    textPage = NULL;
    pixmapPage = NULL;
    userCombo = NULL;
    scenePreview = NULL;

    sqlQuery = NULL;

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
}

HoruxDesigner::~HoruxDesigner()
{
    if(sqlQuery)
        delete sqlQuery;
    delete ui;
}

void HoruxDesigner::loadData(QSplashScreen *sc)
{
    QSettings settings("Letux", "HoruxCardDesigner", this);

    QString host = settings.value("host", "localhost").toString();
    QString username = settings.value("username", "root").toString();
    QString password = settings.value("password", "").toString();
    QString path = settings.value("path", "").toString();
    QString database = settings.value("database", "").toString();
    QString engine = settings.value("engine", "HORUX").toString();
    QString file = settings.value("file", "").toString();

    bool isDbType = false;

    if(engine == "NOT_USED") {
        dbInformation->setText(tr("No database used"));
    } else if(engine == "HORUX") {
        dbInformation->setText(tr("Connection to Horux database"));
        loadHoruxSoap(sc);
    } else if(engine == "CSV") {
        dbInformation->setText(tr("Connection to CSV file: ") + file);
        loadCSVData(sc);
    } else if(engine == "QMYSQL") {
        if(!QSqlDatabase::contains("horux"))
            dbase = QSqlDatabase::addDatabase("QMYSQL","horux");
        dbase.setHostName(host);
        dbase.setDatabaseName(database);
        dbase.setUserName(username);
        dbase.setPassword(password);
        isDbType = true;
        dbInformation->setText(tr("Connection to MySql database: ") + database);
    } else if(engine == "QSQLITE") {
        if(!QSqlDatabase::contains("horux"))
            dbase = QSqlDatabase::addDatabase("QSQLITE","horux");
        dbase.setDatabaseName(database);
        isDbType = true;
        dbInformation->setText(tr("Connection to SQlite database: ") + database );
    } else if(engine == "QPSQL") {
        if(!QSqlDatabase::contains("horux"))
            dbase = QSqlDatabase::addDatabase("QPSQL","horux");
        dbase.setHostName(host);
        dbase.setDatabaseName(database);
        dbase.setUserName(username);
        dbase.setPassword(password);
        isDbType = true;
        dbInformation->setText(tr("Connection to PSQL database: ") + database);
    } else if(engine == "QODBC") {
        if(!QSqlDatabase::contains("horux"))
            dbase = QSqlDatabase::addDatabase("QODBC","horux");
        dbase.setHostName(host);
        dbase.setDatabaseName(database);
        dbase.setUserName(username);
        dbase.setPassword(password);
        isDbType = true;
        dbInformation->setText(tr("Connection to ODBC database: ") + database);
    } else if(engine == "QOCI") {
        if(!QSqlDatabase::contains("horux"))
            dbase = QSqlDatabase::addDatabase("QOCI","horux");
        dbase.setHostName(host);
        dbase.setDatabaseName(database);
        dbase.setUserName(username);
        dbase.setPassword(password);        
        isDbType = true;
        dbInformation->setText(tr("Connection to Oracle database: ") + database);
    }

    if(isDbType)
    {
        if(!dbase.open()) {
            QMessageBox::warning(this,tr("Database connection error"),tr("Not able to connect to the database"));
            dbInformation->setText("Not able to connect to the database");
        } else {
            loadSQLData(sc);
        }

    }
}

void HoruxDesigner::loadHoruxSoap(QSplashScreen *sc)
{
    if(sc != NULL)
    {
        sc->showMessage(tr("The data are loading from Horux Gui..."),Qt::AlignLeft, Qt::white);
        QApplication::processEvents();
    }

    connect(&transport, SIGNAL(responseReady()),this, SLOT(readSoapResponse()), Qt::UniqueConnection);

    pictureBuffer.open(QBuffer::ReadWrite);

    connect(&pictureHttp, SIGNAL(done(bool)), this, SLOT(httpRequestDone(bool)), Qt::UniqueConnection);

    QSettings settings("Letux", "HoruxCardDesigner", this);

    QString host = settings.value("host", "localhost").toString();
    QString username = settings.value("username", "root").toString();
    QString password = settings.value("password", "").toString();
    QString path = settings.value("path", "").toString();
    bool ssl = settings.value("ssl", false).toBool();

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
}


void HoruxDesigner::loadSQLData(QSplashScreen *sc) {
    if(sc != NULL)
    {
        sc->showMessage(tr("The data are loading from the CSV file..."),Qt::AlignLeft, Qt::white);
        QApplication::processEvents();
    }

    if(dbase.isOpen()) {

        QSettings settings("Letux", "HoruxCardDesigner", this);
        QString sql = settings.value("sql", "").toString();
        int column1 = settings.value("column1", 1).toInt();
        int column2 = settings.value("column2", 2).toInt();
        int primaryKey = settings.value("primaryKeyColumn", 0).toInt();

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
                    userCombo->addItem(sqlQuery->value(column1).toString()+ " " + sqlQuery->value(column2).toString (), sqlQuery->value(primaryKey).toInt ());
                else
                    userCombo->addItem(sqlQuery->value(column1).toString(), sqlQuery->value(primaryKey).toInt ());
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

void HoruxDesigner::loadCSVData(QSplashScreen *sc) {
    if(sc != NULL)
    {
        sc->showMessage(tr("The data are loading from the CSV file..."),Qt::AlignLeft, Qt::white);
        QApplication::processEvents();
    }

    QSettings settings("Letux", "HoruxCardDesigner", this);

    QString file = settings.value("file", "").toString();
    int column1 = settings.value("column1", 1).toInt();
    int column2 = settings.value("column2", 2).toInt();
    int primaryKey = settings.value("primaryKeyColumn", 0).toInt();


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
                    csvHeader = listSimplified;
                } else {
                    if(column2>0)
                        userCombo->addItem(list.at(column1).simplified () + " " + list.at(column2).simplified (), list.at(primaryKey).simplified ());
                    else
                        userCombo->addItem(list.at(column1).simplified (), list.at(primaryKey).simplified ());
                    csvData[i] = list;

                    i++;
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
    foreach(QSslError sslError, errors)
    {
        if(sslError.error() == QSslError::SelfSignedCertificate)
        {
            pictureHttp.ignoreSslErrors();
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
    for (int i = 8; i < 30; i = i + 2)
        fontSizeCombo->addItem(QString().setNum(i));
    QIntValidator *validator = new QIntValidator(2, 64, this);
    fontSizeCombo->setValidator(validator);
    connect(fontSizeCombo, SIGNAL(currentIndexChanged(const QString &)),
            this, SLOT(fontSizeChanged(const QString &)));


    connect(fontCombo, SIGNAL(currentFontChanged(const QFont &)),
            this, SLOT(currentFontChanged(const QFont &)));


    sceneScaleCombo = new QComboBox;
    QStringList scales;
    scales << tr("50%") << tr("75%") << tr("100%") << tr("125%") << tr("150%")<< tr("200%")<< tr("250%");
    sceneScaleCombo->addItems(scales);
    sceneScaleCombo->setCurrentIndex(2);
    connect(sceneScaleCombo, SIGNAL(currentIndexChanged(const QString &)),
            this, SLOT(sceneScaleChanged(const QString &)));

    ui->toolBar->addWidget(fontCombo);
    ui->toolBar->addWidget(fontSizeCombo);
    ui->toolBar->addSeparator();
    ui->toolBar->addWidget(sceneScaleCombo);

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

    connect(ui->actionPrint, SIGNAL(triggered()),
            this, SLOT(print()));

    connect(ui->actionPrint_setup, SIGNAL(triggered()),
            this, SLOT(printSetup()));

    connect(ui->actionPrint_all_card, SIGNAL(triggered()),
            this, SLOT(printAll()));

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

    connect(scene, SIGNAL( itemMoved(QGraphicsItem *)),
            this, SLOT(itemMoved(QGraphicsItem *)));

    connect(scene, SIGNAL( mouseRelease() ),
            this, SLOT( mouseRelease() ));

    ui->graphicsView->setScene(scene);
    ui->graphicsView->setRenderHint(QPainter::Antialiasing);

    selectionChanged();
}

void HoruxDesigner::about()
{
    QMessageBox::about(this, tr("About Horux Card Designer"),tr("<h1>Horux Card Designer 0.1 Beta</h1>Copyright 2010 Letux S&agrave;rl.<br/>A Free Software released under the GNU/GPL License"));
}

void HoruxDesigner::readSoapResponse()
{
    const QtSoapMessage &response = transport.getResponse();
    if (response.isFault()) {
        QMessageBox::warning(this,tr("Horux webservice error"),tr("Not able to call the Horux GUI web service."));
        return;
    }

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

        userCombo->addItem(field_name["value"].toString() + " " + field_firstname["value"].toString(), field_id["value"].toInt());
    }

    ui->step->setText("1/" + QString::number(userCombo->count()));

    if(isNew)
        ui->toolBar->addSeparator();
    ui->toolBar->addWidget(userCombo);
    connect( userCombo, SIGNAL(currentIndexChanged(int)), this, SLOT(userChanged(int)));

    if(userCombo->count()>0)
        userChanged(0);


}

void HoruxDesigner::userChanged(int index)
{
    QSettings settings("Letux", "HoruxCardDesigner", this);

    QString host = settings.value("host", "localhost").toString();
    QString username = settings.value("username", "root").toString();
    QString password = settings.value("password", "").toString();
    QString path = settings.value("path", "").toString();
    QString database = settings.value("database", "").toString();
    QString engine = settings.value("engine", "HORUX").toString();
    QString file = settings.value("file", "").toString();
    bool ssl = settings.value("ssl", false).toBool();
    int pictureColumn = settings.value("pictureColumn", -1).toInt();
    int primaryKeyColumn = settings.value("primaryKeyColumn", 0).toInt();
    int column1 = settings.value("column1", 1).toInt();
    int column2 = settings.value("column2", 2).toInt();

    if(userCombo)
    {
        int userId = userCombo->itemData(index).toInt();

        if(engine == "HORUX") {
            disconnect(&transport, 0, this, 0);
            connect(&transport, SIGNAL(responseReady()),this, SLOT(readSoapResponseUser()));

            if(ssl)
                connect(transport.networkAccessManager(),SIGNAL(sslErrors( QNetworkReply *, const QList<QSslError> & )), this, SLOT(sslErrors(QNetworkReply*,QList<QSslError>)));


            QtSoapMessage message;
            message.setMethod("getUserById");
            message.addMethodArgument("id","",userId);

            statusBar()->showMessage("The data are loading from Horux Gui...");

            transport.submitRequest(message, path+"/index.php?soap=horux&password=" + password + "&username=" + username);
        }

        if(engine == "CSV") {

            int i = 0;
            foreach(QString s, csvHeader) {
               userValue[s] = csvData[index].at(i);
               i++;
            }

            if(csvData.count() > 0 && userCombo && userCombo->count() > userCombo->currentIndex()+1 ) {
                ui->next->setEnabled(true);
            }

            userValue["__countIndex"] = QString::number(userCombo->currentIndex());

            if(pictureColumn>=0) {
                pictureBuffer.close();
                pictureBuffer.setData(QByteArray());

                QString picturePath = csvData[index].at(pictureColumn).simplified();

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

            while(sqlQuery->value(primaryKeyColumn).toInt() != userId) {
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
}

void HoruxDesigner::readSoapResponseUser()
{

    const QtSoapMessage &response = transport.getResponse();
    if (response.isFault()) {
        statusBar()->clearMessage();
        QMessageBox::warning(this,tr("Horux webservice error"),tr("Not able to call the Horux GUI web service."));
        return;
    }

    const QtSoapType &value = response.returnValue();

    for(int i=0; i<value.count(); i++)
    {
        const QtSoapType &field =  value[i];
        userValue[field[0].toString()] = field[1].toString();
    }

    if(value.count() > 0 && userCombo && userCombo->count() > userCombo->currentIndex()+1 ) {
        ui->next->setEnabled(true);
    }    

    userValue["__countIndex"] = QString::number(userCombo->currentIndex());

    QString name =  userValue["picture"];

    if(name != "")
    {

        QSettings settings("Letux", "HoruxCardDesigner", this);

        QString host = settings.value("horux", "localhost").toString();
        QString path = settings.value("path", "").toString();
        bool ssl = settings.value("ssl", false).toBool();

        pictureBuffer.close();
        pictureBuffer.setData(QByteArray());
        pictureBuffer.open(QBuffer::ReadWrite);

        pictureHttp.setHost(host, ssl ? QHttp::ConnectionModeHttps : QHttp::ConnectionModeHttp );

        if(ssl)
        {
            connect(&pictureHttp,SIGNAL(sslErrors( const QList<QSslError> & )), this, SLOT(sslErrors(QList<QSslError>)));
        }

        pictureHttp.get(path + "/pictures/" + name, &pictureBuffer);
    }
    else
    {
        statusBar()->clearMessage();
        pictureBuffer.close();
        pictureBuffer.setData(QByteArray());
        pictureBuffer.open(QBuffer::ReadWrite);
    }

    updatePrintPreview();
}

void HoruxDesigner::httpRequestDone ( bool error )
{
    statusBar()->clearMessage();
    if(error)
    {

        QMessageBox::information(this, tr("Picture error"),tr("Not able to load the user picture"));
    }
    else {
        updatePrintPreview();
    }

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
        newCard();
        currenFile.setFileName(action->data().toString());

        QString xml;
        currenFile.open(QIODevice::ReadOnly);
        xml = currenFile.readAll();
        scene->loadScene(xml);
        currenFile.close();
        selectionChanged();
        setWindowTitle("Horux Card Designer - " + currenFile.fileName());
        updatePrintPreview();
    }
}

void HoruxDesigner::open()
{
    QString selectedFilter;
    QString fileName = QFileDialog::getOpenFileName(this,
                                                    tr("Open an Horux Card Designer file"),
                                                    "",
                                                    tr("Horux Card Designer (*.xml)"));
    if (!fileName.isEmpty())
    {
        setCurrentFile(fileName);

        newCard();

        currenFile.setFileName(fileName);

        QString xml;
        currenFile.open(QIODevice::ReadOnly);
        QTextStream data(&currenFile);
        data.setCodec("ISO 8859-1");
        xml = data.readAll();
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

    QSettings settings("Letux", "HoruxCardDesigner", this);
    QString host = settings.value("host", "localhost").toString();
    QString username = settings.value("username", "root").toString();
    QString password = settings.value("password", "").toString();
    QString path = settings.value("path", "").toString();
    QString database = settings.value("database", "").toString();
    bool ssl = settings.value("ssl", false).toBool();
    QString engine = settings.value("engine", "HORUX").toString();
    QString file = settings.value("file", "").toString();
    QString sql = settings.value("sql", "").toString();

    int primaryKeyColumn = settings.value("primaryKeyColumn", 0).toInt();
    int column1 = settings.value("column1", 1).toInt();
    int column2 = settings.value("column2", 2).toInt();
    int pictureColumn = settings.value("pictureColumn", -1).toInt();


    dlg.setHost(host);
    dlg.setUsername(username);
    dlg.setPassword(password);
    dlg.setPath(path);
    dlg.setDatabase(database);
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

        settings.setValue("host",dlg.getHost());
        settings.setValue("username",dlg.getUsername());
        settings.setValue("password",dlg.getPassword());
        settings.setValue("path",dlg.getPath());
        settings.setValue("database",dlg.getDatabase());
        settings.setValue("ssl",dlg.getSSL());
        settings.setValue("engine",dlg.getEngine());
        settings.setValue("file",dlg.getFile());
        settings.setValue("sql",dlg.getSqlRequest());

        settings.setValue("primaryKeyColumn",dlg.getPrimaryKey());
        settings.setValue("column1",dlg.getColumn1());
        settings.setValue("column2",dlg.getColumn2());
        settings.setValue("pictureColumn",dlg.getPictureColumn());


        host = dlg.getHost();
        username = dlg.getUsername();
        password = dlg.getPassword();
        path = dlg.getPath();
        database = dlg.getDatabase();
        ssl = dlg.getSSL();
        file = dlg.getFile();
        engine = dlg.getEngine();
        primaryKeyColumn = dlg.getPrimaryKey();
        column1 = dlg.getColumn1();
        column2 = dlg.getColumn2();
        pictureColumn = dlg.getPictureColumn();
        sql = dlg.getSqlRequest();

        if(engine == "HORUX")
        {
            if(dlg.getSSL())
            {
                isSecure->setToolTip(tr("The communication is safe by SSL"));
                isSecure->setPixmap(QPixmap(":/images/encrypted.png"));
            }
            else
            {
                isSecure->setToolTip(tr("The communication is not safe"));
                isSecure->setPixmap(QPixmap(":/images/decrypted.png"));
            }

            loadHoruxSoap(NULL);

            dbInformation->setText(tr("Connection to Horux database"));
        }
        else
        {
            if(engine == "CSV")
            {
                loadCSVData(NULL);

                dbInformation->setText(tr("Connection to CSV file: ") + file);
            }
            else
            {
                if(engine != "NOT_USED")
                {
                    if(dbase.isOpen())
                        dbase.close();

                    if(!QSqlDatabase::contains("horux"))
                        dbase = QSqlDatabase::addDatabase(engine,"horux");
                    if(engine == "QSQLITE")
                    {
                        dbase.setDatabaseName(file);
                        dbInformation->setText(tr("Connection to Sqlite database: ") + database);
                    }
                    else
                    {
                        dbInformation->setText(tr("Connection to %1 database: ").arg(engine) + database);
                        dbase.setHostName(host);
                        dbase.setDatabaseName(database);
                        dbase.setUserName(username);
                        dbase.setPassword(password);
                    }

                    if(!dbase.open()) {
                        QMessageBox::warning(this,tr("Database connection error"),tr("Not able to connect to the database"));
                        dbInformation->setText("Not able to connect to the database");
                    } else {
                        loadSQLData(NULL);
                    }
                }
            }
        }
    }
}

void HoruxDesigner::save()
{
    if(currenFile.fileName() == "")
    {
        saveAs();
        return;
    }


    currenFile.open(QIODevice::WriteOnly);
    QTextStream data(&currenFile);
    data.setCodec("ISO 8859-1");

    QDomDocument xml;
    QDomElement root = xml.createElement ( "HoruxCardDesigner" );
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
    sceneScaleChanged("100%");
    QRectF cardRect = scene->getCardItem()->boundingRect();

    QPixmap pixmap(cardRect.size().toSize());
    pixmap.fill( Qt::white );
    QPainter painter(&pixmap);
    painter.setRenderHint(QPainter::Antialiasing);
    ui->graphicsView->render(&painter, QRectF(0,0,pixmap.size().width(),pixmap.size().height()), cardRect.toRect(), Qt::KeepAspectRatio );
    painter.end();

    PrintPreview dlg(pixmap, this);

    scene->getCardItem()->setPrintingMode( false, pictureBuffer, userValue );
    scene->getCardItem()->setPos(cardPos);
    sceneScaleChanged(sceneScaleCombo->currentText());



    if (dlg.exec() != QDialog::Rejected )
    {
        print();
    }


}

void HoruxDesigner::print()
{
    scene->clearSelection ();

    printer->setOrientation(( QPrinter::Orientation)scene->getCardItem()->getFormat());

    printer->setPaperSize(scene->getCardItem()->getSizeMm(),QPrinter::Millimeter);
    printer->setPageMargins(0,0,0,0,QPrinter::Millimeter);

    QPointF cardPos = scene->getCardItem()->pos();

    if (QPrintDialog(printer).exec() == QDialog::Accepted)
    {
        scene->getCardItem()->setPrintingMode( true, pictureBuffer, userValue );
        scene->getCardItem()->setPos(0,0);
        sceneScaleChanged("100%");

        QRectF cardRect = scene->getCardItem()->boundingRect();


        QPainter painter(printer);
        painter.setRenderHint(QPainter::Antialiasing);
        ui->graphicsView->render(&painter, printer->pageRect(), cardRect.toRect(), Qt::KeepAspectRatio );

        scene->getCardItem()->setPrintingMode( false, pictureBuffer, userValue );
        scene->getCardItem()->setPos(cardPos);
        sceneScaleChanged(sceneScaleCombo->currentText());
    }
}

void HoruxDesigner::printAll() {
    scene->clearSelection ();

    printer->setOrientation(( QPrinter::Orientation)scene->getCardItem()->getFormat());

    printer->setPaperSize(scene->getCardItem()->getSizeMm(),QPrinter::Millimeter);
    printer->setPageMargins(0,0,0,0,QPrinter::Millimeter);

    QPointF cardPos = scene->getCardItem()->pos();

    if (QPrintDialog(printer).exec() == QDialog::Accepted)
    {

    }

}

void HoruxDesigner::newCard()
{
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
                }

                cardPage->sizeCb->setCurrentIndex(card->getSize());
                cardPage->orientation->setCurrentIndex(card->getFormat());

                if (card->bkgColor.isValid()) {
                    cardPage->color = card->bkgColor;
                    cardPage->bkgColor->setText(card->bkgColor.name());
                    cardPage->bkgColor->setStyleSheet("background-color: " + card->bkgColor.name() + ";");
                }

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

                    pixmapPage->top->setValue(pixmapItem->pos().y());
                    pixmapPage->left->setValue(pixmapItem->pos().x());


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
    scene->setSceneRect(ui->graphicsView->geometry());
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

void HoruxDesigner::itemMoved(QGraphicsItem *item)
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


    }
}

void HoruxDesigner::updatePrintPreview()
{

    QList<QGraphicsItem *> listSelectedItems = scene->selectedItems();

    scene->clearSelection ();

    QPointF cardPos = scene->getCardItem()->pos();

    scene->getCardItem()->setPrintingMode(true, pictureBuffer, userValue);
    scene->getCardItem()->setPos(0,0);
    sceneScaleChanged("100%");
    QRectF cardRect = scene->getCardItem()->boundingRect();

    QPixmap pixmap(cardRect.size().toSize());
    pixmap.fill( Qt::white );
    QPainter painter(&pixmap);

    if(!painter.isActive()) return;

    painter.setRenderHint(QPainter::Antialiasing);
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
    fileChange();
}

void HoruxDesigner::fileChange() {
    setWindowTitle("Horux Card Designer - " + currenFile.fileName() + " *");
    ui->actionSave->setEnabled(true);
}
