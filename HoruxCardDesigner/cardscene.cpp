#include <QtGui>

#include "cardscene.h"

CardScene::CardScene(QObject *parent)  : QGraphicsScene(parent)
{
}

 void CardScene::mousePressEvent(QGraphicsSceneMouseEvent *mouseEvent)
 {
     QGraphicsScene::mousePressEvent(mouseEvent);
 }

 void CardScene::mouseMoveEvent(QGraphicsSceneMouseEvent *mouseEvent)
 {
    QGraphicsScene::mouseMoveEvent(mouseEvent);
 }

 void CardScene::mouseReleaseEvent(QGraphicsSceneMouseEvent *mouseEvent)
 {
     QGraphicsScene::mouseReleaseEvent(mouseEvent);
 }
