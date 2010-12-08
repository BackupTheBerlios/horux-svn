#ifndef PRINTHORUXUSER_H
#define PRINTHORUXUSER_H

#include <QDialog>
#include <QtSoapHttpTransport>

namespace Ui {
    class PrintHoruxUser;
}

class PrintHoruxUser : public QDialog
{
    Q_OBJECT

public:
    explicit PrintHoruxUser(QWidget *parent = 0);
    ~PrintHoruxUser();

    void setUserType(QString t);

protected slots:
    void rfidStep();
    void rfidDetected();
    void subokNext();

    void readSoapResponse();
    void sslErrors ( QNetworkReply * reply, const QList<QSslError> & errors );
    void sslErrors ( const QList<QSslError> & errors );

signals:
    void printCard();
    void newUserAdd();

private:
    Ui::PrintHoruxUser *ui;
    QString type;
    QtSoapHttpTransport transport;
};

#endif // PRINTHORUXUSER_H
