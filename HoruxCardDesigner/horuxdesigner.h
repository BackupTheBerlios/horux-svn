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

    void loadData();
    void loadHoruxSoap();
    void loadCSVData();
    void loadSQLData();

    static QString getHost() { return pThis->host; }
    static QString getUsername() { return pThis->username; }
    static QString getPassword() { return pThis->password; }
    static QString getPath() { return pThis->path; }
    static QString getDatabaseName() { return pThis->databaseName; }
    static bool getSsl() { return pThis->ssl; }
    static QString getEngine() { return pThis->engine; }
    static QString getFile() { return pThis->file; }
    static QString getSql() { return pThis->sql; }
    static int getPrimaryKeyColumn() { return pThis->primaryKeyColumn; }
    static int getColumn1() { return pThis->column1; }
    static int getColumn2() { return pThis->column2; }
    static int getPictureColumn() { return pThis->pictureColumn; }
    static QStringList getHeader() { return pThis->header; }

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
    void itemMoved(QGraphicsItem *, QPointF pos);
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
    void printSelection();
    void exit();
    void save();
    void saveAs();
    void setDatabase();
    void open();
    void openRecentFile();
    void about();

    void readSoapResponse();
    void userChanged(int);
    void httpRequestDone ( QNetworkReply* );
    void sslErrors ( QNetworkReply * reply, const QList<QSslError> & errors );
    void sslErrors ( const QList<QSslError> & errors );

    void nextRecord();
    void backRecord();

    void mouseRelease();

    void fileChange();

protected:
    void resizeEvent ( QResizeEvent * even);
    void updatePrintPreview();

    virtual void closeEvent ( QCloseEvent * event );
private:
    Ui::HoruxDesigner *ui;
    static HoruxDesigner *pThis;


    QButtonGroup *buttonGroup;

    CardScene *scene;
    CardScene *cardScenePreview;

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

    QNetworkAccessManager pictureHttp;
    QBuffer pictureBuffer;
    QMap<QString, QString> userValue;

    QSqlDatabase dbase;

    QGraphicsScene *scenePreview;

    QMap<QNetworkReply *, int>userPictureReply;
    QMap<int, QBuffer*>userPicture;
    QMap<int, QStringList>userData;
    QStringList header;

    int currentUser;

    QSqlQuery *sqlQuery;

    QStringList printedUser;

    //db connection
    QString host;
    QString username;
    QString password;
    QString path;
    QString databaseName;
    bool ssl;
    QString engine;
    QString file;
    QString sql;
    int primaryKeyColumn;
    int column1;
    int column2;
    int pictureColumn;

    bool fileChanged;

};

#endif // HORUXDESIGNER_H
