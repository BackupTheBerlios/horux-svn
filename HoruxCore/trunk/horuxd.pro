# To activate the unit test, uncomment the following line and in ./src/src.pro
# CONFIG += HORUX_UNIT_TEST


SUBDIRS +=  maia_xmlrpc \
 qextserialport \
 src \
 plugins/db/mysql \
 src/interfaces \
 plugins/access/horux \
 plugins/log/html \
 plugins/alarm/horux

HORUX_UNIT_TEST {
    SUBDIRS += plugins/device/unittest
}

TEMPLATE = subdirs 
CONFIG += warn_on \
          qt \
          thread 
