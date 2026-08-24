@echo off
chcp 65001 >nul
title Storozh ARTDOM
echo Storozh zapushchen. Pravki uezzhayut na artdom.oxboxdigital.ru sami.
echo Zakryt okno - Ctrl+C ili krestik.
echo.
"D:\AI\nodejs\node.exe" "D:\AI\Artdom\_deploy\watch.mjs"
pause
