#include "horuxdesigner.h"
#include "ui_horuxdesigner.h"

HoruxDesigner::HoruxDesigner(QWidget *parent)
    : QMainWindow(parent), ui(new Ui::HoruxDesigner)
{
    ui->setupUi(this);

    card = new QGraphicsSvgItem(":/CR-80.svg");
    card->setFlag(QGraphicsItem::ItemIsMovable, true);
    card->setFlag(QGraphicsItem::ItemIsSelectable, true);


    scene = new CardScene(this);
    scene->addItem(card);

    ui->graphicsView->setScene(scene);
}

HoruxDesigner::~HoruxDesigner()
{
    delete ui;
}
