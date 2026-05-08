@echo off
echo Parfum de Climat — Python Engine Setup
echo ========================================
echo.

REM Check Python is available
python --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Python not found in PATH.
    echo Install Python 3.11+ from https://www.python.org/downloads/
    echo Make sure to check "Add Python to PATH" during installation.
    pause
    exit /b 1
)

echo Python found:
python --version
echo.

REM Create virtual environment if it doesn't exist
if not exist "venv\" (
    echo Creating virtual environment...
    python -m venv venv
    if %errorlevel% neq 0 (
        echo ERROR: Failed to create virtual environment.
        pause
        exit /b 1
    )
    echo Virtual environment created.
) else (
    echo Virtual environment already exists, skipping creation.
)

echo.
echo Activating virtual environment and installing dependencies...
call venv\Scripts\activate.bat

pip install --upgrade pip
pip install -r requirements.txt

if %errorlevel% neq 0 (
    echo.
    echo ERROR: pip install failed. Check the output above.
    pause
    exit /b 1
)

echo.
echo ========================================
echo Setup complete!
echo.
echo To run the engine in microservice mode:
echo   run_microservice.bat
echo.
echo The engine also runs automatically as a subprocess
echo when Laravel is in ENGINE_MODE=subprocess (default).
echo ========================================
pause
