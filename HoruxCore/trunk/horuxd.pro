
SUBDIRS +=  maia_xmlrpc \
 qextserialport \
 src \
 plugins/db/mysql \
 src/interfaces \
 plugins/access/horux \
 plugins/log/html \
 plugins/alarm/horux \
 plugins/db/sqlite

TEMPLATE = subdirs 
CONFIG += warn_on \
          qt \
          thread 
