#include "printcounter.h"
#include "ui_printcounter.h"

PrintCounter::PrintCounter(QWidget *parent) :
    QDialog(parent),
    ui(new Ui::PrintCounter)
{
    ui->setupUi(this);
}

PrintCounter::~PrintCounter()
{
    delete ui;
}

void PrintCounter::changeEvent(QEvent *e)
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

int PrintCounter::getInitialValue() {
    return ui->initialValue->value();
}

int PrintCounter::getIncrement() {
    return ui->increment->value();
}

int PrintCounter::getDigits() {
    return ui->digits->value();
}

void PrintCounter::setValues(int initialValue, int increment, int digits) {
    ui->increment->setValue(increment);
    ui->initialValue->setValue(initialValue);
    ui->digits->setValue(digits);
}
