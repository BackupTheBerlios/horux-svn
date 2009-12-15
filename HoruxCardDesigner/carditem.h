#ifndef CARDITEM_H
#define CARDITEM_H

#include <QObject>
#include <QGraphicsPathItem>
#include "confpage.h"

class CardItem : public QObject, public QGraphicsPathItem
{
    Q_OBJECT

public:
    enum { Type = UserType + 1 };
    enum Size { CR80, CR90, CR79 };
    enum Format { P, L };

    CardItem( Size size = CR80, Format format = L, QGraphicsItem * parent = 0);

     int type() const
         { return Type; }

    void paint(QPainter *painter, const QStyleOptionGraphicsItem *option, QWidget *widget);

    Size getSize(){ return cardSize;}
    Format getFormat(){ return cardFormat;}
    bool isViewGrid(){ return isGrid;}
    bool isAlign(){ return isGridAlign;}
    int getGridSize(){ return gridSize;}

    QWidget * getWidgetSetting();

public slots:
    void setSize(int size);
    void setFormat(int format);
    void setBkgColor();
    void setBkgPixmap(QString file);
    void viewGrid(int flag);
    void alignGrid(int flag);
    void setGridSize(int size);


private:
    void definePath();

private:
    Size cardSize;
    Format cardFormat;
    QPainterPath cardPath;
    CardPage *cardSettings;
    QColor bkgColor;
    QString bkgFile;
    QPixmap pix;
    QBrush bkgBrush;
    bool isGrid;
    int gridSize;
    bool isGridAlign;
};

#endif // CARDITEM_H
