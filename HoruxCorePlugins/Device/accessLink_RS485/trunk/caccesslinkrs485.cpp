/***************************************************************************
 *   Copyright (C) 2008 by Jean-Luc Gyger   *
 *   jean-luc.gyger@letux.ch   *
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 3 of the License.     *
 *                                       *
 *                                                                         *
 *   This program is distributed in the hope that it will be useful,       *
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the         *
 *   GNU General Public License for more details.                          *
 *                                                                         *
 *   You should have received a copy of the GNU General Public License     *
 *   along with this program; if not, write to the                         *
 *   Free Software Foundation, Inc.,                                       *
 *   59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.             *
 ***************************************************************************/
#include "caccesslinkrs485.h"
#include <QtCore>
#include <QtSql>

CAccessLinkRS485::CAccessLinkRS485(QObject *parent) : QObject(parent)
{
  _isConnected = false;
  input = 0;
  output = 0;
  status = FREE;
  msgTimeoutTimer = 0;
  doorLockMode = NONE;
  openModeTimeout = 0;
  openModeTimeoutTimer = 0;
  checkStandaloneTimer = 0;
  openModeInput = 0;
  dbSize = 0;
  isFreeAccess = false;

  addFunction("openDoor", CAccessLinkRS485::s_openDoorLock);
}

CDeviceInterface *CAccessLinkRS485::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
  CDeviceInterface *p = new CAccessLinkRS485 ( parent );

  p->setParameter("name",config["name"]);  
  p->setParameter("_isLog",config["isLog"]);
  p->setParameter("accessPlugin",config["accessPlugin"]);
  p->setParameter("id",config["id_device"]);  
  p->setParameter("address",config["address"]);
  p->setParameter("memory",config["memory"]);
  p->setParameter("rtc",config["rtc"]);
  p->setParameter("lcd",config["lcd"]);
  p->setParameter("keyboard",config["keyboard"]);
  p->setParameter("eeprom",config["eeprom"]);
  p->setParameter("defaultText",config["defaultText"]);
  p->setParameter("oTime1",config["outputTime1"]);
  p->setParameter("oTime2",config["outputTime2"]);
  p->setParameter("oTime3",config["outputTime3"]);
  p->setParameter("oTime4",config["outputTime4"]);
  p->setParameter("antipassback",config["antipassback"]);
  p->setParameter("standalone",config["standalone"]);
  p->setParameter("doorLockMode",config["open_mode"]);
  p->setParameter("openModeTimeout",config["open_mode_timeout"]);
  p->setParameter("openModeInput",config["open_mode_input"]);


  return p;
}


void CAccessLinkRS485::deviceAction(QString xml)
{
  QDomDocument doc;
  doc.setContent(xml);

  QDomElement root = doc.documentElement();

  QDomNode node = root.firstChild();

  if(root.tagName() != "deviceAction")
  {
    return;
  }

  if(root.attribute("id").toInt() != id)
    return;

  QDomNode actionNode = root.firstChild();

  while(!actionNode.isNull())
  {
    QDomElement action = actionNode.toElement();

    if(action.tagName() == "action") 
    {
      QString funcName;
      QMap<QString, QVariant>funcParam;

      QDomNode functionNode = action.firstChild();
      while(!functionNode.isNull())
      {
        QDomElement function = functionNode.toElement();

        if(function.tagName() == "function")
          funcName = function.text();

        if(function.tagName() == "params")
        {
          QDomNode paramsNode = function.firstChild();
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

        }

        functionNode = functionNode.nextSibling(); 

      }
      
      if(interfaces[funcName])
      {
          void (*func)(QObject *, QMap<QString, QVariant>) = interfaces[funcName];
          func(getMetaObject(), funcParam);
      }
      else
        qDebug("The function " + funcName.toLatin1() + " is not define in the device " + name.toLatin1());
    }

    actionNode = actionNode.nextSibling(); 
  }

}


void CAccessLinkRS485::connectChild(CDeviceInterface *)
{

}

