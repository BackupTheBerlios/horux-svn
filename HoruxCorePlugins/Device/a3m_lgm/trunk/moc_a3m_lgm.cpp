/****************************************************************************
** Meta object code from reading C++ file 'a3m_lgm.h'
**
** Created: Thu Nov 18 16:12:11 2010
**      by: The Qt Meta Object Compiler version 62 (Qt 4.6.3)
**
** WARNING! All changes made in this file will be lost!
*****************************************************************************/

#include "a3m_lgm.h"
#if !defined(Q_MOC_OUTPUT_REVISION)
#error "The header file 'a3m_lgm.h' doesn't include <QObject>."
#elif Q_MOC_OUTPUT_REVISION != 62
#error "This file was generated using the moc from 4.6.3. It"
#error "cannot be used with the include files from this version of Qt."
#error "(The moc has changed too much.)"
#endif

QT_BEGIN_MOC_NAMESPACE
static const uint qt_meta_data_CA3mLgm[] = {

 // content:
       4,       // revision
       0,       // classname
       6,   14, // classinfo
       6,   26, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       3,       // signalCount

 // classinfo: key, value
      20,    8,
      27,    8,
      43,   37,
      59,   51,
      77,   70,
     158,   88,

 // signals: signature, parameters, type, tag, flags
     186,  177,  176,  176, 0x05,
     226,  207,  176,  176, 0x05,
     279,  258,  176,  176, 0x05,

 // slots: signature, parameters, type, tag, flags
     309,  306,  176,  176, 0x0a,
     341,  337,  176,  176, 0x0a,
     363,  176,  176,  176, 0x09,

       0        // eod
};

static const char qt_meta_stringdata_CA3mLgm[] = {
    "CA3mLgm\0Letux Sàrl\0Author\0Copyright\0"
    "0.0.1\0Version\0a3m_lgm\0PluginName\0"
    "device\0PluginType\0"
    "Lecteur clavier permettant d'ouvrir une porte à l'aide d'un PIN code\0"
    "PluginDescription\0\0xmlEvent\0"
    "deviceEvent(QString)\0deviceId,in,status\0"
    "deviceInputChange(int,int,bool)\0"
    "deviceId,isConnected\0deviceConnection(int,bool)\0"
    "ba\0dispatchMessage(QByteArray)\0xml\0"
    "deviceAction(QString)\0tstcmd()\0"
};

const QMetaObject CA3mLgm::staticMetaObject = {
    { &QObject::staticMetaObject, qt_meta_stringdata_CA3mLgm,
      qt_meta_data_CA3mLgm, 0 }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &CA3mLgm::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *CA3mLgm::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *CA3mLgm::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_CA3mLgm))
        return static_cast<void*>(const_cast< CA3mLgm*>(this));
    if (!strcmp(_clname, "CDeviceInterface"))
        return static_cast< CDeviceInterface*>(const_cast< CA3mLgm*>(this));
    if (!strcmp(_clname, "com.letux.Horux.CDeviceInterface/1.0"))
        return static_cast< CDeviceInterface*>(const_cast< CA3mLgm*>(this));
    return QObject::qt_metacast(_clname);
}

int CA3mLgm::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QObject::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        switch (_id) {
        case 0: deviceEvent((*reinterpret_cast< QString(*)>(_a[1]))); break;
        case 1: deviceInputChange((*reinterpret_cast< int(*)>(_a[1])),(*reinterpret_cast< int(*)>(_a[2])),(*reinterpret_cast< bool(*)>(_a[3]))); break;
        case 2: deviceConnection((*reinterpret_cast< int(*)>(_a[1])),(*reinterpret_cast< bool(*)>(_a[2]))); break;
        case 3: dispatchMessage((*reinterpret_cast< QByteArray(*)>(_a[1]))); break;
        case 4: deviceAction((*reinterpret_cast< QString(*)>(_a[1]))); break;
        case 5: tstcmd(); break;
        default: ;
        }
        _id -= 6;
    }
    return _id;
}

// SIGNAL 0
void CA3mLgm::deviceEvent(QString _t1)
{
    void *_a[] = { 0, const_cast<void*>(reinterpret_cast<const void*>(&_t1)) };
    QMetaObject::activate(this, &staticMetaObject, 0, _a);
}

// SIGNAL 1
void CA3mLgm::deviceInputChange(int _t1, int _t2, bool _t3)
{
    void *_a[] = { 0, const_cast<void*>(reinterpret_cast<const void*>(&_t1)), const_cast<void*>(reinterpret_cast<const void*>(&_t2)), const_cast<void*>(reinterpret_cast<const void*>(&_t3)) };
    QMetaObject::activate(this, &staticMetaObject, 1, _a);
}

// SIGNAL 2
void CA3mLgm::deviceConnection(int _t1, bool _t2)
{
    void *_a[] = { 0, const_cast<void*>(reinterpret_cast<const void*>(&_t1)), const_cast<void*>(reinterpret_cast<const void*>(&_t2)) };
    QMetaObject::activate(this, &staticMetaObject, 2, _a);
}
QT_END_MOC_NAMESPACE
