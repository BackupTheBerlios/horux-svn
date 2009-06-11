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
    library.path = /usr/share/horux/core/plugins/log
    library.files = $$DESTDIR/libhtml.so

    INSTALLS += library
}

