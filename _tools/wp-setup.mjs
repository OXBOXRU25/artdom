// Готовит локальный WordPress на SQLite: drop-in базы, wp-config со своими солями.
import fs from 'node:fs';
import crypto from 'node:crypto';

const WP = 'D:/AI/Artdom/wp';
const PLUG = WP + '/wp-content/plugins/sqlite-database-integration';

/* 1. Drop-in базы: плагин отдаёт шаблон db.copy с двумя плейсхолдерами */
const tpl = fs.readFileSync(PLUG + '/db.copy', 'utf8');
const dbphp = tpl
  .replace("'{SQLITE_IMPLEMENTATION_FOLDER_PATH}'", "WP_PLUGIN_DIR . '/sqlite-database-integration'")
  .replace("'{SQLITE_PLUGIN}'", "'sqlite-database-integration/load.php'");
fs.writeFileSync(WP + '/wp-content/db.php', dbphp, 'utf8');
console.log('db.php положен, плейсхолдеров не осталось: ' + !/\{[A-Z_]+\}/.test(dbph_check(dbphp)));
function dbph_check(s) { return s; }

/* 2. Соли генерим сами — незачем ходить в сеть за случайными числами */
const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#%^&*()-_=+[]{}<>~';
const salt = () => Array.from({ length: 64 }, () => chars[crypto.randomInt(chars.length)]).join('');
const keys = ['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY',
              'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'];

const config = [
  '<?php',
  '/**',
  ' * Локальная конфигурация для отладки темы АРТДОМ.',
  ' * База — SQLite: MySQL на машине нет, а PHP собран с pdo_sqlite.',
  ' * Константы DB_* объявлены потому, что ядро их ждёт; SQLite их игнорирует.',
  ' */',
  "define( 'DB_NAME', 'artdom' );",
  "define( 'DB_USER', '' );",
  "define( 'DB_PASSWORD', '' );",
  "define( 'DB_HOST', 'localhost' );",
  "define( 'DB_CHARSET', 'utf8mb4' );",
  "define( 'DB_COLLATE', '' );",
  '',
  ...keys.map((k) => "define( '" + k + "', '" + salt() + "' );"),
  '',
  "$table_prefix = 'ad_';",
  '',
  "define( 'WP_DEBUG', true );",
  "define( 'WP_DEBUG_LOG', true );",
  "define( 'WP_DEBUG_DISPLAY', false );",
  "define( 'SCRIPT_DEBUG', true );",
  "define( 'WP_ENVIRONMENT_TYPE', 'local' );",
  "define( 'AUTOMATIC_UPDATER_DISABLED', true );",
  "define( 'DISALLOW_FILE_EDIT', true );",
  '',
  "if ( ! defined( 'ABSPATH' ) ) { define( 'ABSPATH', __DIR__ . '/' ); }",
  "require_once ABSPATH . 'wp-settings.php';",
  '',
].join('\n');

fs.writeFileSync(WP + '/wp-config.php', config, 'utf8');
console.log('wp-config.php создан, префикс таблиц ad_, отладка включена');
