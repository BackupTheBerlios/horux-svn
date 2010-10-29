#ifndef PRINTSELECTION_H
#define PRINTSELECTION_H

#include <QDialog>
#include <QtSql>

namespace Ui {
    class PrintSelection;
}

class PrintSelection : public QDialog {
    Q_OBJECT
public:
    PrintSelection(QWidget *parent = 0);
    ~PrintSelection();

    void setHoruxData(QStringList header, QMap<int, QStringList>data);
    void setCSVData(QStringList header, QMap<int, QStringList>data);
    void setSQLData(QSqlQuery *query, int primaryKey);
    void setPrintedUser(QStringList userIds);

    QStringList getCheckedUser(int primaryKey);

protected:
    void changeEvent(QEvent *e);

private:
    Ui::PrintSelection *ui;
    QStringList printedUsers;
};

#endif // PRINTSELECTION_H
