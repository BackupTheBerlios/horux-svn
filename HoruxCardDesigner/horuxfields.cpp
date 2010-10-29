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

    QString engine = HoruxDesigner::getEngine();
    QString sql = HoruxDesigner::getSql();

     if(engine == "CSV" || engine == "HORUX") {

         QStringList list = HoruxDesigner::getHeader();

         for(int i=0; i<list.count(); i++ )
         {
             m_ui->fieldsList->addItem(list.at(i).simplified ());
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
