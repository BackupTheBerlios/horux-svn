TEMPLATE = lib

CONFIG += dll \
plugin \
 release

CONFIG -=debug

HEADERS += clcddisplay.h

SOURCES += clcddisplay.cpp


QT += sql \
 xml \
 network


DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device

INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src

OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o

QT -= gui

