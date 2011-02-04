
#include "caccesslinkinterface.h"

#include <QtCore>
#include "cserver.h"
#include <math.h>


CAccessLinkInterface::CAccessLinkInterface(QObject *parent) : QObject(parent)
{
  _isConnected = false;
  udpSocket = NULL;
  status = FREE;
  checkConnectionTimer = 0;
  msgTimeoutTimer = 0;
  memory_free = 0;
  temperature = 0;
  online_reader = 0;
  deviceReady = false;
  input = 0;
  antivandale = false;
  output = 0;

  ecbEncryption = NULL;
  ecbDecryption = NULL;

  addFunction("setOutput", CAccessLinkInterface::setOutput);
}

CAccessLinkInterface::~CAccessLinkInterface()
{
  delete CServer::getInstance();

  if(ecbEncryption)
  {
    delete ecbEncryption;
    ecbEncryption = NULL;
  }
  
  if(ecbDecryption)
  {
    delete ecbDecryption;
    ecbDecryption = NULL;
  }

  if(udpSocket)
  {
    udpSocket->close();
    delete udpSocket;
  }

  for(int i=0; i<pendingMessage.size(); i++)
  {
    QByteArray *ba = pendingMessage.at(i);
    delete ba;
  }

  pendingMessage.clear();

  QMapIterator<QString, CDeviceInterface *> i(deviceRs485);
  while (i.hasNext()) {
      i.next();
      if(i.value())
        if(i.value()->isOpened())
          i.value()->close();
  }

}

CDeviceInterface *CAccessLinkInterface::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
  CDeviceInterface *p = new CAccessLinkInterface ( parent );

  p->setParameter("name",config["name"]);  
  p->setParameter("_isLog",config["isLog"]); 
  p->setParameter("accessPlugin",config["accessPlugin"]);
 
  p->setParameter("id",config["id_device"]);  

  p->setParameter("ip",config["ip"]);  
  p->setParameter("mask",config["mask"]);  
  p->setParameter("gateway",config["gateway"]);  
  p->setParameter("data_port",config["data_port"]);  
  p->setParameter("ipServer1",config["server1"]);  
  p->setParameter("ipServer2",config["server2"]);  
  p->setParameter("ipServer3",config["server3"]);  
  p->setParameter("password",config["password"]);  
  p->setParameter("timeZone",config["time_zone"]);  
  p->setParameter("tempMax",config["temp_max"]);  

  return p;
}


void CAccessLinkInterface::connectChild(CDeviceInterface *device)
{
  deviceRs485[device->getParameter("address").toString()] = device;

  connect(device->getMetaObject(),
          SIGNAL(sendMessage(const unsigned char *, const int ,  const bool)),
          this,
          SLOT(writeMessage(const unsigned char *, const int, const bool)));

  connect(this, 
          SIGNAL(dispatch(QByteArray)), 
          device->getMetaObject(),
          SLOT(dispatchMessage(QByteArray)));
}

void CAccessLinkInterface::deviceAction(QString xml)
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
        qDebug("The function %s is not define in the device %s", funcName.toLatin1().constData(), name.toLatin1().constData());
    }

    actionNode = actionNode.nextSibling(); 
  }

}


QVariant CAccessLinkInterface::getParameter(QString paramName)
{
  if(paramName == "name")
    return name;
  if(paramName == "id")
    return id;
  if(paramName == "_isLog")
    return _isLog;
  if(paramName == "accessPlugin")
    return accessPlugin;
  if("ip" == paramName)
    return ip;
  if("mask" == paramName)
    return mask;
  if("gateway" == paramName)
    return gateway;
  if("data_port" == paramName)
    return data_port;
  if("ipServer1" == paramName)
    return ipServer1;
  if("ipServer2" == paramName)
    return ipServer2;
  if("ipServer3" == paramName)
    return ipServer3;
  if("password" == paramName)
    return password;
  if("timeZone" == paramName)
    return timeZone;
  if("tempMax" == paramName)
    return tempMax;

  return "undefined";
}

void CAccessLinkInterface::setParameter(QString paramName, QVariant value) 
{
  if(paramName == "name")
    name = value.toString();
  if(paramName == "id")
    id = value.toInt();
  if(paramName == "_isLog")
    _isLog = value.toBool();
  if(paramName == "accessPlugin")
    accessPlugin = value.toString();
  if("ip" == paramName)
    ip = value.toString();
  if("mask" == paramName)
    mask = value.toString();
  if("gateway" == paramName)
    gateway = value.toString();
  if("data_port" == paramName)
    data_port = value.toInt();
  if("ipServer1" == paramName)
    ipServer1 = value.toString();
  if("ipServer2" == paramName)
    ipServer2 = value.toString();
  if("ipServer3" == paramName)
    ipServer3 = value.toString();
  if("password" == paramName)
    password = value.toString();
  if("timeZone" == paramName)
    timeZone = value.toInt();
  if("tempMax" == paramName)
    tempMax = value.toInt();
}

