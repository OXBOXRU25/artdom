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
CODE="404.php archive.php comments.php footer.php functions.php header.php index.php page.php search.php sidebar.php single.php style.css css js inc template-parts templates"
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
ssh -i "$KEY" -o StrictHostKeyChecking=accept-new "$HOST" \
  "chown -R www-data:www-data $DEST && find $DEST -type d -exec chmod 755 {} + && find $DEST -type f -exec chmod 644 {} +" || {
  echo "  права поправить не вышло"; exit 1;
}

echo "==> Готово. На сайте нажмите Ctrl+F5 — статика кешируется на месяц."