QVariant CAccessLinkRS485::getParameter(QString paramName)
{
  if(paramName == "name")
    return name;
  if(paramName == "id")
    return id;
  if(paramName == "_isLog")
    return _isLog;
  if(paramName == "accessPlugin")
    return accessPlugin;


  if(paramName == "address")
    return address;
  if(paramName == "memory")
    return memory;
  if(paramName == "rtc")
    return rtc;
  if(paramName == "lcd")
    return lcd;
  if(paramName == "keyboard")
    return keyboard;
  if(paramName == "eeprom")
    return eeprom;
  if(paramName == "defaultText")
    return defaultText;
  if(paramName == "oTime1")
    return oTime1;
  if(paramName == "oTime2")
    return oTime2;
  if(paramName == "oTime3")
    return oTime3;
  if(paramName == "oTime4")
    return oTime4;
  if(paramName == "antipassback")
    return antipassback;
  if(paramName == "standalone")
    return standalone;
  if(paramName == "doorLockMode")
    return doorLockMode;
  if(paramName == "openModeTimeout")
    return openModeTimeout;
  if(paramName == "openModeInput")
    return openModeInput;



  return "undefined";
}

void CAccessLinkRS485::setParameter(QString paramName, QVariant value) 
{
  if(paramName == "name")
    name = value.toString();
  if(paramName == "id")
    id = value.toInt();
  if(paramName == "_isLog")
    _isLog = value.toBool();
  if(paramName == "accessPlugin")
    accessPlugin = value.toString();

  if(paramName == "address")
    address = value.toInt();
  if(paramName == "memory")
    memory = (MEMORY)value.toInt();
  if(paramName == "rtc")
    rtc = value.toBool();
  if(paramName == "lcd")
    lcd = value.toBool();
  if(paramName == "keyboard")
    keyboard = value.toBool();
  if(paramName == "eeprom")
    eeprom = value.toBool();
  if(paramName == "defaultText")
    defaultText = value.toString();
  if(paramName == "oTime1")
    oTime1 = value.toInt();
  if(paramName == "oTime2")
    oTime2 = value.toInt();
  if(paramName == "oTime3")
    oTime3 = value.toInt();
  if(paramName == "oTime4")
    oTime4 = value.toInt();
  if(paramName == "antipassback")
    antipassback = value.toInt();
  if(paramName == "standalone")
    standalone = value.toBool();
  if(paramName == "doorLockMode")
  {
    doorLockMode = (DOOR_LOCK_MODE)value.toInt();

    if(value.toString().toLatin1() == "NONE") doorLockMode = NONE;
    if(value.toString().toLatin1() == "NO_TIMEOUT") doorLockMode = NO_TIMEOUT;
    if(value.toString().toLatin1() == "TIMEOUT") doorLockMode = TIMEOUT;
    if(value.toString().toLatin1() == "TIMEOUT_IN") doorLockMode = TIMEOUT_IN;
  }

  if(paramName == "openModeTimeout")
    openModeTimeout = value.toInt();

  if(paramName == "openModeInput")
    openModeInput = value.toInt();

}


bool CAccessLinkRS485::open()
{
  _isConnected = true;
  
  emit deviceConnection(id, true);

  //! ask the reader firmware
  getFirmware();  

  //! ask the reader serial number
  getSerialNumber();

  //! set antenna on
  setAntenna(true);

  //! reset the output
  resetOutput();
 
  //! read output status
  getOutputStatus();

  //! if this reader musst work in standalone mode, ask its db size
  if(standalone)
  {
    getDbSize();

    //! start the standalone cheking 20 seconds after the system started
    checkStandaloneTimer = startTimer(20000);
  }

  return true;
}
    
void CAccessLinkRS485::close()
{
  _isConnected = false;
  input = 0;

  if(msgTimeoutTimer)
  {
    killTimer(msgTimeoutTimer);
    msgTimeoutTimer = 0;
  }

  if(openModeTimeoutTimer)
  {
    killTimer(openModeTimeoutTimer);
    openModeTimeoutTimer = 0;
  }

  if(checkStandaloneTimer)
  {
    killTimer(checkStandaloneTimer);
    checkStandaloneTimer = 0;
  }

  QMapIterator<QString, int> i(passBackTimer);
  while (i.hasNext()) 
  {
      i.next();
      killTimer(i.value());
  }

  passBackTimer.clear();

  for(int i=0; i<pendingMessage.size(); i++)
  {
    QByteArray *ba = pendingMessage.at(i);
    delete ba;
  }

  pendingMessage.clear();


  status = FREE;

  emit deviceConnection(id, false);
}

bool CAccessLinkRS485::isOpened()
{
  return _isConnected;
}

