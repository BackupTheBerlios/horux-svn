#ifndef CARDSCENE_H
#define CARDSCENE_H

#include <QGraphicsScene>
#include "carditemtext.h"
#include <QGraphicsSvgItem>

class CardItem;

class CardScene : public QGraphicsScene
{
    Q_OBJECT

public:
    enum Mode { InsertItem, InsertText, MoveItem };

     QFont font() const
         { return myFont; }
     QColor textColor() const
         { return myTextColor; }


    CardScene(QObject *parent = 0);
    void setFont(const QFont &font);
    void setTextColor(const QColor &color);
    CardItem *getCardItem();

public slots:
     void setMode(Mode mode);
     void editorLostFocus(CardTextItem *item);

signals:
     void itemInserted(CardTextItem *item);
     void textInserted(QGraphicsTextItem *item);
     void itemSelected(QGraphicsItem *item);

protected:
     void mousePressEvent(QGraphicsSceneMouseEvent *mouseEvent);
     void mouseMoveEvent(QGraphicsSceneMouseEvent *mouseEvent);
     void mouseReleaseEvent(QGraphicsSceneMouseEvent *mouseEvent);

private:
     bool isItemChange(int type);

private:
     Mode myMode;
     CardTextItem *textItem;
     CardItem *card;
     QFont myFont;
     QColor myTextColor;
};

#endif // CARDSCENE_H
