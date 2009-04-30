SOURCES += main.cpp \
 videowidget.cpp \
 \
 cmedia.cpp \
 cimage.cpp \
 cplayerlist.cpp \
 easing.cpp \
 qabstractanimation.cpp \
 qanimation.cpp \
 qanimationgroup.cpp \
 qconnectionstate_p.cpp \
 qeasingcurve.cpp \
 qpropertystate_p.cpp \
 qstate.cpp \
 qstategroup.cpp \
 qtransition.cpp \
 splashitem.cpp
TEMPLATE = app
CONFIG += warn_on \
	  thread \
          qt
TARGET = horuxmedia
DESTDIR = ../bin
QT += xml \
network \
 phonon \
 opengl \
 sql

HEADERS += videowidget.h \
 cmedia.h \
 cimage.h \
 cplayerlist.h \
 common.h \
 qabstractanimation.h \
 qabstractanimation_p.h \
 qanimationgroup.h \
 qanimationgroup_p.h \
 qanimation.h \
 qanimation_p.h \
 qanimationpointer_p.h \
 qconnectionstate_p.h \
 qeasingcurve.h \
 qpauseanimation_p.h \
 qpropertystate_p.h \
 qstategroup.h \
 qstate.h \
 qstate_p.h \
 qtransition.h \
 qtransition_p.h \
 splashitem.h



DEFINES += QT_EXPERIMENTAL_SOLUTION
DEFINES += QT_BUILD_ANIMATION_LIB

LIBS += ../maia_xmlrpc/libmaia_xmlrpc.a
INCLUDEPATH += ../maia_xmlrpc

TARGETDEPS += ../maia_xmlrpc/libmaia_xmlrpc.a

RESOURCES += res.qrc

