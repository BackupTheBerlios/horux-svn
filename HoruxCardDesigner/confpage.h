#ifndef CONFPAGE_H
#define CONFPAGE_H

#include <QWidget>
#include <QDebug>
#include <QHttp>
#include <QBuffer>
#include <QSslError>

class QLineEdit;
class QComboBox;
class QSpinBox;

#include "ui_cardsetting.h"

 class CardPage : public QWidget, public Ui::cardSetting
 {
     Q_OBJECT
 public:
     CardPage(QWidget *parent = 0);

private slots:
     void setColor();
     void setOpenFileName();

public:
     QColor color;
 };

#include "ui_textsetting.h"

 class TextPage : public QWidget, public Ui::textSetting
 {
     Q_OBJECT

 public:
     TextPage(QWidget *parent = 0);
    void connectDataSource();

private slots:
     void setColor();
     void setFont();
     void setSource(int s);

signals:
    void changeFont(const QFont &);
    void changeColor(const QColor &);


public:
     QColor color;
     QFont font;
 };

#include "ui_pixmapsetting.h"

 class PixmapPage : public QWidget, public Ui::pixmapSetting
 {
     Q_OBJECT
 public:
     PixmapPage(QWidget *parent = 0);
     void connectDataSource();

private slots:
     void setOpenFileName();
     void setSource(int s);
     void httpRequestDone ( bool error );
     void sslErrors ( const QList<QSslError> & errors );

signals:
    void newPicture(QByteArray pict);

private:
     QHttp pictureHttp;
     QBuffer pictureBuffer;
 };

#endif // CONFPAGE_H
