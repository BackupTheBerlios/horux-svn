TEMPLATE = lib

CONFIG += dll \
plugin \
release

QT -= gui

QT += sql xml

SOURCES += dbmysqlplugin.cpp

HEADERS += dbmysqlplugin.h



INCLUDEPATH += ../../../src/interfaces



DESTDIR = ../../../bin/plugins/db

unix {
OBJECTS += ../../../src/cxmlfactory.o
}

win32 {
OBJECTS += ../../../src/release/cxmlfactory.o
}

unix {
  library.path = /usr/share/horux/core/plugins/db
  library.files = $$DESTDIR/libmysql.so

  INSTALLS += library
}
