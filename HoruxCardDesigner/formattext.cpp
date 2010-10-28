#include "formattext.h"
#include "ui_formattext.h"
#include <QDebug>
#include <QDateTime>

FormatText::FormatText(QWidget *parent) :
    QDialog(parent),
    ui(new Ui::FormatText)
{
    ui->setupUi(this);

    connect(ui->value, SIGNAL(textEdited ( const QString & ) ), this, SLOT(valueChanded(const QString & )));
}

FormatText::~FormatText()
{
    delete ui;
}

void FormatText::changeEvent(QEvent *e)
{
    QDialog::changeEvent(e);
    switch (e->type()) {
    case QEvent::LanguageChange:
        ui->retranslateUi(this);
        break;
    default:
        break;
    }
}

void FormatText::valueChanded(const QString & text) {
    switch(format_type) {
        case 1: // integer
            ui->formatted->setText( text.rightJustified(ui->intDigit->value(),'0')  );
            break;
        case 2: // float
            {
                QStringList formatted = text.split(".");
                QString text_tmp = text;

                if(formatted.count() == 2) { // with the point
                   text_tmp = formatted.at(0) + "." + formatted.at(1).leftJustified(ui->floatDecimal->value(),'0');
                } else {
                    text_tmp = formatted.at(0) + "." + QString("").leftJustified(ui->floatDecimal->value(),'0');
                }

                ui->formatted->setText( text_tmp  );
            }
            break;
        case 3: // date
            {
                QDateTime dateTime = QDateTime::fromString(text, ui->dateSourceFormat->text());
                qDebug() << dateTime;
                ui->formatted->setText( dateTime.toString(ui->dateFormat->currentText())  );
            }
            break;

    }
}

void FormatText::setFormat(int type, int digit, int decimal, QString date, QString dateSourceFormat) {

    format_type = type;
    switch(type) {
        case 1: //integer
            {
                ui->integerGroup->setEnabled(true);
                ui->floatGroup->setEnabled(false);
                ui->dateGroup->setEnabled(false);
                ui->intDigit->setValue(digit);

                QValidator *validator = new QIntValidator( this);
                ui->value->setValidator(validator);
            }
            break;
        case 2: // float
            {
                ui->integerGroup->setEnabled(false);
                ui->floatGroup->setEnabled(true);
                ui->dateGroup->setEnabled(false);
                ui->floatDecimal->setValue(decimal);

                QValidator *validator = new QDoubleValidator( this);
                ui->value->setValidator(validator);
            }
            break;
        case 3: //date
            ui->integerGroup->setEnabled(false);
            ui->floatGroup->setEnabled(false);
            ui->dateGroup->setEnabled(true);
            ui->dateFormat->setEditText(date);
            ui->dateSourceFormat->setText(dateSourceFormat);
            break;

    }
}

int FormatText::digit(){
    return ui->intDigit->value();
}

int FormatText::decimal(){
    return ui->floatDecimal->value();
}

QString FormatText::date(){
    return ui->dateFormat->currentText();
}

QString FormatText::sourceDate(){
    return ui->dateSourceFormat->text();
}
