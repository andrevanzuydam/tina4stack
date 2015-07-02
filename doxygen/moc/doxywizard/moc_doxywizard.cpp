/****************************************************************************
** Meta object code from reading C++ file 'doxywizard.h'
**
** Created by: The Qt Meta Object Compiler version 63 (Qt 4.8.6)
**
** WARNING! All changes made in this file will be lost!
*****************************************************************************/

#include "../../addon/doxywizard/doxywizard.h"
#if !defined(Q_MOC_OUTPUT_REVISION)
#error "The header file 'doxywizard.h' doesn't include <QObject>."
#elif Q_MOC_OUTPUT_REVISION != 63
#error "This file was generated using the moc from 4.8.6. It"
#error "cannot be used with the include files from this version of Qt."
#error "(The moc has changed too much.)"
#endif

QT_BEGIN_MOC_NAMESPACE
static const uint qt_meta_data_MainWindow[] = {

 // content:
       6,       // revision
       0,       // classname
       0,    0, // classinfo
      21,   14, // methods
       0,    0, // properties
       0,    0, // enums/sets
       0,    0, // constructors
       0,       // flags
       0,       // signalCount

 // slots: signature, parameters, type, tag, flags
      12,   11,   11,   11, 0x0a,
      21,   11,   11,   11, 0x0a,
      29,   11,   11,   11, 0x0a,
      47,   11,   42,   11, 0x0a,
      60,   11,   42,   11, 0x0a,
      75,   11,   11,   11, 0x0a,
      90,   11,   11,   11, 0x0a,
     108,   11,   11,   11, 0x0a,
     123,   11,   11,   11, 0x0a,
     137,  130,   11,   11, 0x08,
     158,   11,   11,   11, 0x08,
     177,   11,   11,   11, 0x08,
     196,   11,   11,   11, 0x08,
     209,   11,   11,   11, 0x08,
     222,   11,   11,   11, 0x08,
     236,   11,   11,   11, 0x08,
     253,   11,   11,   11, 0x08,
     263,   11,   11,   11, 0x08,
     278,   11,   11,   11, 0x08,
     294,   11,   11,   11, 0x08,
     308,   11,   11,   11, 0x08,

       0        // eod
};

static const char qt_meta_stringdata_MainWindow[] = {
    "MainWindow\0\0manual()\0about()\0openConfig()\0"
    "bool\0saveConfig()\0saveConfigAs()\0"
    "makeDefaults()\0resetToDefaults()\0"
    "selectTab(int)\0quit()\0action\0"
    "openRecent(QAction*)\0selectWorkingDir()\0"
    "updateWorkingDir()\0runDoxygen()\0"
    "readStdout()\0runComplete()\0showHtmlOutput()\0"
    "saveLog()\0showSettings()\0configChanged()\0"
    "clearRecent()\0selectRunTab()\0"
};

void MainWindow::qt_static_metacall(QObject *_o, QMetaObject::Call _c, int _id, void **_a)
{
    if (_c == QMetaObject::InvokeMetaMethod) {
        Q_ASSERT(staticMetaObject.cast(_o));
        MainWindow *_t = static_cast<MainWindow *>(_o);
        switch (_id) {
        case 0: _t->manual(); break;
        case 1: _t->about(); break;
        case 2: _t->openConfig(); break;
        case 3: { bool _r = _t->saveConfig();
            if (_a[0]) *reinterpret_cast< bool*>(_a[0]) = _r; }  break;
        case 4: { bool _r = _t->saveConfigAs();
            if (_a[0]) *reinterpret_cast< bool*>(_a[0]) = _r; }  break;
        case 5: _t->makeDefaults(); break;
        case 6: _t->resetToDefaults(); break;
        case 7: _t->selectTab((*reinterpret_cast< int(*)>(_a[1]))); break;
        case 8: _t->quit(); break;
        case 9: _t->openRecent((*reinterpret_cast< QAction*(*)>(_a[1]))); break;
        case 10: _t->selectWorkingDir(); break;
        case 11: _t->updateWorkingDir(); break;
        case 12: _t->runDoxygen(); break;
        case 13: _t->readStdout(); break;
        case 14: _t->runComplete(); break;
        case 15: _t->showHtmlOutput(); break;
        case 16: _t->saveLog(); break;
        case 17: _t->showSettings(); break;
        case 18: _t->configChanged(); break;
        case 19: _t->clearRecent(); break;
        case 20: _t->selectRunTab(); break;
        default: ;
        }
    }
}

const QMetaObjectExtraData MainWindow::staticMetaObjectExtraData = {
    0,  qt_static_metacall 
};

const QMetaObject MainWindow::staticMetaObject = {
    { &QMainWindow::staticMetaObject, qt_meta_stringdata_MainWindow,
      qt_meta_data_MainWindow, &staticMetaObjectExtraData }
};

#ifdef Q_NO_DATA_RELOCATION
const QMetaObject &MainWindow::getStaticMetaObject() { return staticMetaObject; }
#endif //Q_NO_DATA_RELOCATION

const QMetaObject *MainWindow::metaObject() const
{
    return QObject::d_ptr->metaObject ? QObject::d_ptr->metaObject : &staticMetaObject;
}

void *MainWindow::qt_metacast(const char *_clname)
{
    if (!_clname) return 0;
    if (!strcmp(_clname, qt_meta_stringdata_MainWindow))
        return static_cast<void*>(const_cast< MainWindow*>(this));
    return QMainWindow::qt_metacast(_clname);
}

int MainWindow::qt_metacall(QMetaObject::Call _c, int _id, void **_a)
{
    _id = QMainWindow::qt_metacall(_c, _id, _a);
    if (_id < 0)
        return _id;
    if (_c == QMetaObject::InvokeMetaMethod) {
        if (_id < 21)
            qt_static_metacall(this, _c, _id, _a);
        _id -= 21;
    }
    return _id;
}
QT_END_MOC_NAMESPACE
