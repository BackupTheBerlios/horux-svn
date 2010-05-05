
SUBDIRS +=  maia_xmlrpc \
 qextserialport \
 src \
 plugins/db/mysql \
 src/interfaces \
 plugins/access/horux \
 plugins/log/html \
 plugins/alarm/horux \
 testunit/test_cdbhandling

TEMPLATE = subdirs 
CONFIG += warn_on \
          qt \
          thread 
