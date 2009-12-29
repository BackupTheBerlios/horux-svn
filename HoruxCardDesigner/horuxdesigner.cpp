#include <QtGui>
#include "horuxdesigner.h"
#include "ui_horuxdesigner.h"
#include "carditemtext.h"
#include "carditem.h"
#include "confpage.h"
#include "printpreview.h"
#include "horuxdialog.h"

const int InsertTextButton = 10;
const int InsertImageButton = 11;

HoruxDesigner::HoruxDesigner(QWidget *parent)
    : QMainWindow(parent), ui(new Ui::HoruxDesigner)
{
    ui->setupUi(this);

    printer = new QPrinter(QPrinter::HighResolution);

    cardPage = NULL;
    textPage = NULL;
    pixmapPage = NULL;

    createToolBox();

    scene = new CardScene(this);
    connect(scene, SIGNAL(itemInserted(QGraphicsItem *)),
             this, SLOT(itemInserted(QGraphicsItem *)));

    connect(scene, SIGNAL(textInserted(QGraphicsTextItem *)),
         this, SLOT(textInserted(QGraphicsTextItem *)));

    connect(scene, SIGNAL(itemSelected(QGraphicsItem *)),
         this, SLOT(itemSelected(QGraphicsItem *)));

    connect(scene, SIGNAL( selectionChanged()),
         this, SLOT(selectionChanged()));

    connect(scene, SIGNAL( itemMoved(QGraphicsItem *)),
         this, SLOT(itemMoved(QGraphicsItem *)));


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

     connect(ui->actionPrint, SIGNAL(triggered()),
             this, SLOT(print()));

     connect(ui->actionPrint_setup, SIGNAL(triggered()),
             this, SLOT(printSetup()));

     connect(ui->actionExit, SIGNAL(triggered()),
             this, SLOT(exit()));

     connect(ui->actionSave, SIGNAL(triggered()),
             this, SLOT(save()));

     connect(ui->actionSave_as, SIGNAL(triggered()),
             this, SLOT(saveAs()));

     connect(ui->actionDatabase, SIGNAL(triggered()),
             this, SLOT(setDatabase()));

     connect(ui->actionOpen, SIGNAL(triggered()),
             this, SLOT(open()));


     for (int i = 0; i < MaxRecentFiles; ++i) {
         recentFileActs[i] = new QAction(this);
         recentFileActs[i]->setVisible(false);
         connect(recentFileActs[i], SIGNAL(triggered()),
                 this, SLOT(openRecentFile()));
     }


    currenFile.setFileName("");

    setWindowTitle("Horux Card Designer - new card");

    for (int i = 0; i < MaxRecentFiles; ++i)
    {
        ui->menuRecent_files->addAction(recentFileActs[i]);

    }

    updateRecentFileActions();

    connect(&transport, SIGNAL(responseReady()), SLOT(readSoapResponse()));

    QSettings settings("Letux", "HoruxCardDesigner", this);

    QString host = settings.value("horux", "localhost").toString();
    QString username = settings.value("username", "root").toString();
    QString password = settings.value("password", "").toString();
    QString path = settings.value("path", "").toString();
    bool ssl = settings.value("ssl", "").toBool();

    QtSoapMessage message;
    message.setMethod("getAllUser");

    if(ssl)
        transport.setHost(host, true);
    else
        transport.setHost(host);

    transport.submitRequest(message, path+"/index.php?soap=horux");


}

HoruxDesigner::~HoruxDesigner()
{
    delete ui;
}


