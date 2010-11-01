#include <QtGui>
#include "carditemtext.h"
#include "cardscene.h"
#include "carditem.h"

CardTextItem::CardTextItem(QGraphicsItem *parent, QGraphicsScene *scene)
    : QGraphicsTextItem(parent, scene)
{
    setFlag(QGraphicsItem::ItemIsMovable);
    setFlag(QGraphicsItem::ItemIsSelectable);

#if QT_VERSION >= 0x040600
    setFlag(QGraphicsItem::ItemSendsGeometryChanges);
#endif

    rotation = 0;
    name = "";
    source = 0;
    alignment = 0;

    format = STRING;
    format_digit = 0;
    format_decimal = 0;
    format_date = "";

    isPrinting = false;

    isLocked = false;

    setTextWidth(-1);
    document()->setTextWidth(-1);
}

void CardTextItem::setPrintingMode(bool printing, QMap<QString, QString>userData)
{       
    isPrinting = printing;

    // From Horux
    if(source == 1 && isPrinting)
    {
        text = toPlainText();

        QString text_tmp = name;

        QMapIterator<QString, QString> i(userData);
         while (i.hasNext()) {
             i.next();

             if(text_tmp.contains("%" + i.key() + "%")) {
                 text_tmp = text_tmp.replace("%" + i.key() + "%", i.value().simplified());

                 switch(format) {
                     case 0:
                        break;
                     case 1: // integer
                         text_tmp = text_tmp.rightJustified(format_digit,'0');
                         break;
                     case 2: // float
                         {
                             QStringList formatted = text_tmp.split(".");

                             if(formatted.count() == 2) { // with the point
                                text_tmp = formatted.at(0) + "." + formatted.at(1).leftJustified(format_decimal,'0');
                             } else {
                                 text_tmp = formatted.at(0) + "." + QString("").leftJustified(format_decimal,'0');
                             }

                         }
                         break;
                     case 3: // date
                         QDateTime dateTime = QDateTime::fromString(text_tmp, format_sourceDate);
                         text_tmp = dateTime.toString(format_date);
                         break;
                 }
             }

         }

        setPlainText(text_tmp);

        setTextWidth(-1);
        document()->setTextWidth(-1);

    }

    if(source == 0 && isPrinting) {
        text = toPlainText();
        QString text_tmp = text;

        switch(format) {
            case 0:
               break;
            case 1: // integer
                text_tmp = text_tmp.rightJustified(format_digit,'0');
                break;
            case 2: // float
                {
                    QStringList formatted = text_tmp.split(".");

                    if(formatted.count() == 2) { // with the point
                       text_tmp = formatted.at(0) + "." + formatted.at(1).leftJustified(format_decimal,'0');
                    } else {
                        text_tmp = formatted.at(0) + "." + QString("").leftJustified(format_decimal,'0');
                    }

                }
                break;
            case 3: // date
                QDateTime dateTime = QDateTime::fromString(text_tmp, format_sourceDate);
                text_tmp = dateTime.toString(format_date);
                break;
        }

        setPlainText(text_tmp);

        setTextWidth(-1);
        document()->setTextWidth(-1);
    }

    if(source == 2 && isPrinting) {
        text = toPlainText();

        int counterValue = initialValue /* + (userData["__countIndex"].toInt() * increment)*/;

        setPlainText(QString::number(counterValue).rightJustified(digits,'0',false));

        setTextWidth(-1);
        document()->setTextWidth(-1);
    }

    if(source == 2 && !isPrinting) {
        setPlainText(text);
        setTextWidth(-1);
        document()->setTextWidth(-1);
    }

    if(source == 1 && !isPrinting)
    {
        setPlainText(text);
        setTextWidth(-1);
        document()->setTextWidth(-1);
    }


}


