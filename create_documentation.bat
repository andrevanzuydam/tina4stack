@echo off
cd phpdoc\bin
phpdoc.bat --ignore "Shape.php"  -d "..\..\web_root\tina4" -t "..\..\documentation" 
echo Done Creating Documentation...
pause