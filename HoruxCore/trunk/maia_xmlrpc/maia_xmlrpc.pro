# #####################################################################
# Automatically generated by qmake (2.01a) Fr Mai 25 19:04:58 2007
# #####################################################################
TEMPLATE = lib # app
CONFIG += staticlib
DEPENDPATH += .
INCLUDEPATH += .
QT += xml \
    network
QT -= gui
CONFIG += qt \
    silent \
    release

# Input
HEADERS += maiaObject.h \
    maiaFault.h \
    maiaXmlRpcClient.h \
    maiaXmlRpcServer.h \
    maiaXmlRpcServerConnection.h

SOURCES += maiaObject.cpp \
    maiaFault.cpp \
    maiaXmlRpcClient.cpp \
    maiaXmlRpcServer.cpp \
    maiaXmlRpcServerConnection.cpp

TARGET = maia_xmlrpc
DESTDIR = ./
