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
    horuxfields.cpp \
    databaseconnection.cpp
HEADERS += horuxdesigner.h \
    cardscene.h \
    carditemtext.h \
    carditem.h \
    confpage.h \
    printpreview.h \
    pixmapitem.h \
    horuxdialog.h \
    horuxfields.h \
    databaseconnection.h
FORMS += horuxdesigner.ui \
    textsetting.ui \
    cardsetting.ui \
    printpreview.ui \
    pixmapsetting.ui \
    horuxdialog.ui \
    horuxfields.ui \
    databaseconnection.ui
RESOURCES += ressource.qrc
win32:RC_FILE = myapp.rc
TRANSLATIONS = horuxcarddesigner_fr.ts
CODECFORTR = ISO-8859-5
include(./qtsoap-2.7_1-opensource/src/qtsoap.pri)
OTHER_FILES += TODO.txt
