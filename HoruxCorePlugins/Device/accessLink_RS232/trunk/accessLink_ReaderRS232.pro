TEMPLATE = lib

CONFIG += dll \
plugin \
 release



HEADERS += caccesslinkrs232.h

SOURCES += caccesslinkrs232.cpp

unix : DEFINES = _TTY_POSIX_
win32 : DEFINES = _TTY_WIN_ QWT_DLL QT_DLL


QT += sql \
 xml


DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device

unix {
    library.path = /usr/share/horux/core/plugins/device
    library.files = $$DESTDIR/libaccessLink_ReaderRS232.so

    INSTALLS += library
}


OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o

INCLUDEPATH += ../../../../HoruxCore/trunk/qextserialport \
  ../../../../HoruxCore/trunk/src/interfaces  ../../../../HoruxCore/trunk/src

LIBS += ../../../../HoruxCore/trunk/qextserialport/build/libqextserialport.a

TARGETDEPS += ../../../../HoruxCore/trunk/qextserialport/build/libqextserialport.a


QT -= gui