void CAccessLinkRS485::appendMessage(uchar *msg, int len)
{
  if(status == FREE)
  {
    if(isOpened())
    {
      status = BUSY;
      msgTimeoutTimer = startTimer(200);
      logComm(msg, false, len);
      currentMessage.clear();
      
      for(int i=0; i<len; i++)      
        currentMessage.append(msg[i]);

      emit sendMessage(msg, len, true);
    }
  }
  else
  {
    if(isOpened())
    {
      QByteArray *ba = new QByteArray((const char*)msg, len);
      pendingMessage.append(ba);
    }
  }
}

void CAccessLinkRS485::timerEvent(QTimerEvent *e)
{
  if(msgTimeoutTimer == e->timerId())
  {
      //! one message was maybe not well sended
      QString cmd;
      cmd = cmd.sprintf("%02X",currentMessage.at(2));
      QString xml = CXmlFactory::deviceEvent(QString::number(id),"1017", "Do not receive the response from the reader (" + cmd + ")");
        
      emit deviceEvent(xml);
  
      killTimer(msgTimeoutTimer);
      msgTimeoutTimer = 0;

      status = FREE;
      //! send next message
      if(pendingMessage.size() > 0)
      {
        QByteArray *baNext = pendingMessage.takeFirst();
        if(baNext)
        {
          appendMessage((uchar*)baNext->constData(), baNext->size());
          delete baNext;
        }
      }

      return;
  } 

  if(openModeTimeoutTimer == e->timerId())
  {
    //! we can reaccpet new tag
    killTimer(openModeTimeoutTimer);
    openModeTimeoutTimer = 0;
    return;
  }

  if(checkStandaloneTimer == e->timerId())
  {
    killTimer(checkStandaloneTimer);
    checkStandaloneTimer = 0;
    checkStandalone();
    checkStandaloneTimer = startTimer(10000);
  }

  //! check the antipassback timer
  QMapIterator<QString, int> i(passBackTimer);
  while (i.hasNext()) 
  {
      i.next();

      if(e->timerId() == i.value())
      {
        killTimer(i.value());
        passBackTimer.remove(i.key());
        return;
      }
  }  
}

