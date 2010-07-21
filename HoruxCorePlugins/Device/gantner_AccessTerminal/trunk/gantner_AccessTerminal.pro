TEMPLATE = lib
CONFIG += dll \
    plugin

CONFIG -= release
CONFIG += debug

QT -= gui
QT += xml \
    network \
    sql \
    script
DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device
SOURCES += cgantneraccessterminal.cpp
HEADERS += cgantneraccessterminal.h
INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces \
    ../../../../HoruxCore/trunk/src
LIBS += ../../../../HoruxCore/trunk/maia_xmlrpc/libmaia_xmlrpc.a
LIBS += -lcryptopp
OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o
unix { 
    library.path = /usr/share/horux/core/plugins/device
    library.files = $$DESTDIR/libhorux_media.so
    INSTALLS += library
}
