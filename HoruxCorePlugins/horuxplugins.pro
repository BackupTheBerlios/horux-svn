SUBDIRS +=  Access/velopark/trunk/velopark.pro \
 Database/sqlite/trunk/sqlite.pro \
 Device/accessLink_Interface/trunk/accessLink_Interface.pro \
 Device/accessLink_RS232/trunk/accessLink_ReaderRS232.pro \
 Device/accessLink_RS485/trunk/accessLink_ReaderRS485.pro \
 Device/accessLink_TCPIP/trunk/accessLink_ReaderTCPIP.pro \
 Device/horux_media/trunk/horux_media.pro

TEMPLATE = subdirs 
CONFIG += warn_on \
          qt \
          thread 
