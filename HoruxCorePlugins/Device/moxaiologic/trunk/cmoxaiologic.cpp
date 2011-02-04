
#include "cmoxaiologic.h"
#include <QtCore>
#include <QDateTime>

CMoxaIOLogic::CMoxaIOLogic(QObject *parent) : QObject(parent)
{
  deviceParent = NULL;

  _isConnected = false;
  socket = 0;
  password = "";

  outputValue = 0;
  inputValue = 0;

  timerCheckConnection = NULL;
  timerCheckInput = NULL;

  addFunction("accessAccepted", CMoxaIOLogic::s_accessAccepted);
  addFunction("accessRefused", CMoxaIOLogic::s_accessRefused);
  addFunction("keyDetected", CMoxaIOLogic::s_keyDetected);
}

CDeviceInterface *CMoxaIOLogic::createInstance (QMap<QString, QVariant> config, QObject *parent )
{
  CDeviceInterface *p = new CMoxaIOLogic ( parent );

  p->setParameter("name",config["name"]);
  p->setParameter("_isLog",config["isLog"]);
  p->setParameter("accessPlugin",config["accessPlugin"]);
  p->setParameter("id",config["id_device"]);  

  p->setParameter("ip",config["ip"]);
  p->setParameter("port",config["port"]);
  p->setParameter("password",config["password"]);
  p->setParameter("initialOutput",config["initialOutput"]);

  p->setParameter("output0_func",config["output0_func"]);
  p->setParameter("output1_func",config["output1_func"]);
  p->setParameter("output2_func",config["output2_func"]);
  p->setParameter("output3_func",config["output3_func"]);
  p->setParameter("output4_func",config["output4_func"]);
  p->setParameter("output5_func",config["output5_func"]);
  p->setParameter("output6_func",config["output6_func"]);
  p->setParameter("output7_func",config["output7_func"]);

  p->setParameter("output0Time",config["output0Time"]);
  p->setParameter("output1Time",config["output1Time"]);
  p->setParameter("output2Time",config["output2Time"]);
  p->setParameter("output3Time",config["output3Time"]);
  p->setParameter("output4Time",config["output4Time"]);
  p->setParameter("output5Time",config["output5Time"]);
  p->setParameter("output6Time",config["output6Time"]);
  p->setParameter("output7Time",config["output7Time"]);

  return p;
}


void CMoxaIOLogic::deviceAction(QString xml)
{
    int parent_id = 0;
    if(deviceParent)
        parent_id = deviceParent->getParameter("id").toInt();

    QMap<QString, MapParam> func = CXmlFactory::deviceAction(xml, id, parent_id);
    QMapIterator<QString, MapParam> i(func);
    while (i.hasNext())
    {
         i.next();

         if(interfaces[i.key()])
         {
              void (*func)(QObject *, QMap<QString, QVariant>) = interfaces[i.key()];
              func(getMetaObject(), i.value());
         }
    }
}


void CMoxaIOLogic::connectChild(CDeviceInterface *)
{

}

QVariant CMoxaIOLogic::getParameter(QString paramName)
{
  if(paramName == "name")
    return name;
  if(paramName == "id")
    return id;
  if(paramName == "_isLog")
    return _isLog;
  if(paramName == "accessPlugin")
    return accessPlugin;


  if(paramName == "ip")
    return ip;
  if(paramName == "port")
    return port;

  if(paramName == "password")
    return password;

  if(paramName == "initialOutput")
    return initialOutput;

  if(paramName == "output0_func")
    return output0_func;

  if(paramName == "output1_func")
    return output1_func;

  if(paramName == "output2_func")
    return output2_func;

  if(paramName == "output3_func")
    return output3_func;

  if(paramName == "output4_func")
    return output4_func;

  if(paramName == "output5_func")
    return output5_func;

  if(paramName == "output6_func")
    return output6_func;

  if(paramName == "output7_func")
    return output7_func;

  if(paramName == "output0Time")
    return output0Time;
  if(paramName == "output1Time")
    return output1Time;
  if(paramName == "output2Time")
    return output2Time;
  if(paramName == "output3Time")
    return output3Time;
  if(paramName == "output4Time")
    return output4Time;
  if(paramName == "output5Time")
    return output5Time;
  if(paramName == "output6Time")
    return output6Time;
  if(paramName == "output7Time")
    return output7Time;

  return "undefined";
}

