#ifndef DIAGRAMTEXTITEM_H
 #define DIAGRAMTEXTITEM_H

 #include <QGraphicsTextItem>
 #include <QPen>
 #include "confpage.h"

 class QFocusEvent;
 class QGraphicsItem;
 class QGraphicsScene;
 class QGraphicsSceneMouseEvent;

 class CardTextItem : public QGraphicsTextItem
 {
     Q_OBJECT

 public:
     enum { Type = UserType + 3 };

     CardTextItem(QGraphicsItem *parent = 0, QGraphicsScene *scene = 0);

     int type() const
         { return Type; }

     QWidget * getWidgetSetting();

 signals:
     void lostFocus(CardTextItem *item);
     void selectedChange(QGraphicsItem *item);

 protected:
     QVariant itemChange(GraphicsItemChange change, const QVariant &value);
     void focusOutEvent(QFocusEvent *event);
     void mouseDoubleClickEvent(QGraphicsSceneMouseEvent *event);


private:
    TextPage *textSettings;
 };

 #endif
