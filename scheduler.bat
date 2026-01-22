:: filepath: C:\KIT\MonitoringProject\scheduler.bat
@echo off
echo Starting Laravel Scheduler...
echo Press Ctrl+C to stop

:loop
php artisan schedule:run
timeout /t 60 /nobreak > nul
goto loop