# -------------------------------------------------
# Project created by QtCreator 2009-12-10T16:22:17
# -------------------------------------------------
QT += network \
    sql \
    svg \
    xml
TARGET = HoruxCardDesigner
TEMPLATE = app
SOURCES += main.cpp \
    horuxdesigner.cpp \
    cardscene.cpp \
    carditemtext.cpp \
    carditem.cpp \
    confpage.cpp \
    printpreview.cpp \
    pixmapitem.cpp \
    horuxdialog.cpp \
    horuxfields.cpp
HEADERS += horuxdesigner.h \
    cardscene.h \
    carditemtext.h \
    carditem.h \
    confpage.h \
    printpreview.h \
    pixmapitem.h \
    horuxdialog.h \
    horuxfields.h
FORMS += horuxdesigner.ui \
    textsetting.ui \
    cardsetting.ui \
    printpreview.ui \
    pixmapsetting.ui \
    horuxdialog.ui \
    horuxfields.ui
RESOURCES += ressource.qrc
include(./qtsoap-2.7_1-opensource/src/qtsoap.pri)
