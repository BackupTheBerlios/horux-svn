#ifndef CARDSCENE_H
#define CARDSCENE_H

#include <QGraphicsScene>
#include "carditemtext.h"
#include <QGraphicsSvgItem>

class CardScene : public QGraphicsScene
{
    Q_OBJECT

public:
    enum Mode { InsertItem, InsertText, MoveItem };

    CardScene(QObject *parent = 0);

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
     Mode myMode;
     CardTextItem *textItem;
     QGraphicsSvgItem *card;
};

#endif // CARDSCENE_H
