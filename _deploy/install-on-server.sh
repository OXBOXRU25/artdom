#!/usr/bin/env bash
#
# Установка WordPress под artdom.oxboxdigital.ru на сервер, где nginx уже есть
# и уже работает другой сайт.
#
# Скрипт намеренно осторожный: он НИЧЕГО не трогает у существующих сайтов,
# только добавляет свой конфиг, свою базу и свою папку. Если находит панель
# управления или уже существующий конфиг для этого домена — останавливается
# и говорит, что делать, вместо того чтобы ломать.
#
# Запуск на сервере:
#   bash install-on-server.sh
#
set -euo pipefail

DOMAIN="artdom.oxboxdigital.ru"
ROOT="/var/www/artdom"
DBNAME="artdom"
DBUSER="artdom"
PHPSOCK=""

say()  { printf '\n\033[1;36m==> %s\033[0m\n' "$*"; }
warn() { printf '\033[1;33m !! %s\033[0m\n' "$*"; }
die()  { printf '\n\033[1;31m ХВАТИТ: %s\033[0m\n\n' "$*" >&2; exit 1; }

say "Поехали"

[ "$(id -u)" = "0" ] || die "запускать от root"

# --- 1. Не наступаем на панель управления -----------------------------------
# ВНИМАНИЕ на форму записи. Было "[ -d $p ] && die ..." — и скрипт молча
# закрывался на первой же несуществующей папке: при set -e неудачная левая
# часть && роняет весь список, а значит и скрипт. Никакого вывода при этом нет,
# что делает поломку особенно неприятной. Только if.
for p in /usr/local/mgr5 /usr/local/fastpanel2 /www/server/panel /usr/local/hestia; do
  if [ -d "$p" ]; then
    die "на сервере стоит панель управления ($p). Ставьте сайт через неё, иначе конфиги разъедутся."
  fi
done

# --- 2. Не наступаем на существующий сайт ------------------------------------
if ls /etc/nginx/sites-enabled/ 2>/dev/null | grep "$DOMAIN" >/dev/null; then
  die "конфиг nginx для $DOMAIN уже есть. Проверьте /etc/nginx/sites-available/$DOMAIN"
fi
if [ -e "$ROOT" ]; then
  die "папка $ROOT уже существует. Уберите её или поменяйте ROOT в скрипте."
fi

say "Что уже стоит на сервере"
nginx -v 2>&1 || die "nginx не найден"
php -v 2>/dev/null | head -1 || warn "PHP не найден, поставим"
(mysql --version 2>/dev/null || mariadb --version 2>/dev/null) || warn "MySQL/MariaDB не найдены, поставим"

# --- 3. Пакеты ---------------------------------------------------------------
say "Ставим недостающее"
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y -qq \
  php-fpm php-mysql php-curl php-gd php-mbstring php-xml php-zip php-intl php-imagick \
  curl unzip certbot python3-certbot-nginx

# СУБД ставим ТОЛЬКО если её нет. Слепой "apt-get install mariadb-server" на
# сервере с Oracle MySQL потребовал бы его удалить — и увёл бы за собой
# соседний сайт вместе с его базой.
if pgrep -x mysqld >/dev/null || pgrep -x mariadbd >/dev/null; then
  echo "СУБД уже работает, не трогаем"
else
  warn "СУБД не найдена, ставим MariaDB"
  apt-get install -y -qq mariadb-server
  systemctl enable --now mariadb
fi

# сокет php-fpm: версия зависит от Ubuntu, поэтому находим, а не угадываем
PHPSOCK="$(find /run/php -name 'php*-fpm.sock' 2>/dev/null | sort | tail -1)"
[ -n "$PHPSOCK" ] || die "не нашёл сокет php-fpm в /run/php"
echo "php-fpm: $PHPSOCK"

# Лимиты загрузки у PHP. Поднять их в nginx (client_max_body_size) мало:
# PHP режет сам, по умолчанию на 2 МБ, а плагин ACF весит 6.5 и тема больше мегабайта.
# Ошибка при этом вылезает уже в админке WordPress, далеко от места установки.
PHPCONF="$(dirname "$(dirname "$PHPSOCK")")"
PHPVER="$(basename "$PHPSOCK" | sed 's/^php//; s/-fpm.sock$//')"
PHPINI_DIR="/etc/php/${PHPVER}/fpm/conf.d"
if [ -d "$PHPINI_DIR" ]; then
  {
    echo "upload_max_filesize=64M"
    echo "post_max_size=64M"
    echo "memory_limit=256M"
    echo "max_execution_time=120"
  } > "${PHPINI_DIR}/99-artdom.ini"
  echo "лимиты PHP подняты: ${PHPINI_DIR}/99-artdom.ini"
  systemctl reload "php${PHPVER}-fpm" || warn "не смог перечитать php-fpm, перезапустите вручную"
