#ifndef CSVTEST_H
#define CSVTEST_H

#include <QDialog>

namespace Ui {
    class CsvTest;
}

class CsvTest : public QDialog {
    Q_OBJECT
public:
    CsvTest(QWidget *parent = 0);
    ~CsvTest();

    void setCSV(QString csvContent);

protected:
    void changeEvent(QEvent *e);

private:
    Ui::CsvTest *ui;
};

#endif // CSVTEST_H
