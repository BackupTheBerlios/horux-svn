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
    printpreview.cpp
HEADERS += horuxdesigner.h \
    cardscene.h \
    carditemtext.h \
    carditem.h \
    confpage.h \
    printpreview.h
FORMS += horuxdesigner.ui \
    textsetting.ui \
    cardsetting.ui \
    printpreview.ui
RESOURCES += ressource.qrc
