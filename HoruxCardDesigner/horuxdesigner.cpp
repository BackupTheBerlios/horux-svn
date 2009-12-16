#include <QtGui>
#include "horuxdesigner.h"
#include "ui_horuxdesigner.h"
#include "carditemtext.h"
#include "carditem.h"
#include "confpage.h"

const int InsertTextButton = 10;
const int InsertImageButton = 11;

HoruxDesigner::HoruxDesigner(QWidget *parent)
    : QMainWindow(parent), ui(new Ui::HoruxDesigner)
{
    ui->setupUi(this);

    cardPage = NULL;
    textPage = NULL;

    createToolBox();

    scene = new CardScene(this);
    connect(scene, SIGNAL(itemInserted(CardTextItem *)),
             this, SLOT(itemInserted(CardTextItem *)));

    connect(scene, SIGNAL(textInserted(QGraphicsTextItem *)),
         this, SLOT(textInserted(QGraphicsTextItem *)));

    connect(scene, SIGNAL(itemSelected(QGraphicsItem *)),
         this, SLOT(itemSelected(QGraphicsItem *)));

    connect(scene, SIGNAL( selectionChanged()),
         this, SLOT(selectionChanged()));

    ui->graphicsView->setScene(scene);
    ui->graphicsView->setRenderHint(QPainter::Antialiasing);

    param = NULL;
    selectionChanged();


    fontCombo = new QFontComboBox();
    fontSizeCombo = new QComboBox();
    fontSizeCombo->setEditable(true);
    for (int i = 8; i < 30; i = i + 2)
        fontSizeCombo->addItem(QString().setNum(i));
    QIntValidator *validator = new QIntValidator(2, 64, this);
    fontSizeCombo->setValidator(validator);
    connect(fontSizeCombo, SIGNAL(currentIndexChanged(const QString &)),
         this, SLOT(fontSizeChanged(const QString &)));


    connect(fontCombo, SIGNAL(currentFontChanged(const QFont &)),
         this, SLOT(currentFontChanged(const QFont &)));


     sceneScaleCombo = new QComboBox;
     QStringList scales;
     scales << tr("50%") << tr("75%") << tr("100%") << tr("125%") << tr("150%")<< tr("200%")<< tr("250%");
     sceneScaleCombo->addItems(scales);
     sceneScaleCombo->setCurrentIndex(2);
     connect(sceneScaleCombo, SIGNAL(currentIndexChanged(const QString &)),
             this, SLOT(sceneScaleChanged(const QString &)));

     ui->toolBar->addWidget(fontCombo);
     ui->toolBar->addWidget(fontSizeCombo);
     ui->toolBar->addSeparator();
     ui->toolBar->addWidget(sceneScaleCombo);

     connect(ui->actionItalic, SIGNAL(triggered()),
             this, SLOT(handleFontChange()));

     connect(ui->actionBold, SIGNAL(triggered()),
             this, SLOT(handleFontChange()));

     connect(ui->actionUnderline, SIGNAL(triggered()),
             this, SLOT(handleFontChange()));

     connect(ui->actionDelete, SIGNAL(triggered()),
         this, SLOT(deleteItem()));

     connect(ui->actionBring_to_front, SIGNAL(triggered()),
             this, SLOT(bringToFront()));

     connect(ui->actionSend_to_back, SIGNAL(triggered()),
             this, SLOT(sendToBack()));

     connect(ui->actionNew, SIGNAL(triggered()),
             this, SLOT(newCard()));

     connect(ui->actionPrint_preview, SIGNAL(triggered()),
             this, SLOT(printPreview()));

}

HoruxDesigner::~HoruxDesigner()
{
    delete ui;
}

void HoruxDesigner::printPreview()
{
    QPrinter printer;
    if (QPrintDialog(&printer).exec() == QDialog::Accepted)
    {
         QPainter painter(&printer);
         painter.setRenderHint(QPainter::Antialiasing);
         scene->render(&painter);
    }
}


void HoruxDesigner::newCard()
{
 foreach (QGraphicsItem *item, scene->items()) {
     if (item->type() !=  QGraphicsItem::UserType+1) {
         scene->removeItem(item);
     }
 }

 scene->reset();

}

void HoruxDesigner::deleteItem()
{
 foreach (QGraphicsItem *item, scene->selectedItems()) {
     if (item->type() !=  QGraphicsItem::UserType+1) {
         scene->removeItem(item);
     }
 }
}

void HoruxDesigner::bringToFront()
 {
     if (scene->selectedItems().isEmpty())
         return;

     QGraphicsItem *selectedItem = scene->selectedItems().first();
     QList<QGraphicsItem *> overlapItems = selectedItem->collidingItems();

     qreal zValue = selectedItem->zValue();
     foreach (QGraphicsItem *item, overlapItems) {
         if (item->zValue() >= zValue &&
             item->type() != QGraphicsItem::UserType+1)
         {
             zValue = item->zValue() + 0.1;
         }
     }
     selectedItem->setZValue(zValue);
 }

 void HoruxDesigner::sendToBack()
 {
     if (scene->selectedItems().isEmpty())
         return;

     QGraphicsItem *selectedItem = scene->selectedItems().first();
     QList<QGraphicsItem *> overlapItems = selectedItem->collidingItems();

     qreal zValue = selectedItem->zValue();

     foreach (QGraphicsItem *item, overlapItems) {
         if (item->zValue() <= zValue &&
             item->type()  != QGraphicsItem::UserType+1)
         {
             zValue = item->zValue() - 0.1;
         }
     }
     selectedItem->setZValue(zValue);
 }


