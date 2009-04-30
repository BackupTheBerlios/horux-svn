SUBDIRS +=  qledplugin plugins src

TEMPLATE = subdirs 

CONFIG += warn_on \
          qt \
          thread \
	  release 

CONFIG -= debug

DIST += $$SUBDIRS