TEMPLATE = lib

CONFIG += dll \
plugin \
release 

CONFIG -= debug


SOURCES += accessLinkReaderRS485Plugin.cpp \
 accessLinkReaderRS485Widget.cpp \
 loadtestingdlg.cpp

HEADERS += accessLinkReaderRS485Plugin.h \
 accessLinkReaderRS485Widget.h \
 loadtestingdlg.h


QT += xml

FORMS += widget.ui \
 uiloadtesting.ui

RESOURCES += resource.qrc

DESTDIR = ../../bin/plugins

INCLUDEPATH += ../../qledplugin \
  ../../src

LIBS += ../../qledplugin/libqledplugin.a

TARGETDEPS += ../../qledplugin/libqledplugin.a

unix {
  library.path = /usr/share/horux/simul/plugins
  library.files = $$DESTDIR/libaccessLinkReaderRS485.so

  INSTALLS += library
}