bool CAccessLinkInterface::open()
{
  //! connect to the server to receive the hello connection from the interface
  connect(CServer::getInstance(),
          SIGNAL(newConnection(QTcpSocket *)),
          this,
          SLOT(newConnection(QTcpSocket *)));

  bool res = CServer::getInstance()->start();

  if(!res)
  {
    QString xml = CXmlFactory::deviceEvent(QString::number(id),"1016","The AccesLink interface server cannot be started");
  
    emit deviceEvent(xml);
  }

  return res;
}
    
void CAccessLinkInterface::close()
{

  if(udpSocket)
  {
    if(checkConnectionTimer > 0)
    {
      killTimer(checkConnectionTimer);
      checkConnectionTimer = 0;
    }

    if( msgTimeoutTimer > 0)
    {
      killTimer(msgTimeoutTimer);
      msgTimeoutTimer = 0;
    }

    udpSocket->close();
    udpSocket->deleteLater();
  }

  _isConnected = false;
}

bool CAccessLinkInterface::isOpened()
{
  return _isConnected;
}

/*!
    \fn CAccessLinkInterface::newConnection()
 */
void CAccessLinkInterface::newConnection(QTcpSocket *socket)
{
    if(socket->peerAddress().toString() != ip)
    {
      return;
    }

    connect(socket, SIGNAL(readyRead()), this, SLOT(readHelloData()));
    connect(socket, SIGNAL(disconnected ()), this, SLOT(helloDisconnect()));
}


/*!
    \fn CAccessLinkInterface::readHelloData()
 */
void CAccessLinkInterface::readHelloData()
{
    QTcpSocket* socket = qobject_cast<QTcpSocket* > ( sender() );


    if(udpSocket)
    {
      socket->close();

      socket->deleteLater();
      return;
    }

    QString type, ip, serial;

    QByteArray ba = socket->readAll();

    QString helloMsg(ba);

    //! verify if the hello message is right
    QRegExp regexp("(\\D),([A-Z0-9\\. ]+),(\\d),(\\d{1,3}.\\d{1,3}.\\d{1,3}.\\d{1,3}),([A-Z0-9\\:]+)");

    if(regexp.exactMatch (helloMsg) == -1)
    {
      socket->close();

      socket->deleteLater();
      return;
    }

    //! get parameter from the hello message
    type = helloMsg.section(",",0,0);
    firmwareVersion = helloMsg.section(",",1,1);
    priorityServer = helloMsg.section(",",2,2).toInt()-1;
    ip = helloMsg.section(",",3,3);
    serialNumber = helloMsg.section(",",4,4);

    //! recheck if the ip in the hello message is equal to the instance interface
    if(ip != this->ip)
    {
      socket->close();

      socket->deleteLater();
      return;
    }

    //! send the unix time to the device. This unix time will be used for the crypto
    cryptoDateTime = QDateTime::currentDateTime ();

    uint time = cryptoDateTime.toTime_t();
    char send_ok[5];
    send_ok[0] = 0;
    send_ok[1] = (char)((time & 0xFF000000)>>24);
    send_ok[2] = (char)((time & 0x00FF0000)>>16);
    send_ok[3] = (char)((time & 0x0000FF00)>>8);
    send_ok[4] = (char)((time & 0x000000FF));

    socket->write(send_ok, 5);
    socket->flush();
}


/*!
    \fn CAccessLinkInterface::helloDisconnect()
 */
void CAccessLinkInterface::helloDisconnect()
{
    QTcpSocket* socket = qobject_cast<QTcpSocket* > ( sender() );

    socket->deleteLater();

    udpSocket = new QUdpSocket(this);

    connect(udpSocket, SIGNAL(readyRead()),
             this, SLOT(readPendingDatagrams()));

    connect(udpSocket, SIGNAL(connected()),
             this, SLOT(connected()));

    connect(udpSocket, SIGNAL(destroyed(QObject *)),
             this, SLOT(udpDestroyed(QObject *)));

    udpSocket->connectToHost(QHostAddress(ip), data_port);

}

/*!
    \fn CAccessLinkInterface::readPendingDatagrams()
 */
void CAccessLinkInterface::readPendingDatagrams()
{
    QHostAddress sender;
    quint16 senderPort;
    QByteArray ba;

    ba.resize(udpSocket->pendingDatagramSize());

    udpSocket->readDatagram(ba.data(), ba.size(), &sender, &senderPort);

    //! Every time we receive somthing on the socket, we reset the timer
    if(checkConnectionTimer>0)
    {
      killTimer(checkConnectionTimer);
      checkConnectionTimer = 0;
      //! five second for this timer
      checkConnectionTimer = startTimer(3000);
    }

    //! Check if it is an acknoledge from the interface
    if(ba.size() == 2)
    {
      status = FREE;

      logComm((uchar*)ba.data(), true, 2);

      if(msgTimeoutTimer>0)
      {
        killTimer(msgTimeoutTimer);
        msgTimeoutTimer = 0;
      }
      //! send next message
      if(pendingMessage.size() > 0)
      {
        QByteArray *baNext = pendingMessage.takeFirst();
        if(baNext)
        {
          writeMessage((const unsigned char*)baNext->constData(), baNext->size());
          delete baNext;
        }
      }
    }
    else
      dispatchMessage(ba);
}


