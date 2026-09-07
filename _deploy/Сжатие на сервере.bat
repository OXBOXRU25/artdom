@echo off
rem Включает сжатие статики на боевом сервере АРТДОМа.
rem
rem Сам находит ssh: в PowerShell у Павлона его в PATH нет — он приходит
rem вместе с Git, а каталог Git\usr\bin виден только из Git Bash. Ровно на
rem этом развалилась выкладка 06.09.2026 и команда для nginx 08.09.2026.
rem Поэтому путь ищем, а не надеемся на PATH.
rem
rem Сам скрипт уезжает на сервер через stdin, поэтому кавычки терять негде.
chcp 65001 >nul
setlocal

set "SSH="
for %%P in (
  "D:\AI\Git\usr\bin\ssh.exe"
  "C:\Program Files\Git\usr\bin\ssh.exe"
  "C:\Windows\System32\OpenSSH\ssh.exe"
) do if exist %%P if not defined SSH set "SSH=%%~P"

if not defined SSH (
  echo Не нашёл ssh.exe. Проверенные места:
  echo   D:\AI\Git\usr\bin\ssh.exe
  echo   C:\Program Files\Git\usr\bin\ssh.exe
  echo   C:\Windows\System32\OpenSSH\ssh.exe
  pause
  exit /b 1
)
echo Использую ssh: %SSH%

set "KEY=%USERPROFILE%\.ssh\id_ed25519_game"
if not exist "%KEY%" (
  echo Не нашёл ключ: %KEY%
  pause
  exit /b 1
)

set "SCRIPT=%~dp0nginx-gzip.sh"
if not exist "%SCRIPT%" (
  echo Не нашёл %SCRIPT%
  pause
  exit /b 1
)

"%SSH%" -i "%KEY%" -o StrictHostKeyChecking=accept-new root@5.129.195.139 "bash -s" < "%SCRIPT%"

echo.
echo Готово. Окно можно закрыть.
pause
