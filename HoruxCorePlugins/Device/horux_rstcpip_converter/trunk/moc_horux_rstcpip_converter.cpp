/****************************************************************************
** Meta object code from reading C++ file 'horux_rstcpip_converter.h'
**
** Created: Thu Nov 18 15:02:42 2010
**      by: The Qt Meta Object Compiler version 62 (Qt 4.6.3)
**
** WARNING! All changes made in this file will be lost!
*****************************************************************************/

#include "horux_rstcpip_converter.h"
#if !defined(Q_MOC_OUTPUT_REVISION)
#error "The header file 'horux_rstcpip_converter.h' doesn't include <QObject>."
#elif Q_MOC_OUTPUT_REVISION != 62
#error "This file was generated using the moc from 4.6.3. It"
#error "cannot be used with the include files from this version of Qt."
#error "(The moc has changed too much.)"
#endif

QT_BEGIN_MOC_NAMESPACE
static const uint qt_meta_data_CHRstcpipC[] = {

 // content:
       4,       // revision
       0,       // classname
       6,   14, // classinfo
      10,   26, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       4,       // signalCount

 // classinfo: key, value
      23,   11,
      30,   11,
      46,   40,
      78,   54,
      96,   89,
     177,  107,

 // signals: signature, parameters, type, tag, flags
     205,  196,  195,  195, 0x05,
     245,  226,  195,  195, 0x05,
     298,  277,  195,  195, 0x05,
     328,  325,  195,  195, 0x05,

 // slots: signature, parameters, type, tag, flags
     357,  325,  195,  195, 0x0a,
     389,  385,  195,  195, 0x0a,
     411,  195,  195,  195, 0x09,
     429,  195,  195,  195, 0x09,
     461,  449,  195,  195, 0x09,
     503,  195,  195,  195, 0x09,

       0        // eod
};

static const char qt_meta_stringdata_CHRstcpipC[] = {
    "CHRstcpipC\0Letux Sàrl\0Author\0Copyright\0"
    "0.0.1\0Version\0horux_rstcpip_converter\0"
    "PluginName\0device\0PluginType\0"
    "Lecteur clavier permettant d'ouvrir une porte à l'aide d'un PIN code\0"
    "PluginDescription\0\0xmlEvent\0"
    "deviceEvent(QString)\0deviceId,in,status\0"
    "deviceInputChange(int,int,bool)\0"
    "deviceId,isConnected\0deviceConnection(int,bool)\0"
    "ba\0subDeviceMessage(QByteArray)\0"
    "dispatchMessage(QByteArray)\0xml\0"
    "deviceAction(QString)\0deviceConnected()\0"
    "deviceDiconnected()\0socketError\0"
    "deviceError(QAbstractSocket::SocketError)\0"
    "readyRead()\0"
};

const QMetaObject CHRstcpipC::staticMetaObject = {
    { &QObject::staticMetaObject, qt_meta_stringdata_CHRstcpipC,
      qt_meta_data_CHRstcpipC, 0 }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &CHRstcpipC::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *CHRstcpipC::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *CHRstcpipC::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_CHRstcpipC))
        return static_cast<void*>(const_cast< CHRstcpipC*>(this));
    if (!strcmp(_clname, "CDeviceInterface"))
        return static_cast< CDeviceInterface*>(const_cast< CHRstcpipC*>(this));
    if (!strcmp(_clname, "com.letux.Horux.CDeviceInterface/1.0"))
        return static_cast< CDeviceInterface*>(const_cast< CHRstcpipC*>(this));
    return QObject::qt_metacast(_clname);
}

int CHRstcpipC::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QObject::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        switch (_id) {
        case 0: deviceEvent((*reinterpret_cast< QString(*)>(_a[1]))); break;
        case 1: deviceInputChange((*reinterpret_cast< int(*)>(_a[1])),(*reinterpret_cast< int(*)>(_a[2])),(*reinterpret_cast< bool(*)>(_a[3]))); break;
        case 2: deviceConnection((*reinterpret_cast< int(*)>(_a[1])),(*reinterpret_cast< bool(*)>(_a[2]))); break;
        case 3: subDeviceMessage((*reinterpret_cast< QByteArray(*)>(_a[1]))); break;
        case 4: dispatchMessage((*reinterpret_cast< QByteArray(*)>(_a[1]))); break;
        case 5: deviceAction((*reinterpret_cast< QString(*)>(_a[1]))); break;
        case 6: deviceConnected(); break;
        case 7: deviceDiconnected(); break;
        case 8: deviceError((*reinterpret_cast< QAbstractSocket::SocketError(*)>(_a[1]))); break;
        case 9: readyRead(); break;
        default: ;
        }
        _id -= 10;
    }
    return _id;
}

// SIGNAL 0
void CHRstcpipC::deviceEvent(QString _t1)
{
    void *_a[] = { 0, const_cast<void*>(reinterpret_cast<const void*>(&_t1)) };
    QMetaObject::activate(this, &staticMetaObject, 0, _a);
}

// SIGNAL 1
void CHRstcpipC::deviceInputChange(int _t1, int _t2, bool _t3)
{
    void *_a[] = { 0, const_cast<void*>(reinterpret_cast<const void*>(&_t1)), const_cast<void*>(reinterpret_cast<const void*>(&_t2)), const_cast<void*>(reinterpret_cast<const void*>(&_t3)) };
    QMetaObject::activate(this, &staticMetaObject, 1, _a);
}

// SIGNAL 2
void CHRstcpipC::deviceConnection(int _t1, bool _t2)
{
    void *_a[] = { 0, const_cast<void*>(reinterpret_cast<const void*>(&_t1)), const_cast<void*>(reinterpret_cast<const void*>(&_t2)) };
    QMetaObject::activate(this, &staticMetaObject, 2, _a);
}

// SIGNAL 3
void CHRstcpipC::subDeviceMessage(QByteArray _t1)
{
    void *_a[] = { 0, const_cast<void*>(reinterpret_cast<const void*>(&_t1)) };
    QMetaObject::activate(this, &staticMetaObject, 3, _a);
}
QT_END_MOC_NAMESPACE
