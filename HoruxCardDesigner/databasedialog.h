#ifndef DATABASEDIALOG_H
#define DATABASEDIALOG_H

#include <QtGui/QDialog>

namespace Ui {
    class DatabaseDialog;
}

class DatabaseDialog : public QDialog {
    Q_OBJECT
    Q_DISABLE_COPY(DatabaseDialog)

public:
    explicit DatabaseDialog(QWidget *parent = 0);
    virtual ~DatabaseDialog();

    void setHost(const QString host);
    void setUsername(const QString username);
    void setPassword(const QString password);
    void setDb(const QString db);
    void setEngine(const int engine);

    QString getHost();
    QString getUsername();
    QString getPassword();
    QString getDb();
    int getEngine();

protected:
    virtual void changeEvent(QEvent *e);

private:
    Ui::DatabaseDialog *m_ui;
};

#endif // DATABASEDIALOG_H
