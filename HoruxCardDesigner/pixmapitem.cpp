#include "pixmapitem.h"

PixmapItem::PixmapItem(QGraphicsItem *parent) : QGraphicsPixmapItem(parent)
{

    setPixmap(QPixmap(":/images/gadu.png"));
    setZValue(1000.0);
    setFlag(QGraphicsItem::ItemIsMovable);
    setFlag(QGraphicsItem::ItemIsSelectable);

    name = "";
    source = 0;
}

QDomElement PixmapItem::getXmlItem(QDomDocument xml )
{
    QDomElement textItem = xml.createElement( "CardPixmapItem");

    QDomElement newElement = xml.createElement( "name");
    QDomText text =  xml.createTextNode(name);
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "source");
    text =  xml.createTextNode(QString::number(source));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "file");
    if(file != "")
        text =  xml.createTextNode(file);
    else
        text =  xml.createTextNode(":/images/gadu.png");
    newElement.appendChild(text);
    textItem.appendChild(newElement);


    newElement = xml.createElement( "posX");
    text =  xml.createTextNode(QString::number(pos().x()));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "posY");
    text =  xml.createTextNode(QString::number(pos().y()));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    return textItem;
}


void PixmapItem::setPixmapFile(QString pixmapFile)
{
    file = pixmapFile;
    setPixmap(QPixmap(file));
    update();
}

void PixmapItem::setName(const QString &n)
{
    name = n;
}

void PixmapItem::sourceChanged(const int &s)
{
    source = s;
}

void PixmapItem::loadPixmap(QDomElement text )
{
    QDomNode node = text.firstChild();

    qreal posX = 0;
    qreal posY = 0;

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

        node = node.nextSibling();
    }

    setPos(posX, posY);
    setPixmap(QPixmap(file));
}
