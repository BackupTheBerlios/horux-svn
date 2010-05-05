#include <QtTest>
#include <cdbhandling.h>
#include <QSqlQuery>
#include <QCryptographicHash>
 
 
class TestCDbHandling: public QObject
{
    Q_OBJECT
    private slots:

        void testInit();
        void testUsedTable();
        void testPlugin();


    private:
        CDbHandling *handle;
        QString currentDbName;

};

void TestCDbHandling::testInit()
{
    // get and set the database name
    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

    settings.beginGroup ( "SQL" );
    currentDbName = settings.value("db", "horux").toString();
    settings.setValue("db", "horuxTestUnit");
    settings.endGroup();

    handle = CDbHandling::getInstance();

    QVERIFY2(handle->init() == false, "The database horuxTestUnit should not exist, so it should not be possible to init the handler");
    QVERIFY2(handle->isStarted() == false, "As the init sould not be ok, it should not be ok that the handler is started");

    delete handle;
    handle = CDbHandling::getInstance();

    //
    QString queries = "SHOW TABLES;";
    QVERIFY2(handle->loadSchema(queries) == true, "Do not continue if the schema cannot be loaded");
    QVERIFY2(handle->loadData(queries) == true, "Do not continue if the data cannot be loaded");

    queries = "SHOW TBLES;";
    QVERIFY2(handle->loadData(queries) == false, "Do not continue if we have a sql queries error");


    delete handle;
    handle = CDbHandling::getInstance();

    QVERIFY2(handle->init() == true, "As the database sould be created just before, the init should now be ok");
    QVERIFY2(handle->isStarted() == true, "If the init is ok, the handler sould be started");

    QSqlQuery query;
    query.prepare("DROP DATABASE horuxTestUnit");
    QVERIFY2(query.exec() == true, "Cannot drop the database");

    delete handle;

    settings.beginGroup ( "SQL" );
    settings.setValue("db", currentDbName);
    settings.endGroup();

}


void TestCDbHandling::testUsedTable()
{

    handle = CDbHandling::getInstance();

    QMap<QString,QStringList> map = handle->getUsedTables();

    QStringList lTest;

    lTest << "hr_device" << "hr_config" << "hr_superusers";

    foreach(QString key, map.keys())
    {
      QVERIFY2(key == "DbTableUsed", "check key name");
      QVERIFY2(map.value(key) == lTest, "check table value");
    }
  
    delete handle;
}

void TestCDbHandling::testPlugin()
{
    QSettings settings ( QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat );

    settings.beginGroup ( "SQL" );
    currentDbName = settings.value("db", "horux").toString();
    settings.setValue("db", "horuxTestUnitWithData");
    settings.endGroup();

    handle = CDbHandling::getInstance();

    QVERIFY(handle->init());

    CDbInterface *plugin = handle->plugin();
    QVERIFY(plugin);

    if(plugin)
    {
        QVERIFY2(plugin->getMetaObject(), "Meta object undefined");

        qDebug() << "Test the db plugin : " <<  plugin->getMetaObject()->metaObject()->className();

        //test a super user having the web service right
        QVERIFY( plugin->isXMLRPCAccess("admin", "d033e22ae348aeb5660fc2140aec35850c4da997") == true );

        //test a super user do not have the web service right
        QVERIFY( plugin->isXMLRPCAccess("test", "a94a8fe5ccb19ba61c4c0873d391e987982fbbd3") == false);

        // test if the function return the well value from the hr_config table
        QVERIFY( plugin->getConfigParam("xmlrpc_server").toString() == "localhost" );

        // test if the function return the well value from the hr_config table
        QVERIFY( plugin->getConfigParam("unknow_parameter").toString() == "unknow parameter" );


        // test if the parent device is equal to 0 for the device 6
        QVERIFY( plugin->getParentDevice(6) == 0 );

        // test if the parent device is equal to -1 for unexisting device
        QVERIFY( plugin->getParentDevice(199) == -1 );

        QMap<int, QString> dList = plugin->getDeviceList();

        foreach(int id, dList.keys())
           QVERIFY(id == 6);

        foreach(QString type, dList.values())
           QVERIFY(type == "gantner_AccessTerminal");

    }

    settings.beginGroup ( "SQL" );
    settings.setValue("db", currentDbName);
    settings.endGroup();

    delete handle;
}


QTEST_MAIN(TestCDbHandling)
#include "testcdbhandling.moc"
