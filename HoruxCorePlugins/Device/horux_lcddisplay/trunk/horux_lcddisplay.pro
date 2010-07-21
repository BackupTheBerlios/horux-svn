TEMPLATE = lib

CONFIG += dll \
plugin \
 release



HEADERS += clcddisplay.h

SOURCES += clcddisplay.cpp


QT += sql \
 xml \
 network


DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device

unix {
    library.path = /usr/share/horux/core/plugins/device
    library.files = $$DESTDIR/libhorux_lcddisplay.so

    INSTALLS += library
}

INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src

OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o

QT -= gui

