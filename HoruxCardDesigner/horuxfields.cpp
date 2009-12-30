#include "horuxfields.h"
#include "ui_horuxfields.h"

HoruxFields::HoruxFields(QWidget *parent) :
        QDialog(parent),
        m_ui(new Ui::HoruxFields)
{
    m_ui->setupUi(this);
}

HoruxFields::~HoruxFields()
{
    delete m_ui;
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
    return m_ui->fieldsList->currentItem()->text();
}
