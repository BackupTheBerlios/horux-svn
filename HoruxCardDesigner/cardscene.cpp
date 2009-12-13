#include <QtGui>

#include "cardscene.h"

CardScene::CardScene(QObject *parent)  : QGraphicsScene(parent)
{
    card = new QGraphicsSvgItem(":/CR-80.svg");
    card->setFlag(QGraphicsItem::ItemIsMovable, true);
    card->setFlag(QGraphicsItem::ItemIsSelectable, true);

    addItem(card);
}

 void CardScene::mousePressEvent(QGraphicsSceneMouseEvent *mouseEvent)
 {


    switch (myMode)
    {
        case InsertText:
             textItem = new CardTextItem(card, this);
             //item->setFont(myFont);
             textItem->setTextInteractionFlags(Qt::TextEditorInteraction);
             textItem->setZValue(1000.0);
             connect(textItem, SIGNAL(lostFocus(CardTextItem *)),
                     this, SLOT(editorLostFocus(CardTextItem *)));
             connect(textItem, SIGNAL(selectedChange(QGraphicsItem *)),
                     this, SIGNAL(itemSelected(QGraphicsItem *)));
             addItem(textItem);
             //item->setDefaultTextColor(myTextColor);
             textItem->setPos(mouseEvent->scenePos());
             emit textInserted(textItem);
        default:
         ;
    }

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

 void CardScene::setMode(Mode mode)
 {
     myMode = mode;
 }

 void CardScene::editorLostFocus(CardTextItem *item)
 {
     QTextCursor cursor = item->textCursor();
     cursor.clearSelection();
     item->setTextCursor(cursor);

     if (item->toPlainText().isEmpty()) {
         removeItem(item);
         item->deleteLater();
     }
 }
