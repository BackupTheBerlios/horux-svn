#include "databaseconnection.h"
#include "ui_databaseconnection.h"
#include <QMessageBox>
#include <QNetworkRequest>


DatabaseConnection::DatabaseConnection(QWidget *parent) :
    QDialog(parent),
    m_ui(new Ui::DatabaseConnection)
{
    m_ui->setupUi(this);

    m_ui->engine->setItemData(0,"NOT_USED");
    m_ui->engine->setItemData(1,"HORUX");
    m_ui->engine->setItemData(2,"CSV");


    connect(m_ui->testButton, SIGNAL(clicked()), this, SLOT(onTest()));

    connect(&transport, SIGNAL(responseReady()), SLOT(readResponse()));

    connect(m_ui->engine, SIGNAL(currentIndexChanged(int)), this, SLOT(onEngineCurrentIndexChanged(int)));

    foreach(QString driver, QSqlDatabase::drivers())
    {
        if(driver == "QMYSQL")
            m_ui->engine->addItem("Mysql", "QMYSQL");

        if(driver == "QSQLITE")
            m_ui->engine->addItem("Sqlite 3", "QSQLITE");

        if(driver == "QPSQL")
            m_ui->engine->addItem("Postgresql", "QPSQL");

        if(driver == "QODBC")
            m_ui->engine->addItem("ODBC", "QODBC");

        if(driver == "QOCI")
            m_ui->engine->addItem("Oracle", "QOCI");

    }
}

DatabaseConnection::~DatabaseConnection()
{
    delete m_ui;
}

void DatabaseConnection::changeEvent(QEvent *e)
{
    QDialog::changeEvent(e);
    switch (e->type()) {
    case QEvent::LanguageChange:
        m_ui->retranslateUi(this);
        break;
    default:
        break;
    }
}

void DatabaseConnection::setEngine(const QString engine)
{
    int index = m_ui->engine->findData(engine);

    m_ui->engine->setCurrentIndex(index);

    m_ui->database->setEnabled(false);
    m_ui->password->setEnabled(false);
    m_ui->username->setEnabled(false);
    m_ui->ssl->setEnabled(false);
    m_ui->path->setEnabled(false);
    m_ui->host->setEnabled(false);
    m_ui->testButton->setEnabled(false);
    m_ui->search->setEnabled(false);
    m_ui->file->setEnabled(false);

    QString engineData = m_ui->engine->itemData(m_ui->engine->currentIndex()).toString();

    if(engineData == "HORUX")
    {
        m_ui->database->setEnabled(true);
        m_ui->password->setEnabled(true);
        m_ui->username->setEnabled(true);
        m_ui->ssl->setEnabled(true);
        m_ui->path->setEnabled(true);
        m_ui->host->setEnabled(true);
        m_ui->testButton->setEnabled(true);
    }
    else
    {
        if(engineData == "CSV")
        {
            m_ui->search->setEnabled(true);
            m_ui->file->setEnabled(true);
            m_ui->testButton->setEnabled(true);
        }
        else
        {
            if(engineData == "QSQLITE")
            {
                m_ui->file->setEnabled(true);;
                m_ui->search->setEnabled(true);
                m_ui->testButton->setEnabled(true);
            }
            else
            {
                if(engineData != "NOT_USED")
                {
                    m_ui->database->setEnabled(true);
                    m_ui->password->setEnabled(true);
                    m_ui->username->setEnabled(true);
                    m_ui->host->setEnabled(true);
                    m_ui->testButton->setEnabled(true);
                }
            }
        }
    }

}

void DatabaseConnection::setHost(const QString url)
{
    m_ui->host->setText(url);
}

void DatabaseConnection::setUsername(const QString u)
{
    m_ui->username->setText(u);
}

void DatabaseConnection::setPassword(const QString p)
{
    m_ui->password->setText(p);
}

void DatabaseConnection::setPath(const QString p)
{
    m_ui->path->setText(p);
}

void DatabaseConnection::setDatabase(const QString p)
{
    m_ui->database->setText(p);
}

void DatabaseConnection::setFile(const QString p)
{
    m_ui->file->setText(p);
}


QString DatabaseConnection::getEngine()
{
    return m_ui->engine->itemData(m_ui->engine->currentIndex()).toString();
}


QString DatabaseConnection::getHost()
{
    return m_ui->host->text();
}

QString DatabaseConnection::getUsername()
{
    return m_ui->username->text();
}

QString DatabaseConnection::getPassword()
{
    return m_ui->password->text();
}

QString DatabaseConnection::getPath()
{
    return m_ui->path->text();
}

QString DatabaseConnection::getDatabase()
{
    return m_ui->database->text();
}

QString DatabaseConnection::getFile()
{
    return m_ui->file->text();
}

void DatabaseConnection::onTest()
{
    if(dbase.isOpen()) {
        dbase.close();
    }

    QString engine = m_ui->engine->itemData(m_ui->engine->currentIndex()).toString();

    if(engine == "HORUX")
    {
        QtSoapMessage message;
        message.setMethod("getUserById");

        // test if we receive the user with id 1
        message.addMethodArgument("id","", "1");

        if(getSSL())
        {
            connect(transport.networkAccessManager(),SIGNAL(sslErrors( QNetworkReply *, const QList<QSslError> & )),
                    this, SLOT(sslErrors(QNetworkReply*,QList<QSslError>)));
        }


        transport.setHost(m_ui->host->text(), getSSL());

        transport.submitRequest(message, m_ui->path->text()+"index.php?soap=horux&password=" + m_ui->password->text() + "&username=" + m_ui->username->text() );
    }
    else
    {
        if(engine == "CSV")
        {

        }
        else
        {
            if(engine != "NOT_USED")
            {
                if(!QSqlDatabase::contains("test") )
                    dbase = QSqlDatabase::addDatabase(engine, "test");
                if(engine == "QSQLITE")
                {
                    dbase.setDatabaseName(m_ui->file->text());
                }
                else
                {
                    dbase.setHostName(m_ui->host->text());
                    dbase.setDatabaseName(m_ui->database->text());
                    dbase.setUserName(m_ui->username->text());
                    dbase.setPassword(m_ui->password->text());
                }

                if(dbase.open())
                {
                    QMessageBox::information(this,tr("Database connection"),tr("The configuration is well done"));
                }
                else
                    QMessageBox::warning(this,tr("Database connection error"),tr("Not able to connect to the database"));
            }
        }
    }
}

void DatabaseConnection::sslErrors ( QNetworkReply * reply, const QList<QSslError> & errors )
{
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

void DatabaseConnection::readResponse()
{
    const QtSoapMessage &response = transport.getResponse();
    if (response.isFault()) {
        QMessageBox::warning(this,tr("Horux webservice error"),tr("Not able to call the Horux GUI web service."));
        return;
    }

    const QtSoapType &value = response.returnValue();

    if(value.count() > 0)
        QMessageBox::information(this,tr("Horux webservice"),tr("The configuration is well done"));
    else
        QMessageBox::warning(this,tr("Horux webservice error"),tr("Not able to call the Horux GUI web service."));

}

void DatabaseConnection::setSSL(const bool ssl)
{
    m_ui->ssl->setChecked(ssl);
}

bool DatabaseConnection::getSSL()
{
    return m_ui->ssl->isChecked();
}

void DatabaseConnection::onEngineCurrentIndexChanged(int index)
{
    setEngine(m_ui->engine->itemData(index).toString());

}
