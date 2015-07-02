/****************************************************************************
** Meta object code from reading C++ file 'wizard.h'
**
** Created by: The Qt Meta Object Compiler version 63 (Qt 4.8.6)
**
** WARNING! All changes made in this file will be lost!
*****************************************************************************/

#include "../../addon/doxywizard/wizard.h"
#if !defined(Q_MOC_OUTPUT_REVISION)
#error "The header file 'wizard.h' doesn't include <QObject>."
#elif Q_MOC_OUTPUT_REVISION != 63
#error "This file was generated using the moc from 4.8.6. It"
#error "cannot be used with the include files from this version of Qt."
#error "(The moc has changed too much.)"
#endif

QT_BEGIN_MOC_NAMESPACE
static const uint qt_meta_data_TuneColorDialog[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
       1,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       0,       // signalCount

 // slots: signature, parameters, type, tag, flags
      29,   17,   16,   16, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_TuneColorDialog[] = {
    "TuneColorDialog\0\0hue,sat,val\0"
    "updateImage(int,int,int)\0"
};

void TuneColorDialog::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        TuneColorDialog *_t = static_cast<TuneColorDialog *>(_o);
        switch (_id) {
        case 0: _t->updateImage((*reinterpret_cast< int(*)>(_a[1])),(*reinterpret_cast< int(*)>(_a[2])),(*reinterpret_cast< int(*)>(_a[3]))); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData TuneColorDialog::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject TuneColorDialog::staticMetaObject = {
    { &QDialog::staticMetaObject, qt_meta_stringdata_TuneColorDialog,
      qt_meta_data_TuneColorDialog, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &TuneColorDialog::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *TuneColorDialog::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *TuneColorDialog::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_TuneColorDialog))
        return static_cast<void*>(const_cast< TuneColorDialog*>(this));
    return QDialog::qt_metacast(_clname);
}

int TuneColorDialog::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QDialog::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 1)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 1;
    }
    return _id;
}
static const uint qt_meta_data_ColorPicker[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
       2,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       1,       // signalCount

 // signals: signature, parameters, type, tag, flags
      19,   13,   12,   12, 0x05,

 // slots: signature, parameters, type, tag, flags
      39,   13,   12,   12, 0x0a,

       0        // eod
};

static const char qt_meta_stringdata_ColorPicker[] = {
    "ColorPicker\0\0h,s,g\0newHsv(int,int,int)\0"
    "setCol(int,int,int)\0"
};

void ColorPicker::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        ColorPicker *_t = static_cast<ColorPicker *>(_o);
        switch (_id) {
        case 0: _t->newHsv((*reinterpret_cast< int(*)>(_a[1])),(*reinterpret_cast< int(*)>(_a[2])),(*reinterpret_cast< int(*)>(_a[3]))); break;
        case 1: _t->setCol((*reinterpret_cast< int(*)>(_a[1])),(*reinterpret_cast< int(*)>(_a[2])),(*reinterpret_cast< int(*)>(_a[3]))); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData ColorPicker::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject ColorPicker::staticMetaObject = {
    { &QWidget::staticMetaObject, qt_meta_stringdata_ColorPicker,
      qt_meta_data_ColorPicker, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &ColorPicker::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *ColorPicker::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *ColorPicker::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_ColorPicker))
        return static_cast<void*>(const_cast< ColorPicker*>(this));
    return QWidget::qt_metacast(_clname);
}

int ColorPicker::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QWidget::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 2)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 2;
    }
    return _id;
}