void CAccessLinkRS485::dispatchMessage(QByteArray ba)
{
  //! is this message concerne me?
  if(ba.at(0) != address) 
    return;

  logComm((uchar*)ba.data(), true, ba.size());

  switch((unsigned char)ba.at(2))
  {
    case 0x01: // software version
      {
        QString osVersion, appVersion;
        osVersion = QString::number((uchar)ba.at(4),16) + 
                    QString::number(((uchar)ba.at(5) & 0xF0)>>4,16) + "." + 
                    QString::number(((uchar)ba.at(5) & 0x0F),16);

        appVersion = QString::number((uchar)ba.at(6),16) + 
                    QString::number(((uchar)ba.at(7) & 0xF0)>>4,16) + "." +
                    QString::number(((uchar)ba.at(7) & 0x0F),16);

        firmwareVersion = osVersion + "/" + appVersion;

        checkReaderStatus((uchar)ba.at(3));
      }
      break;
    case 0x02: // serial number 
      {
        unsigned long serialNumber = ((uchar)ba.at(4)<<24);
        serialNumber |= (uchar)ba.at(5)<<16;
        serialNumber |= (uchar)ba.at(6)<<8;
        serialNumber |= (uchar)ba.at(7);
        this->serialNumber = QString::number(serialNumber);

        checkReaderStatus((uchar)ba.at(3));

      }
      break;
    case 0x11:  //input status read
      checkReaderStatus((uchar)ba.at(3));
      break;
    case 0x12:  //output status read
      {
        if(output != (uchar)ba.at(3))
        {
          uchar mask = 1;
      
          for(int i = 0; i<8; i++)
          {
            uchar b_new = (uchar)ba.at(3) & mask;
            uchar b_old = input & mask;
      
            if(b_new != b_old)
            {
              switch(mask)
              {
                case 1: //Output 1
                  break;
                case 2: //Output 2
                  break;
                case 4: //Output 3
                  break;
                case 8: //Output 4
                  break;
                case 16: //modulation
                  if(b_new == 1)
                  {

                    QString xml = CXmlFactory::deviceEvent(QString::number(id),"1015", "The modulation is off");
                    
                    emit deviceEvent(xml); 
                  }
                  break;
                case 32: //not used
                  break;
                case 64: //antenna power
                  if(b_new == 1)
                  {
                    QString xml = CXmlFactory::deviceEvent(QString::number(id),"1015", "The antenna power is off");
                    
                    emit deviceEvent(xml); 
                  }
                  break;
                case 128://not used
                  break;
              }
            }
      
            mask <<= 1;
          }
      
          output = (uchar)ba.at(3);
        }
      }
      break;
    case 0x18:  //output status write (in memory )
      checkReaderStatus((uchar)ba.at(3));
      break;
    case 0x19:  //output status write (not in memory )
      checkReaderStatus((uchar)ba.at(3));
      break;
    case 0x1A:  //output blink frequency
      checkReaderStatus((uchar)ba.at(3));
      break;
    case 0x1B:  //output control write
      checkReaderStatus((uchar)ba.at(3));
      break;
    case 0x80: // internal db clear
      switch((uchar)ba.at(3))
      {
        case 0x00: //! cmd not ok
          {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1017", "The database cannot be cleared (80)");
            
            emit deviceEvent(xml);
          }
          break;
        case 0x01: //! cmd ok
          //! read the db size, the result should be 0
          getDbSize();
          break;
        case 0x02: //! cmd wait
          break;
      }
      break;
    case 0x81: // add a key in the internal db
      switch((uchar)ba.at(3))
      {
        case 0x00: //! cmd not ok
          {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1012", "Cannot insert a new key in the reader database");
            emit deviceEvent(xml);
          }
          break;
        case 0x01: //! cmd ok
          //! read the db size
          getDbSize();
          break;
        case 0x02: //! cmd wait
          break;
      }
      break;
    case 0x82: // remove a key form the internal db
      switch((uchar)ba.at(3))
      {
        case 0x00: //! cmd not ok
          {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1013", "Cannot remove a key from the reader database");
            
            emit deviceEvent(xml);
          }
          break;
        case 0x01: //! cmd ok
          //! read the db size
          getDbSize();
          break;
        case 0x02: //! cmd wait
          break;
      }
      break;
    case 0x84: // PIN code programation
      switch((uchar)ba.at(3))
      {
        case 0x00: //! cmd not ok
          {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1017", "The pin code cannot be programmed (84)");
            
            emit deviceEvent(xml);
          }
          break;
        case 0x01: //! cmd ok
          break;
        case 0x02: //! cmd wait
          break;
      }
      break;    case 0x8E: // internal db size
      switch((uchar)ba.at(3))
      {
        case 0x00: //! cmd not ok
          {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1017", "The database cannot be read (8E)");
            emit deviceEvent(xml);
          }
          break;
        case 0x01: //! cmd ok
          dbSize = 0;
          dbSize = ((uchar)ba.at(4)<< 8);
          dbSize |= (uchar)ba.at(5);

          //! if the memory is equal or greater than 90%, send an alarm
          if( dbSize >=  (int)(90 * memory / 100) )
          {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1011", "The memory database of the reader is almost full");
            
            emit deviceEvent(xml);
          }

          if( dbSize ==  memory )
          {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1010", "The memory database of the reader is full");
            emit deviceEvent(xml);
          }

          break;
        case 0x02: //! cmd wait
          break;
      }
      break;
    case 0x90:
      {
        QString sn;

        if((uchar)ba.at(1) == 0x0B)
        {
          sn = sn.sprintf("%02X%02X%02X%02X%02X%02X%02X%02X",
                          (uchar)ba.at(3),(uchar)ba.at(4),(uchar)ba.at(5),
                          (uchar)ba.at(6),(uchar)ba.at(7),(uchar)ba.at(8),
                          (uchar)ba.at(9),(uchar)ba.at(10));
        }

        handleSn(sn);
      }
      break;
  }

  //! receive the reader response, send the next message if has one
  status = FREE;

  if(pendingMessage.size() > 0)
  {
    QByteArray *baNext = pendingMessage.takeFirst();
    if(baNext)
    {
      appendMessage((uchar*)baNext->constData(), baNext->size());
      delete baNext;
    }
  }
}

void CAccessLinkRS485::getFirmware()
{
  uchar cmd[4];

  cmd[0] = address;
  cmd[1] = 0x03;
  cmd[2] = 0x01;
  cmd[3] = 0x02;

  appendMessage(cmd, 4);
}

