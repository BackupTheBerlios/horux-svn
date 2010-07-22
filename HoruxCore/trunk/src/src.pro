SOURCES += main.cpp \
    cfactory.cpp \
    chorux.cpp \
    cdbhandling.cpp \
    caccesshandling.cpp \
    cdevicehandling.cpp \
    clog.cpp \
    calarmhandling.cpp \
    cxmlfactory.cpp \
    choruxservice.cpp
TEMPLATE = app
CONFIG += warn_on \
    thread \
    qt \
    release
CONFIG -= debug
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
    choruxservice.h
QT += core \
    sql \
    xml \
    network
QT -= gui
INCLUDEPATH += ../maia_xmlrpc \
    interfaces
LIBS += ../maia_xmlrpc/libmaia_xmlrpc.a
win32:RC_FILE = myapp.rc

include(../qtservice-2.6-opensource/src/qtservice.pri)
include(../qtsoap-2.7_1-opensource/src/qtsoap.pri)
