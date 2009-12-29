#include "printpreview.h"
#include "ui_printpreview.h"

PrintPreview::PrintPreview(QPixmap pix, QWidget *parent) :
    QDialog(parent),
    m_ui(new Ui::PrintPreview)
{
    m_ui->setupUi(this);

    scene = new QGraphicsScene(this);

    scene->addPixmap (pix);

    m_ui->graphicsView->setScene(scene);

    connect(m_ui->print, SIGNAL(clicked()), this, SLOT(accept()));
}

PrintPreview::~PrintPreview()
{
    delete m_ui;
}

void PrintPreview::changeEvent(QEvent *e)
{
    QDialog::changeEvent(e);
    switch (e->type()) {
    case QEvent::LanguageChange:
        m_ui->retranslateUi(this);
        break;
    default:
        break;
    }
}

