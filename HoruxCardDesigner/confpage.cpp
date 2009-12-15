#include <QtGui>

#include "confpage.h"

CardPage::CardPage(QWidget *parent)
     : QWidget(parent)
 {
     setupUi(this);

     connect(bkgColorButton, SIGNAL(clicked()), this, SLOT(setColor()));
     connect(bkgPictureButton, SIGNAL(clicked()), this, SLOT(setOpenFileName()));
 }

void CardPage::setColor()
 {
     color = QColorDialog::getColor(Qt::green, this);
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
 }