void CardTextItem::loadText(QDomElement text )
{
    QDomNode node = text.firstChild();

    qreal posX = 0;
    qreal posY = 0;
    QString fontFamily;
    int fontPoint = 0;
    bool fontBold = false;
    bool fontItalic = false;
    bool fontUnderline = false;
    qreal zValue = 0;

    while(!node.isNull())
    {
        if(node.toElement().tagName() == "rotation")
        {
            zValue = node.toElement().text().toFloat();
        }
        if(node.toElement().tagName() == "zvalue")
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
        if(node.toElement().tagName() == "initialValue")
        {
            initialValue = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "increment")
        {
            increment = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "digits")
        {
            digits = node.toElement().text().toInt();
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

        if(node.toElement().tagName() == "format")
        {
            format = (FORMAT)node.toElement().text().toInt();
        }

        if(node.toElement().tagName() == "format_decimal")
        {
            format_decimal = node.toElement().text().toInt();
        }

        if(node.toElement().tagName() == "format_digit")
        {
            format_digit = node.toElement().text().toInt();
        }

        if(node.toElement().tagName() == "format_date")
        {
            format_date = node.toElement().text();
        }

        if(node.toElement().tagName() == "format_sourceDate")
        {
            format_sourceDate = node.toElement().text();
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
    setPos(posX, posY);
    QFont font;
    font.setBold(fontBold);
    font.setFamily(fontFamily);
    font.setPointSize(fontPoint);
    font.setItalic(fontItalic);
    font.setUnderline(fontUnderline);
    setZValue(zValue);

    alignmentChanged(alignment);

    setFont(font);

  //  adjustSize();
    setTextWidth(-1);
    document()->setTextWidth(-1);


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

    newElement = xml.createElement( "initialValue");
    text =  xml.createTextNode(QString::number(initialValue));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "increment");
    text =  xml.createTextNode(QString::number(increment));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "digits");
    text =  xml.createTextNode(QString::number(digits));
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

    newElement = xml.createElement( "zValue");
    text =  xml.createTextNode(QString::number( zValue()));
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

    newElement = xml.createElement( "format");
    text =  xml.createTextNode(QString::number(format));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "format_date");
    text =  xml.createTextNode(format_date);
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "format_sourceDate");
    text =  xml.createTextNode(format_sourceDate);
    newElement.appendChild(text);
    textItem.appendChild(newElement);


    newElement = xml.createElement( "format_decimal");
    text =  xml.createTextNode(QString::number(format_decimal));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "format_digit");
    text =  xml.createTextNode(QString::number(format_digit));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "isLocked");
    text =  xml.createTextNode(QString::number(isLocked));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    return textItem;
}


void CardTextItem::sourceChanged(const int &s)
{
    if(s != source)
        emit itemChange();

    source = s;
}


void  CardTextItem::setName(const QString &n)
{
    if(n != name)
        emit itemChange();
    name = n;
}

void CardTextItem::fontChanged(const QFont &f)
{
    if(f != font())
        emit itemChange();

    setFont(f);
    scene()->update();
   // adjustSize();
    setTextWidth(-1);
    document()->setTextWidth(-1);

}

void CardTextItem::colorChanged(const QColor &c)
{
    if(c != defaultTextColor() )
        emit itemChange();

    setDefaultTextColor(c);
    scene()->update();
}

void CardTextItem::rotationChanged(const QString &text)
{
    if(text.toDouble() != rotation )
        emit itemChange();


    rotate(rotation*-1);
    rotation = text.toDouble();
    rotate(rotation);    
}

void CardTextItem::topChanged(const QString &top)
{
    if(top.toInt() != pos().y() )
        emit itemChange();

    QPointF p = pos();
    p.setY(top.toInt());
    setPos(p);
}

void CardTextItem::leftChanged(const QString &left)
{
    if(left.toInt() != pos().x() )
        emit itemChange();


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
//    adjustSize();
    setTextWidth(-1);
    document()->setTextWidth(-1);

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
    if(align!= alignment )
        emit itemChange();


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

void CardTextItem::setPrintCounter(int iv, int inc, int di) {
    if(initialValue!= iv )
        emit itemChange();

    if(increment!= inc )
        emit itemChange();

    if(digits!= di )
        emit itemChange();

    initialValue = iv;
    increment = inc;
    digits = di;
}

void CardTextItem::setFormat(int _format, int digit, int decimal, QString date, QString sourceDate) {
    if(format!= (FORMAT)_format )
        emit itemChange();

    if(format_decimal!= decimal )
        emit itemChange();

    if(format_digit!= digit )
        emit itemChange();

    if(format_date!= date )
        emit itemChange();

    if(format_sourceDate!= sourceDate )
        emit itemChange();



    format = (FORMAT)_format;
    format_decimal = decimal;
    format_digit = digit;
    format_date = date;
    format_sourceDate = sourceDate;
}

void CardTextItem::incrementCounter() {
    if(source == 2)
        initialValue += increment;
}

void CardTextItem::setLocked(int flag) {
    if(isLocked!= (bool)flag )
        emit itemChange();

    isLocked = (bool)flag;

    if(isLocked) {
        setFlag(QGraphicsItem::ItemIsMovable, false);
    } else {
        setFlag(QGraphicsItem::ItemIsMovable, true);
    }
}
