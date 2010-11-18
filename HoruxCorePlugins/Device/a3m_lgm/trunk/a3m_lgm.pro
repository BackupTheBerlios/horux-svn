TEMPLATE = lib
CONFIG += dll plugin release
QT -= gui
QT += xml network

HEADERS += a3m_lgm.h
SOURCES += a3m_lgm.cpp

# le fichier lib sera copié directement au bon endroit dans Horux Core selon la structure svn
DESTDIR = ../../../../HoruxCore/trunk/bin/plugins/device

# insertion des chemins d'inclusion pour l'interface concernant les plugins ainsi que les entêtes de Horux Core
INCLUDEPATH += ../../../../HoruxCore/trunk/src/interfaces ../../../../HoruxCore/trunk/src

# Afin de pouvoir envoyer des messages aux sous système (accès, alarm), cxmlfactory offre des fonctions statiques permettant de simplifier la structure des messages
OBJECTS += ../../../../HoruxCore/trunk/src/cxmlfactory.o
