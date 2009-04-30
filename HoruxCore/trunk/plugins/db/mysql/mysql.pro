TEMPLATE = lib

CONFIG += dll \
plugin \
release

QT -= gui

QT += sql

SOURCES += dbmysqlplugin.cpp

HEADERS += dbmysqlplugin.h



INCLUDEPATH += ../../../src/interfaces



DESTDIR = ../../../bin/plugins/db

unix {
  library.path = /usr/share/horux/core/plugins/db
  library.files = $$DESTDIR/libmysql.so

  INSTALLS += library
}