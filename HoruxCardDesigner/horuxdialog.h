#ifndef HORUXDIALOG_H
#define HORUXDIALOG_H

#include <QtGui/QDialog>
#include <QtSoapHttpTransport>
#include <QSslError>
#include <QNetworkReply>

namespace Ui {
    class HoruxDialog;
}

class HoruxDialog : public QDialog {
    Q_OBJECT
    Q_DISABLE_COPY(HoruxDialog)
public:
            explicit HoruxDialog(QWidget *parent = 0);
    virtual ~HoruxDialog();

    void setHorux(const QString url);
    void setUsername(const QString u);
    void setPassword(const QString p);
    void setSSL(const bool ssl);
    void setPath(const QString p);
    QString getHorux();
    QString getUsername();
    QString getPassword();
    QString getPath();
    bool getSSL();

protected:
    virtual void changeEvent(QEvent *e);

private slots:
    void onTest();
    void readResponse();
    void sslErrors ( QNetworkReply * reply, const QList<QSslError> & errors );

private:
    Ui::HoruxDialog *m_ui;
    QtSoapHttpTransport transport;
};

#endif // HORUXDIALOG_H
