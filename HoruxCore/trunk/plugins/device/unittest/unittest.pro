TEMPLATE = lib

CONFIG += dll \
 plugin \
 release

QT -= gui

QT += xml 

DESTDIR = ../../../bin/plugins/device

SOURCES += cunittest.cpp

HEADERS += cunittest.h

INCLUDEPATH += ../../../maia_xmlrpc \
  ../../../src/interfaces ../../../src

LIBS += ../../../maia_xmlrpc/libmaia_xmlrpc.a

OBJECTS += ../../../src/cxmlfactory.o


TARGETDEPS += ../../../maia_xmlrpc/libmaia_xmlrpc.a

unix {
    library.path = /usr/share/horux/core/plugins/device
    library.files = $$DESTDIR/libtestunit.so

    INSTALLS += library
}
