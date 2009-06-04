TEMPLATE = lib

CONFIG += dll \
plugin \
 release

QT -= gui


VERSION = 1.0.0.1

HEADERS += chtmllogplugin.h

SOURCES += chtmllogplugin.cpp

INCLUDEPATH += ../../../src/interfaces

DESTDIR = ../../../bin/plugins/log

OBJECTS += ../../../src/cxmlfactory.o


unix {
    library.path = /usr/share/horux/core/plugins/log
    library.files = $$DESTDIR/libhtml.so

    INSTALLS += library
}

