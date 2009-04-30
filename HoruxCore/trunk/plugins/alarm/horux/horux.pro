TEMPLATE = lib

CONFIG += dll \
plugin


QT -= gui

QT += sql \
xml

HEADERS += choruxalarmplugin.h

SOURCES += choruxalarmplugin.cpp

DESTDIR = ../../../bin/plugins/alarm

unix {
    library.path = /usr/share/horux/core/plugins/alarm
    library.files = $$DESTDIR/libhorux.so

    INSTALLS += library
}

CONFIG += release

INCLUDEPATH += ../../../src/interfaces
