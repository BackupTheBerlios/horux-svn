#include <QtGui>

#include "confpage.h"

CardPage::CardPage(QWidget *parent)
     : QWidget(parent)
 {
     setupUi(this);

     connect(bkgColorButton, SIGNAL(clicked()), this, SLOT(setColor()));
     connect(bkgPictureButton, SIGNAL(clicked()), this, SLOT(setOpenFileName()));

     color = QColor(Qt::black);
 }

void CardPage::setColor()
 {
     color = QColorDialog::getColor(color, this);
     if (color.isValid()) {
         bkgColor->setText(color.name());
         bkgColor->setStyleSheet("background-color: " + color.name() + ";");
     }
 }

 void CardPage::setOpenFileName()
 {
     QFileDialog::Options options;
     QString selectedFilter;
     QString fileName = QFileDialog::getOpenFileName(this,
                                 tr("Pictures files"),
                                 bkgPicture->text(),
                                 tr("All files (*);;PNG Files (*.png);;JPEG Files (*.jpg);;GIF Files (*.gif)"),
                                 &selectedFilter);
     if (!fileName.isEmpty())
        bkgPicture->setText(fileName);
}


 TextPage::TextPage(QWidget *parent)
     : QWidget(parent)
 {
    setupUi(this);

     connect(fontButton, SIGNAL(clicked()), this, SLOT(setFont()));
     connect(colorButton, SIGNAL(clicked()), this, SLOT(setColor()));
     color = QColor(Qt::black);
 }


 void TextPage::setColor()
 {
     color = QColorDialog::getColor(color, this);
     if (color.isValid()) {
         emit changeColor(color);
     }
 }

 void TextPage::setFont()
 {
    bool ok;
    QFont newFont = QFontDialog::getFont(
                 &ok, font, this);
    if(ok)
    {
        font = newFont;
        emit changeFont(newFont);
    }
 }

