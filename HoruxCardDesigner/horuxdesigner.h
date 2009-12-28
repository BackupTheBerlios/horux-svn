#ifndef HORUXDESIGNER_H
#define HORUXDESIGNER_H

#include <QtGui/QMainWindow>
 #include <QSqlDatabase>
 #include <QFile>

#include "cardscene.h"
#include "confpage.h"

class QButtonGroup;
class CardTextItem;
class QFontComboBox;

namespace Ui
{
    class HoruxDesigner;
}

class HoruxDesigner : public QMainWindow
{
    Q_OBJECT

public:
    HoruxDesigner(QWidget *parent = 0);
    ~HoruxDesigner();

private:
     void createToolBox();
     void setParamView(QGraphicsItem *item);
     void setCurrentFile(const QString &fileName);
     void updateRecentFileActions();
     QString strippedName(const QString &fullFileName);

private slots:
     void buttonGroupClicked(int id);
     void itemInserted(QGraphicsItem *item);
     void textInserted(QGraphicsTextItem *item);
     void itemSelected(QGraphicsItem *item);
     void selectionChanged();
     void sceneScaleChanged(const QString &scale);
     void currentFontChanged(const QFont &font);
     void fontSizeChanged(const QString &size);
     void handleFontChange();
     void deleteItem();
     void bringToFront();
     void sendToBack();
     void newCard();
     void printPreview();
     void printSetup();
     void print();
     void exit();
     void save();
     void saveAs();
     void setDatabase();
     void open();
     void openRecentFile();

protected:
    void resizeEvent ( QResizeEvent * even);

private:
    Ui::HoruxDesigner *ui;

    QButtonGroup *buttonGroup;

    CardScene *scene;

    QWidget *param;

    QComboBox *sceneScaleCombo;
    QComboBox *textColorCombo;
    QComboBox *fontSizeCombo;
    QFontComboBox *fontCombo;

    CardPage *cardPage;
    TextPage *textPage;
    PixmapPage *pixmapPage;

    QPrinter *printer;

    QSqlDatabase database;

    QFile currenFile;

    enum { MaxRecentFiles = 5 };
    QAction *recentFileActs[MaxRecentFiles];
};

#endif // HORUXDESIGNER_H
