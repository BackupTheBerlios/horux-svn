TEMPLATE = lib

CONFIG += dll \
plugin \
 release

QT -= gui


HEADERS += caccesslinkrs485.h

SOURCES += caccesslinkrs485.cpp


INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src

QT += sql \
 xml

OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o

DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device

unix {
    library.path = /usr/share/horux/core/plugins/device
    library.files = $$DESTDIR/libaccessLink_ReaderRS485.so

    INSTALLS += library
}


