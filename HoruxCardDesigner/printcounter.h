#ifndef PRINTCOUNTER_H
#define PRINTCOUNTER_H

#include <QDialog>

namespace Ui {
    class PrintCounter;
}

class PrintCounter : public QDialog {
    Q_OBJECT
public:
    PrintCounter(QWidget *parent = 0);
    ~PrintCounter();

    int getInitialValue();
    int getIncrement();
    int getDigits();

    void setValues(int, int, int);

protected:
    void changeEvent(QEvent *e);

private:
    Ui::PrintCounter *ui;
};

#endif // PRINTCOUNTER_H
