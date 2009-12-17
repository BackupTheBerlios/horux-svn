#ifndef DATASOURCEDIALOG_H
#define DATASOURCEDIALOG_H

#include <QtGui/QDialog>
#include <QSqlDatabase>
#include <QModelIndex>

namespace Ui {
    class DataSourceDialog;
}

class DataSourceDialog : public QDialog {
    Q_OBJECT
    Q_DISABLE_COPY(DataSourceDialog)
public:
    explicit DataSourceDialog(QWidget *parent = 0);
    virtual ~DataSourceDialog();

    QString getDatasource();
    void setDatasource(QString datasource);

protected:
    virtual void changeEvent(QEvent *e);

protected slots:
    void tableClicked ( const QModelIndex & index );

private:
    Ui::DataSourceDialog *m_ui;
    QSqlDatabase database;
};

#endif // DATASOURCEDIALOG_H
