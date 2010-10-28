#ifndef FORMATTEXT_H
#define FORMATTEXT_H

#include <QDialog>

namespace Ui {
    class FormatText;
}

class FormatText : public QDialog {
    Q_OBJECT
public:
    FormatText(QWidget *parent = 0);
    ~FormatText();

    void setFormat(int, int, int, QString, QString) ;
    int digit();
    int decimal();
    QString date();
    QString sourceDate();

protected:
    void changeEvent(QEvent *e);

protected slots:
    void valueChanded(const QString & );

private:
    Ui::FormatText *ui;
    int format_type;
};

#endif // FORMATTEXT_H
