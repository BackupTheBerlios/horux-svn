#include <QtGui>
#include "carditem.h"
#include "cardscene.h"

CardScene::CardScene(QObject *parent)  : QGraphicsScene(parent)
{
    card = new CardItem();

    myTextColor = Qt::black;

    myMode = MoveItem;

    addItem(card);
}

CardItem *CardScene::getCardItem()
{
    return card;
}

void CardScene::mousePressEvent(QGraphicsSceneMouseEvent *mouseEvent)
{
    switch (myMode)
    {
        case InsertText:
             textItem = new CardTextItem(card);
             textItem->setFont(myFont);
             textItem->setTextInteractionFlags(Qt::TextEditorInteraction);
             textItem->setZValue(1000.0);
             connect(textItem, SIGNAL(lostFocus(CardTextItem *)),
                     this, SLOT(editorLostFocus(CardTextItem *)));
             connect(textItem, SIGNAL(selectedChange(QGraphicsItem *)),
                     this, SIGNAL(itemSelected(QGraphicsItem *)));
             textItem->setDefaultTextColor(myTextColor);
             textItem->setPos(mouseEvent->scenePos());
             emit textInserted(textItem);
        default:
         ;
    }

    QGraphicsScene::mousePressEvent(mouseEvent);
}

void CardScene::mouseMoveEvent(QGraphicsSceneMouseEvent *mouseEvent)
{
    if (myMode == MoveItem)
    {
        if( selectedItems().size() > 0)
        {
            QGraphicsItem *item = selectedItems().at(0);
            if(item->type() > QGraphicsItem::UserType + 1)
            {
            }
        }

        QGraphicsScene::mouseMoveEvent(mouseEvent);
    }
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

void CardScene::setTextColor(const QColor &color)
{
 myTextColor = color;
 if (isItemChange(CardTextItem::Type)) {
     CardTextItem *item =
         qgraphicsitem_cast<CardTextItem *>(selectedItems().first());
     item->setDefaultTextColor(myTextColor);
 }
}

void CardScene::setFont(const QFont &font)
{
    myFont = font;

    if (isItemChange(CardTextItem::Type)) {
     QGraphicsTextItem *item =
         qgraphicsitem_cast<CardTextItem *>(selectedItems().first());
     //At this point the selection can change so the first selected item might not be a DiagramTextItem
     if (item)
         item->setFont(myFont);
    }
}

bool CardScene::isItemChange(int type)
{
     foreach (QGraphicsItem *item, selectedItems()) {
         if (item->type() == type)
             return true;
     }
     return false;
}
