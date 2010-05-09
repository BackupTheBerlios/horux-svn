#include "databaseconnection.h"
#include "ui_databaseconnection.h"
#include <QMessageBox>
#include <QNetworkRequest>


DatabaseConnection::DatabaseConnection(QWidget *parent) :
    QDialog(parent),
    m_ui(new Ui::DatabaseConnection)
{
    m_ui->setupUi(this);

    connect(m_ui->testButton, SIGNAL(clicked()), this, SLOT(onTest()));

    connect(&transport, SIGNAL(responseReady()), SLOT(readResponse()));

    connect(m_ui->engine, SIGNAL(currentIndexChanged(int)), this, SLOT(onEngineCurrentIndexChanged(int)));

    foreach(QString driver, QSqlDatabase::drivers())
    {
        qDebug() << driver;
        if(driver == "QMYSQL")
            m_ui->engine->setItemText(1, tr("MySql - Available"));

        if(driver == "QSQLITE")
            m_ui->engine->setItemText(2, tr("Sqlite 3 - Available"));

        if(driver == "QPSQL")
            m_ui->engine->setItemText(3, tr("Postgres - Available"));

        if(driver == "QODBC")
            m_ui->engine->setItemText(4, tr("ODBC - Available"));

        if(driver == "QOCI")
            m_ui->engine->setItemText(5, tr("Oracle - Available"));

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

void DatabaseConnection::setEngine(const int engine)
{
    m_ui->engine->setCurrentIndex(engine);

    if(engine == 0)
    {
        m_ui->database->setEnabled(false);
        m_ui->path->setEnabled(true);
    }
    else
    {
        m_ui->database->setEnabled(true);
        m_ui->path->setEnabled(false);

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


int DatabaseConnection::getEngine()
{
    return m_ui->engine->currentIndex();
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



void DatabaseConnection::onTest()
{
    switch(m_ui->engine->currentIndex()){
        case 0: //horux
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
            break;
        case 1: //mysql
            {
                dbase = QSqlDatabase::addDatabase("QMYSQL");
                dbase.setHostName(m_ui->host->text());
                dbase.setDatabaseName(m_ui->database->text());
                dbase.setUserName(m_ui->username->text());
                dbase.setPassword(m_ui->password->text());
                if(dbase.open())
                {
                    QMessageBox::information(this,tr("Database connection"),tr("The configuration is well done"));
                }
                else
                    QMessageBox::warning(this,tr("Database connection error"),tr("Not able to connect to the database"));
            }
            break;
        case 2: //sqlite
            {
                dbase = QSqlDatabase::addDatabase("QSQLITE");
                dbase.setDatabaseName(m_ui->database->text());
                if(dbase.open())
                {
                    QMessageBox::information(this,tr("Database connection"),tr("The configuration is well done"));
                }
                else
                    QMessageBox::warning(this,tr("Database connection error"),tr("Not able to connect to the database"));
            }
            break;
        case 3: //postgresql
            {
                dbase = QSqlDatabase::addDatabase("QPSQL");
                dbase.setHostName(m_ui->host->text());
                dbase.setDatabaseName(m_ui->database->text());
                dbase.setUserName(m_ui->username->text());
                dbase.setPassword(m_ui->password->text());
                if(dbase.open())
                {
                    QMessageBox::information(this,tr("Database connection"),tr("The configuration is well done"));
                }
                else
                    QMessageBox::warning(this,tr("Database connection error"),tr("Not able to connect to the database"));
            }
            break;
        case 4: //odbc
            {
                dbase = QSqlDatabase::addDatabase("QODBC");
                dbase.setHostName(m_ui->host->text());
                dbase.setDatabaseName(m_ui->database->text());
                dbase.setUserName(m_ui->username->text());
                dbase.setPassword(m_ui->password->text());
                if(dbase.open())
                {
                    QMessageBox::information(this,tr("Database connection"),tr("The configuration is well done"));
                }
                else
                    QMessageBox::warning(this,tr("Database connection error"),tr("Not able to connect to the database"));

            }
            break;
        case 5: //oracle
            {
                dbase = QSqlDatabase::addDatabase("QOCI");
                dbase.setHostName(m_ui->host->text());
                dbase.setDatabaseName(m_ui->database->text());
                dbase.setUserName(m_ui->username->text());
                dbase.setPassword(m_ui->password->text());
                if(dbase.open())
                {
                    QMessageBox::information(this,tr("Database connection"),tr("The configuration is well done"));
                }
                else
                    QMessageBox::warning(this,tr("Database connection error"),tr("Not able to connect to the database"));

            }
            break;
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
    if(index == 0)
    {
        m_ui->database->setEnabled(false);
        m_ui->path->setEnabled(true);
    }
    else
    {
        m_ui->database->setEnabled(true);
        m_ui->path->setEnabled(false);

    }

}
