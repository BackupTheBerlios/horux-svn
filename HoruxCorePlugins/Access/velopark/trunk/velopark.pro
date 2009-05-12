TEMPLATE = lib

CONFIG += dll \
plugin \
 release

QT -= gui

QT += sql \
xml

INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src

HEADERS += cvelopark.h

SOURCES += cvelopark.cpp

DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/access

OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o


unix {
    library.path = /usr/share/horux/core/plugins/access
    library.files = $$DESTDIR/libvelopark.so

    INSTALLS += library
}

