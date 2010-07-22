TEMPLATE = lib

CONFIG += dll \
plugin \
release


QT -= gui

QT += sql \
xml

INCLUDEPATH += ../../../src/interfaces ../../../src

HEADERS += accesshoruxplugin.h

SOURCES += accesshoruxplugin.cpp

DESTDIR = ../../../bin/plugins/access

OBJECTS += ../../../src/cxmlfactory.o



