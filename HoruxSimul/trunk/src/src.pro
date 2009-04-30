SOURCES += horuxsimul.cpp \
           main.cpp \
 site.cpp

HEADERS += horuxsimul.h \
 site.h \
 deviceInterface.h

TEMPLATE = app

CONFIG += warn_on \
	  thread \
          qt \
 uitools \
	  release 

CONFIG -= debug

TARGET = ../bin/horuxsimul

RESOURCES = application.qrc

FORMS += HSMainWindow.ui \
 horuxSimulAbout.ui \
 adddevice.ui

QT += xml \
network
INCLUDEPATH += ../qledplugin

LIBS += ../qledplugin/libqledplugin.a

TARGETDEPS += ../qledplugin/libqledplugin.a

unix {
  binary.path = /usr/share/horux/simul
  binary.commands = install -m 755 -p $$TARGET /usr/share/horux/simul  && $(SYMLINK) /usr/share/horux/simul/horuxsimul /usr/bin/horuxsimul

  INSTALLS += binary
}

win32 {
 RC_FILE = myapp.rc
}