void HoruxDesigner::readSoapResponse()
{
     userCombo = new QComboBox();

     const QtSoapMessage &response = transport.getResponse();
     if (response.isFault()) {
         QMessageBox::warning(this,tr("Horux webservice error"),tr("Not able to call the Horux GUI web service."));
         return;
     }

    const QtSoapType &returnValue = response.returnValue();

    for(int i=0; i<returnValue.count(); i++ )
    {
       const QtSoapType &record =  returnValue[i];

       for(int j=0; j<record.count(); j+=24)
       {
           const QtSoapType &field_id =  record[j];
           const QtSoapType &field_name =  record[j+1];
           const QtSoapType &field_firstname =  record[j+2];

           userCombo->addItem(field_name["value"].toString() + " " + field_firstname["value"].toString(), field_id["value"].toInt());
       }

    }

    ui->toolBar->addSeparator();
    ui->toolBar->addWidget(userCombo);
}

 void HoruxDesigner::setCurrentFile(const QString &fileName)
 {
      currenFile.setFileName(fileName);
     if (fileName.isEmpty())
         setWindowTitle(tr("Horux Card Designer - new card"));
     else
         setWindowTitle(tr("Horux Card Designer - %2").arg(strippedName(fileName)));

     QSettings settings("Letux", "HoruxCardDesigner",this);
     QStringList files = settings.value("recentFileList").toStringList();
     files.removeAll(fileName);
     files.prepend(fileName);
     while (files.size() > MaxRecentFiles)
         files.removeLast();

     settings.setValue("recentFileList", files);

     foreach (QWidget *widget, QApplication::topLevelWidgets()) {
         HoruxDesigner *mainWin = qobject_cast<HoruxDesigner *>(widget);
         if (mainWin)
             mainWin->updateRecentFileActions();
     }
 }

 void HoruxDesigner::updateRecentFileActions()
 {
     QSettings settings("Letux", "HoruxCardDesigner",this);
     QStringList files = settings.value("recentFileList").toStringList();

     int numRecentFiles = qMin(files.size(), (int)MaxRecentFiles);

     for (int i = 0; i < numRecentFiles; ++i) {
         QString text = tr("&%1 %2").arg(i + 1).arg(strippedName(files[i]));
         recentFileActs[i]->setText(text);
         recentFileActs[i]->setData(files[i]);
         recentFileActs[i]->setVisible(true);
     }
     for (int j = numRecentFiles; j < MaxRecentFiles; ++j)
         recentFileActs[j]->setVisible(false);

     //separatorAct->setVisible(numRecentFiles > 0);
 }

 QString HoruxDesigner::strippedName(const QString &fullFileName)
 {
     return QFileInfo(fullFileName).fileName();
 }

 void HoruxDesigner::openRecentFile()
 {
     QAction *action = qobject_cast<QAction *>(sender());
     if (action)
     {
         newCard();
         currenFile.setFileName(action->data().toString());

        QString xml;
        currenFile.open(QIODevice::ReadOnly);
        xml = currenFile.readAll();
        scene->loadScene(xml);
        currenFile.close();

        setWindowTitle("Horux Card Designer - " + currenFile.fileName());

     }
}

void HoruxDesigner::open()
{
     QString selectedFilter;
     QString fileName = QFileDialog::getOpenFileName(this,
                                 tr("Open an Horux Card Designer file"),
                                 "",
                                 tr("Horux Card Designer (*.xml)"));
     if (!fileName.isEmpty())
     {
        setCurrentFile(fileName);

        newCard();

        currenFile.setFileName(fileName);

        QString xml;
        currenFile.open(QIODevice::ReadOnly);
        QTextStream data(&currenFile);
        data.setCodec("ISO 8859-1");
        xml = data.readAll();
        scene->loadScene(xml);
        currenFile.close();

        setWindowTitle("Horux Card Designer - " + currenFile.fileName());
     }
}

void HoruxDesigner::setDatabase()
{
    HoruxDialog dlg(this);

    QSettings settings("Letux", "HoruxCardDesigner", this);

    QString host = settings.value("horux", "localhost").toString();
    QString username = settings.value("username", "root").toString();
    QString password = settings.value("password", "").toString();
    QString path = settings.value("path", "").toString();
    bool ssl = settings.value("ssl", "").toBool();

    dlg.setHorux(host);
    dlg.setUsername(username);
    dlg.setPassword(password);
    dlg.setPath(path);
    dlg.setSSL(ssl);

    if(dlg.exec() == QDialog::Accepted)
    {
       settings.setValue("horux",dlg.getHorux());
        settings.setValue("username",dlg.getUsername());
        settings.setValue("password",dlg.getPassword());
        settings.setValue("path",dlg.getPath());
        settings.setValue("ssl",dlg.getSSL());
    }
}

