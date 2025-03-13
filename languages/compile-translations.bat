@echo off
echo Compiling translations...
echo Please make sure you have Poedit installed and it's in your PATH
echo.

REM Check if Poedit is installed
where poedit >nul 2>nul
if %errorlevel% neq 0 (
    echo Error: Poedit is not installed or not in PATH
    echo Please install Poedit from https://poedit.net/
    pause
    exit /b 1
)

REM Compile the .po file to .mo
msgfmt.exe -v -o crawlaco-fa_IR.mo crawlaco-fa_IR.po

if %errorlevel% equ 0 (
    echo Translation compilation completed successfully!
) else (
    echo Error compiling translations
)

pause 