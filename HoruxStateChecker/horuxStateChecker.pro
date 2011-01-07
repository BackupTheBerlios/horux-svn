# -------------------------------------------------
# Project created by QtCreator 2010-12-22T08:44:24
# -------------------------------------------------
TARGET = horuxStateChecker
TEMPLATE = app
SOURCES += main.cpp \
    mainwindow.cpp
HEADERS += mainwindow.h \
    ui_mainwindow.h
FORMS += mainwindow.ui

QT += network

include(./qtsoap-2.7_1-opensource/src/qtsoap.pri)
