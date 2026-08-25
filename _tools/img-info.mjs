/* Размеры картинок без ffmpeg: @napi-rs/canvas читает и webp, и jpeg. */
import { createRequire } from 'node:module';
import fs from 'node:fs';
const require = createRequire(import.meta.url);
const { loadImage } = require('@napi-rs/canvas');

for (const f of process.argv.slice(2)) {
  try {
    const img = await loadImage(fs.readFileSync(f));
    const kb = Math.round(fs.statSync(f).size / 1024);
    console.log(`${img.width}x${img.height}  ${kb} КБ  ${f}`);
  } catch (e) {
    console.log(`не прочиталось (${e.message}): ${f}`);
  }
}
