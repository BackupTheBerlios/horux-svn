#include "horuxdialog.h"
#include "ui_horuxdialog.h"
#include <QMessageBox>
 #include <QNetworkRequest>

HoruxDialog::HoruxDialog(QWidget *parent) :
        QDialog(parent),
        m_ui(new Ui::HoruxDialog)
{
    m_ui->setupUi(this);

    connect(m_ui->testButton, SIGNAL(clicked()), this, SLOT(onTest()));

    connect(&transport, SIGNAL(responseReady()), SLOT(readResponse()));
}

HoruxDialog::~HoruxDialog()
{
    delete m_ui;
}

void HoruxDialog::changeEvent(QEvent *e)
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

void HoruxDialog::setHorux(const QString url)
{
    m_ui->url->setText(url);
}

void HoruxDialog::setUsername(const QString u)
{
    m_ui->username->setText(u);
}

void HoruxDialog::setPassword(const QString p)
{
    m_ui->password->setText(p);
}

void HoruxDialog::setPath(const QString p)
{
    m_ui->path->setText(p);
}

QString HoruxDialog::getHorux()
{
    return m_ui->url->text();
}

QString HoruxDialog::getUsername()
{
    return m_ui->username->text();
}

QString HoruxDialog::getPassword()
{
    return m_ui->password->text();
}

QString HoruxDialog::getPath()
{
    return m_ui->path->text();
}

void HoruxDialog::onTest()
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


    transport.setHost(m_ui->url->text(), getSSL());


    transport.submitRequest(message, m_ui->path->text()+"/index.php?soap=horux&password=" + m_ui->username->text() + "&username=" + m_ui->password->text() );

}

void HoruxDialog::sslErrors ( QNetworkReply * reply, const QList<QSslError> & errors )
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

void HoruxDialog::readResponse()
{
    const QtSoapMessage &response = transport.getResponse();
    if (response.isFault()) {
        QMessageBox::warning(this,tr("Horux webservice error"),tr("Not able to call the Horux GUI web service."));
        return;
    }

    QMessageBox::information(this,tr("Horux webservice"),tr("The configuration is well done"));

}

void HoruxDialog::setSSL(const bool ssl)
{
    m_ui->ssl->setChecked(ssl);
}

bool HoruxDialog::getSSL()
{
    return m_ui->ssl->isChecked();
}
