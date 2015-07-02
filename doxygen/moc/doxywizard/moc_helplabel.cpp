/****************************************************************************
** Meta object code from reading C++ file 'helplabel.h'
**
** Created by: The Qt Meta Object Compiler version 63 (Qt 4.8.6)
**
** WARNING! All changes made in this file will be lost!
*****************************************************************************/

#include "../../addon/doxywizard/helplabel.h"
#if !defined(Q_MOC_OUTPUT_REVISION)
#error "The header file 'helplabel.h' doesn't include <QObject>."
#elif Q_MOC_OUTPUT_REVISION != 63
#error "This file was generated using the moc from 4.8.6. It"
#error "cannot be used with the include files from this version of Qt."
#error "(The moc has changed too much.)"
#endif

QT_BEGIN_MOC_NAMESPACE
static const uint qt_meta_data_HelpLabel[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
       3,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       2,       // signalCount

 // signals: signature, parameters, type, tag, flags
      11,   10,   10,   10, 0x05,
      19,   10,   10,   10, 0x05,

 // slots: signature, parameters, type, tag, flags
      29,   27,   10,   10, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_HelpLabel[] = {
    "HelpLabel\0\0enter()\0reset()\0p\0"
    "showMenu(QPoint)\0"
};

void HelpLabel::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        HelpLabel *_t = static_cast<HelpLabel *>(_o);
        switch (_id) {
        case 0: _t->enter(); break;
        case 1: _t->reset(); break;
        case 2: _t->showMenu((*reinterpret_cast< const QPoint(*)>(_a[1]))); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData HelpLabel::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject HelpLabel::staticMetaObject = {
    { &QLabel::staticMetaObject, qt_meta_stringdata_HelpLabel,
      qt_meta_data_HelpLabel, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &HelpLabel::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *HelpLabel::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *HelpLabel::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_HelpLabel))
        return static_cast<void*>(const_cast< HelpLabel*>(this));
    return QLabel::qt_metacast(_clname);
}

int HelpLabel::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QLabel::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 3)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 3;
    }
    return _id;
}

// SIGNAL 0
void HelpLabel::enter()
{
    QMetaObject::activate(this, &staticMetaObject, 0, 0);
}

// SIGNAL 1
void HelpLabel::reset()
{
    QMetaObject::activate(this, &staticMetaObject, 1, 0);
}
QT_END_MOC_NAMESPACE