void CAccessLinkRS485::getSerialNumber()
{
  uchar cmd[4];

  cmd[0] = address;
  cmd[1] = 0x03;
  cmd[2] = 0x02;
  cmd[3] = 0x01;

  appendMessage(cmd, 4);
}

void CAccessLinkRS485::getDbSize()
{
  uchar cmd[4];

  cmd[0] = address;
  cmd[1] = 0x03;
  cmd[2] = 0x8E;
  cmd[3] = 0x8D;

  appendMessage(cmd, 4);
}

void CAccessLinkRS485::setAntenna(bool flag)
{
  uchar cmd[5];

  // true => set antenna on else off
  if(flag)
    output &= 0xBF;   //! 1011 1111
  else
    output |= 0x40;   //! 0100 0000

  cmd[0] = (char)address;
  cmd[1] = 0x04;
  cmd[2] = 0x19;
  cmd[3] = output;
  cmd[4] = cmd[1] ^ cmd[2] ^ cmd[3];

  appendMessage(cmd, 5);

}

void CAccessLinkRS485::resetOutput()
{
  uchar cmd[5];

  output &= 0xF0;   //! 1111 0000

  cmd[0] = (char)address;
  cmd[1] = 0x04;
  cmd[2] = 0x19;
  cmd[3] = output;
  cmd[4] = cmd[1] ^ cmd[2] ^ cmd[3];

  appendMessage(cmd, 5);
}

void CAccessLinkRS485::getRdStatus()
{
  uchar cmd[4];

  cmd[0] = address;
  cmd[1] = 0x03;
  cmd[2] = 0x11;
  cmd[3] = 0x12;

  appendMessage(cmd, 4);
}

void CAccessLinkRS485::getOutputStatus()
{
  uchar cmd[4];

  cmd[0] = address;
  cmd[1] = 0x03;
  cmd[2] = 0x12;
  cmd[3] = 0x11;

  appendMessage(cmd, 4);
}



Q_EXPORT_PLUGIN2(accesslinkrs485, CAccessLinkRS485);




/*!
    \fn CAccessLinkRS485::checkReaderStatus(uchar status)
 */
void CAccessLinkRS485::checkReaderStatus(uchar status)
{
  if(input != status)
  {
    uchar mask = 1;


    for(int i = 0; i<INPUT_NUMBER; i++)
    {
      uchar b_new = status & mask;
      uchar b_old = input & mask;

      if(b_new != b_old)
      {

        if(TIMEOUT_IN == doorLockMode)
        {

            if(openModeInput == mask)
            {
              //! we can reaccpet new tag
              if(openModeTimeoutTimer)
              {
                killTimer(openModeTimeoutTimer);
                openModeTimeoutTimer = 0;
              }
            }
        }

        emit deviceInputChange(id, mask, (bool)b_new);
      }

      mask <<= 1;
    }

    //! if the door lock mode is waiting on the 2 input activation we need to check the to input
    if(TIMEOUT_IN == doorLockMode)
    {
        if(openModeInput == 3)
        {
          if((status & 0x03) == 0)
          {
            //! we can reaccpet new tag
            if(openModeTimeoutTimer)
            {

              killTimer(openModeTimeoutTimer); 
              openModeTimeoutTimer = 0;
            }
          }
        }
    }

    //!Tag control
    uchar tc_new = status & 0x40;
    uchar tc_old = input & 0x40;

    if(tc_new != tc_old)
    {
        if(tc_new > 0)
        {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1015", "The tag control is off");
            
            emit deviceEvent(xml); 
        }
        else
        {
            QString xml = CXmlFactory::deviceEvent(QString::number(id),"1014", "The tag control is on");
            
            emit deviceEvent(xml); 
        }
    }

    input = status;
  }
}


/*!
    \fn CAccessLinkRS485::handleSn(QString sn)
 */
