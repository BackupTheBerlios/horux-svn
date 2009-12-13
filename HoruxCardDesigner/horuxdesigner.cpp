#include <QtGui>
#include "horuxdesigner.h"
#include "ui_horuxdesigner.h"
#include "carditemtext.h"

const int InsertTextButton = 10;
const int InsertImageButton = 11;

HoruxDesigner::HoruxDesigner(QWidget *parent)
    : QMainWindow(parent), ui(new Ui::HoruxDesigner)
{
    ui->setupUi(this);

    createToolBox();

    scene = new CardScene(this);
    connect(scene, SIGNAL(itemInserted(CardTextItem *)),
             this, SLOT(itemInserted(CardTextItem *)));

    connect(scene, SIGNAL(textInserted(QGraphicsTextItem *)),
         this, SLOT(textInserted(QGraphicsTextItem *)));

    connect(scene, SIGNAL(itemSelected(QGraphicsItem *)),
         this, SLOT(itemSelected(QGraphicsItem *)));


    ui->graphicsView->setScene(scene);
}

HoruxDesigner::~HoruxDesigner()
{
    delete ui;
}

 void HoruxDesigner::createToolBox()
 {
     ui->toolbox->removeItem(0);

     buttonGroup = new QButtonGroup;
     buttonGroup->setExclusive(false);
     connect(buttonGroup, SIGNAL(buttonClicked(int)),
             this, SLOT(buttonGroupClicked(int)));

     QGridLayout *layout = new QGridLayout;

     //Text
     QToolButton *textButton = new QToolButton;
     textButton->setCheckable(true);
     buttonGroup->addButton(textButton, InsertTextButton);

     textButton->setIcon(QIcon(QPixmap(":/images/textpointer.png")
                         .scaled(30, 30)));
     textButton->setIconSize(QSize(50, 50));
     QGridLayout *textLayout = new QGridLayout;
     textLayout->addWidget(textButton, 0, 0, Qt::AlignHCenter);
     textLayout->addWidget(new QLabel(tr("Text")), 1, 0, Qt::AlignCenter);
     QWidget *textWidget = new QWidget;
     textWidget->setLayout(textLayout);
     layout->addWidget(textWidget, 1, 1);


     //Image
     QToolButton *imageButton = new QToolButton;
     imageButton->setCheckable(true);
     buttonGroup->addButton(imageButton, InsertImageButton);

     imageButton->setIcon(QIcon(QPixmap(":/images/656.jpg")));
     imageButton->setIconSize(QSize(50, 50));
     QGridLayout *imageLayout = new QGridLayout;
     imageLayout->addWidget(imageButton, 0, 0, Qt::AlignHCenter);
     imageLayout->addWidget(new QLabel(tr("Picture")), 1, 0, Qt::AlignCenter);
     QWidget *imageWidget = new QWidget;
     imageWidget->setLayout(imageLayout);
     layout->addWidget(imageWidget, 1, 2);



     layout->setRowStretch(3, 10);
     layout->setColumnStretch(2, 10);

     QWidget *itemWidget = new QWidget;
     itemWidget->setLayout(layout);


     ui->toolbox->setSizePolicy(QSizePolicy(QSizePolicy::Maximum, QSizePolicy::Ignored));
     ui->toolbox->setMinimumWidth(itemWidget->sizeHint().width());
     ui->toolbox->insertItem(1, itemWidget, tr("Object"));
 }

void HoruxDesigner::buttonGroupClicked(int id)
 {
     QList<QAbstractButton *> buttons = buttonGroup->buttons();
     foreach (QAbstractButton *button, buttons) {
     if (buttonGroup->button(id) != button)
         button->setChecked(false);
     }
     if (id == InsertTextButton) {
         scene->setMode(CardScene::InsertText);
     } else {
         //scene->setItemType(DiagramItem::DiagramType(id));
         scene->setMode(CardScene::InsertItem);
     }
 }

void HoruxDesigner::itemInserted(CardTextItem *item)
 {
     //pointerTypeGroup->button(int(DiagramScene::MoveItem))->setChecked(true);
     //scene->setMode(DiagramScene::Mode(pointerTypeGroup->checkedId()));
     //buttonGroup->button(int(item->diagramType()))->setChecked(false);
 }

 void HoruxDesigner::textInserted(QGraphicsTextItem *)
 {
     buttonGroup->button(InsertTextButton)->setChecked(false);
     scene->setMode(CardScene::MoveItem);
 }

 void HoruxDesigner::itemSelected(QGraphicsItem *item)
 {
     CardTextItem *textItem =
        qgraphicsitem_cast<CardTextItem *>(item);
 }
