SOURCES += main.cpp \
    cfactory.cpp \
    chorux.cpp \
    cdbhandling.cpp \
    caccesshandling.cpp \
    cdevicehandling.cpp \
    clog.cpp \
    calarmhandling.cpp \
    cxmlfactory.cpp \
    choruxservice.cpp \
    cnotification.cpp

TEMPLATE = app

CONFIG += warn_on \
    thread \
    qt \
    debug

TARGET = ../bin/horuxd

RESOURCES -= application.qrc

HEADERS += cfactory.h \
    chorux.h \
    cdbhandling.h \
    include.h \
    clog.h \
    caccesshandling.h \
    cdevicehandling.h \
    calarmhandling.h \
    cxmlfactory.h \
    choruxservice.h \
    cnotification.h

QT += core \
    sql \
    xml \
    network
QT -= gui

INCLUDEPATH += ../maia_xmlrpc \
    interfaces

LIBS += ../maia_xmlrpc/libmaia_xmlrpc.a

win32:RC_FILE = myapp.rc
unix { 
    binary.path = /usr/share/horux/core
    binary.commands = install \
        -m \
        755 \
        -p \
        $$TARGET \
        /usr/share/horux/core \
        && \
        $(SYMLINK) \
        /usr/share/horux/core/horuxd \
        /usr/bin/horuxd \
        && \
        install \
        -m \
        755 \
        -p \
        ../init.d/horuxd \
        /etc/init.d \
        && \
        update-rc.d \
        horuxd \
        defaults
    INSTALLS += binary
}

include(../qtservice-2.6-opensource/src/qtservice.pri)
include(../qtsoap-2.7_1-opensource/src/qtsoap.pri)

