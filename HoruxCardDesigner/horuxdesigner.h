#ifndef HORUXDESIGNER_H
#define HORUXDESIGNER_H

#include <QtGui/QMainWindow>
#include <QGraphicsSvgItem>

#include "cardscene.h"

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

    CardScene *scene;

    QGraphicsSvgItem *card;
};

#endif // HORUXDESIGNER_H
