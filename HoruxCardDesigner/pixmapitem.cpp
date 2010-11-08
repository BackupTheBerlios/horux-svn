#include "pixmapitem.h"
#include "carditem.h"
#include <QDebug>
#include <QGraphicsWidget>
#include <QLabel>
#include <QMovie>
#include <QFile>

PixmapItem::PixmapItem(QGraphicsItem *parent) : QGraphicsPixmapItem(parent)
{

    setPixmap(QPixmap(":/images/gadu.png"));
    //setZValue(1000.0);
    setFlag(QGraphicsItem::ItemIsMovable);
    setFlag(QGraphicsItem::ItemIsSelectable);

#if QT_VERSION >= 0x040600
    setFlag(QGraphicsItem::ItemSendsGeometryChanges);
#endif

    file = "";
    name = "";
    source = 0;
    size.setWidth(QPixmap(":/images/gadu.png").width());
    size.setHeight(QPixmap(":/images/gadu.png").height());

    isPrinting = false;

    isLocked = false;
}

void PixmapItem::setPrintingMode(bool printing, QBuffer &picture)
{
    isPrinting = printing;
    // From Horux
    if(source == 1 && printing)
    {
        setHoruxPixmap(picture.data());
    }

    if(source == 1 && !printing)
    {
        QFile unknown(":/images/unknown.jpg");

        if(unknown.open(QIODevice::ReadOnly)) {
            pictureBufferUnknown.setData(unknown.readAll());
            setHoruxPixmap(pictureBufferUnknown.data());
        }
    }
}

QVariant PixmapItem::itemChange(GraphicsItemChange change,
                                const QVariant &value)
{
    if (change == ItemPositionChange)
    {
        // value is the new position.
        QPointF newPos = value.toPointF();
        QRectF rect = parentItem()->boundingRect();

        rect.moveLeft(boundingRect().width()*3/4*-1);
        rect.setWidth(rect.width() + boundingRect().width()*2/4 );

        rect.moveTop(boundingRect().height()*3/4*-1);
        rect.setHeight(rect.height() + boundingRect().height()*2/4 );

        CardItem *card = qgraphicsitem_cast<CardItem *>(parentItem());

        if (!rect.contains(newPos))
        {
            // Keep the item inside the scene rect.
            int newX = (int)qMin(rect.right(), qMax(newPos.x(), rect.left()));
            int newY = (int)qMin(rect.bottom(), qMax(newPos.y(), rect.top()));



            if(card->isAlign())
            {
                int gridSize = card->getGridSize();
                newX = (newX/(10*gridSize))*(10*gridSize);
                newY = (newY/(10*gridSize))*(10*gridSize);
            }

            newPos.setX(newX);
            newPos.setY(newY);
            return newPos;
        }
        else
        {
            int newX =  newPos.x();
            int newY = newPos.y();

            if(card->isAlign())
            {
                int gridSize = card->getGridSize();
                newX = newPos.x()/(10*gridSize);
                newX = newX * (10*gridSize);
                newY = newPos.y()/(10*gridSize);
                newY = newY*(10*gridSize);

            }

            newPos.setX(newX);
            newPos.setY(newY);
            return newPos;

        }
    }

    return QGraphicsItem::itemChange(change, value);
}

QDomElement PixmapItem::getXmlItem(QDomDocument xml )
{
    QDomElement pictureItem = xml.createElement( "CardPixmapItem");

    QDomElement newElement = xml.createElement( "name");
    QDomText text =  xml.createTextNode(name);
    newElement.appendChild(text);
    pictureItem.appendChild(newElement);

    newElement = xml.createElement( "source");
    text =  xml.createTextNode(QString::number(source));
    newElement.appendChild(text);
    pictureItem.appendChild(newElement);

    newElement = xml.createElement( "file");
    if(file != "" && source == 0)
        text =  xml.createTextNode(file);
    else
    {
        if(source == 0)
            text =  xml.createTextNode(":/images/gadu.png");
        else
            text = xml.createTextNode("");
    }
    newElement.appendChild(text);
    pictureItem.appendChild(newElement);


    newElement = xml.createElement( "posX");
    text =  xml.createTextNode(QString::number(pos().x()));
    newElement.appendChild(text);
    pictureItem.appendChild(newElement);

    newElement = xml.createElement( "posY");
    text =  xml.createTextNode(QString::number(pos().y()));
    newElement.appendChild(text);
    pictureItem.appendChild(newElement);

    newElement = xml.createElement( "zValue");
    text =  xml.createTextNode(QString::number( zValue() ));
    newElement.appendChild(text);
    pictureItem.appendChild(newElement);

    newElement = xml.createElement( "width");
    text =  xml.createTextNode(QString::number(boundingRect().width()));
    newElement.appendChild(text);
    pictureItem.appendChild(newElement);

    newElement = xml.createElement( "height");
    text =  xml.createTextNode(QString::number(boundingRect().height()));
    newElement.appendChild(text);
    pictureItem.appendChild(newElement);


    newElement = xml.createElement( "isLocked");
    text =  xml.createTextNode(QString::number(isLocked));
    newElement.appendChild(text);
    pictureItem.appendChild(newElement);

    return pictureItem;
}


