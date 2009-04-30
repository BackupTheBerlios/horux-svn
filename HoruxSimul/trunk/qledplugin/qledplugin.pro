TEMPLATE = lib

CONFIG += plugin \
designer \
staticlib \
release

SOURCES += qled.cpp \
qledplugin.cpp
HEADERS += qled.h \
qledplugin.h

CONFIG -= debug

DESTDIR = ./

