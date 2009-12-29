#include "pixmapitem.h"
#include <QDebug>
#include <QGraphicsWidget>
#include <QLabel>
#include <QMovie>
#include <QSettings>

PixmapItem::PixmapItem(QGraphicsItem *parent) : QGraphicsPixmapItem(parent)
{

    setPixmap(QPixmap(":/images/gadu.png"));
    setZValue(1000.0);
    setFlag(QGraphicsItem::ItemIsMovable);
    setFlag(QGraphicsItem::ItemIsSelectable);

    file = "";
    name = "";
    source = 0;
    size.setWidth(QPixmap(":/images/gadu.png").width());
    size.setHeight(QPixmap(":/images/gadu.png").height());

    spinner = NULL;

     pictureBuffer.open(QBuffer::ReadWrite);
     connect(&pictureHttp, SIGNAL(done(bool)), this, SLOT(httpRequestDone(bool)));
}

QDomElement PixmapItem::getXmlItem(QDomDocument xml )
{
    QDomElement textItem = xml.createElement( "CardPixmapItem");

    QDomElement newElement = xml.createElement( "name");
    QDomText text =  xml.createTextNode(name);
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "source");
    text =  xml.createTextNode(QString::number(source));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "file");
    if(file != "")
        text =  xml.createTextNode(file);
    else
        text =  xml.createTextNode(":/images/gadu.png");
    newElement.appendChild(text);
    textItem.appendChild(newElement);


    newElement = xml.createElement( "posX");
    text =  xml.createTextNode(QString::number(pos().x()));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "posY");
    text =  xml.createTextNode(QString::number(pos().y()));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "width");
    text =  xml.createTextNode(QString::number(boundingRect().width()));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    newElement = xml.createElement( "height");
    text =  xml.createTextNode(QString::number(boundingRect().height()));
    newElement.appendChild(text);
    textItem.appendChild(newElement);

    return textItem;
}


void PixmapItem::setHoruxPixmap(QByteArray pict)
{
    pHorux.loadFromData(pict);

    pHorux = pHorux.scaledToWidth(size.width(),Qt::SmoothTransformation);

    setPixmap(pHorux);
    update();
}

void PixmapItem::setPixmapFile(QString pixmapFile)
{
    file = pixmapFile;
    setPixmap(QPixmap(file));
    update();


    size.setWidth(QPixmap(file).width());
    size.setHeight(QPixmap(file).height());
}

void PixmapItem::setName(const QString &n)
{
    name = n;
}

void PixmapItem::sourceChanged(const int &s)
{
    source = s;
}

void PixmapItem::loadPixmap(QDomElement text )
{
    QDomNode node = text.firstChild();

    qreal posX = 0;
    qreal posY = 0;

    while(!node.isNull())
    {
        if(node.toElement().tagName() == "name")
        {
            name = node.toElement().text();
        }
        if(node.toElement().tagName() == "source")
        {
            source = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "file")
        {
            file = node.toElement().text();
        }
        if(node.toElement().tagName() == "posX")
        {
            posX = node.toElement().text().toInt();
        }
        if(node.toElement().tagName() == "posY")
        {
            posY = node.toElement().text().toInt();
        }

        if(node.toElement().tagName() == "width")
        {
            size.setWidth( node.toElement().text().toInt() );
        }
        if(node.toElement().tagName() == "height")
        {
            size.setHeight(node.toElement().text().toInt());
        }


        node = node.nextSibling();
    }
    if(source==0)
    {
        QPixmap p(file);
        p = p.scaledToWidth(size.width(),Qt::SmoothTransformation);
        setPos(posX, posY);
        setPixmap(p);
    }
    else
    {

        spinner = new QGraphicsProxyWidget(this);
        QLabel *label = new QLabel;
        QMovie *movie = new QMovie(":/images/spinner.gif");
        movie->setBackgroundColor(Qt::white);
        label->setMovie(movie);
        movie->start();
        spinner->setWidget(label);
        setPixmap(QPixmap());
        setPos(posX, posY);

        QSettings settings("Letux", "HoruxCardDesigner", this);

        QString host = settings.value("horux", "localhost").toString();
        QString path = settings.value("path", "").toString();
        bool ssl = settings.value("ssl", "").toBool();

        pictureHttp.setHost(host, ssl ? QHttp::ConnectionModeHttps : QHttp::ConnectionModeHttp );
        pictureHttp.get(path + "/pictures/unknown.jpg", &pictureBuffer);

    }
}

void PixmapItem::setWidth(const QString &w)
{
    QString f = file == "" ? ":/images/gadu.png" : file;
    QPixmap p(f);

    if(source == 1)
        p = pHorux;

    size.setWidth(w.toInt());

    p = p.scaledToWidth(w.toInt(), Qt::SmoothTransformation);
    setPixmap(p);
    update();
}

void PixmapItem::setHeight(const QString &h)
{
    QString f = file == "" ? ":/images/gadu.png" : file;
    QPixmap p(f);

    if(source == 1)
        p = pHorux;

    size.setHeight(h.toInt());

    p = p.scaledToHeight(h.toInt(), Qt::SmoothTransformation);
    setPixmap(p);
    update();
}

void PixmapItem::topChanged(const QString &top)
{
    QPointF p = pos();
    p.setY(top.toInt());
    setPos(p);
}

void PixmapItem::leftChanged(const QString &left)
{
    QPointF p = pos();
    p.setX(left.toInt());
    setPos(p);
}

void PixmapItem::httpRequestDone ( bool error )
{
    spinner->deleteLater();
    setHoruxPixmap(pictureBuffer.data());
}