/*!
    \fn CAccessLinkInterface::connected()
 */
void CAccessLinkInterface::connected()
{
    if(!initCryptoKey())
    {
      if(checkConnectionTimer > 0)
      {
        killTimer(checkConnectionTimer);
        checkConnectionTimer = 0;
      }
  
      if( msgTimeoutTimer > 0)
      {
        killTimer(msgTimeoutTimer);
        msgTimeoutTimer = 0;
      }

      udpSocket->close();
      udpSocket->deleteLater();
      return;
    }


    _isConnected = true;

    emit deviceConnection(id, true); 

    //! send the first ack
    sendAck();

    if(checkConnectionTimer > 0)
    {
      killTimer(checkConnectionTimer);
      checkConnectionTimer = 0;
    }

    //! start the connection check
    checkConnectionTimer = startTimer(3000);
}


bool CAccessLinkInterface::decrypt(const unsigned char *encrypt_msg, 
                                   const int encrypt_len,
                                   unsigned char *clear_msg, 
                                   int *clear_len)
{
  int padding = 0;
  int blockNbre = encrypt_len / 16; //! How many 16byte blocks do we have?
  padding = 16 - (encrypt_len % 16); //! How many padding bytes do we have to add?

  //! if the paddin is less that 16, the message is wrong
  if(padding < 16)
  {
    return false;
  }


  int index = 0;
  //! uncrypt each 16 bytes blocks
  for(int i = 0; i<blockNbre; i++)
  {
    ecbDecryption->ProcessData( (byte*)clear_msg+index, (const byte*)encrypt_msg+index, 16);

    index += 16;
  }
  *clear_len = blockNbre*16;
  return true;
}

bool CAccessLinkInterface::encrypt(const unsigned char *clear_msg, 
                                   const int clear_len,
                                   unsigned char *encrypt_msg, 
                                   int *encrypt_len)
{
  int padding = 0;
  int blockNbre = clear_len / 16; //! How many 16byte blocks do we have?
  padding = 16 - (clear_len % 16); //! How many padding bytes do we have to add?

  //! if the paddin is less that 16, the message is wrong
  if(padding < 16 )
  {
    blockNbre++;
  }

  //! buffer where the encrypte message will be copy
  char *tmp_src = new char[clear_len+padding];

  if(tmp_src)
  {
    //! clear the encrypt buffer
    memset(tmp_src, 0, clear_len+padding);

    //! prepare the encrypt message buffer with the clear message
    memcpy(tmp_src, clear_msg,clear_len);

    int index = 0;
    //! encrypt 16 bytes per 16 bytes
    for(int i = 0; i<blockNbre; i++)
    {

      ecbEncryption->ProcessData( (byte*)encrypt_msg+index, (const byte*)tmp_src+index, 16);
      
      index += 16;
    }
    delete [] tmp_src;
    *encrypt_len = blockNbre*16;
    return true;
  }
  else
  {
    return false;
  }
}



bool CAccessLinkInterface::initCryptoKey()
{
  QHostAddress hostAddress;

  union u_ipRemote
  {
    long l_ip;
    unsigned char b_ip[4];
  };

  u_ipRemote ipRemote;

  ipRemote.b_ip[3] = ip.section(".",0,0).toInt();
  ipRemote.b_ip[2] = ip.section(".",1,1).toInt();
  ipRemote.b_ip[1] = ip.section(".",2,2).toInt();
  ipRemote.b_ip[0] = ip.section(".",3,3).toInt();

  long IpAdrSvr,IpAdr;

  if(udpSocket)
  {
    hostAddress = udpSocket->localAddress();  //! get the ip address where horuxd is running (the server in our case)

    IpAdrSvr = hostAddress.toIPv4Address();
    IpAdr = ipRemote.l_ip;

    unsigned char aesdata[16];
    memset(aesdata,0,16);
    unsigned char aesdata2[16];
    memset(aesdata2,0,16);
    unsigned char aeskey[16];
    memset(aeskey,0,16);

    for (int i=0; i<16; i++)
      aeskey[i]=i*13;

    aesdata[0]= (priorityServer+1) * 83;
    aesdata[1]= (unsigned char)(IpAdrSvr >> (22+priorityServer));
    aesdata[2]= (priorityServer+1) * 27;
    aesdata[3]= (unsigned char)(IpAdr >> (6+priorityServer));
    aesdata[4]= (unsigned char)(IpAdrSvr >> (14+priorityServer));
    aesdata[5]= (unsigned char)((cryptoDateTime.toTime_t() & 0xFF000000)>>24); //GMT 1
    aesdata[6]= (unsigned char)(IpAdr >> (13+priorityServer));
    aesdata[7]= (unsigned char)((cryptoDateTime.toTime_t() & 0x00FF0000)>>16);// GMT 2
    aesdata[8]= (unsigned char)(IpAdr >> (0+priorityServer));
    aesdata[9]= (unsigned char)(IpAdrSvr >> (7+priorityServer));
    aesdata[10]= (priorityServer+1) * 39;
    aesdata[11]= (unsigned char)(IpAdr >> (2+priorityServer));
    aesdata[12]= (unsigned char)((cryptoDateTime.toTime_t() & 0x0000FF00)>>8); //GMT 3
    aesdata[13]= (unsigned char)(IpAdrSvr >> (1+priorityServer));
    aesdata[14]= (unsigned char)((cryptoDateTime.toTime_t() & 0x000000FF));// GMT 4
    aesdata[15]= (priorityServer+1) * 71;

    ECB_Mode<AES >::Encryption ini(aeskey, AES::DEFAULT_KEYLENGTH);
    ini.ProcessData((byte*)aesdata2, (const byte*)aesdata, 16);

    ecbEncryption = new ECB_Mode<AES >::Encryption(aesdata2, AES::DEFAULT_KEYLENGTH);

    ecbDecryption = new ECB_Mode<AES >::Decryption(aesdata2, AES::DEFAULT_KEYLENGTH);

    return true;
  }
  else
  {
    return false;
  }
}

