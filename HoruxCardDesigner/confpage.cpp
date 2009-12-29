#include <QtGui>
#include "horuxfields.h"
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
     QString selectedFilter;
     QString fileName = QFileDialog::getOpenFileName(this,
                                 tr("Pictures files"),
                                 bkgPicture->text(),
                                 tr("All files (*);;PNG Files (*.png);;JPEG Files (*.jpg);;GIF Files (*.gif)"),
                                 &selectedFilter, QFileDialog::DontUseNativeDialog);
     if (!fileName.isEmpty())
        bkgPicture->setText(fileName);
}

 /*****************************************************************************************************************/

 TextPage::TextPage(QWidget *parent)
     : QWidget(parent)
 {
    setupUi(this);

     connect(fontButton, SIGNAL(clicked()), this, SLOT(setFont()));
     connect(colorButton, SIGNAL(clicked()), this, SLOT(setColor()));


     color = QColor(Qt::black);
     source->setCurrentIndex(0);
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

void TextPage::setSource(int s)
{
    // database source
    if(s == 1)
    {
        HoruxFields dlg;

        if(dlg.exec() == QDialog::Accepted)
        {
            name->setText(dlg.getDatasource());
            name->setReadOnly(true);
        }
    }
    else
    {
         name->setReadOnly(false);
    }
}

void TextPage::connectDataSource()
{
     connect(source, SIGNAL(currentIndexChanged ( int )), this, SLOT(setSource(int)));
}

/*****************************************************************************************************************/

PixmapPage::PixmapPage(QWidget *parent)
     : QWidget(parent)
 {
     setupUi(this);
     connect(pixFileButton, SIGNAL(clicked()), this, SLOT(setOpenFileName()));

     pictureBuffer.open(QBuffer::ReadWrite);
     connect(&pictureHttp, SIGNAL(done(bool)), this, SLOT(httpRequestDone(bool)));
 }

 void PixmapPage::setOpenFileName()
 {
     QString selectedFilter;
     QString fileName = QFileDialog::getOpenFileName(this,
                                 tr("Pictures files"),
                                 file->text(),
                                 tr("All files (*);;PNG Files (*.png);;JPEG Files (*.jpg);;GIF Files (*.gif)"),
                                 &selectedFilter, QFileDialog::DontUseNativeDialog);
     if (!fileName.isEmpty())
        file->setText(fileName);
}

void PixmapPage::setSource(int s)
{
    // database source
    if(s == 1)
    {
        //pictureBuffer.reset();

        QSettings settings("Letux", "HoruxCardDesigner", this);

        QString host = settings.value("horux", "localhost").toString();
        QString path = settings.value("path", "").toString();
        bool ssl = settings.value("ssl", "").toBool();

        pictureHttp.setHost(host, ssl ? QHttp::ConnectionModeHttps : QHttp::ConnectionModeHttp );
        pictureHttp.get(path + "/pictures/unknown.jpg", &pictureBuffer);
    }
}

void PixmapPage::httpRequestDone ( bool error )
{
    emit newPicture( pictureBuffer.data() );
}

void PixmapPage::connectDataSource()
{
     connect(source, SIGNAL(currentIndexChanged ( int )), this, SLOT(setSource(int)));
}
