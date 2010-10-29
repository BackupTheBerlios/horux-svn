#include <QtGui>
#include "horuxfields.h"
#include "printcounter.h"
#include "confpage.h"
#include "formattext.h"
#include "horuxdesigner.h"

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
    connect(formatButton, SIGNAL(clicked()), this, SLOT(setFormat()));
    connect(format, SIGNAL(currentIndexChanged(int)), this, SLOT(formatCBChange(int)));

    dataSource->hide();

    color = QColor(Qt::black);
    source->setCurrentIndex(0);
}

void TextPage::formatCBChange ( int index ) {
    emit changeFormat(index,format_digit, format_decimal, format_date, format_sourceDate);
}

void TextPage::setFormat() {
    FormatText dlg(this);

    dlg.setFormat(format->currentIndex(), format_digit, format_decimal, format_date, format_sourceDate);

    if(dlg.exec() == QDialog::Accepted) {
        format_digit = dlg.digit();
        format_decimal = dlg.decimal();
        format_date = dlg.date();
        format_sourceDate = dlg.sourceDate();

        emit changeFormat(format->currentIndex(),format_digit, format_decimal, format_date, format_sourceDate);
    }
}

void TextPage::setFormat(int f, int digit, int decimal, QString date, QString sourceDate) {
    format_digit = digit;
    format_decimal = decimal;
    format_date = date;
    format_sourceDate = sourceDate;
    format->setCurrentIndex(f);
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
                HoruxFields dlg(this);
                dlg.setDatasource(name->text());

                if(dlg.exec() == QDialog::Accepted)
                {
                    name->setText(dlg.getDatasource());
                    name->setReadOnly(true);
                }
            }
            break;
        case 2: // print counter
            {
                PrintCounter dlg(this);
                dlg.setValues(initialValue,increment,digits);
                if(dlg.exec() == QDialog::Accepted)
                {
                    initialValue = dlg.getInitialValue();
                    increment =  dlg.getIncrement();
                    digits = dlg.getDigits();
                    emit changePrintCounter(dlg.getInitialValue(), dlg.getIncrement(), dlg.getDigits());
                }
            }
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

void TextPage::setPrintCounter(int iv, int inc, int d) {
    initialValue = iv;
    increment = inc;
    digits = d;
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

        QString host = HoruxDesigner::getHost();
        QString path = HoruxDesigner::getPath();
        bool ssl = HoruxDesigner::getSsl();

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
