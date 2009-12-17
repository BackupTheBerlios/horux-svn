#include "cconfig.h"

CConfig::CConfig() : QDomDocument()
{
    error = false;
}

void CConfig::parsXml()
{
    QDomElement root = documentElement();

    if(root.tagName() != "configuration")
        error = false;

    if(!error)
    {
        QDomNode node = root.firstChild();

        while( !node.isNull() )
        {
            if( node.toElement().tagName() == "GatTimeCe")
            {
                QDomElement GatTimeCe = node.toElement();
                QDomNode GatTimeCeNode = GatTimeCe.firstChild();
                while( !GatTimeCeNode.isNull() )
                {
                    if( GatTimeCeNode.toElement().tagName() == "Display")
                    {
                        QDomElement Display = GatTimeCeNode.toElement();
                        QDomNode DisplayNode = Display.firstChild();
                        while( !DisplayNode.isNull() )
                        {
                            if( DisplayNode.toElement().tagName() == "Brightness")
                            {
                                QDomElement Active = DisplayNode.toElement();
                                QDomNode ActiveNode = Active.firstChild();
                                BrightnessActive = ActiveNode.toElement();


                            }
                            DisplayNode = DisplayNode.nextSibling();
                        }
                    }

                    GatTimeCeNode = GatTimeCeNode.nextSibling();
                }

            }
            if( node.toElement().tagName() == "Timeouts")
            {
                QDomElement Timeouts = node.toElement();
                QDomNode TimeoutsNode = Timeouts.firstChild();

                while( !TimeoutsNode.isNull() )
                {
                    if( TimeoutsNode.toElement().tagName() == "DisplayTimeout")
                    {
                        DisplayTimeout = TimeoutsNode.toElement();
                    }
                    if( TimeoutsNode.toElement().tagName() == "InputTimeout")
                    {
                        InputTimeout = TimeoutsNode.toElement();
                    }

                    TimeoutsNode = TimeoutsNode.nextSibling();
                }
            }
            if( node.toElement().tagName() == "UdpServer")
            {
                QDomElement UdpServer = node.toElement();
                QDomNode UdpServerNode = UdpServer.firstChild();

                while( !UdpServerNode.isNull() )
                {
                    if( UdpServerNode.toElement().tagName() == "Enabled")
                    {
                        UdpServerEnabled = UdpServerNode.toElement();
                    }
                    if( UdpServerNode.toElement().tagName() == "Clients")
                    {
                        UdpServerClient = UdpServerNode.toElement();
                    }

                    UdpServerNode = UdpServerNode.nextSibling();
                }
            }
            if( node.toElement().tagName() == "AutoRestart")
            {
                QDomElement AutoRestart = node.toElement();
                QDomNode AutoRestartNode = AutoRestart.firstChild();

                while( !AutoRestartNode.isNull() )
                {
                    if( AutoRestartNode.toElement().tagName() == "Enabled")
                    {
                        AutoRestartEnabled = AutoRestartNode.toElement();
                    }
                    if( AutoRestartNode.toElement().tagName() == "Time")
                    {
                        AutoRestartTime = AutoRestartNode.toElement();
                    }

                    AutoRestartNode = AutoRestartNode.nextSibling();
                }
            }

            node = node.nextSibling();
        }
    }
}

bool CConfig::isError()
{
    return error;
}

int CConfig::getBrightness()
{
    return BrightnessActive.attribute("value").toInt();
}

void CConfig::setBrightness(int value)
{
    BrightnessActive.setAttribute("value", value);
}

int CConfig::getDisplayTimeout()
{
    return DisplayTimeout.attribute("value").toInt();
}

void CConfig::setDisplayTimeout(int value)
{
    DisplayTimeout.setAttribute("value", value);
}

int CConfig::getInputTimeout()
{
    return InputTimeout.attribute("value").toInt();
}

void CConfig::setInputTimeout(int value)
{
    InputTimeout.setAttribute("value", value);
}

bool CConfig::getUdpServerEnabled()
{
    if( UdpServerEnabled.attribute("value") == "true")
        return true;
    else
        return false;
}

void CConfig::setUdpServerEnabled(bool value)
{
    UdpServerEnabled.setAttribute("value", value ? "true" : "false");
}

QString CConfig::getUdpServerClient()
{
    return UdpServerClient.attribute("value");
}

void CConfig::setUdpServerClient(QString value)
{
    UdpServerClient.setAttribute("value", value);
}

bool CConfig::getAutoRestartEnabled()
{
    if( AutoRestartEnabled.attribute("value") == "true")
        return true;
    else
        return false;
}

void CConfig::setAutoRestartEnabled(bool value)
{
    AutoRestartEnabled.setAttribute("value", value ? "true" : "false");
}

QString CConfig::getAutoRestartTime()
{
    return AutoRestartTime.attribute("value");
}

void CConfig::setAutoRestartTime(QString value)
{
    AutoRestartTime.setAttribute("value", value);
}