void HoruxDesigner::save()
{
    if(currenFile.fileName() == "")
    {
        saveAs();
        return;
    }


    currenFile.open(QIODevice::WriteOnly);
    QTextStream data(&currenFile);
    data.setCodec("ISO 8859-1");

    QDomDocument xml;
    QDomElement root = xml.createElement ( "HoruxCardDesigner" );
    QDomElement card = qgraphicsitem_cast<CardItem*>(scene->getCardItem())->getXmlItem(xml);

    root.appendChild ( card );


    foreach (QGraphicsItem *item, scene->getCardItem()->childItems())
    {
        switch(item->type())
        {
            case QGraphicsItem::UserType + 3:
                card.appendChild(qgraphicsitem_cast<CardTextItem*>(item)->getXmlItem(xml));
                break;
            case QGraphicsItem::UserType + 4:
                card.appendChild(qgraphicsitem_cast<PixmapItem*>(item)->getXmlItem(xml));
                break;
        }
    }

    xml.appendChild ( root );

    QDomNode xmlNode =  xml.createProcessingInstruction ( "xml", "version=\"1.0\" encoding=\"ISO 8859-1\"" );
    xml.insertBefore ( xmlNode, xml.firstChild() );

    data << xml.toString() ;


    currenFile.close();

    setWindowTitle("Horux Card Designer - " + currenFile.fileName());
}

void HoruxDesigner::saveAs()
{
    QString name = QFileDialog::getSaveFileName(this,tr("Save the card"),"",tr("Horux Card Designer (*.xml)"));

    if(name != "")
    {
       currenFile.setFileName( name );
       save();
    }
}


void HoruxDesigner::exit()
{
    close ();
}


void HoruxDesigner::printSetup()
{
    QPageSetupDialog dlg(printer, this);

    dlg.exec();
}

void HoruxDesigner::printPreview()
{
    QPointF cardPos = scene->getCardItem()->pos();

    scene->getCardItem()->isPrinting = true;
    scene->getCardItem()->setPos(0,0);
    sceneScaleChanged("100%");
    QRectF cardRect = scene->getCardItem()->boundingRect();

    QPixmap pixmap(cardRect.size().toSize());
    pixmap.fill( Qt::white );
    QPainter painter(&pixmap);
    painter.setRenderHint(QPainter::Antialiasing);
    ui->graphicsView->render(&painter, QRectF(0,0,pixmap.size().width(),pixmap.size().height()), cardRect.toRect(), Qt::KeepAspectRatio );
    painter.end();

    PrintPreview dlg(pixmap, this);

    scene->getCardItem()->isPrinting = false;
    scene->getCardItem()->setPos(cardPos);
    sceneScaleChanged(sceneScaleCombo->currentText());



    if (dlg.exec() != QDialog::Rejected )
    {
        print();
    }


}

