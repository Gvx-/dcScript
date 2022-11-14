@echo off
set errorCode=0

set project=%~dp0..\phpStan.neon
if not exist %project% goto :error
echo phpStan analyse en cours...
php %userprofile%\vendor\phpstan\phpstan\phpstan analyse -c "%project%"
goto :endBatch

:error
echo.
echo /!\ -- Le fichier %project% n'a pas ete trouve.
echo.
set errorCode=1

:: https://askcodez.com/conditionnel-pause-pas-en-ligne-de-commande.html
:endBatch
echo.%cmdcmdline% | find /I "%~0" >nul
if not errorlevel 1 pause
exit /b %errorCode%
