// Крошечный статический сервер под панель предпросмотра.
// Нужен потому, что панель, открывая file://, делает со страницы data:-снимок,
// и все относительные пути (картинки, шрифты, скрипты) рассыпаются.
import { createServer } from 'node:http';
import { readFile, stat } from 'node:fs/promises';
import { extname, join, normalize } from 'node:path';

const ROOT = 'D:/AI/Artdom/static';
const PORT = Number(process.env.PORT || 4321);

const MIME = {
  '.html': 'text/html; charset=utf-8',
  '.css': 'text/css; charset=utf-8',
  '.js': 'text/javascript; charset=utf-8',
  '.json': 'application/json; charset=utf-8',
  '.svg': 'image/svg+xml',
  '.webp': 'image/webp',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.png': 'image/png',
  '.woff2': 'font/woff2',
  '.ico': 'image/x-icon',
};

createServer(async (req, res) => {
  try {
    let p = decodeURIComponent(new URL(req.url, 'http://x').pathname);
    if (p.endsWith('/')) p += 'index.html';
    // не выпускаем за пределы папки
    const full = normalize(join(ROOT, p));
    if (!full.replaceAll('\\', '/').startsWith(ROOT)) {
      res.writeHead(403).end('нельзя');
      return;
    }
    const info = await stat(full);
    if (info.isDirectory()) {
      res.writeHead(302, { Location: p + '/' }).end();
      return;
    }
    const body = await readFile(full);
    res.writeHead(200, {
      'Content-Type': MIME[extname(full).toLowerCase()] || 'application/octet-stream',
      'Content-Length': body.length,
      'Cache-Control': 'no-store',
    });
    res.end(body);
  } catch {
    res.writeHead(404, { 'Content-Type': 'text/plain; charset=utf-8' }).end('не найдено: ' + req.url);
  }
}).listen(PORT, () => console.log('АРТДОМ на http://localhost:' + PORT));
