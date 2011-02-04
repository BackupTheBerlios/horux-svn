
#include "cserver.h"
#include <QHostAddress>
#include <QSettings>
#include <QSqlQuery>
#include <QTcpSocket>
#include <QCoreApplication>

CServer *CServer::pThis = NULL;

CServer::CServer(QObject *parent) : QTcpServer(parent)
{
}


CServer::~CServer()
{
  CServer::pThis = NULL;
}

CServer* CServer::getInstance()
{
  if(pThis)
    return pThis;
  else
  {
    pThis = new CServer();
    return pThis;
  }
}


/*!
    \fn CServer::start()
 */
bool CServer::start()
{
    if( !isListening() )
    {
        QSettings settings(QCoreApplication::instance()->applicationDirPath() +"/horux.ini", QSettings::IniFormat);
        settings.beginGroup("AccessLinkInterface");
        int hello_port = settings.value("helloPort", 6998).toInt();
        if(!settings.contains("helloPort")) settings.setValue("helloPort", 6998);
        settings.endGroup();

        connect(this, SIGNAL(newConnection()), SLOT(newInternfaceConnection()));

        return listen( QHostAddress::Any,hello_port); 
    }

    return true;
}


/*!
    \fn CServer::newIternfaceConnection()
 */
void CServer::newInternfaceConnection()
{
    QTcpSocket *socket = nextPendingConnection () ;

    //! filter the connection 
    QSqlQuery query("SELECT COUNT(ip) FROM hr_accessLink_Interface WHERE ip='" + socket->peerAddress().toString() + "'");

    query.next();

    if(query.value(0).toInt()>0)
      emit newConnection(socket);
    else
    {
      socket->close();
      socket->deleteLater();
    }
}
