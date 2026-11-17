@echo off
cd /d "%~dp0"
set "PATH=C:\Users\s8107\mingit\cmd;%PATH%"
echo ====================================================
echo Novalyx Cloud Webmail - Push Latest Changes to GitHub
echo ====================================================
echo.
echo Pushing latest commits to https://github.com/21Aswin/Email-System.git ...
echo.
git push origin main
if %ERRORLEVEL% EQU 0 (
    echo.
    echo ====================================================
    echo [SUCCESS] All latest changes pushed to GitHub successfully!
    echo Now your Render deployment will build the latest updated code!
    echo ====================================================
) else (
    echo.
    echo ====================================================
    echo [NOTE] If a GitHub login window pops up, please authenticate.
    echo If GitHub asks to rebase or force push, you can run:
    echo        git push origin main --force
    echo ====================================================
)
echo.
pause