void CMoxaIOLogic::setParameter(QString paramName, QVariant value)
{
  if(paramName == "name")
    name = value.toString();
  if(paramName == "id")
    id = value.toInt();
  if(paramName == "_isLog")
    _isLog = value.toBool();
  if(paramName == "accessPlugin")
    accessPlugin = value.toString();

  if(paramName == "ip")
    ip = value.toString();
  if(paramName == "port")
    port = value.toInt();
  if(paramName == "password")
    password = value.toString();
  if(paramName == "initialOutput")
    initialOutput = value.toString();

  if(paramName == "output0_func")
    output0_func = value.toString();
  if(paramName == "output1_func")
    output1_func = value.toString();
  if(paramName == "output2_func")
    output2_func = value.toString();
  if(paramName == "output3_func")
    output3_func = value.toString();
  if(paramName == "output4_func")
    output4_func = value.toString();
  if(paramName == "output5_func")
    output5_func = value.toString();
  if(paramName == "output6_func")
    output6_func = value.toString();
  if(paramName == "output7_func")
    output7_func = value.toString();

  if(paramName == "output0Time")
    output0Time = value.toInt();
  if(paramName == "output1Time")
    output1Time = value.toInt();
  if(paramName == "output2Time")
    output2Time = value.toInt();
  if(paramName == "output3Time")
    output3Time = value.toInt();
  if(paramName == "output4Time")
    output4Time = value.toInt();
  if(paramName == "output5Time")
    output5Time = value.toInt();
  if(paramName == "output6Time")
    output6Time = value.toInt();
  if(paramName == "output7Time")
    output7Time = value.toInt();
}


bool CMoxaIOLogic::open()
{

    if(socket > 0 )
        return true;

#if defined(Q_WS_WIN)
    if(MXEIO_Init() != MXIO_OK) {
    }
#endif

    int ret ;

    if( (ret = MXEIO_E1K_Connect( ip.toLatin1().data(), port, 1000, &socket, password.toLatin1().data())) == MXIO_OK) {

        _isConnected = true;
        emit deviceConnection(id, true);

        if(timerCheckConnection == NULL) {
            timerCheckConnection = new QTimer(this);

            connect(timerCheckConnection, SIGNAL(timeout()), this, SLOT(checkConnection()));
        }

        timerCheckConnection->start(1000);

        if(timerCheckInput == NULL) {
            timerCheckInput = new QTimer(this);

            connect(timerCheckInput, SIGNAL(timeout()), this, SLOT(readInput()));
        }

        timerCheckInput->start(100);


        // get the firmware version
        getFirmware();

        // get the device type - Not possible to obtain the serial number
        getSerialNumber();

        // read the current status of the output
        readOutput();

        // read the current status of the input
        readInput();


        //reset the output
        for(int i=0; i<IO_NUMBER; i++) {
            int value = initialOutput.section(",",i,i).toInt();
            setOutput(i,value, 0);
        }

        qDebug() << "MXIO CONNECTED";


        return true;
    } else {

        qDebug() << "MXIO NOT CONNECTED";
        close();        
        QTimer::singleShot(1000, this, SLOT(reopen()));
        return false;
    }
}



void CMoxaIOLogic::close()
{

    if(!_isConnected)
        return;

qDebug() << "MXIO CLOSED";
  if(MXEIO_Disconnect(socket) == MXIO_OK) {

    _isConnected = false;

    socket = 0;

    if(timerCheckConnection)
        timerCheckConnection->stop();

    if(timerCheckInput)
        timerCheckInput->stop();

    #if defined(Q_WS_WIN)
        MXEIO_Exit();
    #endif

      emit deviceConnection(id, false);
    }

}

bool CMoxaIOLogic::isOpened()
{
  return _isConnected;
}



void CMoxaIOLogic::dispatchMessage(QByteArray )
{
}

/*!
    \fn CMoxaIOLogic::logComm(QByteArray ba)
 */
void CMoxaIOLogic::logComm(uchar *, bool , int )
{
}

QDomElement CMoxaIOLogic::getDeviceInfo(QDomDocument xml_info )
{
  QDomElement device = xml_info.createElement( "device");
  device.setAttribute("id", QString::number(id));

  QDomElement newElement = xml_info.createElement( "name");
  QDomText text =  xml_info.createTextNode(name);
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

  newElement = xml_info.createElement( "serialNumber");
  text =  xml_info.createTextNode(serialNumber);
  newElement.appendChild(text);
  device.appendChild(newElement);

  int mask = 1;

  for(int i=0; i<IO_NUMBER; i++) {

      newElement = xml_info.createElement( "Output" + QString::number(i));
      text =  xml_info.createTextNode( (bool)(outputValue & mask) == true ? "1" : "0" );
      newElement.appendChild(text);
      device.appendChild(newElement);

      mask <<=1;

  }

  mask = 1;
  for(int i=0; i<IO_NUMBER; i++) {

      newElement = xml_info.createElement( "Input" + QString::number(i));
      text =  xml_info.createTextNode( (bool)(inputValue & mask) == true ? "1" : "0" );
      newElement.appendChild(text);
      device.appendChild(newElement);

      mask <<=1;
  }


  return device;

}

