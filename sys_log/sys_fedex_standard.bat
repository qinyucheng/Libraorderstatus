@echo off
    setlocal enableextensions
    for /f "delims=" %%a in (
       'C:\Xampp\php\php.exe -f C:\xampp\htdocs\Libraorderstatus\sys_log\fedex_delivery_tracking_standard.php'
    ) do set "retVar=%%a"

    echo %retVar%
	For /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set mydate=%%c-%%a-%%b)
	For /f "tokens=1-2 delims=/:" %%a in ('time /t') do (set mytime=%%a%%b)
	echo %mydate%_%mytime%


echo %retVar%"success" >>C:\xampp\htdocs\Libraorderstatus\sys_log\logs\%mydate%_%mytime%.log";