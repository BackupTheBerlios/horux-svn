TEMPLATE = lib

CONFIG += dll \
plugin \
 debug

QT -= gui

QT += sql \
xml \
network

INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src

HEADERS += cgantnertime.h

SOURCES += cgantnertime.cpp

DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/access

OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o


unix {
    library.path = /usr/share/horux/core/plugins/access
    library.files = $$DESTDIR/libgantnertime.so

    INSTALLS += library
}

include(../../../../HoruxCore/trunk/qtsoap-2.7_1-opensource/src/qtsoap.pri)
