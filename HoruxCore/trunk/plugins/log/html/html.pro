TEMPLATE = lib

CONFIG += dll \
plugin \
 release

QT -= gui

HEADERS += chtmllogplugin.h

SOURCES += chtmllogplugin.cpp

INCLUDEPATH += ../../../src/interfaces

DESTDIR = ../../../bin/plugins/log

