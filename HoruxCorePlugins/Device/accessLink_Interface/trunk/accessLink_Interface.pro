TEMPLATE = lib

CONFIG += dll \
plugin \
 release

QT -= gui

QT += network \
 sql \
 xml

HEADERS += caccesslinkinterface.h \
 cserver.h 

SOURCES += caccesslinkinterface.cpp \
 cserver.cpp

INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src

DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device

LIBS += -lcryptopp

OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o


unix {
    library.path = /usr/share/horux/core/plugins/device
    library.files = $$DESTDIR/libaccessLink_Interface.so

    INSTALLS += library
}


