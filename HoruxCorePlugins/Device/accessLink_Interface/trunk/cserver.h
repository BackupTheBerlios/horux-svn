
#ifndef CSERVER_H
#define CSERVER_H

#include <QTcpServer>

/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CServer : public QTcpServer
{
Q_OBJECT
public:
    static CServer* getInstance();
    ~CServer();
    bool start();


protected:
    CServer(QObject *parent = 0);

private:
    static CServer *pThis;
protected slots:
    void newInternfaceConnection();
signals:
    void newConnection(QTcpSocket *);
};

#endif
