#include "printhoruxuser.h"
#include "ui_printhoruxuser.h"
#include "horuxdesigner.h"
#include <QMessageBox>
#include <QSslError>
#include <QNetworkReply>

PrintHoruxUser::PrintHoruxUser(QWidget *parent) :
    QDialog(parent),
    ui(new Ui::PrintHoruxUser)
{
    ui->setupUi(this);

    connect(&transport, SIGNAL(responseReady()),this, SLOT(readSoapResponse()), Qt::UniqueConnection);
    connect(ui->rfidNumber, SIGNAL(textChanged(QString)), this, SLOT(rfidDetected()));
    connect(ui->next, SIGNAL(clicked()), this, SLOT(subokNext()));

    if(HoruxDesigner::getSsl())
    {
        transport.setHost(HoruxDesigner::getHost(), true);
        connect(transport.networkAccessManager(),SIGNAL(sslErrors( QNetworkReply *, const QList<QSslError> & )),
                this, SLOT(sslErrors(QNetworkReply*,QList<QSslError>)), Qt::UniqueConnection);
    }
    else
    {
        transport.setHost(HoruxDesigner::getHost());
    }

    QtSoapMessage message;
    message.setMethod("getVoucherList");


    transport.submitRequest(message, HoruxDesigner::getPath()+"/index.php?soap=ticketing&password=" + HoruxDesigner::getPassword() + "&username=" + HoruxDesigner::getUsername());
    QApplication::processEvents();
}

PrintHoruxUser::~PrintHoruxUser()
{
    delete ui;
}


void PrintHoruxUser::rfidDetected() {
    QString n = ui->rfidNumber->text();

    n.replace("#","");
    ui->rfidNumber->setText(n);

    ui->rfid->setEnabled(false);

    ui->ticket->setEnabled(true);
    ui->ticketList->setEnabled(true);

    ui->info->setText(tr("Choose the subcription..."));
    ui->next->setEnabled(true);
}

void PrintHoruxUser::sslErrors ( QNetworkReply * reply, const QList<QSslError> & errors )
{
    foreach(QSslError sslError, errors)
    {
        if(sslError.error() == QSslError::SelfSignedCertificate)
        {
            reply->ignoreSslErrors();
        }
    }
}

void PrintHoruxUser::sslErrors ( const QList<QSslError> & errors )
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

void PrintHoruxUser::readSoapResponse()
{

    const QtSoapMessage &response = transport.getResponse();
    if (response.isFault()) {
        QMessageBox::warning(this,tr("Horux webservice error"),tr("Not able to call the Horux GUI web service."));
        return;
    }

    if( response.method().name().name() == "getVoucherListResponse") {

        const QtSoapType &returnValue = response.returnValue();

        for(int i=0; i<returnValue.count(); i++ ) {
            const QtSoapType &record =  returnValue[i];

            const QtSoapType &field_id =  record[0];
            const QtSoapType &field_name =  record[1];

            if(field_name["value"].toString().contains(type))
                ui->ticketList->addItem(field_name["value"].toString() , field_id["value"].toInt());

        }

        ui->info->setText(tr("Print the card..."));
        emit printCard();


    }

    if( response.method().name().name() == "addVoucherResponse") {

        const QtSoapType &returnValue = response.returnValue();
        if( returnValue.toBool() ) {
            ui->sendToHorux->setEnabled(false);

            ui->print->setEnabled(true);
            emit newUserAdd();
            accept ();
        } else {
            QMessageBox::warning(this,tr("Horux webservice error"),tr("Cannot create the user in Horux. Please check your database connexion"));
        }
    }
}

void PrintHoruxUser::setUserType(QString t) {
    type = t;
}

void PrintHoruxUser::rfidStep() {
    ui->rfid->setEnabled(true);
    ui->rfidNumber->setEnabled(true);
    ui->rfidNumber->setFocus();
    ui->info->setText(tr("Present the rfid card to the reader..."));
}

void PrintHoruxUser::subokNext() {
    ui->sendToHorux->setEnabled(true);
    ui->next->setEnabled(false);
    ui->ticket->setEnabled(false);

    ui->info->setText(tr("Send the info to Horux..."));

    QtSoapMessage message;
    message.setMethod("addVoucher");
    message.addMethodArgument("transactionNumber","", "0");
    message.addMethodArgument("voucherCode", "",ui->ticketList->itemData(ui->ticketList->currentIndex()).toString() );
    message.addMethodArgument("rfidNumber", "",ui->rfidNumber->text());
    message.addMethodArgument("vendorName", "",HoruxDesigner::getUsername());
    message.addMethodArgument("id", "","0");
    message.addMethodArgument("name", "",HoruxDesigner::getHoruxUserName());
    message.addMethodArgument("firstname", "",HoruxDesigner::getHoruxUserFirstName());
    message.addMethodArgument("street","", HoruxDesigner::getHoruxUserStreet());
    message.addMethodArgument("NPA", "",HoruxDesigner::getHoruxUserZip());
    message.addMethodArgument("city", "",HoruxDesigner::getHoruxUserCity());
    message.addMethodArgument("email", "",HoruxDesigner::getHoruxUserEmail());
    message.addMethodArgument("phone", "",HoruxDesigner::getHoruxUserPhone());
    message.addMethodArgument("birthday", "",HoruxDesigner::getHoruxUserBirthday());
    message.addMethodArgument("group", "",HoruxDesigner::getHoruxUserGroup());

    QString picture(HoruxDesigner::getHoruxUserPicture().toBase64());
    message.addMethodArgument("picture", "",picture);


    transport.submitRequest(message, HoruxDesigner::getPath()+"/index.php?soap=ticketing&password=" + HoruxDesigner::getPassword() + "&username=" + HoruxDesigner::getUsername());
    QApplication::processEvents();

}
