#include "databasedialog.h"
#include "ui_databasedialog.h"

DatabaseDialog::DatabaseDialog(QWidget *parent) :
    QDialog(parent),
    m_ui(new Ui::DatabaseDialog)
{
    m_ui->setupUi(this);
}

DatabaseDialog::~DatabaseDialog()
{
    delete m_ui;
}

void DatabaseDialog::changeEvent(QEvent *e)
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

void DatabaseDialog::setHost(const QString host)
{
    m_ui->host->setText(host);
}

void DatabaseDialog::setUsername(const QString username)
{
    m_ui->username->setText(username);
}

void DatabaseDialog::setPassword(const QString password)
{
    m_ui->password->setText(password);
}

void DatabaseDialog::setDb(const QString db)
{
    m_ui->database->setText(db);
}

void DatabaseDialog::setEngine(const int engine)
{
    m_ui->engine->setCurrentIndex(engine);
}

QString DatabaseDialog::getHost()
{
    return m_ui->host->text();
}

QString DatabaseDialog::getUsername()
{
    return  m_ui->username->text();
}

QString DatabaseDialog::getPassword()
{
    return m_ui->password->text();
}

QString DatabaseDialog::getDb()
{
    return m_ui->database->text();
}

int DatabaseDialog::getEngine()
{
    return m_ui->engine->currentIndex();
}
