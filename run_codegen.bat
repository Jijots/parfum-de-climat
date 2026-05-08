@echo off
cd /d "C:\xampp\htdocs\Parfum de Climat\mobile"
echo CWD: %CD% > C:\xampp\htdocs\codegen_output.txt
C:\flutter\bin\dart.bat run build_runner build --delete-conflicting-outputs >> C:\xampp\htdocs\codegen_output.txt 2>&1
echo Exit: %ERRORLEVEL% >> C:\xampp\htdocs\codegen_output.txt