void CMoxaIOLogic::getFirmware() {
    unsigned char revision[4];

    if(socket > 0 && MXIO_ReadFirmwareRevision(socket,revision ) == MXIO_OK){
        QString s;
        firmwareVersion = s.sprintf("%02d.%02d.%02d.%02d",revision[0],revision[1],revision[2],revision[3]);
    } else {
        firmwareVersion = "ERROR";
    }


}

void CMoxaIOLogic::getSerialNumber(){

    unsigned short type = 0;

    if(socket > 0 && MXIO_GetModuleType(socket,0,&type ) == MXIO_OK){
        serialNumber = "Type: " + QString::number(type, 16);
    } else {
        serialNumber = "";
    }



}

void CMoxaIOLogic::setOutput(int output, int value, int timer) {

    if(!isOpened())
        return;

    if(E1K_DO_Writes(socket, output, 1, value) == MXIO_OK) {

        // if the timer is bigger than 0 and the value is 1, start a timer to disable the output status after X miliseconds
        if(timer > 0) {
            if(!timerOutputReset.contains(output)) {
                timerOutputReset[output] = new QTimer(this);
                connect(timerOutputReset[output], SIGNAL(timeout()), this, SLOT(resetOutput()));
            }

            timerOutputReset[output]->start(timer);

        } else {
            if(timerOutputReset.contains(output)) {
                timerOutputReset[output]->stop();
            }
        }

        // re read the current output value
        readOutput();
    }
}

void CMoxaIOLogic::resetOutput() {
    QTimer *t = (QTimer *)sender();

    QMap<int, QTimer*>::const_iterator i = timerOutputReset.constBegin();
     while (i != timerOutputReset.constEnd()) {

         if(i.value() == t) {
             t->stop();
             setOutput(i.key(), initialOutput.section(",",i.key(),i.key()).toInt(), 0);
         }

        i++;
     }
}

void CMoxaIOLogic::s_accessAccepted(QObject *p, QMap<QString, QVariant>params) {

    CMoxaIOLogic *pThis = qobject_cast<CMoxaIOLogic *>(p);

    if( pThis->output0_func.contains("accessAccepted") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",0,0).toInt();

        pThis->setOutput(0, ivo == 1 ? 0 : 1, pThis->output0Time);
    }

    if( pThis->output1_func.contains("accessAccepted") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",1,1).toInt();

        pThis->setOutput(1, ivo == 1 ? 0 : 1, pThis->output1Time);
    }

    if( pThis->output2_func.contains("accessAccepted") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",2,2).toInt();

        pThis->setOutput(2, ivo == 1 ? 0 : 1, pThis->output2Time);
    }

    if( pThis->output3_func.contains("accessAccepted") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",3,3).toInt();

        pThis->setOutput(3, ivo == 1 ? 0 : 1, pThis->output3Time);
    }

    if( pThis->output4_func.contains("accessAccepted") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",4,4).toInt();

        pThis->setOutput(4, ivo == 1 ? 0 : 1, pThis->output4Time);
    }

    if( pThis->output5_func.contains("accessAccepted") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",5,5).toInt();

        pThis->setOutput(5, ivo == 1 ? 0 : 1, pThis->output5Time);
    }

    if( pThis->output6_func.contains("accessAccepted") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",6,6).toInt();

        pThis->setOutput(6, ivo == 1 ? 0 : 1, pThis->output6Time);
    }

    if( pThis->output7_func.contains("accessAccepted") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",7,7).toInt();

        pThis->setOutput(7, ivo == 1 ? 0 : 1, pThis->output7Time);
    }

}

