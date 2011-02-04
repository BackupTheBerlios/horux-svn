
#ifndef CLOGINTERFACE_H
#define CLOGINTERFACE_H


/**
	@author Jean-Luc Gyger <jean-luc.gyger@letux.ch>
*/
class CLogInterface{
public:
    virtual ~CLogInterface() {}

    virtual void debug(QString msg) = 0;
    virtual void warning(QString msg) = 0;
    virtual void critical(QString msg) = 0;
    virtual void fatal(QString msg) = 0;
    void setLogPath(QString path) {this->path = path;}

    /*!
      Return the meta object
    */

    virtual QObject *getMetaObject() = 0;

protected:
    QString path;
};


Q_DECLARE_INTERFACE(CLogInterface,
                     "com.letux.Horux.CLogInterface/1.0");

#endif
