@ECHO OFF
SET BIN_TARGET=%~dp0/../vendor/justinrainbow/json-schema/bin/validate-json
php "%BIN_TARGET%" %*