void CAccessLinkInterface::sendAck()
{

  unsigned char msg_clear[6];
  unsigned char msg_encrypted[17];

  QDateTime dateTime = QDateTime::currentDateTime ();
  uint time = dateTime.toTime_t();

  msg_clear[0] = 0;
  msg_clear[1] = 0x03;

  //! Unix time used to compute the crypto key
  msg_clear[2] = (char)((time & 0xFF000000)>>24);
  msg_clear[3] = (char)((time & 0x00FF0000)>>16);
  msg_clear[4] = (char)((time & 0x0000FF00)>>8);
  msg_clear[5] = (char)((time & 0x000000FF));

  msg_encrypted[0] = 0x06;

  int len = 0;

  logComm(msg_clear, false, 6);

  if( encrypt(msg_clear,6,msg_encrypted+1,&len) )
  {
    writeMessage((const unsigned char *)msg_encrypted, len+1);
  }
}

void CAccessLinkInterface::dispatchMessage(QByteArray ba)
{
  unsigned char clear_msg[1023];
  int nlen = 0;

  for(int i=0; i<ba.size(); i++)
  {
    long brut_len = ba.at(i);

    //! how many AES packet (16 bytes) the message contains
    unsigned int npacket = (int)(brut_len / 16) + ( brut_len % 16 > 0 ? 1 : 0 );

    if(decrypt((const unsigned char*)ba.constData()+i+1,npacket * 16,clear_msg,&nlen))
    {

      if(clear_msg[1] == 0x01)
      {
        QByteArray ba_clear((const char*)clear_msg+2, brut_len-2);
        emit dispatch(ba_clear);
        
      }
      else
      {
        QByteArray ba_clear((const char*)clear_msg+1,brut_len-1);
        logComm((uchar*)ba_clear.data(), true, ba_clear.size());
        dispatch_interface(ba_clear);
      }
    }

    i += ( npacket * 16 ) + 1;
    i--;
  }
}

void CAccessLinkInterface::dispatch_interface(QByteArray ba)
{
  switch(ba.at(0))
  {
  case 0x03:
    readInterfaceStatus(ba);
    sendAck();
    
    //! to have an interface ready, we have to chek its EEPROM
    if(!deviceReady)
      sendCheckEEPROM();
    break;
  case 0x04:  //!set eeprom


    //! We receive the eeprom write response
    //! to active the ne paramater in the interface, we have to close the connexion with him
    if(checkConnectionTimer > 0)
    {
      killTimer(checkConnectionTimer);
      checkConnectionTimer = 0;
    }

    if( msgTimeoutTimer > 0)
    {
      killTimer(msgTimeoutTimer);
      msgTimeoutTimer = 0;
    }

    udpSocket->close();
    udpSocket->deleteLater();
    break;
  case 0x14:  //!get eeprom
    checkEEPROM(ba);
    break;
  case 0x07:  //!set output
    //! currently not used
    break;
  case 0x08:  //!get input
    {
      if(input != (uchar)ba.at(1))
      {
        uchar mask = 1;

        for(int i = 0; i<8; i++)
        {
          uchar b_new = (uchar)ba.at(1) & mask;
          uchar b_old = input & mask;

          if(b_new != b_old)
          {
            emit deviceInputChange(id, mask, (bool)b_new);
          }

          mask <<= 1;
        }

        input = (uchar)ba.at(1);
      }
    }
    break;
  case 0x06:	//!antivandale
    {
      if(antivandale !=  (bool)ba.at(1) )
      {
        QString xml;
      
        if(ba.at(1))
        {
            xml = CXmlFactory::deviceEvent(QString::number(id), "1001", "The antivandal is actif");
        }
        else
        {
            xml = CXmlFactory::deviceEvent(QString::number(id), "1002", "The antivandal is cleared");
        }
        
        emit deviceEvent(xml);
        
        antivandale = (bool)ba.at(1);
      }
    }
    break;
  default:
    break;
  }
}