else
  warn "не нашёл ${PHPINI_DIR}, лимиты PHP придётся поднять вручную"
fi

# --- 4. База -----------------------------------------------------------------
say "Заводим базу"
DBPASS="$(openssl rand -base64 24 | tr -d '/+=' | head -c 24)"
mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`${DBNAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DBUSER}'@'localhost' IDENTIFIED BY '${DBPASS}';
GRANT ALL PRIVILEGES ON \`${DBNAME}\`.* TO '${DBUSER}'@'localhost';
FLUSH PRIVILEGES;
SQL

# --- 5. WordPress ------------------------------------------------------------
say "Качаем WordPress"
mkdir -p "$ROOT"
cd /tmp
curl -fsSL https://ru.wordpress.org/latest-ru_RU.tar.gz -o wp.tar.gz
tar -xzf wp.tar.gz
cp -a wordpress/. "$ROOT"/
rm -rf wordpress wp.tar.gz

say "Пишем wp-config.php"
cd "$ROOT"
cp wp-config-sample.php wp-config.php
sed -i "s/database_name_here/${DBNAME}/; s/username_here/${DBUSER}/; s|password_here|${DBPASS}|" wp-config.php
# соли берём у самого WordPress
SALTS="$(curl -fsSL https://api.wordpress.org/secret-key/1.1/salt/)"
python3 - "$SALTS" <<'PY'
import sys, re, io
salts = sys.argv[1]
p = 'wp-config.php'
s = io.open(p, encoding='utf-8').read()
s = re.sub(r"define\(\s*'(AUTH|SECURE_AUTH|LOGGED_IN|NONCE)_(KEY|SALT)'.*?\);\s*\n", '', s)
s = s.replace("$table_prefix", salts + "\n$table_prefix", 1)
io.open(p, 'w', encoding='utf-8').write(s)
PY
sed -i "s/\$table_prefix = 'wp_';/\$table_prefix = 'ad_';/" wp-config.php

chown -R www-data:www-data "$ROOT"
find "$ROOT" -type d -exec chmod 755 {} +
find "$ROOT" -type f -exec chmod 644 {} +

# --- 6. nginx ----------------------------------------------------------------
say "Конфиг nginx (только новый файл, существующие не трогаем)"
cat > "/etc/nginx/sites-available/${DOMAIN}" <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    root ${ROOT};
    index index.php;

    client_max_body_size 64m;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:${PHPSOCK};
    }

    # Статику отдаём напрямую и надолго
    location ~* \.(webp|jpg|jpeg|png|svg|ico|woff2|mp4|webm|css|js)\$ {
        expires 30d;
        add_header Cache-Control "public";
        access_log off;
        try_files \$uri =404;
    }

    location ~ /\.(?!well-known) { deny all; }
    location = /xmlrpc.php { deny all; }
}
NGINX

ln -s "/etc/nginx/sites-available/${DOMAIN}" "/etc/nginx/sites-enabled/${DOMAIN}"
nginx -t
systemctl reload nginx

# --- 7. HTTPS ----------------------------------------------------------------
say "Сертификат Let's Encrypt"
certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos --register-unsafely-without-email --redirect || \
  warn "certbot не справился. Сайт работает по http, сертификат можно выпустить позже: certbot --nginx -d ${DOMAIN}"

# --- 8. Итог -----------------------------------------------------------------
say "Готово"
cat <<DONE

  Адрес:        https://${DOMAIN}
  Папка сайта:  ${ROOT}
  База:         ${DBNAME}
  Пользователь: ${DBUSER}
  Пароль базы:  ${DBPASS}

  Пароль записан в ${ROOT}/wp-config.php, отдельно запоминать не нужно.

  Дальше в браузере: откройте https://${DOMAIN} — WordPress попросит
  придумать логин и пароль администратора. Язык выберите русский.

  Потом по инструкции: сперва плагин ACF PRO, следом тема artdom-theme.zip.

DONE
