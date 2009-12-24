#include <QtGui>
#include "carditemtext.h"
#include "cardscene.h"

 CardTextItem::CardTextItem(QGraphicsItem *parent, QGraphicsScene *scene)
     : QGraphicsTextItem(parent, scene)
 {
     setFlag(QGraphicsItem::ItemIsMovable);
     setFlag(QGraphicsItem::ItemIsSelectable);

     rotation = 0;
     name = "";
     source = 0;
     alignment = 0;
 }

void CardTextItem::loadText(QDomElement text )
{
    QDomNode node = text.firstChild();

    qreal posX = 0;
    qreal posY = 0;
    QString fontFamily;
    int fontPoint;
    bool fontBold;
    bool fontItalic;
    bool fontUnderline;

    while(!node.isNull())
    {
        if(node.toElement().tagName() == "rotation")
        {
            rotation = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "name")
        {
            name = node.toElement().text();
        }
        if(node.toElement().tagName() == "source")
        {
            source = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "color")
        {
            setDefaultTextColor(QColor(node.toElement().text()));
        }
        if(node.toElement().tagName() == "alignment")
        {
            //rotation = (Size)node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "text")
        {
            setPlainText(node.toElement().text());
        }
        if(node.toElement().tagName() == "posX")
        {
            posX = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "posY")
        {
            posY = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "font-family")
        {
            fontFamily = node.toElement().text();
        }
        if(node.toElement().tagName() == "font-point")
        {
            fontPoint = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "font-bold")
        {
            fontBold = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "font-italic")
        {
            fontItalic = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "font-underline")
        {
            fontUnderline = node.toElement().text().toInt();
        }

        node = node.nextSibling();
    }

    setPos(posX, posY);
    QFont font;
    font.setBold(fontBold);
    font.setFamily(fontFamily);
    font.setPointSize(fontPoint);
    font.setItalic(fontItalic);
    font.setUnderline(fontUnderline);

    setFont(font);
}

QDomElement CardTextItem::getXmlItem(QDomDocument xml )
{
    QDomElement textItem = xml.createElement( "CardTextItem");

    QDomElement newElement = xml.createElement( "rotation");
    QDomText text =  xml.createTextNode(QString::number(rotation));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "name");
    text =  xml.createTextNode(name);
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "source");
    text =  xml.createTextNode(QString::number(source));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "color");
    text =  xml.createTextNode(color.name());
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "alignment");
    text =  xml.createTextNode(QString::number(alignment));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "text");
    text =  xml.createTextNode(toPlainText ());
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

    newElement = xml.createElement( "font-family");
    text =  xml.createTextNode(font().family());
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "font-point");
    text =  xml.createTextNode(QString::number(font().pointSize ()));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "font-bold");
    text =  xml.createTextNode(QString::number(font().bold()));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "font-italic");
    text =  xml.createTextNode(QString::number(font().italic()));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "font-underline");
    text =  xml.createTextNode(QString::number(font().underline()));
    newElement.appendChild(text);
    textItem.appendChild(newElement);


    return textItem;
}


void CardTextItem::sourceChanged(const int &s)
{
    source = s;
}


void  CardTextItem::setName(const QString &n)
{
    name = n;
}

void CardTextItem::fontChanged(const QFont &font)
{
    setFont(font);
    scene()->update();
}

void CardTextItem::colorChanged(const QColor &color)
{
    setDefaultTextColor(color);
    scene()->update();
}

void CardTextItem::rotationChanged(const QString &text)
{
    rotate(rotation*-1);
    rotation = text.toDouble();
    rotate(rotation);
}



 QVariant CardTextItem::itemChange(GraphicsItemChange change,
                      const QVariant &value)
 {
     if (change == QGraphicsItem::ItemSelectedHasChanged)
         emit selectedChange(this);
     return value;
 }

 void CardTextItem::focusOutEvent(QFocusEvent *event)
 {
     setTextInteractionFlags(Qt::NoTextInteraction);
     emit lostFocus(this);
     QGraphicsTextItem::focusOutEvent(event);
 }

 void CardTextItem::mouseDoubleClickEvent(QGraphicsSceneMouseEvent *event)
 {
     if (textInteractionFlags() == Qt::NoTextInteraction)
         setTextInteractionFlags(Qt::TextEditorInteraction);
     QGraphicsTextItem::mouseDoubleClickEvent(event);
 }
