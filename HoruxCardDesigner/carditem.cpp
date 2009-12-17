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

    isPrinting = false;
}

void CardItem::reset()
{
    bkgColor = QColor(Qt::white);

    cardSize = CR80;
    cardFormat = L;
    gridSize = 1;
    isGrid = false;
    isGridAlign = false;

    update();
}

QSizeF CardItem::getSizeMm()
{
    switch(cardSize)
    {
        case CR80:
            return QSizeF(85.6,53.98);
            break;
        case CR90:
            return QSizeF(92.07,60.33);
            break;
        case CR79:
            return QSizeF(83.90,52.10);
            break;
    }
}

void CardItem::paint(QPainter *painter, const QStyleOptionGraphicsItem *option, QWidget *widget)
{
    QPainterPath path;

    float ratio;
    int width = 0;

    switch(cardSize)
    {
        case CR80:
            ratio = 85.6/53.98;
            width = 195;
            break;
        case CR90:
            ratio = 92.07/60.33;
            width = 210;
            break;
        case CR79:
            ratio = 83.90/52.10;
            width = 191;
            break;
        default:
            break;
    }

    if(cardFormat == P)
        path.addRoundedRect(0,0,width,width*ratio,10,10);
    else
        path.addRoundedRect(0,0,width*ratio,width,10,10);

    if(bkgFile != "")
    {

        if(cardFormat == P)
        {
            //bkgBrush.setStyle(Qt::TexturePattern);
            bkgBrush.setTexture(pix.scaled(width,width*ratio, Qt::IgnoreAspectRatio, Qt::FastTransformation));
        }
        else
        {
            //bkgBrush.setStyle(Qt::TexturePattern);
            bkgBrush.setTexture(pix.scaled(width*ratio,width, Qt::IgnoreAspectRatio, Qt::SmoothTransformation));
        }
    }
    else
    {
       bkgBrush.setStyle(Qt::SolidPattern);
       bkgBrush.setColor(bkgColor);

    }

    if(isPrinting)
    {
        painter->setPen(Qt::NoPen);
    }
    else
    {
        painter->setPen(Qt::SolidLine);
    }

    painter->setBrush(bkgBrush);

    painter->drawPath(path);
    setPath(path);

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

    if(!isPrinting)
        QGraphicsPathItem::paint(painter, option, widget);
}

void CardItem::setSize(int size)
{
    cardSize = (Size)size;
    update();
}

void CardItem::setFormat(int format)
{
    cardFormat = (Format)format;
    update();
}

void CardItem::setBkgColor(const QString &color)
{
    bkgColor.setNamedColor(color);;
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
