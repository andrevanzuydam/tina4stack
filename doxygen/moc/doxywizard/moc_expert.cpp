/****************************************************************************
** Meta object code from reading C++ file 'expert.h'
**
** Created by: The Qt Meta Object Compiler version 63 (Qt 4.8.6)
**
** WARNING! All changes made in this file will be lost!
*****************************************************************************/

#include "../../addon/doxywizard/expert.h"
#if !defined(Q_MOC_OUTPUT_REVISION)
#error "The header file 'expert.h' doesn't include <QObject>."
#elif Q_MOC_OUTPUT_REVISION != 63
#error "This file was generated using the moc from 4.8.6. It"
#error "cannot be used with the include files from this version of Qt."
#error "(The moc has changed too much.)"
#endif

QT_BEGIN_MOC_NAMESPACE
static const uint qt_meta_data_Expert[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
       8,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       2,       // signalCount

 // signals: signature, parameters, type, tag, flags
       8,    7,    7,    7, 0x05,
      18,    7,    7,    7, 0x05,

 // slots: signature, parameters, type, tag, flags
      27,   25,    7,    7, 0x0a,
      90,   85,   76,    7, 0x0a,
     122,    7,    7,    7, 0x0a,
     132,    7,    7,    7, 0x08,
     149,    7,    7,    7, 0x08,
     161,    7,    7,    7, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_Expert[] = {
    "Expert\0\0changed()\0done()\0,\0"
    "activateTopic(QTreeWidgetItem*,QTreeWidgetItem*)\0"
    "QWidget*\0elem\0createTopicWidget(QDomElement&)\0"
    "refresh()\0showHelp(Input*)\0nextTopic()\0"
    "prevTopic()\0"
};

void Expert::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        Expert *_t = static_cast<Expert *>(_o);
        switch (_id) {
        case 0: _t->changed(); break;
        case 1: _t->done(); break;
        case 2: _t->activateTopic((*reinterpret_cast< QTreeWidgetItem*(*)>(_a[1])),(*reinterpret_cast< QTreeWidgetItem*(*)>(_a[2]))); break;
        case 3: { QWidget* _r = _t->createTopicWidget((*reinterpret_cast< QDomElement(*)>(_a[1])));
            if (_a[0]) *reinterpret_cast< QWidget**>(_a[0]) = _r; }  break;
        case 4: _t->refresh(); break;
        case 5: _t->showHelp((*reinterpret_cast< Input*(*)>(_a[1]))); break;
        case 6: _t->nextTopic(); break;
        case 7: _t->prevTopic(); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData Expert::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject Expert::staticMetaObject = {
    { &QSplitter::staticMetaObject, qt_meta_stringdata_Expert,
      qt_meta_data_Expert, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &Expert::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *Expert::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *Expert::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_Expert))
        return static_cast<void*>(const_cast< Expert*>(this));
    if (!strcmp(_clname, "DocIntf"))
        return static_cast< DocIntf*>(const_cast< Expert*>(this));
    return QSplitter::qt_metacast(_clname);
}

int Expert::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QSplitter::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 8)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 8;
    }
    return _id;
}

// SIGNAL 0
void Expert::changed()
{
    QMetaObject::activate(this, &staticMetaObject, 0, 0);
}

// SIGNAL 1
void Expert::done()
{
    QMetaObject::activate(this, &staticMetaObject, 1, 0);
}
QT_END_MOC_NAMESPACE
