#ifndef PIXMAPITEM_H
#define PIXMAPITEM_H

#include <QObject>
#include <QGraphicsPixmapItem>
#include <QDomElement>

class PixmapItem : public QObject, public QGraphicsPixmapItem
{
    Q_OBJECT
public:

    enum { Type = UserType + 4 };

    PixmapItem(QGraphicsItem *parent = 0);

    void loadPixmap(QDomElement text );

    QDomElement getXmlItem(QDomDocument xml );

     int type() const
         { return Type; }

public slots:
    void setPixmapFile(QString pixmapFile);
    void setName(const QString &n);
    void sourceChanged(const int &);

public:
     QString file;
     QString name;
     int source;
};

#endif // PIXMAPITEM_H
