@echo off
setlocal
cd /d "%~dp0"
echo.
echo ==========================================
echo   BAST - Build InfinityFree Package
echo ==========================================
echo.
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0deploy\infinityfree\build-package.ps1"
set "EXIT_CODE=%ERRORLEVEL%"
echo.
if not "%EXIT_CODE%"=="0" (
  echo Build FAILED with exit code %EXIT_CODE%.
  echo Kirim screenshot error-nya ke ChatGPT.
) else (
  echo Build selesai. Buka folder dist\artifact.
)
echo.
pause
exit /b %EXIT_CODE%
