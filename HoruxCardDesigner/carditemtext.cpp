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