// SIGNAL 0
void ColorPicker::newHsv(int _t1, int _t2, int _t3)
{
    void *_a[] = { 0, const_cast<void*>(reinterpret_cast<const void*>(&_t1)), const_cast<void*>(reinterpret_cast<const void*>(&_t2)), const_cast<void*>(reinterpret_cast<const void*>(&_t3)) };
    QMetaObject::activate(this, &staticMetaObject, 0, _a);
}
static const uint qt_meta_data_Step1[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
       9,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       0,       // signalCount

 // slots: signature, parameters, type, tag, flags
       7,    6,    6,    6, 0x08,
      25,    6,    6,    6, 0x08,
      48,    6,    6,    6, 0x08,
      73,   68,    6,    6, 0x08,
     102,   97,    6,    6, 0x08,
     131,  127,    6,    6, 0x08,
     161,  157,    6,    6, 0x08,
     183,  157,    6,    6, 0x08,
     210,    6,    6,    6, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_Step1[] = {
    "Step1\0\0selectSourceDir()\0"
    "selectDestinationDir()\0selectProjectIcon()\0"
    "name\0setProjectName(QString)\0desc\0"
    "setProjectBrief(QString)\0num\0"
    "setProjectNumber(QString)\0dir\0"
    "setSourceDir(QString)\0setDestinationDir(QString)\0"
    "setRecursiveScan(int)\0"
};

void Step1::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        Step1 *_t = static_cast<Step1 *>(_o);
        switch (_id) {
        case 0: _t->selectSourceDir(); break;
        case 1: _t->selectDestinationDir(); break;
        case 2: _t->selectProjectIcon(); break;
        case 3: _t->setProjectName((*reinterpret_cast< const QString(*)>(_a[1]))); break;
        case 4: _t->setProjectBrief((*reinterpret_cast< const QString(*)>(_a[1]))); break;
        case 5: _t->setProjectNumber((*reinterpret_cast< const QString(*)>(_a[1]))); break;
        case 6: _t->setSourceDir((*reinterpret_cast< const QString(*)>(_a[1]))); break;
        case 7: _t->setDestinationDir((*reinterpret_cast< const QString(*)>(_a[1]))); break;
        case 8: _t->setRecursiveScan((*reinterpret_cast< int(*)>(_a[1]))); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData Step1::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject Step1::staticMetaObject = {
    { &QWidget::staticMetaObject, qt_meta_stringdata_Step1,
      qt_meta_data_Step1, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &Step1::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *Step1::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *Step1::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_Step1))
        return static_cast<void*>(const_cast< Step1*>(this));
    return QWidget::qt_metacast(_clname);
}

int Step1::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QWidget::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 9)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 9;
    }
    return _id;
}
static const uint qt_meta_data_Step2[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
       3,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       0,       // signalCount

 // slots: signature, parameters, type, tag, flags
      14,    7,    6,    6, 0x08,
      31,    7,    6,    6, 0x08,
      48,    7,    6,    6, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_Step2[] = {
    "Step2\0\0choice\0optimizeFor(int)\0"
    "extractMode(int)\0changeCrossRefState(int)\0"
};

void Step2::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        Step2 *_t = static_cast<Step2 *>(_o);
        switch (_id) {
        case 0: _t->optimizeFor((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 1: _t->extractMode((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 2: _t->changeCrossRefState((*reinterpret_cast< int(*)>(_a[1]))); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData Step2::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject Step2::staticMetaObject = {
    { &QWidget::staticMetaObject, qt_meta_stringdata_Step2,
      qt_meta_data_Step2, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &Step2::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *Step2::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *Step2::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_Step2))
        return static_cast<void*>(const_cast< Step2*>(this));
    return QWidget::qt_metacast(_clname);
}

int Step2::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QWidget::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 3)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 3;
    }
    return _id;
}
static const uint qt_meta_data_Step3[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
       9,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       0,       // signalCount

 // slots: signature, parameters, type, tag, flags
       7,    6,    6,    6, 0x08,
      28,    6,    6,    6, 0x08,
      50,    6,    6,    6, 0x08,
      69,    6,    6,    6, 0x08,
      88,    6,    6,    6, 0x08,
     107,    6,    6,    6, 0x08,
     129,    6,    6,    6, 0x08,
     149,    6,    6,    6, 0x08,
     170,    6,    6,    6, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_Step3[] = {
    "Step3\0\0setHtmlEnabled(bool)\0"
    "setLatexEnabled(bool)\0setManEnabled(int)\0"
    "setRtfEnabled(int)\0setXmlEnabled(int)\0"
    "setSearchEnabled(int)\0setHtmlOptions(int)\0"
    "setLatexOptions(int)\0tuneColorDialog()\0"
};

void Step3::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        Step3 *_t = static_cast<Step3 *>(_o);
        switch (_id) {
        case 0: _t->setHtmlEnabled((*reinterpret_cast< bool(*)>(_a[1]))); break;
        case 1: _t->setLatexEnabled((*reinterpret_cast< bool(*)>(_a[1]))); break;
        case 2: _t->setManEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 3: _t->setRtfEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 4: _t->setXmlEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 5: _t->setSearchEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 6: _t->setHtmlOptions((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 7: _t->setLatexOptions((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 8: _t->tuneColorDialog(); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData Step3::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject Step3::staticMetaObject = {
    { &QWidget::staticMetaObject, qt_meta_stringdata_Step3,
      qt_meta_data_Step3, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &Step3::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *Step3::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *Step3::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_Step3))
        return static_cast<void*>(const_cast< Step3*>(this));
    return QWidget::qt_metacast(_clname);
}

int Step3::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QWidget::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 9)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 9;
    }
    return _id;
}
static const uint qt_meta_data_Step4[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
       8,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       0,       // signalCount

 // slots: signature, parameters, type, tag, flags
       7,    6,    6,    6, 0x08,
      37,   31,    6,    6, 0x08,
      63,   31,    6,    6, 0x08,
      97,   31,    6,    6, 0x08,
     131,   31,    6,    6, 0x08,
     159,   31,    6,    6, 0x08,
     190,   31,    6,    6, 0x08,
     215,   31,    6,    6, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_Step4[] = {
    "Step4\0\0diagramModeChanged(int)\0state\0"
    "setClassGraphEnabled(int)\0"
    "setCollaborationGraphEnabled(int)\0"
    "setGraphicalHierarchyEnabled(int)\0"
    "setIncludeGraphEnabled(int)\0"
    "setIncludedByGraphEnabled(int)\0"
    "setCallGraphEnabled(int)\0"
    "setCallerGraphEnabled(int)\0"
};

void Step4::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        Step4 *_t = static_cast<Step4 *>(_o);
        switch (_id) {
        case 0: _t->diagramModeChanged((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 1: _t->setClassGraphEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 2: _t->setCollaborationGraphEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 3: _t->setGraphicalHierarchyEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 4: _t->setIncludeGraphEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 5: _t->setIncludedByGraphEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 6: _t->setCallGraphEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 7: _t->setCallerGraphEnabled((*reinterpret_cast< int(*)>(_a[1]))); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData Step4::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject Step4::staticMetaObject = {
    { &QWidget::staticMetaObject, qt_meta_stringdata_Step4,
      qt_meta_data_Step4, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &Step4::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *Step4::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *Step4::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_Step4))
        return static_cast<void*>(const_cast< Step4*>(this));
    return QWidget::qt_metacast(_clname);
}

int Step4::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QWidget::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 8)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 8;
    }
    return _id;
}
static const uint qt_meta_data_Wizard[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
       5,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       1,       // signalCount

 // signals: signature, parameters, type, tag, flags
       8,    7,    7,    7, 0x05,

 // slots: signature, parameters, type, tag, flags
      15,    7,    7,    7, 0x0a,
      31,   25,    7,    7, 0x08,
      80,    7,    7,    7, 0x08,
      92,    7,    7,    7, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_Wizard[] = {
    "Wizard\0\0done()\0refresh()\0item,\0"
    "activateTopic(QTreeWidgetItem*,QTreeWidgetItem*)\0"
    "nextTopic()\0prevTopic()\0"
};

void Wizard::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        Wizard *_t = static_cast<Wizard *>(_o);
        switch (_id) {
        case 0: _t->done(); break;
        case 1: _t->refresh(); break;
        case 2: _t->activateTopic((*reinterpret_cast< QTreeWidgetItem*(*)>(_a[1])),(*reinterpret_cast< QTreeWidgetItem*(*)>(_a[2]))); break;
        case 3: _t->nextTopic(); break;
        case 4: _t->prevTopic(); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData Wizard::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject Wizard::staticMetaObject = {
    { &QSplitter::staticMetaObject, qt_meta_stringdata_Wizard,
      qt_meta_data_Wizard, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &Wizard::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *Wizard::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *Wizard::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_Wizard))
        return static_cast<void*>(const_cast< Wizard*>(this));
    return QSplitter::qt_metacast(_clname);
}

int Wizard::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QSplitter::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 5)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 5;
    }
    return _id;
}

// SIGNAL 0
void Wizard::done()
{
    QMetaObject::activate(this, &staticMetaObject, 0, 0);
}
QT_END_MOC_NAMESPACE
