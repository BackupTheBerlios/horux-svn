#ifndef CCONFIG_H
#define CCONFIG_H

#include <QtCore>
#include <QtXml>

class CConfig: public QDomDocument
{
public:
    CConfig();
    void parsXml();
    bool isError();

    int getBrightness();
    void setBrightness(int value);

    int getDisplayTimeout();
    void setDisplayTimeout(int value);

    int getInputTimeout();
    void setInputTimeout(int value);

    bool getUdpServerEnabled();
    void setUdpServerEnabled(bool value);

    QString getUdpServerClient();
    void setUdpServerClient(QString value);

    bool getAutoRestartEnabled();
    void setAutoRestartEnabled(bool value);

    QString getAutoRestartTime();
    void setAutoRestartTime(QString value);

private:
    bool error;

    QDomElement BrightnessActive;

    QDomElement DisplayTimeout;
    QDomElement InputTimeout;

    QDomElement UdpServerEnabled;
    QDomElement UdpServerClient;

    QDomElement AutoRestartEnabled;
    QDomElement AutoRestartTime;


};

#endif // CCONFIG_H
