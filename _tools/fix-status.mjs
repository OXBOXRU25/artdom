import fs from 'node:fs';
const f = 'D:/AI/Artdom/STATUS.md';
const good = fs.readFileSync('C:/Temp/claude/D--AI-Artdom/581c594b-2694-4878-8f3a-98280ae199dd/scratchpad/round1.md', 'utf8');
let s = fs.readFileSync(f, 'utf8');
const i = s.indexOf('## Круг правок 1');
if (i < 0) { console.log('раздел не найден'); process.exit(1); }
s = s.slice(0, i) + good;
fs.writeFileSync(f, s, 'utf8');
const check = fs.readFileSync(f, 'utf8');
console.log('переписано. бэктиков в разделе: ' + (check.slice(i).match(/`/g) || []).length + ' (было 0 — их съела оболочка)');
