TEMPLATE = lib

CONFIG += dll \
plugin \
 release

QT -= gui


HEADERS += caccesslinkrs485.h

SOURCES += caccesslinkrs485.cpp


INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces

QT += sql \
 xml


DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device

unix {
    library.path = /usr/share/horux/core/plugins/device
    library.files = $$DESTDIR/libaccessLink_ReaderRS485.so

    INSTALLS += library
}


