#include "datasourcedialog.h"
#include "ui_datasourcedialog.h"
#include <QSqlQueryModel>
#include <QDebug>

DataSourceDialog::DataSourceDialog(QWidget *parent) :
    QDialog(parent),
    m_ui(new Ui::DataSourceDialog)
{
    m_ui->setupUi(this);

    database = QSqlDatabase::database();

    if(database.isValid())
    {
        QSqlQueryModel *model = new QSqlQueryModel(this);
        model->setQuery("SHOW TABLES");
        m_ui->tableListView->setModel(model);
    }

    connect(m_ui->tableListView, SIGNAL(clicked ( const QModelIndex & )), this, SLOT(tableClicked ( const QModelIndex & )));
}

DataSourceDialog::~DataSourceDialog()
{
    delete m_ui;
}

void DataSourceDialog::changeEvent(QEvent *e)
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

void DataSourceDialog::tableClicked ( const QModelIndex & index )
{
    QItemSelectionModel *m = m_ui->fieldListView->selectionModel();

    QSqlQueryModel *model = new QSqlQueryModel(this);
    model->setQuery("SHOW COLUMNS FROM " + index.data(0).toString());
    m_ui->fieldListView->setModel(model);

    delete m;
}

QString DataSourceDialog::getDatasource()
{
    QModelIndex t = m_ui->tableListView->currentIndex () ;
    QModelIndex f = m_ui->fieldListView->currentIndex () ;

    return t.data(0).toString() + "." + f.data(0).toString();
}

void DataSourceDialog::setDatasource(QString datasource)
{

}

