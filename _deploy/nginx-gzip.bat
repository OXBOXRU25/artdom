@echo off
rem Vkluchaet szhatie statiki na boevom servere ARTDOM.
rem
rem Tekst zdes latinicey namerenno: .bat lezhit v UTF-8, a cmd chitaet ego
rem v starой kodirovke - kirillica v kommentariyah i echo lomaet razbor strok.
rem Proveryeno 08.09.2026: s kirillicey fayl ne zapuskalsya voobshe.
rem
rem ssh ishem po spisku putey: v PowerShell u Pavlona ego v PATH net, on
rem prihodit vmeste s Git. Sam skript uezzhaet na server cherez stdin,
rem poetomu kavychki teryat negde.
setlocal

set "SSH="
for %%P in (
  "D:\AI\Git\usr\bin\ssh.exe"
  "C:\Program Files\Git\usr\bin\ssh.exe"
  "C:\Windows\System32\OpenSSH\ssh.exe"
) do if exist %%P if not defined SSH set "SSH=%%~P"

if not defined SSH (
  echo ssh.exe ne nayden. Iskal zdes:
  echo   D:\AI\Git\usr\bin\ssh.exe
  echo   C:\Program Files\Git\usr\bin\ssh.exe
  echo   C:\Windows\System32\OpenSSH\ssh.exe
  exit /b 1
)
echo ssh: %SSH%

set "KEY=%USERPROFILE%\.ssh\id_ed25519_game"
if not exist "%KEY%" (
  echo Klyuch ne nayden: %KEY%
  exit /b 1
)

set "SCRIPT=%~dp0nginx-gzip.sh"
if not exist "%SCRIPT%" (
  echo Ne nayden %SCRIPT%
  exit /b 1
)

"%SSH%" -i "%KEY%" -o StrictHostKeyChecking=accept-new root@5.129.195.139 "bash -s" < "%SCRIPT%"
exit /b %ERRORLEVEL%
