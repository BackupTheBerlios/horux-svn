#include <QtGui>
#include "carditemtext.h"
#include "cardscene.h"
#include "carditem.h"

 CardTextItem::CardTextItem(QGraphicsItem *parent, QGraphicsScene *scene)
     : QGraphicsTextItem(parent, scene)
 {
     setFlag(QGraphicsItem::ItemIsMovable);
     setFlag(QGraphicsItem::ItemIsSelectable);

     rotation = 0;
     name = "";
     source = 0;
     alignment = 0;

     isPrinting = false;
 }

void CardTextItem::setPrintingMode(bool printing, QMap<QString, QString>userData)
{       
    isPrinting = printing;


    // From Horux
    if(source == 1 && isPrinting)
    {
        text = toPlainText();

        if(name == "Name")
        {
            setPlainText(userData["name"]);
        }

        if(name == "Firstname")
        {
            setPlainText(userData["firstname"]);
        }

        if(name == "Validity date")
        {
            setPlainText(userData["validity_date"]);
        }

        if(name == "Department")
        {
            setPlainText(userData["department"]);
        }

        if(name == "Pin code")
        {
            setPlainText(userData["pin_code"]);
        }

        if(name == "Street (private)")
        {
            setPlainText(userData["street_private"]);
        }

        if(name == "City (private)")
        {
            setPlainText(userData["city_private"]);
        }

        if(name == "Zip (private)")
        {
            setPlainText(userData["zip_private"]);
        }

        if(name == "Country (private)")
        {
            setPlainText(userData["country_private"]);
        }

        if(name == "Phone (private)")
        {
            setPlainText(userData["phone_private"]);
        }

        if(name == "Email (private)")
        {
            setPlainText(userData["email_private"]);
        }

        if(name == "Firme")
        {
            setPlainText(userData["firme"]);
        }

        if(name == "Street (Professional)")
        {
            setPlainText(userData["street_professional"]);
        }

        if(name == "City (Professional)")
        {
            setPlainText(userData["city_professional"]);
        }

        if(name == "Zip  (Professional)")
        {
            setPlainText(userData["zip_professional"]);
        }

        if(name == "Country  (Professional)")
        {
            setPlainText(userData["country_professional"]);
        }

        if(name == "Email (Professional)")
        {
            setPlainText(userData["email_professional"]);
        }

        if(name == "Phone (Professional)")
        {
            setPlainText(userData["phone_professional"]);
        }

        if(name == "Fax (Professional)")
        {
            setPlainText(userData["fax_professional"]);
        }
    }

    if(source == 1 && !isPrinting)
    {
        setPlainText(text);
    }

    adjustSize();
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
            alignment = node.toElement().text().toInt();
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

    alignmentChanged(alignment);

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
    text =  xml.createTextNode( toPlainText());
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

void CardTextItem::topChanged(const QString &top)
{
    QPointF p = pos();
    p.setY(top.toInt());
    setPos(p);
}

void CardTextItem::leftChanged(const QString &left)
{
    QPointF p = pos();
    p.setX(left.toInt());
    setPos(p);
}


 QVariant CardTextItem::itemChange(GraphicsItemChange change,
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
                newX = (newX/(5*gridSize))*(5*gridSize);
                newY = (newY/(5*gridSize))*(5*gridSize);
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
                newX = newPos.x()/(5*gridSize);
                newX = newX * (5*gridSize);
                newY = newPos.y()/(5*gridSize);
                newY = newY*(5*gridSize);

            }

            newPos.setX(newX);
            newPos.setY(newY);
            return newPos;

        }
    }

    if (change == QGraphicsItem::ItemSelectedHasChanged)
    {
         emit selectedChange(this);
    }

    return QGraphicsItem::itemChange(change, value);
 }

 void CardTextItem::focusOutEvent(QFocusEvent *event)
 {
     setTextInteractionFlags(Qt::NoTextInteraction);
     emit lostFocus(this);
     adjustSize();
     QGraphicsTextItem::focusOutEvent(event);
 }

 void CardTextItem::mouseDoubleClickEvent(QGraphicsSceneMouseEvent *event)
 {
     if (textInteractionFlags() == Qt::NoTextInteraction)
         setTextInteractionFlags(Qt::TextEditorInteraction);
     QGraphicsTextItem::mouseDoubleClickEvent(event);
 }

void CardTextItem::alignmentChanged(int align)
{
    alignment = align;

    QTextDocument *doc = document();
    doc->setTextWidth(boundingRect().width());

    QTextOption option = doc->defaultTextOption ();
    switch(align)
    {
        case 0: //left
            option.setAlignment(Qt::AlignLeft);
            break;
        case 1: //right
            option.setAlignment(Qt::AlignRight);
            break;
        case 2: //center
            option.setAlignment(Qt::AlignHCenter);
            break;
    }

    doc->setDefaultTextOption(option);
    setDocument(doc);
}