void CAccessLinkInterface::writeMessage(const unsigned char *msg, const int len,  const bool subDeviceMessage)
{


  if(subDeviceMessage)
  {
    int npacket = len / 16;

    if(len % 16 > 0) npacket++;

    unsigned char msg_encrypted[(npacket*16)+1];
    unsigned char msg_clear[len+2];
  
    msg_clear[0] = 0;
    msg_clear[1] = 0x01;
  
    for(int i=0; i<len; i++)
    {
      msg_clear[i+2] = msg[i];
    }
  
    msg_encrypted[0] = len + 2;
  
    int nlen = 0;

    if( encrypt(msg_clear,len+2,msg_encrypted+1,&nlen) )
    {
      writeMessage((const unsigned char *)msg_encrypted, nlen+1);
      return;
    }
  }


  if(status == FREE && udpSocket)
  {
    if(udpSocket->isOpen())
    {
      if(udpSocket->write((const char *)msg, len) != -1)
      {
        status = BUSY;
        msgTimeoutTimer = startTimer(200);
      }
    }
  }
  else
  {
    if(udpSocket->isOpen())
    {
      QByteArray *ba = new QByteArray((const char*)msg, len);
      pendingMessage.append(ba);
    }
  }
}

void CAccessLinkInterface::timerEvent(QTimerEvent *e)
{
  if(checkConnectionTimer == e->timerId())
  {
      killTimer(checkConnectionTimer);
      checkConnectionTimer = 0;

      //! close and destroy the udp connection
      udpSocket->close();
      udpSocket->deleteLater();

      return;
  }

  if(msgTimeoutTimer == e->timerId())
  {
      //! one message was maybe not well sended
      QString xml = CXmlFactory::deviceEvent(QString::number(id), "1017", "Do not receive a response from the interface ");
        
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
          writeMessage((const unsigned char*)baNext->constData(), baNext->size());
          delete baNext;
        }
      }

      return;
   }
}

void CAccessLinkInterface::udpDestroyed(QObject *)
{
  udpSocket = NULL;
  status = FREE;
  memory_free = 0;
  temperature = 0;
  online_reader = 0;
  deviceReady = false;
  input = 0;
  antivandale = false;
  output = 0;

  for(int i=0; i<pendingMessage.size(); i++)
  {
    QByteArray *ba = pendingMessage.at(i);
    delete ba;
  }

  pendingMessage.clear();

  QMapIterator<QString, CDeviceInterface *> i(deviceRs485);
  while (i.hasNext()) {
      i.next();
      if(i.value())
        if(i.value()->isOpened())
          i.value()->close();
  }

  _isConnected = false;

  emit deviceConnection(id, false);

  if(ecbEncryption)
  {
    delete ecbEncryption;
    ecbEncryption = NULL;
  }
  
  if(ecbDecryption)
  {
    delete ecbDecryption;
    ecbDecryption = NULL;
  }

}

void CAccessLinkInterface::readInterfaceStatus(QByteArray ba)
{
  float temp = 0;     //! device temperature
  int temp_ = 0;
  unsigned long online = 0;    //! online reader
  int memfree  = 0;   //! Device memory free

  //! the message musst have 9 byte, not more, not less
  if(ba.size() != 9)
    return ;

  //! Interface memory check
  memfree = (unsigned char)ba.at(1)<<8;
  memfree |= (unsigned char)ba.at(2);

  if( memfree != memory_free )
  {
    memory_free =  memfree;
    memoryChange(memory_free);
  }
  
  //! Interface temparature check
  temp_ = (unsigned char)ba.at(3)<<8;
  temp_ |= (unsigned char)ba.at(4);
  temp = (float)temp_;

  float Rt = (float)(1000 * temp);

  Rt = (float) Rt / (1024 - temp);
  temp = (1/((1/(273.15 + 25)) - (log(1000/Rt)/4500)))-273;
  if( temp != temperature )
  {
    temperature =  (unsigned long)temp;
    temperature =  (unsigned long)temp;
    if(tempMax < temperature)
    {
      tempTooHot(temperature);
    }
  }

  //! reader online check

  online = (unsigned char)ba.at(5)<<24;
  online |= (unsigned char)ba.at(6)<<16;
  online |= (unsigned char)ba.at(7)<<8;
  online |= (unsigned char)ba.at(8);

  if( online != online_reader )
  {
    online_reader =  online;
     //! address 0 not possible, start the mask at 1
    long mask = 1;

    //! An access link interface can only manage 32 device
    for( int i=0; i< 32; i++)
    {
      //! is this reader online
      if(mask & online)
      {
        if(deviceRs485.contains(QString::number(i+1)))
        {
          if(!deviceRs485[QString::number(i+1)]->isOpened())
          {
            deviceRs485[QString::number(i+1)]->open();
          }
        }
      }
      else
      {
        if(deviceRs485.contains(QString::number(i+1)))
        {
          if(deviceRs485[QString::number(i+1)]->isOpened())
          {
            deviceRs485[QString::number(i+1)]->close();
          }
        }
      }

      mask = mask << 1;
    } 
  }
}

