TEMPLATE = lib

CONFIG += dll \
plugin

QT += network xml
CONFIG += release
CONFIG -= debug 

HEADERS += accesslinkInterfacePlugin.h \
 accessLinkInterfaceWidget.h


SOURCES += accesslinkInterfacePlugin.cpp \
 accessLinkInterfaceWidget.cpp

RESOURCES += resource.qrc

FORMS += widget.ui

DESTDIR = ../../bin/plugins

TARGETDEPS += ../../qledplugin/libqledplugin.a

INCLUDEPATH += ../../qledplugin \
  ../../src


LIBS += ../../qledplugin/libqledplugin.a \
  -lcryptopp

unix {
  library.path = /usr/share/horux/simul/plugins
  library.files = $$DESTDIR/libaccessLinkInterface.so

  INSTALLS += library
}