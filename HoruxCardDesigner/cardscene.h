#ifndef CARDSCENE_H
#define CARDSCENE_H

#include <QGraphicsScene>

class CardScene : public QGraphicsScene
{
    Q_OBJECT
public:
    CardScene(QObject *parent = 0);

protected:
     void mousePressEvent(QGraphicsSceneMouseEvent *mouseEvent);
     void mouseMoveEvent(QGraphicsSceneMouseEvent *mouseEvent);
     void mouseReleaseEvent(QGraphicsSceneMouseEvent *mouseEvent);

};

#endif // CARDSCENE_H
