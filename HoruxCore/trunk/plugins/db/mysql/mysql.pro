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

