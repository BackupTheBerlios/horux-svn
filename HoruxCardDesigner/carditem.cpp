#include <QtGui>

#include "carditem.h"

CardItem::CardItem( Size size,  Format format, QGraphicsItem * parent) : QGraphicsPathItem (parent)
{
    bkgColor = QColor(Qt::white);

    cardSize = size;
    cardFormat = format;
    gridSize = 1;
    isGrid = false;
    isGridAlign = false;

    setFlag(QGraphicsItem::ItemIsMovable, true);
    setFlag(QGraphicsItem::ItemIsSelectable, true);
    setFlag(QGraphicsItem::ItemClipsChildrenToShape, true);

    cardSettings = new CardPage();
    connect(cardSettings->sizeCombo, SIGNAL(currentIndexChanged ( int )), this, SLOT(setSize(int)));
    connect(cardSettings->orientationCombo, SIGNAL(currentIndexChanged ( int )), this, SLOT(setFormat(int)));
    connect(cardSettings->bkgColorColorLineEdit, SIGNAL(textChanged(const QString & )), this, SLOT(setBkgColor()));
    connect(cardSettings->bkgPicturePictureLineEdit, SIGNAL(textChanged(const QString & )), this, SLOT(setBkgPixmap(QString)));

    connect(cardSettings->gridDrawCombo, SIGNAL(currentIndexChanged ( int )), this, SLOT(viewGrid(int)));
    connect(cardSettings->gridSizeSpinBox, SIGNAL(valueChanged  ( int )), this, SLOT(setGridSize(int)));
    connect(cardSettings->gridAlignCombo, SIGNAL(currentIndexChanged ( int )), this, SLOT(alignGrid(int)));


    cardSettings->sizeCombo->setCurrentIndex(getSize());
    cardSettings->orientationCombo->setCurrentIndex(getFormat());

    update();
}

void CardItem::definePath()
{
    float ratio;

    switch(cardSize)
    {
        case CR80:
            ratio = 85.6/53.98;
            break;
        case CR90:
            ratio = 92.07/60.33;
            break;
        case CR79:
            ratio = 83.90/52.10;
            break;
        default:
            break;
    }

    if(cardFormat == P)
        cardPath.addRoundedRect(0,0,195,195*ratio,10,10);
    else
        cardPath.addRoundedRect(0,0,195*ratio,195,10,10);


    setPath(cardPath);


}

void CardItem::paint(QPainter *painter, const QStyleOptionGraphicsItem *, QWidget *)
{
    QPainterPath path;

    float ratio;

    switch(cardSize)
    {
        case CR80:
            ratio = 85.6/53.98;
            break;
        case CR90:
            ratio = 92.07/60.33;
            break;
        case CR79:
            ratio = 83.90/52.10;
            break;
        default:
            break;
    }

    if(cardFormat == P)
        path.addRoundedRect(0,0,195,195*ratio,10,10);
    else
        path.addRoundedRect(0,0,195*ratio,195,10,10);

    if(bkgFile != "")
    {

        if(cardFormat == P)
        {
            bkgBrush.setStyle(Qt::TexturePattern);
            bkgBrush.setTexture(pix.scaled(195,195*ratio, Qt::IgnoreAspectRatio, Qt::SmoothTransformation));
        }
        else
        {
            bkgBrush.setStyle(Qt::TexturePattern);
            bkgBrush.setTexture(pix.scaled(195*ratio,195, Qt::IgnoreAspectRatio, Qt::SmoothTransformation));
        }
    }
    else
    {
       bkgBrush.setStyle(Qt::SolidPattern);
       bkgBrush.setColor(bkgColor);

    }

    painter->setBrush(bkgBrush);

    painter->drawPath(path);

    if(isGrid)
    {
        if(cardFormat == L)
        {
            for(int x=gridSize*5; x<195*ratio; x+=gridSize*5)
            {
                for(int y=gridSize*5; y<195; y+=gridSize*5)
                {
                    painter->drawPoint(x,y);
                }
            }
        }
        else
        {
            for(int x=gridSize*5; x<195; x+=gridSize*5)
            {
                for(int y=gridSize*5; y<195*ratio; y+=gridSize*5)
                {
                    painter->drawPoint(x,y);
                }
            }
        }
    }

}

void CardItem::setSize(int size)
{
    cardSize = (Size)size;
    definePath();
}

void CardItem::setFormat(int format)
{
    cardFormat = (Format)format;
    definePath();
}

void CardItem::setBkgColor()
{
    bkgColor = cardSettings->bkgColor;
    update();
}

void CardItem::setBkgPixmap(QString file)
{
    bkgFile = file;
    pix.load(file);
    update();
}

void CardItem::viewGrid(int flag)
{
    isGrid = (bool)flag;
    update();
}

void CardItem::alignGrid(int flag)
{
    isGridAlign = (bool)flag;
    update();
}

void CardItem::setGridSize(int size)
{
    gridSize = size;
    update();
}
