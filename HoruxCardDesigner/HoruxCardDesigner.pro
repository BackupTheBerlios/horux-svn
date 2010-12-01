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
    databaseconnection.cpp \
    printcounter.cpp \
    csvtest.cpp \
    formattext.cpp \
    printselection.cpp

win32:SOURCES += twain/twaincpp.cpp \
    twain/qtwainsubstitute.cpp \
    twain/qtwaininterface.cpp \
    twain/qtwain.cpp \
    twain/dibutil.c \
    twain/dibfile.c \
    twain/dib.cpp

HEADERS += horuxdesigner.h \
    cardscene.h \
    carditemtext.h \
    carditem.h \
    confpage.h \
    printpreview.h \
    pixmapitem.h \
    horuxdialog.h \
    horuxfields.h \
    databaseconnection.h \
    printcounter.h \
    csvtest.h \
    formattext.h \
    printselection.h

win32:SOURCES +=  twain/twaincpp.h \
    twain/twain.h \
    twain/stdafx.h \
    twain/qtwainsubstitute.h \
    twain/qtwaininterface.h \
    twain/qtwain.h \
    twain/dibutil.h \
    twain/dibapi.h \
    twain/dib.h
FORMS += horuxdesigner.ui \
    textsetting.ui \
    cardsetting.ui \
    printpreview.ui \
    pixmapsetting.ui \
    horuxdialog.ui \
    horuxfields.ui \
    databaseconnection.ui \
    printcounter.ui \
    csvtest.ui \
    formattext.ui \
    printselection.ui
RESOURCES += ressource.qrc
win32:RC_FILE = myapp.rc
TRANSLATIONS = horuxcarddesigner_fr.ts
CODECFORTR = ISO-8859-5
include(./qtsoap-2.7_1-opensource/src/qtsoap.pri)
OTHER_FILES += TODO.txt
