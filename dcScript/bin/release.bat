::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: Release plugin dotclear
:: Author	: Gilles Grandveaux
:: Copyright: (c)2020 Gilles Grandveaux
:: License	: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
:: Changelog
::
::	* 15/08/2023	V0.1.4.5
::		Suppression de la copie du fchiers README
::	* 13/08/2023	V0.1.3.4
::		Changement de repertoire de destination
::		Ajout la copie des fichiers README, CHANGELOG, LICENCE, dcstore
::	* 21/04/2020	V0.1.2.3
::		Correction repertoire de destination
::	* 18/04/2020	V0.1.1.2
::		Correction exit sur message d'erreur et help
::		Correction protection des noms de fichiers
::	* 18/04/2020	V0.1.0.1
::		version initiale
::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
@echo off

setlocal enabledelayedexpansion
:: Version script
set VERSION=0.1.4.5

if /I "%~1"=="-h" goto :help
if /I "%~1"=="--help" goto :help
if /I "%~1"=="-v" echo %VERSION% & exit /b
if /I "%~1"=="--version" echo %VERSION% & exit /b
if NOT [%1]==[] goto :help

:: TODO: initialiser <source> et <dest>
set source=%~dp0..
call :parsePath nx %source% plugin
set dest=%USERPROFILE%\Documents\_mesDocs\Repositories\GitHub\%plugin%\%plugin%\

call :parsePath f %source% source
call :parsePath f %dest% dest

if not exist %source% call :fileError %source%
if not exist %dest% call :fileError %dest%

del /f /s /q "%dest%" >NUL
xcopy "%source%" "%dest%" /q /s /e /y /exclude:.\excludes.txt

xcopy "%source%\CHANGELOG.md" "%dest%\..\" /q /y
xcopy "%source%\LICENCE" "%dest%\..\" /q /y
xcopy "%source%\dcstore.xml" "%dest%\..\" /q /y

endlocal
exit /b

:help
echo --== HELP ==--
echo.
echo Usage: %~n0 [^<-v^>^|^<--version^>^|^<-h^>^|^<--help^>]
echo.
echo    ^<-v^>^|^<--version^>    : Display version
echo    ^<-h^>^|^<--help^>       : Display help
echo.
echo --== HELP ==--
pause
exit /b  1

:fileError <pathfilename>
echo --== ERREUR ==--
echo.
echo le fichier "%~1" n'a pas ete trouve.
echo.
echo --== ERREUR ==--
pause
exit 2

:parsePath <part> <pathname> <result>
set %3=%~f2
if /I "%~1"=="d" set %3=%~d2
if /I "%~1"=="p" set %3=%~p2
if /I "%~1"=="n" set %3=%~n2
if /I "%~1"=="x" set %3=%~x2
if /I "%~1"=="f" set %3=%~f2
if /I "%~1"=="s" set %3=%~s2
if /I "%~1"=="a" set %3=%~a2
if /I "%~1"=="t" set %3=%~t2
if /I "%~1"=="z" set %3=%~z2
if /I "%~1"=="dp" set %3=%~dp2
if /I "%~1"=="nx" set %3=%~nx2
if /I "%~1"=="pathSearch" set %3=%~$PATH:2	rem search in PATH
goto:eof
