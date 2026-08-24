const lin = (v) => { v /= 255; return v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
const lum = (h) => { const n = parseInt(h.slice(1), 16); return 0.2126*lin((n>>16)&255) + 0.7152*lin((n>>8)&255) + 0.0722*lin(n&255); };
const ratio = (a,b) => { const [x,y] = [lum(a),lum(b)].sort((p,q)=>q-p); return (x+0.05)/(y+0.05); };
const pairs = [
  ['серый текст 16-18px на сером фоне', '#6a6d81', '#edeff4', 4.5],
  ['серый текст 16-18px на белом',      '#6a6d81', '#ffffff', 4.5],
  ['чип 14px на плашке',                '#6a6d81', '#edeef4', 4.5],
  ['текст аккордеона 18px',             '#202023', '#edeff4', 4.5],
  ['синий 28px (заголовок карточки)',   '#4d6dd9', '#ffffff', 3.0],
  ['синие цифры 90px',                  '#4d6dd9', '#ffffff', 3.0],
  ['синяя ссылка 16px «Узнать больше»', '#4d6dd9', '#edeff4', 4.5],
  ['белый на синей кнопке 18px',        '#ffffff', '#4d6dd9', 4.5],
  ['заголовки',                         '#000003', '#edeff4', 4.5],
  ['белый текст в футере',              '#ffffff', '#000000', 4.5],
  ['серый в футере (если #6a6d81)',     '#6a6d81', '#000000', 4.5],
];
console.log('=== КОНТРАСТ ПО ИСТИННЫМ ЦВЕТАМ ===');
for (const [n,fg,bg,need] of pairs) {
  const r = ratio(fg,bg);
  console.log('  ' + n.padEnd(36) + fg + ' / ' + bg + '  = ' + r.toFixed(2).padStart(5) + ':1  нужно ' + need.toFixed(1) + '  ' + (r>=need?'OK':'НЕ ПРОХОДИТ'));
}
// подбор минимального затемнения серого до 4.5 на #edeff4
const target = 4.5, bg = '#edeff4';
for (let d = 0; d < 60; d++) {
  const c = '#' + [0x6a,0x6d,0x81].map(v => Math.max(0, v-d).toString(16).padStart(2,'0')).join('');
  if (ratio(c,bg) >= target) { console.log('\n  Минимальная правка серого: ' + c + ' даёт ' + ratio(c,bg).toFixed(2) + ':1 (было #6a6d81 = ' + ratio('#6a6d81',bg).toFixed(2) + ')'); break; }
}
