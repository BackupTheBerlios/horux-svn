#ifndef PIXMAPITEM_H
#define PIXMAPITEM_H

#include <QObject>
#include <QGraphicsPixmapItem>
#include <QDomElement>
#include <QGraphicsProxyWidget>
#include <QHttp>
#include <QBuffer>

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

    void setPrintingMode(bool printing, QBuffer &picture);

public slots:
    void setPixmapFile(QString pixmapFile);
    void setName(const QString &n);
    void sourceChanged(const int &);
    void setHoruxPixmap(QByteArray pict);
    void setWidth(const QString &);
    void setHeight(const QString &);
    void topChanged(const QString &);
    void leftChanged(const QString &);

protected:
    QVariant itemChange(GraphicsItemChange change, const QVariant &value);

public:
    QString file;
    QString name;
    int source;
    QPixmap pHorux;
    QSize size;
    bool isPrinting;

private:
    QBuffer pictureBufferUnknown;
};

#endif // PIXMAPITEM_H
