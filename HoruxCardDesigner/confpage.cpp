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
    connect(dataSource, SIGNAL(clicked()), this, SLOT(setDataSource()));

    dataSource->hide();

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

void TextPage::setDataSource() {
    int indexSource = source->currentIndex();

    switch(indexSource) {
        case 1: // database
            {
                HoruxFields dlg;

                if(dlg.exec() == QDialog::Accepted)
                {
                    name->setText(dlg.getDatasource());
                    name->setReadOnly(true);
                }
            }
            break;
        case 2: // print counter
            break;
        case 3: // date and time
            break;
    }
}

void TextPage::setSource(int s)
{
    // database source
    if(s == 1 || s == 2 || s == 3)
    {
        dataSource->show();

        /*HoruxFields dlg;

        if(dlg.exec() == QDialog::Accepted)
        {
            name->setText(dlg.getDatasource());
            name->setReadOnly(true);
        }*/
    }
    else
    {
        dataSource->hide();
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

        pictureBuffer.close();
        pictureBuffer.setData(QByteArray());
        pictureBuffer.open(QBuffer::ReadWrite);

        pictureHttp.setHost(host, ssl ? QHttp::ConnectionModeHttps : QHttp::ConnectionModeHttp );
        if(ssl)
        {
            connect(&pictureHttp,SIGNAL(sslErrors( const QList<QSslError> & )), this, SLOT(sslErrors(QList<QSslError>)));
        }


        pictureHttp.get(path + "/pictures/unknown.jpg", &pictureBuffer);
    }
}

void PixmapPage::httpRequestDone ( bool  )
{
    emit newPicture( pictureBuffer.data() );
}

void PixmapPage::connectDataSource()
{
    connect(source, SIGNAL(currentIndexChanged ( int )), this, SLOT(setSource(int)));
}

void PixmapPage::sslErrors ( const QList<QSslError> & errors )
{
    foreach(QSslError sslError, errors)
    {
        if(sslError.error() == QSslError::SelfSignedCertificate)
        {
            pictureHttp.ignoreSslErrors();
        }
        else
            qDebug() << sslError;
    }
}
