#ifndef HORUXDESIGNER_H
#define HORUXDESIGNER_H

#include <QtGui/QMainWindow>
#include <QFile>
#include <QHttp>
#include <QBuffer>
#include <QMessageBox>
#include <QtSoapHttpTransport>
#include <QMap>
#include <QSplashScreen>
#include <QtSql>


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

    void loadData(QSplashScreen *sc);
    void loadHoruxSoap(QSplashScreen *sc);

private:
    void createToolBox();
    void initScene();
    void createAction();
    void createToolBar();


    void setParamView(QGraphicsItem *item);
    void setCurrentFile(const QString &fileName);
    void updateRecentFileActions();
    QString strippedName(const QString &fullFileName);

private slots:
    void buttonGroupClicked(int id);
    void itemInserted(QGraphicsItem *item);
    void textInserted(QGraphicsTextItem *item);
    void itemSelected(QGraphicsItem *item);
    void itemMoved(QGraphicsItem *);
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
    void printAll();
    void exit();
    void save();
    void saveAs();
    void setDatabase();
    void open();
    void openRecentFile();
    void about();

    void readSoapResponse();
    void readSoapResponseUser();
    void userChanged(int);
    void httpRequestDone ( bool error );
    void sslErrors ( QNetworkReply * reply, const QList<QSslError> & errors );
    void sslErrors ( const QList<QSslError> & errors );

    void nextRecord();
    void backRecord();

protected:
    void resizeEvent ( QResizeEvent * even);
    void updatePrintPreview();

private:
    Ui::HoruxDesigner *ui;

    QButtonGroup *buttonGroup;

    CardScene *scene;

    QComboBox *sceneScaleCombo;
    QComboBox *textColorCombo;
    QComboBox *fontSizeCombo;
    QFontComboBox *fontCombo;
    QComboBox *userCombo;

    CardPage *cardPage;
    TextPage *textPage;
    PixmapPage *pixmapPage;

    QPrinter *printer;

    QFile currenFile;

    QtSoapHttpTransport transport;

    enum { MaxRecentFiles = 5 };
    QAction *recentFileActs[MaxRecentFiles];

    QLabel *isSecure;
    QLabel *dbInformation;

    QHttp pictureHttp;
    QBuffer pictureBuffer;
    QMap<QString, QString> userValue;

    QSqlDatabase dbase;

    QGraphicsScene *scenePreview;
};

#endif // HORUXDESIGNER_H