void CAccessLinkRS485::handleSn(QString sn)
{

  switch(doorLockMode)
  {
    //! we only track the sn like for a timestamp reader
    case NONE:
    case NO_TIMEOUT:
      break;

    //! we wait some seconds before to accept new tag
    case TIMEOUT:
    case TIMEOUT_IN:
      if(openModeTimeoutTimer)
        return;
      break;
  }

  //! the sn is in the antipassback, do not accept it
  if(passBackTimer[sn])
  {
    return;
  }

  if(antipassback > 0)
  {
    //! put the sn in the antipassback. The antipassback is in second in the database
    passBackTimer[sn] = startTimer(antipassback*1000);
  }
  else //! protect for multiple presentation
    passBackTimer[sn] = startTimer(500);


  switch(doorLockMode)
  {
    //! we only track the sn like for a timestamp reader
    case NONE:
    case NO_TIMEOUT:
      break;

    case TIMEOUT:
    case TIMEOUT_IN:
      if(openModeTimeout>0)
        openModeTimeoutTimer = startTimer(openModeTimeout*1000);
      break;
  }

  QString xml = CXmlFactory::keyDetection(QString::number(id),getAccessPluginName(),sn);

  emit deviceEvent(xml);

}

void CAccessLinkRS485::s_openDoorLock(QObject *p, QMap<QString, QVariant>params)
{
  CAccessLinkRS485 *pThis = qobject_cast<CAccessLinkRS485 *>(p);

  //! check by which access plugin the device musst be controlled
  if(pThis->getAccessPluginName() != "")
  {
    if(params.contains("PluginName"))
    {
      if(params["PluginName"].toString() != pThis->getAccessPluginName() )
        return;
    }
    else
      return;
  }

	
  //! do we need to open or close the door for free acces time 	
  if(params.contains("freeAccess"))
  {
	//! do nothing if isAccess don't change
    if( pThis->isFreeAccess == params["freeAccess"].toBool() ) return;
  
    pThis->isFreeAccess = params["freeAccess"].toBool();
	

    uchar cmd[5];
    if(pThis->isFreeAccess)
    {
        //!  enable the output
        cmd[0] = pThis->address;
        cmd[1] = 0x05;
        cmd[2] = 0x19;
        cmd[3] = OUTPUT_1;
          
        cmd[4] =  cmd[1] ^ cmd[2] ^ cmd[3];

        pThis->appendMessage(cmd, 5);    
    }
    else
    {
        //! reset the output
        cmd[0] = pThis->address;
        cmd[1] = 0x05;
        cmd[2] = 0x19;
        cmd[3] = 0x00;
          
        cmd[4] =  cmd[1] ^ cmd[2] ^ cmd[3];

        pThis->appendMessage(cmd, 5);    	
    }
    return;
  }  	
	
  //! don't accept open door command during a free access
  if(pThis->isFreeAccess) return;
	
	
  bool isAccess = false;
  if(params.contains("isAccess"))
    isAccess = params["isAccess"].toBool();

 // pThis->setAntenna(false);

  uchar cmd[6];

  uchar o = 0;


  if(pThis->doorLockMode != NONE)
    o |= OUTPUT_1;

  if( pThis->oTime2 > 0)
    o |= OUTPUT_2;

  if( pThis->oTime3 > 0)
    o |= OUTPUT_3;

  //! reset the output
  cmd[0] = pThis->address;
  cmd[1] = 0x05;
  cmd[2] = 0x1B;
  cmd[3] = o;
  cmd[4] = 0;

  cmd[5] =  cmd[1] ^ cmd[2] ^ cmd[3] ^ cmd[4];

  pThis->appendMessage(cmd, 6);

  if(isAccess)
  {
    //! do not open in this mode
    if(pThis->doorLockMode != NONE)
    {
      cmd[0] = pThis->address;
      cmd[1] = 0x05;
      cmd[2] = 0x1B;
      cmd[3] = OUTPUT_1;
      cmd[4] = pThis->oTime1;
      
      cmd[5] =  cmd[1] ^ cmd[2] ^ cmd[3] ^ cmd[4];
  
      pThis->appendMessage(cmd, 6);
    }

    if( pThis->oTime2 > 0)
    {
      cmd[0] = pThis->address;
      cmd[1] = 0x05;
      cmd[2] = 0x1B;
      cmd[3] = OUTPUT_2;
      cmd[4] = pThis->oTime2;
      
      cmd[5] =  cmd[1] ^ cmd[2] ^ cmd[3] ^ cmd[4];
  
      pThis->appendMessage(cmd, 6);
    }
  }
  else
  {
    if( pThis->oTime3 > 0)
    {
      cmd[0] = pThis->address;
      cmd[1] = 0x05;
      cmd[2] = 0x1B;
      cmd[3] = OUTPUT_3;
      cmd[4] = pThis->oTime3;
      
      cmd[5] =  cmd[1] ^ cmd[2] ^ cmd[3] ^ cmd[4];
  
      pThis->appendMessage(cmd, 6);
    }
  }

 // pThis->setAntenna(true);

}