void CMoxaIOLogic::s_accessRefused(QObject *p, QMap<QString, QVariant>params) {
    CMoxaIOLogic *pThis = qobject_cast<CMoxaIOLogic *>(p);

    if( pThis->output0_func.contains("accessRefused") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",0,0).toInt();

        pThis->setOutput(0, ivo == 1 ? 0 : 1, pThis->output0Time);
    }

    if( pThis->output1_func.contains("accessRefused") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",1,1).toInt();

        pThis->setOutput(1, ivo == 1 ? 0 : 1, pThis->output1Time);
    }

    if( pThis->output2_func.contains("accessRefused") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",2,2).toInt();

        pThis->setOutput(2, ivo == 1 ? 0 : 1, pThis->output2Time);
    }

    if( pThis->output3_func.contains("accessRefused") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",3,3).toInt();

        pThis->setOutput(3, ivo == 1 ? 0 : 1, pThis->output3Time);
    }

    if( pThis->output4_func.contains("accessRefused") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",4,4).toInt();

        pThis->setOutput(4, ivo == 1 ? 0 : 1, pThis->output4Time);
    }

    if( pThis->output5_func.contains("accessRefused") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",5,5).toInt();

        pThis->setOutput(5, ivo == 1 ? 0 : 1, pThis->output5Time);
    }

    if( pThis->output6_func.contains("accessRefused") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",6,6).toInt();

        pThis->setOutput(6, ivo == 1 ? 0 : 1, pThis->output6Time);
    }

    if( pThis->output7_func.contains("accessRefused") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",7,7).toInt();

        pThis->setOutput(7, ivo == 1 ? 0 : 1, pThis->output7Time);
    }
}

void CMoxaIOLogic::s_keyDetected(QObject *p, QMap<QString, QVariant>params) {
    CMoxaIOLogic *pThis = qobject_cast<CMoxaIOLogic *>(p);


    if( pThis->output0_func.contains("keyDetectedReset") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",0,0).toInt();

        pThis->setOutput(0, ivo == 1 ? 0 : 1, 0);
    }

    if( pThis->output1_func.contains("keyDetectedReset") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",1,1).toInt();

        pThis->setOutput(1, ivo == 1 ? 0 : 1, 0);
    }

    if( pThis->output2_func.contains("keyDetectedReset") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",2,2).toInt();

        pThis->setOutput(2, ivo == 1 ? 0 : 1, 0);
    }

    if( pThis->output3_func.contains("keyDetectedReset") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",3,3).toInt();

        pThis->setOutput(3, ivo == 1 ? 0 : 1, 0);
    }

    if( pThis->output4_func.contains("keyDetectedReset") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",4,4).toInt();

        pThis->setOutput(4, ivo == 1 ? 0 : 1, 0);
    }

    if( pThis->output5_func.contains("keyDetectedReset") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",5,5).toInt();

        pThis->setOutput(5, ivo == 1 ? 0 : 1, 0);
    }

    if( pThis->output6_func.contains("keyDetectedReset") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",6,6).toInt();

        pThis->setOutput(6, ivo == 1 ? 0 : 1, 0);
    }

    if( pThis->output7_func.contains("keyDetectedReset") ) {
        // get the initial value of the output
        int ivo = pThis->initialOutput.section(",",7,7).toInt();

        pThis->setOutput(7, ivo == 1 ? 0 : 1, 0);
    }
}

int CMoxaIOLogic::readOutput() {
    if(!isOpened()) {
        return 0;
    }

    int ret;

    if( (ret = E1K_DO_Reads(socket, 0, IO_NUMBER, &outputValue)) == MXIO_OK) {
        return outputValue;
    } else {
        return -1;
    }
}


void CMoxaIOLogic::readInput() {
    if(!isOpened()) {
        return;
    }

    int ret;
    unsigned int inputValue_t = 0;

    if( (ret = E1K_DI_Reads(socket, 0, IO_NUMBER, &inputValue_t)) == MXIO_OK) {

        if(inputValue_t != inputValue) {

            unsigned int mask = 1;

            for(int i = 0; i<8; i++)
            {
              unsigned int b_new = inputValue_t & mask;
              unsigned int b_old = inputValue & mask;

              if(b_new != b_old)
              {

                emit deviceInputChange(id, i, (bool)b_new);
              }

              mask <<= 1;
            }

            inputValue = inputValue_t;
        }

    }
}

void CMoxaIOLogic::checkConnection() {
    if(!isOpened()) {
        return;
    }

    int ret;
    unsigned char status;

    if( (ret = MXEIO_CheckConnection(socket, 100, &status) ) == MXIO_OK) {
        if(status != 0) {
            close();
            QTimer::singleShot(1000, this, SLOT(reopen()));
        }
    }
}

void CMoxaIOLogic::reopen() {
    open();
}


void CMoxaIOLogic::connection(int deviceId, bool isConnected) {
    if(deviceId == deviceParent->getParameter("id") ) {
        if(isConnected) {
            open();
        } else {
            close();
        }
    }
}

Q_EXPORT_PLUGIN2(cmoxaiologic, CMoxaIOLogic);
