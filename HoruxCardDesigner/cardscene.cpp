#include <QtGui>
#include "carditem.h"
#include "cardscene.h"

CardScene::CardScene(QObject *parent)  : QGraphicsScene(parent)
{
    card = new CardItem();

    myTextColor = Qt::black;

    myMode = MoveItem;

    addItem(card);

    card->setPos(100,100);

}

CardItem *CardScene::getCardItem()
{
    return card;
}

void CardScene::reset()
{
    card->reset();
}

void CardScene::mousePressEvent(QGraphicsSceneMouseEvent *mouseEvent)
{
    switch (myMode)
    {
        case InsertText:
             textItem = new CardTextItem(card);
             textItem->setFont(defaultFont);
             textItem->setTextInteractionFlags(Qt::TextEditorInteraction);
             textItem->setZValue(1000.0);
             connect(textItem, SIGNAL(lostFocus(CardTextItem *)),
                     this, SLOT(editorLostFocus(CardTextItem *)));
             connect(textItem, SIGNAL(selectedChange(QGraphicsItem *)),
                     this, SIGNAL(itemSelected(QGraphicsItem *)));
             textItem->setDefaultTextColor(myTextColor);

             textItem->setPos( textItem->mapFromScene(mouseEvent->scenePos()) );

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


bool CardScene::isItemChange(int type)
{
     foreach (QGraphicsItem *item, selectedItems()) {
         if (item->type() == type)
             return true;
     }
     return false;
}

 void CardScene::setFont(const QFont &font)
 {
     defaultFont = font;
 }
