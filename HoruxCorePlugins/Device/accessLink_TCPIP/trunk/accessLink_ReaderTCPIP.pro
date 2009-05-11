TEMPLATE = lib

CONFIG += dll \
plugin \
 release



HEADERS += caccesslinktcpip.h

SOURCES += caccesslinktcpip.cpp


QT += sql \
 xml \
 network


DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device

unix {
    library.path = /usr/share/horux/core/plugins/device
    library.files = $$DESTDIR/libaccessLink_ReaderTCPIP.so

    INSTALLS += library
}

INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces



QT -= gui

