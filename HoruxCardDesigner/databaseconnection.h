#ifndef DATABASECONNECTION_H
#define DATABASECONNECTION_H

#include <QDialog>
#include <QtSoapHttpTransport>
#include <QSslError>
#include <QNetworkReply>
#include <QtSql>

namespace Ui {
    class DatabaseConnection;
}

class DatabaseConnection : public QDialog {
    Q_OBJECT
public:
    DatabaseConnection(QWidget *parent = 0);
    ~DatabaseConnection();

    void setEngine(const int engine);
    void setHost(const QString url);
    void setUsername(const QString u);
    void setPassword(const QString p);
    void setSSL(const bool ssl);
    void setPath(const QString p);
    void setDatabase(const QString p);
    QString getHost();
    QString getUsername();
    QString getPassword();
    QString getPath();
    bool getSSL();
    int getEngine();
    QString getDatabase();


protected:
    void changeEvent(QEvent *e);

private slots:
    void onEngineCurrentIndexChanged(int index);
    void onTest();
    void readResponse();
    void sslErrors ( QNetworkReply * reply, const QList<QSslError> & errors );

private:
    Ui::DatabaseConnection *m_ui;
    QtSoapHttpTransport transport;
    QSqlDatabase dbase;
};

#endif // DATABASECONNECTION_H