Q_EXPORT_PLUGIN2(accesslinkinterface, CAccessLinkInterface);


/*!
    \fn CAccessLinkInterface::checkEEPROM()
 */
void CAccessLinkInterface::sendCheckEEPROM()
{
  int lenpswd = password.length();

  uchar msg[12]; 
  uchar msgEncrypted[17]; 

  msgEncrypted[0] = 12;

  memset(msg,0,12);

  msg[0] = 0;
  msg[1] = 0x14;
  msg[2] = V2_EEP_MACADR;
  msg[3] = V2_EEP_SIZE;

  if(lenpswd > 8)
  {
    qWarning("The password for the interface %s is too long", name.toLatin1().constData());
    return;
  }

  for(int i=0; i<lenpswd; i++)
  {
    msg[i+4] = password.at(i).toLatin1();
  }

  logComm(msg, false, 4);

  if(udpSocket)
  {
     int nlen = 0;
    if(encrypt( msg,12,msgEncrypted+1, &nlen ))
    {
      writeMessage((const uchar *)msgEncrypted,17);
    }
  }
}


/*!
    \fn CAccessLinkInterface::checkEEPROM()
 */
void CAccessLinkInterface::checkEEPROM(QByteArray data)
{
    
  data = data.remove(0,1);

  if(data.size() == V2_EEP_SIZE)
  {
    QString mac_address = "",
    concentrator_ip = "",
    server1_ip = "",
    server2_ip = "",
    server3_ip = "",
    mask_ip = "",
    gway_ip = "";
    int port_hello = 0;
    int port_data = 0;
    unsigned long reader_list = 0;

    mac_address = QString::number((uchar)data[0],16) + ":" +
                  QString::number((uchar)data[1],16) + ":" +
                  QString::number((uchar)data[2],16) + ":" +
                  QString::number((uchar)data[3],16) + ":" +
                  QString::number((uchar)data[4],16) + ":" +
                  QString::number((uchar)data[5],16);
    concentrator_ip =
      QString::number((uchar)data[6]) + "." +
      QString::number((uchar)data[7]) + "." +
      QString::number((uchar)data[8])+ "."+
      QString::number((uchar)data[9]);

    server1_ip = QString::number((uchar)data[10]) + "." +
                 QString::number((uchar)data[11]) + "." +
                 QString::number((uchar)data[12]) + "." +
                 QString::number((uchar)data[13]);
    server2_ip = QString::number((uchar)data[14]) + "." +
                 QString::number((uchar)data[15]) + "." +
                 QString::number((uchar)data[16]) + "." +
                 QString::number((uchar)data[17]);
    server3_ip = QString::number((uchar)data[18]) + "." +
                 QString::number((uchar)data[19]) + "." +
                 QString::number((uchar)data[20]) + "." +
                 QString::number((uchar)data[21]);
    mask_ip = QString::number((uchar)data[22]) + "." +
              QString::number((uchar)(uchar)data[23]) + "." +
              QString::number((uchar)data[24]) + "." +
              QString::number((uchar)data[25]);
    port_hello = (uchar)data[26]<<8;
    port_hello |= (uchar)data[27];

    port_data = (uchar)data[28]<<8;
    port_data |= (uchar)data[29];
    gway_ip = QString::number((uchar)data[30]) + "." +
              QString::number((uchar)data[31]) + "." +
              QString::number((uchar)data[32]) + "." +
              QString::number((uchar)data[33]);

    reader_list = (uchar)data[39] << 24;
    reader_list |= (uchar)data[40] << 16;
    reader_list |= (uchar)data[41] << 8;
    reader_list |= (uchar)data[42];


    bool isOK = true;
    
    if(ip != concentrator_ip)
    {
      qDebug("The ip musst be updated in the EEPROM for the interface %s", name.toLatin1().constData());
      isOK = false;
    }

    if(ipServer1 != server1_ip)
    {
      qDebug("The ipServer1 musst be updated in the EEPROM for the interface %s", name.toLatin1().constData());
      isOK = false;
    }

    if(ipServer2 != server2_ip)
    {
      qDebug("The ipServer2 musst be updated in the EEPROM for the interface %s", name.toLatin1().constData());
      isOK = false;
    }

    if(ipServer3 != server3_ip)
    {
      qDebug("The ipServer3 musst be updated in the EEPROM for the interface %s", name.toLatin1().constData());
      isOK = false;
    }

    if(mask != mask_ip)
    {
      qDebug("The mask musst be updated in the EEPROM for the interface %s", name.toLatin1().constData());
      isOK = false;
    }

    if(gateway != gway_ip)
    {
      qDebug("The gateway musst be updated in the EEPROM for the interface %s", name.toLatin1().constData());
      isOK = false;
    }

    if(data_port != port_data)  
    {
      qDebug("The data_port musst be updated in the EEPROM for the interface %s", name.toLatin1().constData());
      isOK = false;
    }

    unsigned long readerListDB = 0;

    QMapIterator<QString, CDeviceInterface *> i(deviceRs485);

    while (i.hasNext()) 
    {
      i.next();
      if(i.value())
      {
        long mask = long( 1 << (i.value()->getParameter("address").toInt()-1) );
        readerListDB |= mask;
      }
    }

    if( readerListDB != reader_list)
    {
      qDebug("The online reader musst be updated in the EEPROM for the interface %s", name.toLatin1().constData());
      isOK = false;
    }
    
    if(!isOK)
      rewriteEEPROM();
    else
    {
      deviceReady = true;

      //! start the reader communication only when the interface is ready
      QMapIterator<QString, CDeviceInterface *> i(deviceRs485);
      while (i.hasNext()) {
          i.next();
          if(i.value())
            if(!i.value()->isOpened())
                i.value()->open();
      }

    }
  }
  else
    qWarning("The number of bytes return by the interface (%s ) when reading the EEPROM was not right", name.toLatin1().constData());
}


