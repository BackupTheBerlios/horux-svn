# -------------------------------------------------
# Project created by QtCreator 2009-12-10T16:22:17
# -------------------------------------------------
QT += core gui network \
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
    printselection.cpp \
    printhoruxuser.cpp


win32:SOURCES += twain/twaincpp.cpp \
    twain/qtwaininterface.cpp \
    twain/qtwain.cpp \
    twain/dib.cpp \
    twain/dibfile.c \
    twain/dibutil.c

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
    printselection.h \
    printhoruxuser.h

win32:HEADERS +=  twain/twaincpp.h \
    twain/twain.h \
    twain/stdafx.h \
    twain/qtwaininterface.h \
    twain/dib.h \
    twain/dibapi.h \
    twain/qtwain.h

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
    printselection.ui \
    printhoruxuser.ui
RESOURCES += ressource.qrc
win32:RC_FILE = myapp.rc
TRANSLATIONS = horuxcarddesigner_fr.ts
CODECFORTR = ISO-8859-5

win32:INCLUDEPATH += ./twain

include(./qtsoap-2.7_1-opensource/src/qtsoap.pri)
OTHER_FILES += TODO.txt


win32:LIBS += -lkernel32 -luser32 -lgdi32 -lcomdlg32 -lole32 -ldinput -lddraw -ldxguid -lwinmm -ldsound
