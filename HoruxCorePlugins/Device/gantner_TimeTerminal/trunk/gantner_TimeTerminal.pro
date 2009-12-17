TEMPLATE = lib
CONFIG += dll \
    plugin \
    debug
QT -= gui
QT += xml \
    network \
    sql \
    script
DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device
SOURCES += cgantnertimeterminal.cpp \
    cconfig.cpp
HEADERS += cgantnertimeterminal.h \
    cconfig.h
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
RESOURCES += ressources.qrc
OTHER_FILES += timeterminal.js
OTHER_FILES += timeterminal.js.aes
