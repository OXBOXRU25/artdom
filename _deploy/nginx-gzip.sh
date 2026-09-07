#!/usr/bin/env bash
#
# Включает сжатие статики в конфиге АРТДОМа на боевом.
#
# Уезжает на сервер через stdin (ssh ... bash -s), поэтому здесь можно писать
# обычный bash: ни PowerShell, ни ssh его не разбирают и кавычки не портят.
# Прошлая попытка отдать то же самое одной строкой развалилась ровно на
# экранировании — три уровня кавычек подряд.
#
# Зачем: в общем /etc/nginx/nginx.conf строка gzip_types закомментирована,
# поэтому сжимался только text/html. Замер боевого 08.09.2026:
#   CSS  style.min.css  71.3 КБ без сжатия (с gzip ожидается ~14)
#   JS   main.js        61.3 КБ без сжатия (с gzip ожидается ~18)
# Отрисовка страницы ждёт CSS, и эти килобайты — главное, что держало LCP.
#
# Правится ТОЛЬКО конфиг АРТДОМа. Соседние сайты на сервере не трогаем: у них
# свои gzip-настройки в их собственных server-блоках.
set -u

CFG=/etc/nginx/sites-available/artdom.oxboxdigital.ru

echo "==> Сжатие статики для АРТДОМа"

if [ ! -f "$CFG" ]; then
  echo "  НЕ НАЙДЕН конфиг: $CFG"
  exit 1
fi

if grep -q "gzip_types" "$CFG"; then
  echo "  gzip_types уже прописан — файл не трогаю."
else
  BAK="$CFG.bak-$(date +%Y%m%d-%H%M%S)"
  cp "$CFG" "$BAK"
  echo "  копия: $BAK"

  # Вставляем ПОСЛЕ первой строки "server {" — то есть внутрь блока, который
  # обслуживает https. Второй server-блок в файле только редиректит с http,
  # ему сжатие ни к чему.
  awk '
    !done && /^server[ \t]*\{/ {
      print
      print "    # Сжатие статики: в общем nginx.conf gzip_types закомментирован,"
      print "    # поэтому жался только text/html, а CSS уходил 71 КБ вместо ~14."
      print "    gzip on;"
      print "    gzip_vary on;"
      print "    gzip_comp_level 6;"
      print "    gzip_min_length 1024;"
      print "    gzip_types text/css application/javascript application/json image/svg+xml text/xml application/xml;"
      done = 1
      next
    }
    { print }
  ' "$CFG" > "$CFG.new"

  if ! grep -q "gzip_types" "$CFG.new"; then
    echo "  ВСТАВКА НЕ УДАЛАСЬ: строки server { в файле не нашлось. Ничего не меняю."
    rm -f "$CFG.new"
    exit 1
  fi

  mv "$CFG.new" "$CFG"
  echo "  директивы вставлены"
fi

echo "==> Проверка конфига"
if ! nginx -t; then
  echo "  КОНФИГ НЕ ПРОШЁЛ ПРОВЕРКУ. nginx НЕ перезагружен, сайт работает по-старому."
  echo "  Вернуть как было:  cp $CFG.bak-* $CFG"
  exit 1
fi

systemctl reload nginx
echo "==> nginx перезагружен"

echo "==> Что отдаёт сервер теперь"
for f in css/style.min.css js/main.js; do
  enc=$(curl -sI -H 'Accept-Encoding: gzip' "https://artdom.oxboxdigital.ru/wp-content/themes/artdom/$f" | tr -d '\r' | awk -F': ' '/^[Cc]ontent-[Ee]ncoding/{print $2}')
  echo "  $f -> ${enc:-СЖАТИЯ НЕТ}"
done
