#include "csvtest.h"
#include "ui_csvtest.h"
#include <QTextStream>
#include <QDebug>

CsvTest::CsvTest(QWidget *parent) :
    QDialog(parent),
    ui(new Ui::CsvTest)
{
    ui->setupUi(this);
}

CsvTest::~CsvTest()
{
    delete ui;
}

void CsvTest::changeEvent(QEvent *e)
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

void CsvTest::setCSV(QString csvContent)
{
    QTextStream t( &csvContent );
    int testMax = 0;
    QString line;
    while ( !t.atEnd() && testMax < 4  ) { // until end of file...
        line = t.readLine(); // line of text excluding '\n'


        if(line != "") {
            QStringList list = line.split(",");
            if(testMax == 0) {
                ui->tableWidget->setColumnCount(list.size());

                ui->tableWidget->setHorizontalHeaderLabels(list);

            } else {
                int row = ui->tableWidget->rowCount();
                ui->tableWidget->insertRow(row);

                int i = 0;
                foreach(QString s, list) {
                    QTableWidgetItem *item = new QTableWidgetItem(s);
                    ui->tableWidget->setItem(row,i,item);
                    i++;
                }
            }
        }
        testMax++;
    }
}
