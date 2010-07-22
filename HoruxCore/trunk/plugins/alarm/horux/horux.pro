TEMPLATE = lib

CONFIG += dll plugin release

QT -= gui
QT += sql xml

HEADERS += choruxalarmplugin.h
SOURCES += choruxalarmplugin.cpp
DESTDIR = ../../../bin/plugins/alarm
OBJECTS += ../../../src/cxmlfactory.o

INCLUDEPATH += ../../../src/interfaces  ../../../src
