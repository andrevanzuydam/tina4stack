/****************************************************************************
** Meta object code from reading C++ file 'inputstring.h'
**
** Created by: The Qt Meta Object Compiler version 63 (Qt 4.8.6)
**
** WARNING! All changes made in this file will be lost!
*****************************************************************************/

#include "../../addon/doxywizard/inputstring.h"
#if !defined(Q_MOC_OUTPUT_REVISION)
#error "The header file 'inputstring.h' doesn't include <QObject>."
#elif Q_MOC_OUTPUT_REVISION != 63
#error "This file was generated using the moc from 4.8.6. It"
#error "cannot be used with the include files from this version of Qt."
#error "(The moc has changed too much.)"
#endif

QT_BEGIN_MOC_NAMESPACE
static const uint qt_meta_data_InputString[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
       7,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       2,       // signalCount

 // signals: signature, parameters, type, tag, flags
      13,   12,   12,   12, 0x05,
      23,   12,   12,   12, 0x05,

 // slots: signature, parameters, type, tag, flags
      40,   12,   12,   12, 0x0a,
      48,   12,   12,   12, 0x0a,
      66,   12,   12,   12, 0x08,
      75,   12,   12,   12, 0x08,
      83,   12,   12,   12, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_InputString[] = {
    "InputString\0\0changed()\0showHelp(Input*)\0"
    "reset()\0setValue(QString)\0browse()\0"
    "clear()\0help()\0"
};

void InputString::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        InputString *_t = static_cast<InputString *>(_o);
        switch (_id) {
        case 0: _t->changed(); break;
        case 1: _t->showHelp((*reinterpret_cast< Input*(*)>(_a[1]))); break;
        case 2: _t->reset(); break;
        case 3: _t->setValue((*reinterpret_cast< const QString(*)>(_a[1]))); break;
        case 4: _t->browse(); break;
        case 5: _t->clear(); break;
        case 6: _t->help(); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData InputString::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject InputString::staticMetaObject = {
    { &QObject::staticMetaObject, qt_meta_stringdata_InputString,
      qt_meta_data_InputString, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &InputString::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *InputString::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *InputString::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_InputString))
        return static_cast<void*>(const_cast< InputString*>(this));
    if (!strcmp(_clname, "Input"))
        return static_cast< Input*>(const_cast< InputString*>(this));
    return QObject::qt_metacast(_clname);
}

int InputString::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QObject::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 7)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 7;
    }
    return _id;
}

// SIGNAL 0
void InputString::changed()
{
    QMetaObject::activate(this, &staticMetaObject, 0, 0);
}

// SIGNAL 1
void InputString::showHelp(Input * _t1)
{
    void *_a[] = { 0, const_cast<void*>(reinterpret_cast<const void*>(&_t1)) };
    QMetaObject::activate(this, &staticMetaObject, 1, _a);
}
QT_END_MOC_NAMESPACE
