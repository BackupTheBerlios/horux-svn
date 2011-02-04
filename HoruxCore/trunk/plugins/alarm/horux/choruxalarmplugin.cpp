
#include "choruxalarmplugin.h"
#include <QtCore>
#include <QtXml>
#include <QtSql>



void CHoruxAlarmPlugin::alarmMonitor(QString xml)
{
  QDomDocument doc;
  doc.setContent(xml);
  QDomElement root = doc.documentElement();

  QDomNode node = root.firstChild();

  if(root.tagName() != "deviceEvent" && root.tagName() != "accessAlarm" && root.tagName() != "systemAlarm")
  {
    return;
  }

  QString id = root.attribute("id");

  QMap<QString, QVariant>funcParam;
  QString func;

  QDomNode eventNode = root.firstChild();

  QDomElement event = eventNode.toElement();

  if(event.tagName() == "event") 
  {
    func = event.text();

    eventNode = eventNode.nextSibling(); 
  
    QDomElement params = eventNode.toElement();

    QDomNode paramsNode = params.firstChild();
    while(!paramsNode.isNull())
    {
      QDomElement params = paramsNode.toElement();

      if(params.tagName() == "param")
      {
        QString pName;
        QVariant pValue;
        QDomNode p = params.firstChild();
        if(p.toElement().tagName() == "name")
        {  
          pName = p.toElement().text();
          p = p.nextSibling();
          if(p.toElement().tagName() == "value")
          {
            pValue = p.toElement().text();
            funcParam[pName] = pValue;
            
          }
        } 
      }
        
      paramsNode = paramsNode.nextSibling(); 
    }

    funcParam["id"] = id;

    handleHalarm(func, funcParam);
  }
}

void CHoruxAlarmPlugin::deviceConnectionMonitor(int deviceId, bool isConnected)
{
  if(isConnected)
  {
    QSqlQuery query("INSERT INTO `hr_alarms` ( `type` , `datetime_` , `id_object`) VALUES ( '" +
    QString(ALARM_DEVICE_OPEN) +
    "',CURRENT_TIMESTAMP(), '" + QString::number(deviceId) + "')");

    QMap<QString, QVariant> p;
    p["type"] = "ALARM"; //ALARM, LOG, CUSTOM
    p["code"] = ALARM_DEVICE_OPEN;
    p["object"] = deviceId;
    
    
    emit notification(p);

  }
  else
  {
    QSqlQuery query("INSERT INTO `hr_alarms` ( `type` , `datetime_` , `id_object`) VALUES ( '" +
    QString(ALARM_DEVICE_CLOSE) +
    "',CURRENT_TIMESTAMP(), '" + QString::number(deviceId) + "')");

    QMap<QString, QVariant> p;
    p["type"] = "ALARM"; //ALARM, LOG, CUSTOM
    p["code"] = ALARM_DEVICE_CLOSE;
    p["object"] = deviceId;
    
    
    emit notification(p);

  }
}

void CHoruxAlarmPlugin::deviceInputMonitor(int , int , bool )
{
 // qDebug("(Alarm plugin) Input changed on %u (%u:%u)", deviceId, in, status);
}

Q_EXPORT_PLUGIN2(horuxalarmplugin, CHoruxAlarmPlugin);




/*!
    \fn CHoruxAlarmPlugin::handleHalarm(QString name, QMap<QString, QVariant>params)
 */
void CHoruxAlarmPlugin::handleHalarm(QString name, QMap<QString, QVariant>params)
{
  QString type = "";


 // antivandal
  if(name == "1001" || name == "1002")
  {
      type = name;
  }

  //Door Ajar
  if(name == "1005" || name=="1006")
  {
    type = name;
  }


  if(name == "1007")
  {
      type = ALARM_DOOR_FORCED;
  }

  if(name == "1008")
  {
      type = ALARM_TOO_MANY_PIN;
  }

  if(name=="1009")
  {
    type = ALARM_DEVICE_TEMPERATUR;
  }

  if(name=="1010")
  {
    type = ALARM_DEVICE_EEPROM_DB_FULL;
  }

  if(name=="1011")
  {
    type = ALARM_DEVICE_EEPROM_DB_WARNING;

  }
 
  if(name=="1012")
  {
    type = ALARM_DEVICE_EEPROM_DB_INSERT;
  }

  if(name=="1013")
  {
    type = ALARM_DEVICE_EEPROM_DB_REMOVE;
  }



  //Antenna
  if(name == "1015" || name == "1014")  
  {
        type = name;
  }


  if(name == "1016")
  {
      type = ALARM_DEVICE_CONNECTION_ERROR;
  }

  if(name == "1017")
  {
      type = ALARM_DEVICE_CMD_ERROR;
  }


  if(name=="1100")
  {
    type = ALARM_KEY_BLOCKED;
  } 

  if(name=="1101")
  {
    type = ALARM_PERSON_BLOCKED;
  } 

  if(name=="1102")
  {
    type = ALARM_PINCODE_HOLDUP;
  }


  if(name == "1200")
  {
    type = ALARM_XML_RPC_SERVER;
  }

  if(type != "")
  {
    QSqlQuery query("INSERT INTO `hr_alarms` ( `type` , `datetime_` , `id_object`, `message`) VALUES ( '" +
    type +
    "',CURRENT_TIMESTAMP(), '" + params["id"].toString() + "', '" + params["message"].toString()  + "')");

    QMap<QString, QVariant> p;
    p["type"] = "ALARM"; //ALARM, LOG, CUSTOM
    p["code"] = type;
    p["object"] = params["id"].toString();
    p["message"] = params["message"].toString();
    
    
    emit notification(p);

  }


}