void CAccessLinkRS485::s_setOutput(QObject *, QMap<QString, QVariant>)
{
  //! @todo
}


/*!
    \fn CAccessLinkRS485::logComm(QByteArray ba)
 */
void CAccessLinkRS485::logComm(uchar *ba, bool isReceive, int len)
{
  if(!_isLog)
    return;

  QString date = QDateTime::currentDateTime().toString(Qt::ISODate);

	checkPermision(logPath + "log_" + name + ".html");

  QFile file(logPath + "log_" + name + ".html");
  if (!file.open(QIODevice::Append | QIODevice::Text))
   return;

  QString s = "",s1;
  
  for(int i=0;i<len; i++)
    s += s1.sprintf("%02X ",ba[i]);

  QTextStream out(&file);

  if(isReceive)
    out << "<span class=\"date\">" << date << "</span>" << "<span  style=\"color:blue\" class=\"receive\">" << s << "</span>" << "<br/>\n";
  else
    out << "<span class=\"date\">" << date << "</span>" << "<span style=\"color:green\" class=\"send\">" << s << "</span>" << "<br/>\n";

  file.close();
}

QDomElement CAccessLinkRS485::getDeviceInfo(QDomDocument xml_info )
{
  QDomElement device = xml_info.createElement( "device");
  device.setAttribute("id", QString::number(id));

  QDomElement newElement = xml_info.createElement( "name");
  QDomText text =  xml_info.createTextNode(name);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "serialNumber");
  text =  xml_info.createTextNode(serialNumber);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "isConnected");
  text =  xml_info.createTextNode(QString::number(_isConnected));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "firmwareVersion");
  text =  xml_info.createTextNode(firmwareVersion);
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "address");
  text =  xml_info.createTextNode(QString::number(address));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "dbSize");
  text =  xml_info.createTextNode(QString::number(dbSize));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "input1");
  text =  xml_info.createTextNode(QString::number((bool)(input & 0x01)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "input2");
  text =  xml_info.createTextNode(QString::number((bool)(input & 0x02)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output1");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x01)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output2");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x02)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output3");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x04)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output4");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x08)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "modulation");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x10)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "antenna");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x40)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  return device;

}


void CAccessLinkRS485::checkStandalone()
{
    QSqlQuery query("SELECT * FROM hr_standalone_action_service WHERE rd_id=" + QString::number(id));

    while (query.next()) 
    {
      QSqlQuery del_query("DELETE FROM hr_standalone_action_service WHERE id=" + query.value(0).toString());

      del_query.exec();

      QString type = query.value(1).toString();
      QString sn = query.value(2).toString();
      uchar opcode = 0x00;
      if(type == "add")
      {
        opcode = 0x81;
      }
      else if(type == "sub")
      {
        opcode = 0x82;
      }
      else
        return;
    
      uchar cmd[12];
      long msb = sn.left(8).toLong(0,16);
      long lsb = sn.right(8).toLong(0,16);
    
      cmd[0] = address;
      cmd[1] = 11; //length
      cmd[2] = opcode; 
      cmd[3] = (unsigned char)((msb & 0xFF000000) >> 24);
      cmd[4] = (unsigned char)((msb & 0x00FF0000) >> 16);
      cmd[5] = (unsigned char)((msb & 0x0000FF00) >> 8);
      cmd[6] = (unsigned char)((msb & 0x000000FF) );
      cmd[7] = (unsigned char)((lsb & 0xFF000000) >> 24);
      cmd[8] = (unsigned char)((lsb & 0x00FF0000) >> 16);
      cmd[9] = (unsigned char)((lsb & 0x0000FF00) >> 8);
      cmd[10] = (unsigned char)((lsb & 0x000000FF));
    
      cmd[11] =  cmd[1] ^ cmd[2] ^ cmd[3] ^ cmd[4] ^ cmd[5] ^ cmd[6] ^ 
                cmd[7] ^ cmd[8] ^ cmd[9] ^ cmd[10];
      
      appendMessage(cmd, 12);
    }
}
