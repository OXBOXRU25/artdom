#!/usr/bin/env bash
#
# Выкладка темы АРТДОМ на боевой сервер.
#
#   bash push.sh        только код: php, css, js (~200 КБ, пара секунд)
#   bash push.sh all    плюс картинки, видео, шрифты (~1.9 МБ)
#
# Работаем локально на 127.0.0.1:8080, а эта команда переносит накопленное
# на artdom.oxboxdigital.ru. Гонять её на каждую правку не нужно — раз в пачку.
#
set -u

KEY="$HOME/.ssh/id_ed25519_game"
HOST="root@5.129.195.139"
DEST="/var/www/artdom/wp-content/themes/artdom"
SRC="D:/AI/Artdom/theme/artdom"

# Первая же строка вывода. Если её нет — скрипт вообще не запустился,
# а не тихо умер на середине.
echo "==> Выкладка темы АРТДОМ"

if [ ! -f "$KEY" ]; then echo "  нет ключа: $KEY"; exit 1; fi
if [ ! -d "$SRC" ]; then echo "  нет папки темы: $SRC"; exit 1; fi

cd "$SRC" || exit 1

# Код. Корневые php перечислены явно, папки уходят целиком.
# Корневые php НЕ перечисляем поимённо: новый шаблон (archive-тип.php,
# single-тип.php) в такой список забыть — секунда, а на сервере он тогда
# просто не появится, и WordPress молча откатится на шаблон базовой темы.
# Ровно так и вышло 25.08.2026: каталог отдавал 500, а файла там не было.
CODE="$(echo *.php) style.css css js inc template-parts templates"
MEDIA="fonts img video favicon.svg screenshot.png"

WHAT="$CODE"
if [ "${1:-code}" = "all" ]; then
  WHAT="$CODE $MEDIA"
  echo "  режим: всё, включая медиа"
else
  echo "  режим: только код (для медиа: bash push.sh all)"
fi

echo "  заливаю в $HOST:$DEST"
# shellcheck disable=SC2086
scp -q -i "$KEY" -o StrictHostKeyChecking=accept-new -r $WHAT "$HOST:$DEST/" || {
  echo "  scp не справился"; exit 1;
}

# Права: файлы приезжают от root, веб-серверу нужно их читать.
#
# Заодно один раз включаем сжатие для SVG. По умолчанию nginx его не жмёт, и
# точечная карта едет мегабайтом вместо полутора сотен килобайт. Проверка на
# наличие файла делает шаг однократным: перечитывание конфига на каждой
# выкладке стоило бы дороже, чем сам выигрыш.
#
# -n обязателен: без него ssh забирает стандартный ввод, и запущенный из
# сторожа скрипт зависает, ожидая того, чего никто не введёт.
ssh -n -i "$KEY" -o StrictHostKeyChecking=accept-new -o ConnectTimeout=15 "$HOST" \
  "chown -R www-data:www-data $DEST && find $DEST -type d -exec chmod 755 {} + && find $DEST -type f -exec chmod 644 {} + ; if [ ! -f /etc/nginx/conf.d/gzip-svg.conf ]; then echo 'gzip_types image/svg+xml;' > /etc/nginx/conf.d/gzip-svg.conf && nginx -t >/dev/null 2>&1 && systemctl reload nginx && echo '  сжатие SVG включено'; fi" || {
  echo "  права поправить не вышло"; exit 1;
}

echo "==> Готово. На сайте нажмите Ctrl+F5 — статика кешируется на месяц."
