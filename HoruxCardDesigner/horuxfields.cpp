#include "horuxfields.h"
#include "ui_horuxfields.h"
#include <QMessageBox>
#include <QSslError>
#include <QNetworkReply>

HoruxFields::HoruxFields(QWidget *parent) :
        QDialog(parent),
        m_ui(new Ui::HoruxFields)
{
    m_ui->setupUi(this);
    connect(m_ui->fieldsList, SIGNAL(itemSelectionChanged()), this, SLOT(itemSelectionChanged()));

    connect(&transport, SIGNAL(responseReady()),this, SLOT(readSoapResponse()), Qt::UniqueConnection);

    QSettings settings("Letux", "HoruxCardDesigner", this);

    QString host = settings.value("host", "localhost").toString();
    QString username = settings.value("username", "root").toString();
    QString password = settings.value("password", "").toString();
    QString path = settings.value("path", "").toString();
    bool ssl = settings.value("ssl", "").toBool();

    QtSoapMessage message;
    message.setMethod("getUserFields");


    if(ssl)
    {
        transport.setHost(host, true);
        connect(transport.networkAccessManager(),SIGNAL(sslErrors( QNetworkReply *, const QList<QSslError> & )),
                this, SLOT(sslErrors(QNetworkReply*,QList<QSslError>)), Qt::UniqueConnection);
    }
    else
    {
        transport.setHost(host);
    }

    transport.submitRequest(message, path+"/index.php?soap=horux&password=" + password + "&username=" + username);

}

HoruxFields::~HoruxFields()
{
    delete m_ui;
}

void HoruxFields::readSoapResponse()
{

    const QtSoapMessage &response = transport.getResponse();
    if (response.isFault()) {
        QMessageBox::warning(this,tr("Horux webservice error"),tr("Not able to call the Horux GUI web service."));
        return;
    }

    const QtSoapType &value = response.returnValue();

    for(int i=0; i<value.count(); i++ )
    {
        const QtSoapType &record =  value[i];

        m_ui->fieldsList->addItem(record.toString());
    }

}

void HoruxFields::sslErrors ( QNetworkReply * reply, const QList<QSslError> & errors )
{
    foreach(QSslError sslError, errors)
    {
        if(sslError.error() == QSslError::SelfSignedCertificate)
        {
            reply->ignoreSslErrors();
        }
    }
}



void HoruxFields::changeEvent(QEvent *e)
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

QString HoruxFields::getDatasource()
{

    return m_ui->textDisplay->toPlainText();
}

void HoruxFields::setDatasource(QString source) {
    m_ui->textDisplay->setPlainText(source);
}


void HoruxFields::itemSelectionChanged () {
    QList<QListWidgetItem *>items = m_ui->fieldsList->selectedItems();

    foreach(QListWidgetItem *item , items) {
        QString itemText = item->text();

        if(!m_ui->textDisplay->toPlainText().contains(itemText)) {
            m_ui->textDisplay->insertPlainText("%" + itemText + "%");
        }
    }
}
