TEMPLATE = lib

CONFIG += dll \
plugin \
 release

QT -= gui
QT += xml

HEADERS += chtmllogplugin.h

SOURCES += chtmllogplugin.cpp

INCLUDEPATH += ../../../src/interfaces

DESTDIR = ../../../bin/plugins/log

unix {
OBJECTS += ../../../src/cxmlfactory.o
}

win32 {
OBJECTS += ../../../src/release/cxmlfactory.o
}


unix {
    library.path = /usr/share/horux/core/plugins/log
    library.files = $$DESTDIR/libhtml.so

    INSTALLS += library
}