void HoruxDesigner::print()
{
    printer->setPaperSize(scene->getCardItem()->getSizeMm(),QPrinter::Millimeter);
    printer->setPageMargins(0,0,0,0,QPrinter::Millimeter);

    QPointF cardPos = scene->getCardItem()->pos();

    if (QPrintDialog(printer).exec() == QDialog::Accepted)
    {
         scene->getCardItem()->isPrinting = true;
         scene->getCardItem()->setPos(0,0);
         sceneScaleChanged("100%");

         QRectF cardRect = scene->getCardItem()->boundingRect();


         QPainter painter(printer);
         painter.setRenderHint(QPainter::Antialiasing);
         ui->graphicsView->render(&painter, printer->pageRect(), cardRect.toRect(), Qt::KeepAspectRatio );

         scene->getCardItem()->isPrinting = false;
         scene->getCardItem()->setPos(cardPos);
         sceneScaleChanged(sceneScaleCombo->currentText());
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
         if (item->zValue() >= zValue)
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
         if (item->zValue() <= zValue)
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

                    if(pixmapPage)
                        pixmapPage->hide();


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
                        connect(textPage->source, SIGNAL(currentIndexChanged ( int )), textItem, SLOT(sourceChanged(int)));
                        connect(textPage->top, SIGNAL(textChanged(QString)), textItem, SLOT(topChanged(const QString &)));
                        connect(textPage->left, SIGNAL(textChanged(QString)), textItem, SLOT(leftChanged(const QString &)));
                        connect(textPage->alignment, SIGNAL(currentIndexChanged ( int )), textItem, SLOT(alignmentChanged(int)));


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
                        textPage->alignment->setCurrentIndex(textItem->alignment);

                        textPage->source->setCurrentIndex( textItem->source );
                        textPage->connectDataSource();

                    }

                    if(cardPage)
                        cardPage->hide();
                    if(pixmapPage)
                        pixmapPage->hide();


                    textPage->show();

                }
            }
            break;
         case QGraphicsItem::UserType+4: //Pixmap
             {
                PixmapItem *pixmapItem = qgraphicsitem_cast<PixmapItem *>(item);

                if(pixmapItem)
                {
                    if(pixmapPage)
                    {
                        delete pixmapPage;
                        pixmapPage = NULL;
                    }

                    if(!pixmapPage)
                    {
                        pixmapPage = new PixmapPage(ui->widget);

                        pixmapPage->name->setText(pixmapItem->name);
                        pixmapPage->file->setText(pixmapItem->file);
                        pixmapPage->widthEdit->setText(QString::number(pixmapItem->boundingRect().width()));
                        pixmapPage->heightEdit->setText(QString::number(pixmapItem->boundingRect().height()));

                        connect(pixmapPage->name, SIGNAL(textChanged ( const QString & )), pixmapItem, SLOT(setName(const QString &)));
                        connect(pixmapPage->file, SIGNAL(textChanged(const QString & )), pixmapItem, SLOT(setPixmapFile(QString)));
                        connect(pixmapPage->source, SIGNAL(currentIndexChanged ( int )), pixmapItem, SLOT(sourceChanged(int)));
                        connect(pixmapPage, SIGNAL(newPicture(QByteArray)), pixmapItem, SLOT(setHoruxPixmap(QByteArray )));
                        connect(pixmapPage->widthEdit, SIGNAL(textChanged ( const QString & )), pixmapItem, SLOT(setWidth(const QString &)));
                        connect(pixmapPage->heightEdit, SIGNAL(textChanged ( const QString & )), pixmapItem, SLOT(setHeight(const QString &)));

                        connect(pixmapPage->top, SIGNAL(textChanged(QString)), pixmapItem, SLOT(topChanged(const QString &)));
                        connect(pixmapPage->left, SIGNAL(textChanged(QString)), pixmapItem, SLOT(leftChanged(const QString &)));

                        pixmapPage->top->setText(QString::number(pixmapItem->pos().y()));
                        pixmapPage->left->setText(QString::number(pixmapItem->pos().x()));


                        pixmapPage->source->setCurrentIndex( pixmapItem->source );
                        pixmapPage->connectDataSource();
                    }

                    if(textPage)
                        textPage->hide();
                    if(cardPage)
                        cardPage->hide();


                    pixmapPage->show();

                }

            }
            break;
    }
}

void HoruxDesigner::resizeEvent ( QResizeEvent * )
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

     imageButton->setIcon(QIcon(QPixmap(":/images/gadu.png")));
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
     }

     if(id == InsertImageButton) {
         scene->setMode(CardScene::InsertPicture);
     }
 }

void HoruxDesigner::itemInserted(QGraphicsItem *)
 {
     scene->setMode(CardScene::MoveItem);
     buttonGroup->button(InsertImageButton)->setChecked(false);
 }

 void HoruxDesigner::textInserted(QGraphicsTextItem *)
 {
     buttonGroup->button(InsertTextButton)->setChecked(false);
     scene->setMode(CardScene::MoveItem);
 }

 void HoruxDesigner::itemSelected(QGraphicsItem *)
 {
    /* CardTextItem *textItem =
        qgraphicsitem_cast<CardTextItem *>(item);*/
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

 void HoruxDesigner::itemMoved(QGraphicsItem *item)
 {
     if(item)
     {
        if(item->type() == QGraphicsItem::UserType + 3)
        {
            if(textPage)
            {
                textPage->top->setText(QString::number(item->pos().y()));
                textPage->left->setText(QString::number(item->pos().x()));
            }
        }
        if(item->type() == QGraphicsItem::UserType + 4)
        {
            if(pixmapPage)
            {
                pixmapPage->top->setText(QString::number(item->pos().y()));
                pixmapPage->left->setText(QString::number(item->pos().x()));
            }
        }
     }
 }
