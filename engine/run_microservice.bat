@echo off
echo Parfum de Climat — Starting Recommendation Engine (FastAPI microservice)
echo =========================================================================
echo.
echo Listening on http://127.0.0.1:8001
echo Make sure ENGINE_MODE=microservice is set in backend/.env
echo.
echo Press Ctrl+C to stop.
echo.

if not exist "venv\" (
    echo ERROR: Virtual environment not found.
    echo Run setup.bat first.
    pause
    exit /b 1
)

call venv\Scripts\activate.bat
python recommendation_engine.py --serve
