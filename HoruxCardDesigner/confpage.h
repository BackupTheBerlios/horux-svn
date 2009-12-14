#ifndef CONFPAGE_H
#define CONFPAGE_H

#include <QWidget>

class QLineEdit;
class QComboBox;
class QSpinBox;

 class CardPage : public QWidget
 {
     Q_OBJECT
 public:
     CardPage(QWidget *parent = 0);

private slots:
     void setColor();
     void setOpenFileName();

public:
    QLineEdit *bkgColorColorLineEdit;
    QColor bkgColor;
    QLineEdit *bkgPicturePictureLineEdit;
    QComboBox *sizeCombo;
    QComboBox *orientationCombo;
    QSpinBox *gridSizeSpinBox;
    QComboBox *gridDrawCombo;
    QComboBox *gridAlignCombo;
 };

 class TextPage : public QWidget
 {
 public:
     TextPage(QWidget *parent = 0);
 };



#endif // CONFPAGE_H
