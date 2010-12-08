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
    setFlag(QGraphicsItem::ItemClipsChildrenToShape, true);

    isPrinting = false;

    setSize(cardSize);

    setCacheMode(QGraphicsItem::DeviceCoordinateCache);

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

    setSize(cardSize);

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

    if(isPrinting) {
        foreach(QGraphicsLineItem *p, gridPoint) {
            p->hide();
        }
    } else {
        foreach(QGraphicsLineItem *p, gridPoint) {
            p->show();
        }
    }

    update();
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
            setSize(cardSize);
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
            setBkgColor(node.toElement().text());
        }
        if(node.toElement().tagName() == "bkgFile")
        {
            bkgFile = node.toElement().text();
            setBkgPixmap(bkgFile);
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
        return QSizeF(85.60,53.98);
        break;
    case CR90:
        return QSizeF(92.07,60.33);
        break;
    case CR79:
        return QSizeF(83.90,52.10);
        break;
    }

    return QSizeF(85.60,53.98);
}

void CardItem::paint(QPainter *painter, const QStyleOptionGraphicsItem *option, QWidget *widget)
{
    if(isPrinting) {
        setPen(Qt::NoPen);
    } else {
        setPen(Qt::SolidLine);
    }

    QPointF p = pos();
    QRectF r = boundingRect();

    r.setWidth( r.width() + p.x() + 50 );
    r.setHeight( r.height() + p.y() + 50 );

    scene()->setSceneRect(r);

    QGraphicsPathItem::paint(painter, option, widget);
}

void CardItem::setSize(int size)
{
    if((Size)size != cardSize)
        emit itemChange();

    cardSize = (Size)size;

    switch(cardSize)
    {
        case CR80:
            ratio = 85.6/53.98;
            cardWidth = 85.6 / 25.4 * PRINTER_DPI;
            break;
        case CR90:
            ratio = 92.07/60.33;
            cardWidth = 92.07/ 25.4 * PRINTER_DPI;
            break;
        case CR79:
            ratio = 83.90/52.10;
            cardWidth = 83.90/ 25.4 * PRINTER_DPI;
            break;
        default:
            break;
    }

    QPainterPath path;

    if(cardFormat == P)
        path.addRoundedRect(0,0,cardWidth,cardWidth*ratio,10,10);
    else
        path.addRoundedRect(0,0,cardWidth*ratio,cardWidth,10,10);

    setPath(path);

    viewGrid(isGrid);
}

void CardItem::setFormat(int format)
{
    if((Format)format != cardFormat)
        emit itemChange();

    cardFormat = (Format)format;

    setSize(cardSize);
}

void CardItem::setBkgColor(const QString &color)
{
    if(color!= bkgColor.name())
        emit itemChange();

    bkgColor.setNamedColor(color);

    bkgBrush.setColor(bkgColor);
    bkgBrush.setStyle(Qt::SolidPattern);
    setBrush(bkgBrush);

}

void CardItem::setBkgPixmap(QString file)
{
    if(bkgFile!= file)
        emit itemChange();

    bkgFile = file;


    if(bkgFile != "")
    {
        if(cardFormat == P)
        {
            bkgBrush.setTextureImage(QImage(file).scaled(cardWidth,cardWidth*ratio, Qt::IgnoreAspectRatio, Qt::SmoothTransformation));
        }
        else
        {
            bkgBrush.setTextureImage(QImage(file).scaled(cardWidth*ratio, cardWidth, Qt::IgnoreAspectRatio, Qt::SmoothTransformation));
        }

    }
    else
    {
        bkgBrush.setColor(bkgColor);
        bkgBrush.setStyle(Qt::SolidPattern);
    }

    setBrush(bkgBrush);
}

void CardItem::viewGrid(int flag)
{
    if((bool)flag!= isGrid)
        emit itemChange();

    isGrid = (bool)flag;

    foreach(QGraphicsLineItem *p, gridPoint) {
        delete p;
    }
    gridPoint.clear();

    if(isGrid)
    {
        if( gridSize == 0 ) gridSize = 1;

        if(cardFormat == L)
        {

            for(int x=gridSize*10; x<cardWidth*ratio; x+=gridSize*10)
            {
                for(int y=gridSize*10; y<cardWidth; y+=gridSize*10)
                {
                    QGraphicsLineItem *p = new QGraphicsLineItem(x,y,x+0.1,y,this);
                    p->setZValue(-1000);
                    gridPoint.append(p);
                }
            }
        }
        else
        {
            for(int x=gridSize*10; x<cardWidth; x+=gridSize*10)
            {
                for(int y=gridSize*10; y<cardWidth*ratio; y+=gridSize*10)
                {
                    QGraphicsLineItem *p = new QGraphicsLineItem(x,y,x+0.1,y,this);
                    p->setZValue(-1000);
                    gridPoint.append(p);
                }
            }
        }
    }
}

void CardItem::alignGrid(int flag)
{
    if((bool)flag!= isGridAlign)
        emit itemChange();

    isGridAlign = (bool)flag;

}

void CardItem::setGridSize(int size)
{
    if(size!= gridSize) {
        emit itemChange(); 
        foreach(QGraphicsLineItem *p, gridPoint) {
            delete p;
        }
        gridPoint.clear();        
    }

    gridSize = size;

    viewGrid(isGrid);
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
