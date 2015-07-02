/****************************************************************************
** Meta object code from reading C++ file 'inputstrlist.h'
**
** Created by: The Qt Meta Object Compiler version 63 (Qt 4.8.6)
**
** WARNING! All changes made in this file will be lost!
*****************************************************************************/

#include "../../addon/doxywizard/inputstrlist.h"
#if !defined(Q_MOC_OUTPUT_REVISION)
#error "The header file 'inputstrlist.h' doesn't include <QObject>."
#elif Q_MOC_OUTPUT_REVISION != 63
#error "This file was generated using the moc from 4.8.6. It"
#error "cannot be used with the include files from this version of Qt."
#error "(The moc has changed too much.)"
#endif

QT_BEGIN_MOC_NAMESPACE
static const uint qt_meta_data_InputStrList[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
      10,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       2,       // signalCount

 // signals: signature, parameters, type, tag, flags
      14,   13,   13,   13, 0x05,
      24,   13,   13,   13, 0x05,

 // slots: signature, parameters, type, tag, flags
      41,   13,   13,   13, 0x0a,
      49,   13,   13,   13, 0x08,
      61,   13,   13,   13, 0x08,
      73,   13,   13,   13, 0x08,
      90,   88,   13,   13, 0x08,
     110,   13,   13,   13, 0x08,
     124,   13,   13,   13, 0x08,
     136,   13,   13,   13, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_InputStrList[] = {
    "InputStrList\0\0changed()\0showHelp(Input*)\0"
    "reset()\0addString()\0delString()\0"
    "updateString()\0s\0selectText(QString)\0"
    "browseFiles()\0browseDir()\0help()\0"
};

void InputStrList::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        InputStrList *_t = static_cast<InputStrList *>(_o);
        switch (_id) {
        case 0: _t->changed(); break;
        case 1: _t->showHelp((*reinterpret_cast< Input*(*)>(_a[1]))); break;
        case 2: _t->reset(); break;
        case 3: _t->addString(); break;
        case 4: _t->delString(); break;
        case 5: _t->updateString(); break;
        case 6: _t->selectText((*reinterpret_cast< const QString(*)>(_a[1]))); break;
        case 7: _t->browseFiles(); break;
        case 8: _t->browseDir(); break;
        case 9: _t->help(); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData InputStrList::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject InputStrList::staticMetaObject = {
    { &QObject::staticMetaObject, qt_meta_stringdata_InputStrList,
      qt_meta_data_InputStrList, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &InputStrList::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *InputStrList::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *InputStrList::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_InputStrList))
        return static_cast<void*>(const_cast< InputStrList*>(this));
    if (!strcmp(_clname, "Input"))
        return static_cast< Input*>(const_cast< InputStrList*>(this));
    return QObject::qt_metacast(_clname);
}

int InputStrList::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QObject::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 10)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 10;
    }
    return _id;
}

// SIGNAL 0
void InputStrList::changed()
{
    QMetaObject::activate(this, &staticMetaObject, 0, 0);
}

// SIGNAL 1
void InputStrList::showHelp(Input * _t1)
{
    void *_a[] = { 0, const_cast<void*>(reinterpret_cast<const void*>(&_t1)) };
    QMetaObject::activate(this, &staticMetaObject, 1, _a);
}
QT_END_MOC_NAMESPACE
