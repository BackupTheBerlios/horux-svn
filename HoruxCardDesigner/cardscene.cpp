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

    currentSelectedPos.setX(0);
    currentSelectedPos.setY(0);
}

CardItem *CardScene::getCardItem()
{
    return card;
}

void CardScene::reset()
{
    card->reset();
}

void CardScene::loadScene(QString xml)
{
    QDomDocument doc;

    doc.setContent(xml, false);

    QDomElement root = doc.documentElement();

    if( root.tagName() != "HoruxCardDesigner")
    {
        return;
    }

    QDomNode node = root.firstChild();

    while(!node.isNull())
    {
        if(node.toElement().tagName() == "CardItem")
        {
            card->loadCard(node.toElement());
        }

        node = node.nextSibling();
    }
}

void CardScene::mousePressEvent(QGraphicsSceneMouseEvent *mouseEvent)
{
    QGraphicsScene::mousePressEvent(mouseEvent);

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

        textItem->setPlainText("Text");

        emit textInserted(textItem);
        emit itemChange();
        break;
    case InsertPicture:
        {
            pixmapItem = new PixmapItem(card);
            pixmapItem->setPos( pixmapItem->mapFromScene(mouseEvent->scenePos()) );

            emit itemInserted(pixmapItem);
            emit itemChange();
        }
        break;
    default:
        if(selectedItems().count()>0) {
            currentSelectedPos = selectedItems().at(0)->pos();
        }
    }


}

void CardScene::mouseMoveEvent(QGraphicsSceneMouseEvent *mouseEvent)
{
    if (myMode == MoveItem)
    {
        QGraphicsScene::mouseMoveEvent(mouseEvent);

        if( selectedItems().size() > 0)
        {
            QGraphicsItem *item = selectedItems().at(0);
            if(item->type() > QGraphicsItem::UserType + 1)
            {
                emit itemMoved(item, currentSelectedPos);
            }
        }


    }
}

void CardScene::mouseReleaseEvent(QGraphicsSceneMouseEvent *mouseEvent)
{    
    QGraphicsScene::mouseReleaseEvent(mouseEvent);

    if( selectedItems().size() > 0)
    {
        QGraphicsItem *item = selectedItems().at(0);
        if(item->type() > QGraphicsItem::UserType + 1)
        {
            currentSelectedPos.setX(0);
            currentSelectedPos.setY(0);

            emit itemMoved(item, currentSelectedPos);
        }
    }

    emit mouseRelease();

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

    if( selectedItems().size() > 0)
    {
        QGraphicsItem *item = selectedItems().at(0);

        if(item->type() > QGraphicsItem::UserType + 1)
        {
            CardTextItem *textItem = qgraphicsitem_cast<CardTextItem *>(selectedItems().at(0));
            textItem->setFont(font);
        }
    }
}
