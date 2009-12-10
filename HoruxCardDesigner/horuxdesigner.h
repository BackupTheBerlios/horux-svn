#ifndef HORUXDESIGNER_H
#define HORUXDESIGNER_H

#include <QtGui/QMainWindow>
#include <QSvgWidget>


namespace Ui
{
    class HoruxDesigner;
}

class HoruxDesigner : public QMainWindow
{
    Q_OBJECT

public:
    HoruxDesigner(QWidget *parent = 0);
    ~HoruxDesigner();

private:
    Ui::HoruxDesigner *ui;

    QSvgWidget *card;
};

#endif // HORUXDESIGNER_H
