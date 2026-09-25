@echo off
chcp 65001 > nul
echo =======================================================================
echo   கைதடி வடக்கு கயிற்றசிட்டி அருள்மிகு கந்தசுவாமி தேவஸ்தானம்
echo   Temple Website Local Server with PHP & Browser Auto-launch
echo =======================================================================
echo.
echo Server starting at http://localhost:8000
echo Admin Login: http://localhost:8000/admin/login.php
echo.
echo Username: admin
echo Password: temple@123
echo.
echo Press Ctrl+C in this window to stop the server.
echo.
start http://localhost:8000
php -S localhost:8000 -t htdocs
pause
