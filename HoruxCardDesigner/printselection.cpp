#include "printselection.h"
#include "ui_printselection.h"

PrintSelection::PrintSelection(QWidget *parent) :
    QDialog(parent),
    ui(new Ui::PrintSelection)
{
    ui->setupUi(this);
}

PrintSelection::~PrintSelection()
{
    delete ui;
}

void PrintSelection::changeEvent(QEvent *e)
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

void PrintSelection::setHoruxData(QStringList header, QMap<int, QStringList>data)
{
    header.prepend(tr("Was printed"));
    header.prepend(tr("Check for print"));

    ui->tableWidget->setColumnCount(header.size());
    ui->tableWidget->setHorizontalHeaderLabels(header);

    QMapIterator<int, QStringList> i(data);
    while (i.hasNext()) {
        i.next();
        int j=2;

        int row = ui->tableWidget->rowCount();
        ui->tableWidget->insertRow(row);

        QTableWidgetItem *item = new QTableWidgetItem;
        item->setCheckState(Qt::Unchecked);
        ui->tableWidget->setItem(row,0,item);

        QTableWidgetItem *item2 = new QTableWidgetItem;

        if(!printedUsers.contains(QString::number(i.key())))
            item2->setCheckState(Qt::Unchecked);
        else
            item2->setCheckState(Qt::Checked);

        ui->tableWidget->setItem(row,1,item2);

        foreach(QString s, i.value()) {
            QTableWidgetItem *item = new QTableWidgetItem(s);
            ui->tableWidget->setItem(row,j,item);
            j++;
        }
    }
}

void PrintSelection::setCSVData(QStringList header, QMap<int, QStringList>data) {
    setHoruxData(header, data);
}

void PrintSelection::setSQLData(QSqlQuery *query, int primaryKey) {
    QStringList header;

    query->first();

    QSqlRecord record = query->record();

    for(int i=0; i<record.count(); i++) {
        header.append( record.fieldName(i) );
    }


    header.prepend(tr("Was printed"));
    header.prepend(tr("Check for print"));

    ui->tableWidget->setColumnCount(header.size());
    ui->tableWidget->setHorizontalHeaderLabels(header);

    query->first();

    do {

        int row = ui->tableWidget->rowCount();
        ui->tableWidget->insertRow(row);

        QTableWidgetItem *item = new QTableWidgetItem;
        item->setCheckState(Qt::Unchecked);
        ui->tableWidget->setItem(row,0,item);

        QTableWidgetItem *item2 = new QTableWidgetItem;

        if(!printedUsers.contains(query->value(primaryKey).toString()))
            item2->setCheckState(Qt::Unchecked);
        else
            item2->setCheckState(Qt::Checked);

        ui->tableWidget->setItem(row,1,item2);

        for(int j=0; j<query->record().count(); j++) {            
            QTableWidgetItem *item = new QTableWidgetItem(query->value(j).toString());
            ui->tableWidget->setItem(row,j+2,item);            
        }

    } while(query->next());
}

void PrintSelection::setPrintedUser(QStringList userIds) {
    printedUsers = userIds;
}

QStringList PrintSelection::getCheckedUser(int primaryKey) {

    QStringList userIds;

    for(int i=0; i< ui->tableWidget->rowCount () ; i++) {
        QTableWidgetItem *item = ui->tableWidget->item(i,0);

        if(item->checkState() == Qt::Checked) {
            userIds.append( ui->tableWidget->item(i,primaryKey+2)->text()  );
        }

    }

    return userIds;
}