/*!
    \fn CAccessLinkInterface::rewriteEEPROM()
 */
void CAccessLinkInterface::rewriteEEPROM()
{
  uchar msg[49] = {0,
                   0x04,
                   (char)V2_EEP_IPADR, 
                   V2_EEP_SIZE-V2_EEP_MACADR_LEN, 
                   0x0,0x0,0x0,0x0,0x0,0x0,0x0,0x0,
                   0x0,0x0,0x0,0x0,
                   0x0,0x0,0x0,0x0,
                   0x0,0x0,0x0,0x0,
                   0x0,0x0,0x0,0x0,
                   0x0,0x0,0x0,0x0,
                   0x0,0x0,
                   0x0,0x0,
                   0x0,0x0,0x0,0x0,
                   0x0,0x0,0x0,0x0,
                   0x0, 
                   0x0,0x0,0x0,0x0 
                  };

  if(password.length() > 8)
  {
    qWarning("The password for the interface %s is too long", name.toLatin1().constData());
    return;
  }

  for(int i=0; i<password.length(); i++)
  {
    msg[i+4] = password.at(i).toLatin1();
  }

  msg[12] = ip.section( '.', 0, 0 ).toInt();
  msg[13] = ip.section( '.', 1, 1 ).toInt();
  msg[14] = ip.section( '.', 2, 2 ).toInt();
  msg[15] = ip.section( '.', 3, 3 ).toInt();
  msg[16] = ipServer1.section( '.', 0, 0 ).toInt();
  msg[17] = ipServer1.section( '.', 1, 1 ).toInt();
  msg[18] = ipServer1.section( '.', 2, 2 ).toInt();
  msg[19] = ipServer1.section( '.', 3, 3 ).toInt();
  msg[20] = ipServer2.section( '.', 0, 0 ).toInt();
  msg[21] = ipServer2.section( '.', 1, 1 ).toInt();
  msg[22] = ipServer2.section( '.', 2, 2 ).toInt();
  msg[23] = ipServer2.section( '.', 3, 3 ).toInt();
  msg[24] = ipServer3.section( '.', 0, 0 ).toInt();
  msg[25] = ipServer3.section( '.', 1, 1 ).toInt();
  msg[26] = ipServer3.section( '.', 2, 2 ).toInt();
  msg[27] = ipServer3.section( '.', 3, 3 ).toInt();
  msg[28] = mask.section( '.', 0, 0 ).toInt();
  msg[29] = mask.section( '.', 1, 1 ).toInt();
  msg[30] = mask.section( '.', 2, 2 ).toInt();
  msg[31] = mask.section( '.', 3, 3 ).toInt();

  QSettings settings(QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat);
  settings.beginGroup("AccessLinkInterface");
  int hello_port = settings.value("helloPort", 6998).toInt();

  if(!settings.contains("helloPort")) settings.setValue("helloPort", 6998);

  settings.endGroup();

  msg[32] = (char)((hello_port & 0xFF00) >> 8);
  msg[33] = (char)((hello_port & 0x00FF));
  msg[34] = (char)((data_port & 0xFF00) >> 8);
  msg[35] = (char)((data_port & 0x00FF));
  msg[36] = gateway.section( '.', 0, 0 ).toInt();
  msg[37] = gateway.section( '.', 1, 1 ).toInt();
  msg[38] = gateway.section( '.', 2, 2 ).toInt();
  msg[39] = gateway.section( '.', 3, 3 ).toInt();
  msg[44] = (char)timeZone;


  unsigned long readerListDB = 0;

  QMapIterator<QString, CDeviceInterface *> i(deviceRs485);

  while (i.hasNext()) 
  {
    i.next();
    if(i.value())
    {
      long mask = long( 1 << (i.value()->getParameter("address").toInt()-1) );
      readerListDB |= mask;
    }
  }

  msg[45] =  (uchar)(((readerListDB & 0xFF000000) >> 24));
  msg[46] =  (uchar)(((readerListDB & 0x00FF0000) >> 16));
  msg[47] =  (uchar)(((readerListDB & 0x0000FF00) >> 8));
  msg[48] =  (uchar)((readerListDB & 0x000000FF));


  uchar msgEncrypted[65]; 

  msgEncrypted[0] = 49;

  logComm(msg, false, 49);


  if(udpSocket)
  {
    int nlen = 0;
    if(encrypt( msg,49,msgEncrypted+1, &nlen ))
    {
      writeMessage((const unsigned char *)msgEncrypted,65);
    }
  }

}

