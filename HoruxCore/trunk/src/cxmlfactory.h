
#ifndef CXMLFACTORY_H
#define CXMLFACTORY_H

#include <QtCore>

typedef QMap<QString,QVariant> MapParam;

class CXmlFactory
{
public:
    /*!
        Create the xml structure to send a system alarm
        @id Id of the destination object
        @e Event number
        @m Message text
     */
    static QString systemAlarm(QString id, QString e, QString m);

    /*!
        Create the xml structure to send a device event
        @id Id of the device
        @e Event number
        @m Message text
     */
    static QString deviceEvent(QString id, QString e, QString m);

    /*!
        Create the xml structure to send a device event
        @id Id of the device
        @e Event name
        @m Message text
     */
    static QString deviceEvent(QString id, QString e, QMap<QString, QString>p);

    /*!
        Create the xml structure to send a access alarm
        @id Id of the object
        @e Event number
        @m Message text
     */
    static QString accessAlarm(QString id, QString e, QString m);

    /*!
        Create the xml structure to send a device action
        @id Id of the destination object
        @f Function name
        @p list of the parameters QMap<[Param name], [Param value]>
     */
    static QString deviceAction(QString id, QString f, QMap<QString, QString> p);

    /*!
        Create the xml structure to send a key detection
        @id Id of the destination object
        @pn Name of the access plugin
        @k Value of the key
     */
    static QString keyDetection(QString id, QString id_parent, QString pn, QString k);

    /*!
      Parse the xml device action and return it as a QMap
      MapParam is a type QMap<QString,QVariant
      @xml Xml device action
      @id id of the device who receive the device action
      @id parent_id of the device who receive the device action
      @return Return a QMap list of device action
    */
    static QMap<QString, MapParam> deviceAction(QString xml , int id, int parent_id = 0);

    /*!
      Parse the xml device event and return it as a QMap
      MapParam is a type QMap<QString,QVariant
      @xml Xml device action
      @id Id of the device who receive the device action
      @return Return a QMap list of device action
    */
    static QMap<QString, QVariant> deviceEvent(QString xml);
};

#endif // CXMLFACTORY_H
