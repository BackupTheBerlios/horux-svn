#ifndef DIAGRAMTEXTITEM_H
 #define DIAGRAMTEXTITEM_H

 #include <QGraphicsTextItem>
 #include <QPen>
#include <QDomElement>
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

     QDomElement getXmlItem(QDomDocument xml );

     void loadText(QDomElement text );

     int type() const
         { return Type; }

     void setPrintingMode(bool printing);

 signals:
     void lostFocus(CardTextItem *item);
     void selectedChange(QGraphicsItem *item);
     void textChanged(const QString &);

 protected:
     QVariant itemChange(GraphicsItemChange change, const QVariant &value);
     void focusOutEvent(QFocusEvent *event);
     void mouseDoubleClickEvent(QGraphicsSceneMouseEvent *event);


public slots:
     void setName(const QString &n);
     void fontChanged(const QFont &);
     void colorChanged(const QColor &);
     void rotationChanged(const QString &);
     void topChanged(const QString &);
     void leftChanged(const QString &);
     void sourceChanged(const int &);
     void alignmentChanged(int);

public:
    double rotation;
    QString name;
    QColor color;
    int alignment;
    int source;
    bool isPrinting;

 };

 #endif