void HoruxDesigner::currentFontChanged(const QFont &)
{
 handleFontChange();
}

void HoruxDesigner::fontSizeChanged(const QString &)
{
 handleFontChange();
}


void HoruxDesigner::handleFontChange()
{
 QFont font = fontCombo->currentFont();
 font.setPointSize(fontSizeCombo->currentText().toInt());
 font.setWeight(ui->actionBold->isChecked() ? QFont::Bold : QFont::Normal);
 font.setItalic(ui->actionItalic->isChecked());
 font.setUnderline(ui->actionUnderline->isChecked());

 scene->setFont(font);
}


void HoruxDesigner::sceneScaleChanged(const QString &scale)
 {
     double newScale = scale.left(scale.indexOf(tr("%"))).toDouble() / 100.0;
     QMatrix oldMatrix = ui->graphicsView->matrix();
     ui->graphicsView->resetMatrix();
     ui->graphicsView->translate(oldMatrix.dx(), oldMatrix.dy());
     ui->graphicsView->scale(newScale, newScale);
 }

void HoruxDesigner::setParamView(QGraphicsItem *item)
{
    switch(item->type())
    {
        case QGraphicsItem::UserType+1: //card
            {
                CardItem *card = qgraphicsitem_cast<CardItem *>(item);
                if(card)
                {
                    if(!cardPage)
                    {
                        cardPage = new CardPage(ui->widget);
                        connect(cardPage->sizeCb, SIGNAL(currentIndexChanged ( int )), card, SLOT(setSize(int)));
                        connect(cardPage->orientation, SIGNAL(currentIndexChanged ( int )), card, SLOT(setFormat(int)));
                        connect(cardPage->bkgColor, SIGNAL(textChanged(const QString & )), card, SLOT(setBkgColor(const QString &)));
                        connect(cardPage->bkgPicture, SIGNAL(textChanged(const QString & )), card, SLOT(setBkgPixmap(QString)));

                        connect(cardPage->gridDraw, SIGNAL(currentIndexChanged ( int )), card, SLOT(viewGrid(int)));
                        connect(cardPage->gridSize, SIGNAL(valueChanged  ( int )), card, SLOT(setGridSize(int)));
                        connect(cardPage->gridAlign, SIGNAL(currentIndexChanged ( int )), card, SLOT(alignGrid(int)));


                        cardPage->sizeCb->setCurrentIndex(card->getSize());
                        cardPage->orientation->setCurrentIndex(card->getFormat());

                        if (card->bkgColor.isValid()) {
                             cardPage->color = card->bkgColor;
                             cardPage->bkgColor->setText(card->bkgColor.name());
                             cardPage->bkgColor->setStyleSheet("background-color: " + card->bkgColor.name() + ";");
                         }

                        cardPage->bkgPicture->setText(card->bkgFile);

                        cardPage->gridAlign->setCurrentIndex((int)card->isGridAlign);
                        cardPage->gridDraw->setCurrentIndex((int)card->isGrid);
                        cardPage->gridSize->setValue(card->gridSize);
                    }

                    if(textPage)
                        textPage->hide();
                    cardPage->show();
                }
            }
            break;
        case QGraphicsItem::UserType+3: //text
            {
                CardTextItem *textItem = qgraphicsitem_cast<CardTextItem *>(item);
                if(textItem)
                {
                    if(textPage)
                    {
                        delete textPage;
                        textPage = NULL;
                    }

                    if(!textPage)
                    {
                        textPage = new TextPage(ui->widget);

                        connect(textPage->name, SIGNAL(textChanged ( const QString & )), textItem, SLOT(setName(const QString &)));
                        connect(textPage, SIGNAL(changeFont(const QFont &)), textItem, SLOT(fontChanged(const QFont &)));
                        connect(textPage, SIGNAL(changeColor ( const QColor & )), textItem, SLOT(colorChanged(const QColor &)));
                        connect(textPage->rotation, SIGNAL(textChanged(QString)), textItem, SLOT(rotationChanged(const QString &)));

                        textPage->name->setText(textItem->name);

                        textPage->font = textItem->font();
                        textPage->fontText->setText(textItem->font().family());

                        if (textItem->defaultTextColor().isValid()) {
                             textPage->color = textItem->defaultTextColor();
                             textPage->colorText->setText(textItem->defaultTextColor().name());
                             textPage->colorText->setStyleSheet("background-color: " + textItem->defaultTextColor().name() + ";");
                         }

                        textPage->rotation->setText(QString::number(textItem->rotation));
                        textPage->top->setText(QString::number(textItem->pos().y()));
                        textPage->left->setText(QString::number(textItem->pos().x()));
                        textPage->widthText->setText(QString::number(textItem->document()->size().width()));
                        textPage->heightText->setText(QString::number(textItem->document()->size().height()));
                    }

                    if(cardPage)
                        cardPage->hide();


                    textPage->show();

                }
            }
            break;
    }
}

void HoruxDesigner::resizeEvent ( QResizeEvent * even)
{
    scene->setSceneRect(ui->graphicsView->geometry());
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

 void HoruxDesigner::selectionChanged()
 {

     if (scene->selectedItems().isEmpty() || scene->selectedItems().count() > 1 )
     {
         setParamView(scene->getCardItem());
         return;
     }
     setParamView(scene->selectedItems().at(0));

 }
