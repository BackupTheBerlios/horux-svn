#include "horuxdesigner.h"
#include "ui_horuxdesigner.h"

HoruxDesigner::HoruxDesigner(QWidget *parent)
    : QMainWindow(parent), ui(new Ui::HoruxDesigner)
{
    ui->setupUi(this);

    card = new QSvgWidget(":/CR-80.svg",ui->cardWidget);
}

HoruxDesigner::~HoruxDesigner()
{
    delete ui;
}
