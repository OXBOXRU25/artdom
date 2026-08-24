// Сторож папки темы: сам выкладывает правки на боевой.
//
// Запускается ОДИН раз за сеанс:
//   D:/AI/nodejs/node.exe D:/AI/Artdom/_deploy/watch.mjs
//
// Дальше висит в терминале и молчит. Как только я меняю любой php, css или js —
// ждёт две секунды (вдруг правок несколько подряд) и заливает. В окне видно,
// что именно уехало и во сколько. Останов — Ctrl+C.
//
// Флаг --dry печатает, но не заливает: чтобы проверить сторожа, не трогая боевой.

import { watch } from 'node:fs';
import { spawn } from 'node:child_process';

const SRC = 'D:/AI/Artdom/theme/artdom';
const PUSH = 'D:/AI/Artdom/_deploy/push.sh';
const BASH = 'D:/AI/Git/bin/bash.exe';
const DRY = process.argv.includes('--dry');
const WAIT = 2000;

const CODE = /\.(php|css|js)$/i;
const SEP = String.fromCharCode(92);   // обратный слеш, собранный кодом:
// в heredoc он не переживает передачу и молча ломает регулярку.
const пропустить = (f) => f.includes('.git') || f.includes('node_modules');

const время = () => new Date().toTimeString().slice(0, 8);
let таймер = null;
let занят = false;
let накопилось = new Set();

function выложить() {
  if (занят) { таймер = setTimeout(выложить, WAIT); return; }
  const список = [...накопилось];
  накопилось.clear();
  if (!список.length) return;

  console.log(`[${время()}] правок: ${список.length} — ${список.slice(0, 4).join(', ')}${список.length > 4 ? ' и ещё…' : ''}`);
  if (DRY) { console.log(`[${время()}] --dry: заливку пропускаю`); return; }

  занят = true;
  const p = spawn(BASH, [PUSH], { stdio: ['ignore', 'pipe', 'pipe'] });
  let хвост = '';
  p.stdout.on('data', d => { хвост += d; });
  p.stderr.on('data', d => { хвост += d; });
  p.on('close', code => {
    занят = false;
    if (code === 0) {
      console.log(`[${время()}] выложено. На сайте Ctrl+F5`);
    } else {
      console.log(`[${время()}] НЕ ВЫЛОЖЕНО, код ${code}`);
      console.log(хвост.trim().split('\n').map(s => '    ' + s).join('\n'));
    }
  });
}

console.log(`Сторож темы АРТДОМ${DRY ? ' (--dry, без заливки)' : ''}`);
console.log(`  слежу за ${SRC}`);
console.log(`  правки уезжают на artdom.oxboxdigital.ru сами, через ${WAIT / 1000} с после последней`);
console.log(`  остановить — Ctrl+C\n`);

watch(SRC, { recursive: true }, (тип, файл) => {
  if (!файл || пропустить(файл) || !CODE.test(файл)) return;
  накопилось.add(файл.split(SEP).join('/'));
  clearTimeout(таймер);
  таймер = setTimeout(выложить, WAIT);
});
