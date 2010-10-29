#include "horuxfields.h"
#include "ui_horuxfields.h"
#include <QMessageBox>
#include <QSslError>
#include <QNetworkReply>
#include <QtSql>
#include "horuxdesigner.h"

HoruxFields::HoruxFields(QWidget *parent) :
        QDialog(parent),
        m_ui(new Ui::HoruxFields)
{
    m_ui->setupUi(this);
    connect(m_ui->fieldsList, SIGNAL(itemSelectionChanged()), this, SLOT(itemSelectionChanged()));


    QString host = HoruxDesigner::getHost();
    QString username = HoruxDesigner::getUsername();
    QString password = HoruxDesigner::getPassword();
    QString path = HoruxDesigner::getPath();
    QString engine = HoruxDesigner::getEngine();
    QString file = HoruxDesigner::getFile();
    QString sql = HoruxDesigner::getSql();
    bool ssl = HoruxDesigner::getSsl();

    if(engine == "HORUX") {
        connect(&transport, SIGNAL(responseReady()),this, SLOT(readSoapResponse()), Qt::UniqueConnection);


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

     if(engine == "CSV") {

         QFile f(file);
         if ( f.open(QIODevice::ReadOnly) ) { // file opened successfully
             QTextStream t( &f ); // use a text stream
             QString line = t.readLine(); // line of text excluding '\n'
             QStringList list = line.split(",");

             for(int i=0; i<list.count(); i++ )
             {
                 m_ui->fieldsList->addItem(list.at(i).simplified ());
             }

         } else {
             QMessageBox::warning(this,tr("CSV file error"),tr("Not able to open the file"));

         }
     }


     if(engine != "NOT_USED" && engine != "CSV" && engine != "HORUX") {
         QSqlDatabase dbase = QSqlDatabase::database("horux");

         if(dbase.isOpen()) {
            QSqlQuery query(sql,dbase);
            query.next();

            QSqlRecord record = query.record();

            for(int i=0; i<record.count(); i++) {
               m_ui->fieldsList->addItem(record.fieldName(i));
            }
         }
     }
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
