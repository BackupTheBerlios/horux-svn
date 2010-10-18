SOURCES += main.cpp \
    choruxgui.cpp \
    caccesslinkusb.cpp \
    caccesslinkserial.cpp \
    cdevice.cpp \
    caccesslinkdevice.cpp \
    cgat5250b.cpp \
    cgat6000.cpp
TEMPLATE = app
CONFIG += warn_on \
    thread \
    qt
TARGET = ../bin/horuxsystray
HEADERS += choruxgui.h \
    caccesslinkusb.h \
    caccesslinkserial.h \
    cdevice.h \
    caccesslinkdevice.h \
    cgat5250b.h \
    cgat6000.h
RESOURCES += ressource.qrc
TARGETDEPS += ../qextserialport/build/libqextserialport.a
INCLUDEPATH += ../qextserialport
unix:DEFINES = _TTY_POSIX_
win32:DEFINES = _TTY_WIN_ \
    QWT_DLL \
    QT_DLL
FORMS += settings.ui
LIBS += ../qextserialport/build/libqextserialport.a
LIBS += -lhid
win32:LIBS += -lsetupapi
CONFIG -= debug
CONFIG += release
QT += xml \
    network
win32:CONFIG += qaxcontainer
TRANSLATIONS += horux_fr_FR.ts
win32:RC_FILE = myapp.rc
unix { 
    binary.path = /usr/share/horux/systray
    binary.commands = install \
        -m \
        755 \
        -p \
        $$TARGET \
        /usr/share/horux/systray \
        && \
        install \
        -m \
        755 \
        -p \
        horux_fr_FR.qm \
        /usr/share/horux/systray \
        && \
        $(SYMLINK) \
        /usr/share/horux/systray/horuxguitray \
        /usr/bin/horuxguitray
    INSTALLS += binary
}
