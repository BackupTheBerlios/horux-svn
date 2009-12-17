#ifndef PRINTPREVIEW_H
#define PRINTPREVIEW_H

#include <QtGui/QDialog>
#include <QGraphicsScene>

namespace Ui {
    class PrintPreview;
}

class PrintPreview : public QDialog {
    Q_OBJECT
    Q_DISABLE_COPY(PrintPreview)
public:
    explicit PrintPreview(QPixmap pix, QWidget *parent = 0);
    virtual ~PrintPreview();

protected:
    virtual void changeEvent(QEvent *e);

private:
    Ui::PrintPreview *m_ui;
    QGraphicsScene *scene;
};

#endif // PRINTPREVIEW_H
