#include <QtGui>
#include <QDataStream>

#include "carditem.h"
#include "carditemtext.h"
#include "pixmapitem.h"

CardItem::CardItem( Size size,  Format format, int f, QGraphicsItem * parent) : QGraphicsPathItem (parent)
{
    bkgColor = QColor(Qt::white);

    cardSize = size;
    cardFormat = format;
    gridSize = 1;
    isGrid = false;
    isGridAlign = false;

    isLocked = false;

    face = f;

    setFlag(QGraphicsItem::ItemIsMovable, true);
    setFlag(QGraphicsItem::ItemIsSelectable, true);
  //  setFlag(QGraphicsItem::ItemClipsChildrenToShape, true);

    isPrinting = false;

    this->setCacheMode(QGraphicsItem::ItemCoordinateCache);
}

void CardItem::incrementCounter() {
    foreach(QGraphicsItem *item, this->childItems())
    {
        if(item->type() == QGraphicsItem::UserType+3) {
            CardTextItem *textItem = qgraphicsitem_cast<CardTextItem *>(item);

            textItem->incrementCounter();
        }
    }
}

void CardItem::setPrintingMode(bool printing, QBuffer &picture, QMap<QString, QString>userData)
{
    isPrinting = printing;
    update();

    foreach(QGraphicsItem *item, this->childItems())
    {
        switch(item->type())
        {
        case QGraphicsItem::UserType+3: //text
            {
                CardTextItem *textItem = qgraphicsitem_cast<CardTextItem *>(item);
                textItem->setPrintingMode(printing, userData);
            }
            break;
        case QGraphicsItem::UserType+4: //Pixmap
            {
                PixmapItem *pixmapItem = qgraphicsitem_cast<PixmapItem *>(item);
                pixmapItem->setPrintingMode( printing, picture );
            }
            break;
        }
    }

}

void CardItem::loadCard(QDomElement card )
{
    QDomNode node = card.firstChild();

    qreal posX = 0;
    qreal posY = 0;

    while(!node.isNull())
    {
        if(node.toElement().tagName() == "face")
        {
            face = node.toElement().text().toInt();
        }

        if(node.toElement().tagName() == "cardSize")
        {
            cardSize = (Size)node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "cardFormat")
        {
            cardFormat = (Format)node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "gridSize")
        {
            gridSize = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "isGrid")
        {
            isGrid = (bool)node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "isGridAlign")
        {
            isGridAlign = (bool)node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "posX")
        {
            posX = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "posY")
        {
            posY = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "bkgColor")
        {
            bkgColor.setNamedColor( node.toElement().text() );
        }
        if(node.toElement().tagName() == "bkgFile")
        {
            bkgFile = node.toElement().text();
            pix.load(bkgFile);
        }
        if(node.toElement().tagName() == "CardTextItem")
        {
            CardTextItem* textItem = new CardTextItem(this);
            textItem->loadText( node.toElement() );

            connect(textItem, SIGNAL(lostFocus(CardTextItem *)),
                    scene (), SLOT(editorLostFocus(CardTextItem *)));

            connect(textItem, SIGNAL(selectedChange(QGraphicsItem *)),
                    scene (), SIGNAL(itemSelected(QGraphicsItem *)));

        }

        if(node.toElement().tagName() == "CardPixmapItem")
        {
            PixmapItem *pixmapItem = new PixmapItem(this);
            pixmapItem->loadPixmap( node.toElement() );

            /*connect(pixmapItem, SIGNAL(selectedChange(QGraphicsItem *)),
                   scene (), SIGNAL(itemSelected(QGraphicsItem *)));*/
        }

        if(node.toElement().tagName() == "isLocked")
        {
            isLocked = node.toElement().text().toInt();

            if(isLocked) {
                setFlag(QGraphicsItem::ItemIsMovable, false);
            }

        }

        node = node.nextSibling();
    }


    this->setPos(posX, posY);
}


