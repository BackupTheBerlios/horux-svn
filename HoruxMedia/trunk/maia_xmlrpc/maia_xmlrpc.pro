TEMPLATE = lib

CONFIG += staticlib release

QT -= gui

QT += xml \
network
SOURCES += maiaFault.cpp \
maiaObject.cpp \
maiaXmlRpcClient.cpp \
maiaXmlRpcServerConnection.cpp \
maiaXmlRpcServer.cpp
HEADERS += maiaFault.h \
maiaObject.h \
maiaXmlRpcClient.h \
maiaXmlRpcServerConnection.h \
maiaXmlRpcServer.h
