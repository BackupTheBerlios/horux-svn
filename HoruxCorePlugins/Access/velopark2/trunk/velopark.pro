TEMPLATE = lib

CONFIG += dll \
plugin \
 release

CONFIG -= debug

QT -= gui

QT += sql \
xml

INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src

HEADERS += cvelopark.h

SOURCES += cvelopark.cpp

DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/access

OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o


include(../../../../HoruxCore/trunk/qtsoap-2.7_1-opensource/src/qtsoap.pri)
