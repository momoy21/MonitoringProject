@echo off
echo BAT CALLED AT %date% %time% >> C:\KIT\MonitoringProject\storage\logs\bat-test.log
"C:\php82\php.exe" "C:\KIT\MonitoringProject\artisan" schedule:run
