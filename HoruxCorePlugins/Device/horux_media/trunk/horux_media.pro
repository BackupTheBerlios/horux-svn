TEMPLATE = lib

CONFIG += dll \
plugin \
 release

QT -= gui

QT += xml \
network \
sql

DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device

SOURCES += choruxmedia.cpp

HEADERS += choruxmedia.h

INCLUDEPATH += ../../../../HoruxCore/trunk/maia_xmlrpc \
  ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src

LIBS += ../../../../HoruxCore/trunk/maia_xmlrpc/libmaia_xmlrpc.a

OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o


TARGETDEPS += ../../../../HoruxCore/trunk/maia_xmlrpc/libmaia_xmlrpc.a

unix {
    library.path = /usr/share/horux/core/plugins/device
    library.files = $$DESTDIR/libhorux_media.so

    INSTALLS += library
}