void CAccessLinkInterface::setOutput(QObject* p, QMap<QString, QVariant> params)
{
  CAccessLinkInterface *pThis = qobject_cast<CAccessLinkInterface *>(p);

  bool status = params["status"].toBool();

  if(status)
    pThis->output = params["output"].toInt() | pThis->output;
  else
   pThis->output = ~params["output"].toInt() & pThis->output;

  unsigned char msg[3] = {0,7,pThis->output};
  unsigned char msgEncrypted[17];

  msgEncrypted[0] = 3;

  pThis->logComm(msg, false, 3);


  if(pThis->udpSocket)
  {
    int nlen = 0;
    if(pThis->encrypt( msg,3,msgEncrypted+1, &nlen ))
    {
      pThis->writeMessage((const uchar *)msgEncrypted,17);
    }
  }
}


/*!
    \fn CAccessLinkInterface::memoryChange(int mem) 
 */
void CAccessLinkInterface::memoryChange(int)
{
    // not handled

  /*QString xml = "<deviceEvent id=\"" + QString::number(id) + "\">";

  xml += "<event>memoryChange</event>";
  xml += "<params><param><name>memory</name><value>"+ QString::number(mem)  + "</value></param></params>";

  xml += "</deviceEvent>";
  
  emit deviceEvent(xml); */
}


/*!
    \fn CAccessLinkInterface::tempTooHot(float temp)
 */
void CAccessLinkInterface::tempTooHot(float temp)
{
  QString xml = CXmlFactory::deviceEvent(QString::number(id), "1009", "The temparature is too hot( " + QString::number(temp, 'f',2).toLatin1() + " C°)");
  
  emit deviceEvent(xml);
}


/*!
    \fn CAccessLinkInterface::logComm(QByteArray ba)
 */
void CAccessLinkInterface::logComm(uchar *ba, bool isReceive, int len)
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
    s += s1.sprintf("%02X ",(uchar)ba[i]);

  QTextStream out(&file);

  if(isReceive)
    out << "<span class=\"date\">" << date << "</span>" << "<span  style=\"color:blue\" class=\"receive\">" << s << "</span>" << "<br/>\n";
  else
    out << "<span class=\"date\">" << date << "</span>" << "<span style=\"color:green\" class=\"send\">" << s << "</span>" << "<br/>\n";

  file.close();


}

QDomElement CAccessLinkInterface::getDeviceInfo(QDomDocument xml_info )
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

  newElement = xml_info.createElement( "antivandale");
  text =  xml_info.createTextNode(QString::number(antivandale));
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

  newElement = xml_info.createElement( "input3");
  text =  xml_info.createTextNode(QString::number((bool)(input & 0x04)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "input4");
  text =  xml_info.createTextNode(QString::number((bool)(input & 0x08)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "input5");
  text =  xml_info.createTextNode(QString::number((bool)(input & 0x10)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "input6");
  text =  xml_info.createTextNode(QString::number((bool)(input & 0x20)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "input7");
  text =  xml_info.createTextNode(QString::number((bool)(input & 0x40)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "input8");
  text =  xml_info.createTextNode(QString::number((bool)(input & 0x80)));
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

  newElement = xml_info.createElement( "output5");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x10)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output6");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x20)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output7");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x40)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "output8");
  text =  xml_info.createTextNode(QString::number((bool)(output & 0x80)));
  newElement.appendChild(text);
  device.appendChild(newElement);

  newElement = xml_info.createElement( "temperature");
  text =  xml_info.createTextNode(QString::number(temperature, 'f', 2));
  newElement.appendChild(text);
  device.appendChild(newElement);

  return device;
}