QDomElement CardItem::getXmlItem(QDomDocument xml )
{
    QDomElement card = xml.createElement( "CardItem");

    QDomElement newElement = xml.createElement( "cardSize");
    QDomText text =  xml.createTextNode(QString::number(cardSize));
    newElement.appendChild(text);
    card.appendChild(newElement);

    newElement = xml.createElement( "cardFormat");
    text =  xml.createTextNode(QString::number(cardFormat));
    newElement.appendChild(text);
    card.appendChild(newElement);

    newElement = xml.createElement( "gridSize");
    text =  xml.createTextNode(QString::number(gridSize));
    newElement.appendChild(text);
    card.appendChild(newElement);

    newElement = xml.createElement( "isGrid");
    text =  xml.createTextNode(QString::number(isGrid));
    newElement.appendChild(text);
    card.appendChild(newElement);

    newElement = xml.createElement( "isGridAlign");
    text =  xml.createTextNode(QString::number(isGridAlign));
    newElement.appendChild(text);
    card.appendChild(newElement);

    newElement = xml.createElement( "posX");
    text =  xml.createTextNode(QString::number(pos().x()));
    newElement.appendChild(text);
    card.appendChild(newElement);

    newElement = xml.createElement( "posY");
    text =  xml.createTextNode(QString::number(pos().y()));
    newElement.appendChild(text);
    card.appendChild(newElement);

    newElement = xml.createElement( "bkgColor");
    text =  xml.createTextNode(bkgColor.name());
    newElement.appendChild(text);
    card.appendChild(newElement);

    newElement = xml.createElement( "bkgFile");
    text =  xml.createTextNode(bkgFile);
    newElement.appendChild(text);
    card.appendChild(newElement);

    newElement = xml.createElement( "isLocked");
    text =  xml.createTextNode(QString::number(isLocked));
    newElement.appendChild(text);
    card.appendChild(newElement);

    newElement = xml.createElement( "face");
    text =  xml.createTextNode(QString::number(face));
    newElement.appendChild(text);
    card.appendChild(newElement);

    return card;
}


void CardItem::reset()
{
    bkgColor = QColor(Qt::white);

    cardSize = CR80;
    cardFormat = L;
    gridSize = 1;
    isGrid = false;
    isGridAlign = false;

    setPos(100,100);

    bkgFile = "";

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

    return QSizeF(85.6,53.98);
}

void CardItem::paint(QPainter *painter, const QStyleOptionGraphicsItem *option, QWidget *widget)
{
    QPainterPath path;

    float ratio;
    int width = 0;

    if(isPrinting)
    {
        painter->setPen(Qt::NoPen);
    }
    else
    {
        QPen pen;  // creates a default pen
        pen.setWidthF(1);
        painter->setPen(pen);

        /*if(face == 1) {
            QGraphicsTextItem *faceItem = new QGraphicsTextItem(tr("Front card"), this);
            faceItem->setPos(10,-30);
            faceItem->setDefaultTextColor(Qt::white);
        } else {
            QGraphicsTextItem *faceItem = new QGraphicsTextItem(tr("Back card"), this);
            faceItem->setPos(10,-30);
        }*/
    }

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
            bkgBrush.setTexture(pix.scaled(width,width*ratio, Qt::IgnoreAspectRatio, Qt::SmoothTransformation));
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


    painter->setBrush(bkgBrush);

    painter->drawPath(path);
    setPath(path);

    if(isGrid && !isPrinting)
    {
        if( gridSize == 0 ) gridSize = 1;

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
    if((Size)size != cardSize)
        emit itemChange();

    cardSize = (Size)size;
    update();
}

void CardItem::setFormat(int format)
{
    if((Format)format != cardFormat)
        emit itemChange();


    cardFormat = (Format)format;
    update();
}

void CardItem::setBkgColor(const QString &color)
{
    if(color!= bkgColor.name())
        emit itemChange();

    bkgColor.setNamedColor(color);;
    update();
}

void CardItem::setBkgPixmap(QString file)
{
    if(bkgFile!= file)
        emit itemChange();

    bkgFile = file;
    pix.load(file);
    update();
}

void CardItem::viewGrid(int flag)
{
    if((bool)flag!= isGrid)
        emit itemChange();

    isGrid = (bool)flag;
    update();
}

void CardItem::alignGrid(int flag)
{
    if((bool)flag!= isGridAlign)
        emit itemChange();

    isGridAlign = (bool)flag;
    update();
}

void CardItem::setGridSize(int size)
{
    if(size!= gridSize)
        emit itemChange();

    gridSize = size;
    update();
}

void CardItem::setLocked(int flag) {
    if((bool)flag!= isLocked)
        emit itemChange();

    isLocked = (bool)flag;

    if(isLocked) {
        setFlag(QGraphicsItem::ItemIsMovable, false);
    } else {
        setFlag(QGraphicsItem::ItemIsMovable, true);
    }
}
