// Закрывает все правила с :hover медиазапросом (hover: hover).
//
//   node _tools/guard-hovers.mjs [--write]
//
// Зачем. На тач-экране :hover не «не срабатывает», а ЗАЛИПАЕТ: после тапа
// элемент остаётся наведённым, пока человек не ткнёт в другое место. Поэтому
// перекаты текста, подъезды картинок и смены цвета живут только там, где есть
// настоящий указатель.
//
// Правило оборачивается НА МЕСТЕ, а не переносится в конец файла: порядок
// каскада и специфичность обязаны остаться прежними. Вложенный @media внутри
// @media — законный CSS.
//
// ЩУП. Самописное преобразование стилей в этом проекте уже дважды молча ломало
// боевой сайт (минификатор ел пробелы в calc и перед двоеточием), причём без
// единой ошибки в консоли: недействительное правило CSS браузер просто
// игнорирует. Поэтому здесь проверка не «сошлось число скобок», а разбор ОБЕИХ
// версий настоящим движком: список правил, селекторы и объявления обязаны
// совпасть один в один, а цепочка медиазапросов — отличаться ровно на
// добавленный (hover: hover). Не сошлось — файл НЕ переписывается.
import { readFileSync, writeFileSync } from 'node:fs';
import { spawn } from 'node:child_process';
import { mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const CSS = new URL('../theme/artdom/css/style.css', import.meta.url);
const CHROME = 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const WRITE = process.argv.includes('--write');
const GUARD_OPEN = '@media (hover: hover) {\n';

const src = readFileSync(CSS, 'utf8');

/* --- разбор ---------------------------------------------------------------
   Посимвольный проход, который знает про строки в кавычках и комментарии:
   иначе «{» внутри content: "{" или внутри /* … *\/ порвал бы вложенность. */
function scanRules(css, from, to) {
  const rules = [];
  let i = from, preludeStart = from;
  while (i < to) {
    const c = css[i];
    if (c === '/' && css[i + 1] === '*') { i = css.indexOf('*/', i + 2); i = i < 0 ? to : i + 2; continue; }
    if (c === '"' || c === "'") {
      const q = c; i++;
      while (i < to && css[i] !== q) { if (css[i] === '\\') i++; i++; }
      i++; continue;
    }
    if (c === ';') {                       // @import и прочее без блока
      preludeStart = i + 1; i++; continue;
    }
    if (c === '{') {
      const braceOpen = i;
      let depth = 1; i++;
      while (i < to && depth > 0) {
        const d = css[i];
        if (d === '/' && css[i + 1] === '*') { i = css.indexOf('*/', i + 2); i = i < 0 ? to : i + 2; continue; }
        if (d === '"' || d === "'") {
          const q = d; i++;
          while (i < to && css[i] !== q) { if (css[i] === '\\') i++; i++; }
          i++; continue;
        }
        if (d === '{') depth++;
        else if (d === '}') depth--;
        i++;
      }
      rules.push({ preludeStart, braceOpen, blockEnd: i, prelude: css.slice(preludeStart, braceOpen) });
      preludeStart = i;
      continue;
    }
    i++;
  }
  return rules;
}

// Убираем комментарии из прелюдии: «:hover» упомянутый в комментарии — не селектор.
const bare = (s) => s.replace(/\/\*[\s\S]*?\*\//g, '').trim();
const isNestingAtRule = (p) => /^@(media|supports|layer|container|scope)\b/i.test(bare(p));
const isAtRule = (p) => bare(p).startsWith('@');

// Собираем позиции вставок, в файл ничего не пишем — сперва щуп.
const inserts = [];   // { at, text }
function walk(from, to) {
  for (const r of scanRules(src, from, to)) {
    const p = bare(r.prelude);
    if (isNestingAtRule(p)) {
      // уже под (hover: hover) — внутрь не лезем, там всё закрыто
      if (/\(\s*hover\s*:\s*hover\s*\)/.test(p)) continue;
      walk(r.braceOpen + 1, r.blockEnd - 1);
      continue;
    }
    if (isAtRule(p)) continue;                       // @keyframes, @font-face
    if (!p.includes(':hover')) continue;
    /* Врезаемся не сразу после «}» предыдущего правила, а с начала СТРОКИ, на
       которой стоит селектор: иначе @media приклеивается к чужой строке хвостом
       и файл становится нечитаемым. Отступ берём тот же, что у правила. */
    /* Пропускаем не только пробелы, но и комментарии: прелюдия начинается сразу
       за «}» предыдущего правила и часто тянет за собой хвостовой комментарий
       с ЧУЖОЙ строки. Первый заход этого не учёл — и обёртка накрыла соседнее
       правило без :hover. Поймал щуп. */
    let sel = r.preludeStart;
    for (;;) {
      const ws = (src.slice(sel, r.braceOpen).match(/^\s*/) || [''])[0].length;
      sel += ws;
      if (src.startsWith('/*', sel)) {
        const end = src.indexOf('*/', sel + 2);
        if (end < 0 || end > r.braceOpen) break;
        sel = end + 2;
        continue;
      }
      break;
    }
    const at = src.lastIndexOf('\n', sel - 1) + 1;
    const indent = (src.slice(at, sel).match(/^[ \t]*/) || [''])[0];
    inserts.push({ at, text: indent + GUARD_OPEN });
    inserts.push({ at: r.blockEnd, text: '\n' + indent + '}' });
  }
}
walk(0, src.length);

if (!inserts.length) {
  console.log('Правил с :hover без защиты не нашлось — файл не тронут.');
  process.exit(0);
}

inserts.sort((a, b) => a.at - b.at);
let out = '', cur = 0;
for (const ins of inserts) { out += src.slice(cur, ins.at) + ins.text; cur = ins.at; }
out += src.slice(cur);

console.log('Правил с :hover обёрнуто: ' + inserts.length / 2);
console.log('Было ' + src.length + ' байт, стало ' + out.length + '.');

/* --- ЩУП: разбор обеих версий настоящим движком ---------------------------- */
const profile = mkdtempSync(join(tmpdir(), 'hoverguard-'));
const chrome = spawn(CHROME, [
  '--headless=new', '--disable-gpu', '--remote-debugging-port=0',
  '--user-data-dir=' + profile, 'about:blank',
], { stdio: ['ignore', 'pipe', 'pipe'] });

const wsUrl = await new Promise((res, rej) => {
  let buf = '';
  const t = setTimeout(() => rej(new Error('Chrome не отдал адрес отладки')), 15000);
  chrome.stderr.on('data', (d) => {
    buf += d.toString();
    const m = buf.match(/ws:\/\/[^\s]+/);
    if (m) { clearTimeout(t); res(m[0]); }
  });
});
const browser = new WebSocket(wsUrl);
await new Promise((r) => browser.addEventListener('open', r, { once: true }));
let id = 0;
const pending = new Map();
const send = (method, params, sessionId) => new Promise((res, rej) => {
  const n = ++id;
  pending.set(n, { res, rej });
  browser.send(JSON.stringify({ id: n, method, params: params || {}, sessionId }));
});
browser.addEventListener('message', (ev) => {
  const m = JSON.parse(ev.data);
  if (m.id && pending.has(m.id)) {
    const p = pending.get(m.id); pending.delete(m.id);
    m.error ? p.rej(new Error(m.error.message)) : p.res(m.result);
  }
});
const { targetId } = await send('Target.createTarget', { url: 'about:blank' });
const { sessionId } = await send('Target.attachToTarget', { targetId, flatten: true });
const S = (m, p) => send(m, p, sessionId);
await S('Runtime.enable');

// Раскладываем таблицу стилей в плоский список: цепочка условий + селектор +
// объявления. Именно этот список и обязан совпасть.
const FLATTEN = `(async function(css){
  var sheet = new CSSStyleSheet();
  await sheet.replace(css);
  var list = [];
  function walk(rules, chain) {
    for (var i = 0; i < rules.length; i++) {
      var r = rules[i];
      if (r.type === 1) {                                   // обычное правило
        var d = [];
        for (var k = 0; k < r.style.length; k++) {
          var name = r.style[k];
          d.push(name + ':' + r.style.getPropertyValue(name) + (r.style.getPropertyPriority(name) ? '!' : ''));
        }
        list.push({ chain: chain.slice().sort().join(' && '), sel: r.selectorText, decl: d.join(';') });
      } else if (r.cssRules) {
        var cond = r.conditionText || r.name || r.cssText.split('{')[0].trim();
        walk(r.cssRules, r.type === 4 || r.type === 12 ? chain.concat([cond]) : chain);
      }
    }
  }
  walk(sheet.cssRules, []);
  return list;
})(CSS_HERE)`;

const flatten = async (css) => {
  const expr = FLATTEN.replace('CSS_HERE', JSON.stringify(css));
  const r = await S('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true });
  if (r.exceptionDetails) throw new Error('движок не разобрал стили: ' + r.exceptionDetails.text);
  return r.result.value;
};

const A = await flatten(src);
const B = await flatten(out);
chrome.kill();
// Профиль Chrome освобождает не сразу — уборка не должна ронять проверку.
try { rmSync(profile, { recursive: true, force: true }); }
catch (e) { console.log('  (временный профиль ' + profile + ' остался: ' + e.code + ')'); }

const problems = [];
if (A.length !== B.length) {
  problems.push('число правил разошлось: было ' + A.length + ', стало ' + B.length);
} else {
  for (let i = 0; i < A.length; i++) {
    const a = A[i], b = B[i];
    if (a.sel !== b.sel) { problems.push('#' + i + ' селектор: «' + a.sel + '» -> «' + b.sel + '»'); continue; }
    if (a.decl !== b.decl) { problems.push('#' + i + ' объявления у «' + a.sel + '»:\n      было  ' + a.decl + '\n      стало ' + b.decl); continue; }
    const hover = a.sel.includes(':hover');
    const added = b.chain.split(' && ').filter((c) => c && !a.chain.split(' && ').includes(c));
    if (hover) {
      if (!/hover\s*:\s*hover/.test(b.chain)) problems.push('#' + i + ' «' + a.sel + '» осталось без защиты');
      if (added.length !== 1 || !/hover\s*:\s*hover/.test(added[0])) {
        problems.push('#' + i + ' «' + a.sel + '» получило лишние условия: ' + JSON.stringify(added));
      }
    } else if (added.length) {
      problems.push('#' + i + ' «' + a.sel + '» без :hover, а условия изменились: ' + JSON.stringify(added));
    }
  }
}

console.log('\n=== ЩУП: разбор движком ===');
console.log('  правил в исходнике: ' + A.length + ', в новой версии: ' + B.length);
if (problems.length) {
  console.log('  РАСХОЖДЕНИЙ: ' + problems.length);
  problems.slice(0, 25).forEach((p) => console.log('    ' + p));
  console.log('\nФайл НЕ переписан.');
  process.exit(1);
}
console.log('  расхождений нет: селекторы и объявления совпали, у правил с :hover');
console.log('  добавилось ровно одно условие (hover: hover).');

if (WRITE) {
  writeFileSync(CSS, out);
  console.log('\nЗаписано в theme/artdom/css/style.css');
} else {
  console.log('\nПрогон вхолостую. Записать: node _tools/guard-hovers.mjs --write');
}
process.exit(0);
