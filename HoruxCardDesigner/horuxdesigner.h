#ifndef HORUXDESIGNER_H
#define HORUXDESIGNER_H

#include <QtGui/QMainWindow>


#include "cardscene.h"

class QButtonGroup;
class QTableWidget;
class CardTextItem;

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
     void createToolBox();
     void setTableParam(QGraphicsItem *item);

private slots:
     void buttonGroupClicked(int id);
     void itemInserted(CardTextItem *item);
     void textInserted(QGraphicsTextItem *item);
     void itemSelected(QGraphicsItem *item);
     void selectionChanged();

protected:
    void resizeEvent ( QResizeEvent * even);

private:
    Ui::HoruxDesigner *ui;

    QButtonGroup *buttonGroup;

    CardScene *scene;

    QWidget *param;
};

#endif // HORUXDESIGNER_H
