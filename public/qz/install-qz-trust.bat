@echo off
:: Install Fablead ERP certificate so QZ Tray "Remember this decision" + Allow works.
:: Right-click this file -> Run as administrator

set "DEST=C:\Program Files\QZ Tray\override.crt"
set "SRC=%~dp0override.crt"

if not exist "%SRC%" (
  echo ERROR: override.crt not found next to this script.
  pause
  exit /b 1
)

if not exist "C:\Program Files\QZ Tray\" (
  echo ERROR: QZ Tray folder not found. Install QZ Tray first from https://qz.io/download/
  pause
  exit /b 1
)

copy /Y "%SRC%" "%DEST%"
if errorlevel 1 (
  echo ERROR: Copy failed. Run this file as Administrator.
  pause
  exit /b 1
)

echo.
echo OK - Certificate installed to:
echo   %DEST%
echo.
echo Next: Right-click green Q in system tray -^> Exit
echo Then start QZ Tray again from Start Menu.
echo After that, print again, tick Remember, click Allow.
echo.
pause
