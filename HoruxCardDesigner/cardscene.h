#ifndef CARDSCENE_H
#define CARDSCENE_H

#include <QGraphicsScene>
#include "carditemtext.h"
#include "pixmapitem.h"
#include <QGraphicsPixmapItem>

class CardItem;

class CardScene : public QGraphicsScene
{
    Q_OBJECT

public:
    enum Mode { InsertPicture, InsertText, MoveItem };

    QFont font() const
    { return defaultFont; }
    QColor textColor() const
    { return myTextColor; }

    CardScene(QObject *parent = 0);
    CardItem *getCardItem();
    void setFont(const QFont &font);
    void reset();
    void loadScene(QString xml);

public slots:
    void setMode(Mode mode);
    void editorLostFocus(CardTextItem *item);

signals:
    void itemInserted(QGraphicsItem *item);
    void textInserted(QGraphicsTextItem *item);
    void itemSelected(QGraphicsItem *item);
    void itemMoved(QGraphicsItem *item, QPointF pos);
    void mouseRelease();
    void itemChange();

protected:
    void mousePressEvent(QGraphicsSceneMouseEvent *mouseEvent);
    void mouseMoveEvent(QGraphicsSceneMouseEvent *mouseEvent);
    void mouseReleaseEvent(QGraphicsSceneMouseEvent *mouseEvent);

private:
    bool isItemChange(int type);

private:
    Mode myMode;
    CardTextItem *textItem;
    PixmapItem * pixmapItem;
    CardItem *card;
    QFont defaultFont;
    QColor myTextColor;
    QPointF currentSelectedPos;
};

#endif // CARDSCENE_H
