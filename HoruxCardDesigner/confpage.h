#ifndef CONFPAGE_H
#define CONFPAGE_H

#include <QWidget>

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

 };



#endif // CONFPAGE_H
