#include <QtGui>
#include "carditemtext.h"
#include "cardscene.h"

 CardTextItem::CardTextItem(QGraphicsItem *parent, QGraphicsScene *scene)
     : QGraphicsTextItem(parent, scene)
 {
     setFlag(QGraphicsItem::ItemIsMovable);
     setFlag(QGraphicsItem::ItemIsSelectable);


 }

 QWidget *  CardTextItem::getWidgetSetting()
 {
    textSettings = new TextPage();

    return textSettings;
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
