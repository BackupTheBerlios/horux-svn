#ifndef HORUXFIELDS_H
#define HORUXFIELDS_H

#include <QtGui/QDialog>
#include <QtSoapHttpTransport>

namespace Ui {
    class HoruxFields;
}

class HoruxFields : public QDialog {
    Q_OBJECT
    Q_DISABLE_COPY(HoruxFields)
public:

    explicit HoruxFields(QWidget *parent = 0);
    virtual ~HoruxFields();

    QString getDatasource();
    void setDatasource(QString source);

protected:
    virtual void changeEvent(QEvent *e);

protected slots:
    void itemSelectionChanged ();

private:
    Ui::HoruxFields *m_ui;
};

#endif // HORUXFIELDS_H
