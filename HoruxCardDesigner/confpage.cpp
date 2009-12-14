#include <QtGui>

#include "confpage.h"

CardPage::CardPage(QWidget *parent)
     : QWidget(parent)
 {
     QGroupBox *configGroup = new QGroupBox(tr("Card setting"));

     QLabel *sizeLabel = new QLabel(tr("Size:"));
     sizeCombo = new QComboBox;
     sizeCombo->addItem(tr("CR-80 (85.60 x 53.98)"));
     sizeCombo->addItem(tr("CR-90 (92.07 x 60.33)"));
     sizeCombo->addItem(tr("CR-79 (83.90 x 52.10)"));

     QLabel *orientationLabel = new QLabel(tr("Orientation:"));
     orientationCombo = new QComboBox;
     orientationCombo->addItem(tr("Portrait"));
     orientationCombo->addItem(tr("Landscape"));

     QLabel *bkgColorLabel = new QLabel(tr("Background color:"));
     bkgColorColorLineEdit = new QLineEdit("#ffffff");
     bkgColorColorLineEdit->setStyleSheet("background-color: rgb(255, 255, 255);");
     bkgColorColorLineEdit->setReadOnly(true);
     QPushButton *bkgColorPushButton = new QPushButton(tr("..."));
     bkgColorPushButton->setMaximumWidth(40);
     connect(bkgColorPushButton, SIGNAL(clicked()), this, SLOT(setColor()));


     QLabel *bkgPictureLabel = new QLabel(tr("Background picture:"));
     bkgPicturePictureLineEdit = new QLineEdit;
     QPushButton *bkgFilePushButton = new QPushButton(tr("..."));
     bkgFilePushButton->setMaximumWidth(40);
     connect(bkgFilePushButton, SIGNAL(clicked()), this, SLOT(setOpenFileName()));


     QLabel *gridSizeLabel = new QLabel(tr("Grid size:"));
     gridSizeSpinBox = new QSpinBox;
     gridSizeSpinBox->setMinimum(1);
     gridSizeSpinBox->setMaximum(10);
     gridSizeSpinBox->setValue(1);

     QLabel *gridDrawLabel = new QLabel(tr("Grid draw:"));
     gridDrawCombo = new QComboBox;
     gridDrawCombo->addItem(tr("No"));
     gridDrawCombo->addItem(tr("Yes"));
     gridDrawCombo-> setCurrentIndex(0);

     QLabel *gridAlignLabel = new QLabel(tr("Grid align:"));
     gridAlignCombo = new QComboBox;
     gridAlignCombo->addItem(tr("No"));
     gridAlignCombo->addItem(tr("Yes"));

     QHBoxLayout *sizeLayout = new QHBoxLayout;
     sizeLayout->addWidget(sizeLabel);
     sizeLayout->addWidget(sizeCombo);

     QHBoxLayout *orientationLayout = new QHBoxLayout;
     orientationLayout->addWidget(orientationLabel);
     orientationLayout->addWidget(orientationCombo);

     QHBoxLayout *bkgColorLayout = new QHBoxLayout;
     bkgColorLayout->addWidget(bkgColorLabel);
     bkgColorLayout->addWidget(bkgColorColorLineEdit);
     bkgColorLayout->addWidget(bkgColorPushButton);

     QHBoxLayout *bkgPictureLayout = new QHBoxLayout;
     bkgPictureLayout->addWidget(bkgPictureLabel);
     bkgPictureLayout->addWidget(bkgPicturePictureLineEdit);
     bkgPictureLayout->addWidget(bkgFilePushButton);


     QHBoxLayout *gridSizeLayout = new QHBoxLayout;
     gridSizeLayout->addWidget(gridSizeLabel);
     gridSizeLayout->addWidget(gridSizeSpinBox);


     QHBoxLayout *gridDrawLayout = new QHBoxLayout;
     gridDrawLayout->addWidget(gridDrawLabel);
     gridDrawLayout->addWidget(gridDrawCombo);

     QHBoxLayout *gridAlignLayout = new QHBoxLayout;
     gridAlignLayout->addWidget(gridAlignLabel);
     gridAlignLayout->addWidget(gridAlignCombo);


     QVBoxLayout *configLayout = new QVBoxLayout;
     configLayout->addLayout(sizeLayout);
     configLayout->addLayout(orientationLayout);
     configLayout->addLayout(bkgColorLayout);
     configLayout->addLayout(bkgPictureLayout);
     configLayout->addLayout(gridSizeLayout);
     configLayout->addLayout(gridDrawLayout);
     configLayout->addLayout(gridAlignLayout);
     configGroup->setLayout(configLayout);

     QVBoxLayout *mainLayout = new QVBoxLayout;
     mainLayout->addWidget(configGroup);
     mainLayout->addStretch(1);
     setLayout(mainLayout);
 }

void CardPage::setColor()
 {
     bkgColor = QColorDialog::getColor(Qt::green, this);
     if (bkgColor.isValid()) {
         bkgColorColorLineEdit->setText(bkgColor.name());
         bkgColorColorLineEdit->setStyleSheet("background-color: " + bkgColor.name() + ";");
     }
 }

 void CardPage::setOpenFileName()
 {
     QFileDialog::Options options;
     QString selectedFilter;
     QString fileName = QFileDialog::getOpenFileName(this,
                                 tr("Pictures files"),
                                 bkgPicturePictureLineEdit->text(),
                                 tr("All files (*);;PNG Files (*.png);;JPEG Files (*.jpg);;GIF Files (*.gif)"),
                                 &selectedFilter);
     if (!fileName.isEmpty())
        bkgPicturePictureLineEdit->setText(fileName);
}


 TextPage::TextPage(QWidget *parent)
     : QWidget(parent)
 {
     QGroupBox *updateGroup = new QGroupBox(tr("Package selection"));
     QCheckBox *systemCheckBox = new QCheckBox(tr("Update system"));
     QCheckBox *appsCheckBox = new QCheckBox(tr("Update applications"));
     QCheckBox *docsCheckBox = new QCheckBox(tr("Update documentation"));

     QGroupBox *packageGroup = new QGroupBox(tr("Existing packages"));

     QListWidget *packageList = new QListWidget;
     QListWidgetItem *qtItem = new QListWidgetItem(packageList);
     qtItem->setText(tr("Qt"));
     QListWidgetItem *qsaItem = new QListWidgetItem(packageList);
     qsaItem->setText(tr("QSA"));
     QListWidgetItem *teamBuilderItem = new QListWidgetItem(packageList);
     teamBuilderItem->setText(tr("Teambuilder"));

     QPushButton *startUpdateButton = new QPushButton(tr("Start update"));

     QVBoxLayout *updateLayout = new QVBoxLayout;
     updateLayout->addWidget(systemCheckBox);
     updateLayout->addWidget(appsCheckBox);
     updateLayout->addWidget(docsCheckBox);
     updateGroup->setLayout(updateLayout);

     QVBoxLayout *packageLayout = new QVBoxLayout;
     packageLayout->addWidget(packageList);
     packageGroup->setLayout(packageLayout);

     QVBoxLayout *mainLayout = new QVBoxLayout;
     mainLayout->addWidget(updateGroup);
     mainLayout->addWidget(packageGroup);
     mainLayout->addSpacing(12);
     mainLayout->addWidget(startUpdateButton);
     mainLayout->addStretch(1);
     setLayout(mainLayout);
 }