void PixmapItem::setHoruxPixmap(QByteArray pict)
{

    if(pict.size() > 0)
    {
        pHorux.loadFromData(pict);
        pHorux = pHorux.scaledToWidth(size.width(),Qt::SmoothTransformation);

    }
    else
    {
        pHorux = pHorux.scaledToWidth(0,Qt::SmoothTransformation);
    }

    setPixmap(pHorux);
    update();


}

void PixmapItem::setPixmapFile(QString pixmapFile)
{
    if(source==1) return;

    if(pixmapFile != file)
        emit itemChange();


    file = pixmapFile;
    setPixmap(QPixmap(file));
    update();


    size.setWidth(QPixmap(file).width());
    size.setHeight(QPixmap(file).height());
}

void PixmapItem::setName(const QString &n)
{
    if(n != name)
        emit itemChange();

    name = n;
}

void PixmapItem::sourceChanged(const int &s)
{
    if(s!=source)
        emit itemChange();

    source = s;

}

void PixmapItem::loadPixmap(QDomElement text )
{
    QDomNode node = text.firstChild();

    qreal posX = 0;
    qreal posY = 0;
    qreal zValue = 0;

    while(!node.isNull())
    {
        if(node.toElement().tagName() == "name")
        {
            name = node.toElement().text();
        }
        if(node.toElement().tagName() == "source")
        {
            source = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "file")
        {
            file = node.toElement().text();
        }
        if(node.toElement().tagName() == "posX")
        {
            posX = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "posY")
        {
            posY = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "zValue")
        {
            zValue = node.toElement().text().toFloat();
        }

        if(node.toElement().tagName() == "width")
        {
            size.setWidth( node.toElement().text().toInt() );
        }
        if(node.toElement().tagName() == "height")
        {
            size.setHeight(node.toElement().text().toInt());
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

    setZValue(zValue);

    if(source==0)
    {
        QPixmap p(file);
        p = p.scaledToWidth(size.width(),Qt::SmoothTransformation);
        setPos(posX, posY);
        setPixmap(p);
    }
    else
    {
        setPos(posX, posY);

        QFile unknown(":/images/unknown.jpg");

        if(unknown.open(QIODevice::ReadOnly)) {
            pictureBufferUnknown.setData(unknown.readAll());
            setHoruxPixmap(pictureBufferUnknown.data());
        }
    }
}

void PixmapItem::setWidth(const QString &w)
{
    if(w.toInt() != size.width())
        emit itemChange();


    QString f = file == "" ? ":/images/gadu.png" : file;
    QPixmap p(f);

    if(source == 1)
        p = pHorux;

    size.setWidth(w.toInt());

    p = p.scaledToWidth(w.toInt(), Qt::SmoothTransformation);
    setPixmap(p);
    update();

    size.setHeight(boundingRect().height());


}

void PixmapItem::setHeight(const QString &h)
{
    if(h.toInt() != size.height())
        emit itemChange();

    QString f = file == "" ? ":/images/gadu.png" : file;
    QPixmap p(f);

    if(source == 1)
        p = pHorux;

    size.setHeight(h.toInt());

    p = p.scaledToHeight(h.toInt(), Qt::SmoothTransformation);

    setPixmap(p);
    update();

    size.setWidth(boundingRect().width());

}

void PixmapItem::topChanged(const QString &top)
{
    if(top.toInt() != pos().y())
        emit itemChange();

    QPointF p = pos();
    p.setY(top.toInt());
    setPos(p);

}

void PixmapItem::leftChanged(const QString &left)
{
    if(left.toInt() != pos().x())
        emit itemChange();

    QPointF p = pos();
    p.setX(left.toInt());
    setPos(p);

}

void PixmapItem::setLocked(int flag) {

    if((bool)flag != isLocked)
        emit itemChange();

    isLocked = (bool)flag;

    if(isLocked) {
        setFlag(QGraphicsItem::ItemIsMovable, false);
    } else {
        setFlag(QGraphicsItem::ItemIsMovable, true);
    }


}
