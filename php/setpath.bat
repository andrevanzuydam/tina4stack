@echo off
SET mypath=%~dp0
SET PATH=%PATH%;%mypath%
cd ..\phpdoc
echo "Setting the paths done"
echo "Running Install"
composer install