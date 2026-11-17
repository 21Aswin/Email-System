@echo off
title Email-System Webmail
cd /d "C:\Users\s8107\Desktop\project\Email-System-main"
echo Starting Email Webmail on http://127.0.0.1:8000 ...
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
pause
