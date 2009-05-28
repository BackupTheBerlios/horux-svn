TEMPLATE = lib

CONFIG += dll \
plugin \
release

QT -= gui

QT += sql

SOURCES += dbsqliteplugin.cpp

HEADERS += dbsqliteplugin.h



INCLUDEPATH +=  ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src



DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/db

unix {
    library.path = /usr/share/horux/core/plugins/db
    library.files = $$DESTDIR/libmysql.so

    INSTALLS += library
}