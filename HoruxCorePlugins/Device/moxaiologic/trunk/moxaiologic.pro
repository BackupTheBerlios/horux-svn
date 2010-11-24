TEMPLATE = lib

CONFIG += dll \
plugin \
 release

CONFIG -=debug

HEADERS += cmoxaiologic.h

SOURCES += cmoxaiologic.cpp


QT += sql \
 xml \
 network


DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device

INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src

OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o

unix:LIBS += /usr/local/lib/libmxio.a

QT -= gui

