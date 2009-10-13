TEMPLATE = lib

CONFIG += dll plugin release

QT -= gui
QT += sql xml

HEADERS += choruxalarmplugin.h
SOURCES += choruxalarmplugin.cpp
DESTDIR = ../../../bin/plugins/alarm
OBJECTS += ../../../src/cxmlfactory.o

unix {
    library.path = /usr/share/horux/core/plugins/alarm
    library.files = $$DESTDIR/libhorux.so

    INSTALLS += library
}

INCLUDEPATH += ../../../src/interfaces  ../../../src
