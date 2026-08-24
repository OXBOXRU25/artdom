const lin=(v)=>{v/=255;return v<=0.04045?v/12.92:Math.pow((v+0.055)/1.055,2.4);};
const lum=(h)=>{const n=parseInt(h.slice(1),16);return 0.2126*lin((n>>16)&255)+0.7152*lin((n>>8)&255)+0.0722*lin(n&255);};
const R=(a,b)=>{const[x,y]=[lum(a),lum(b)].sort((p,q)=>q-p);return(x+0.05)/(y+0.05);};
const hex=(r,g,b)=>'#'+[r,g,b].map(v=>Math.max(0,Math.min(255,Math.round(v))).toString(16).padStart(2,'0')).join('');
// затемняем акцент пропорционально, пока не пройдёт 4.5 на сером фоне
const base=[0x4d,0x6d,0xd9];
for(let k=100;k>=40;k--){
  const c=hex(base[0]*k/100,base[1]*k/100,base[2]*k/100);
  if(R(c,'#edeff4')>=4.5){
    console.log('акцент как текст на #edeff4 : '+c+'  ('+R(c,'#edeff4').toFixed(2)+':1)');
    console.log('  он же на белом            : '+R(c,'#ffffff').toFixed(2)+':1');
    console.log('  белый текст на нём (кнопка): '+R('#ffffff',c).toFixed(2)+':1');
    break;
  }
}
console.log('\nисходный #4d6dd9 на #edeff4  : '+R('#4d6dd9','#edeff4').toFixed(2)+':1');
console.log('серый #686b7f на #edeff4     : '+R('#686b7f','#edeff4').toFixed(2)+':1');
console.log('серый #686b7f на #ffffff     : '+R('#686b7f','#ffffff').toFixed(2)+':1');
console.log('серый #686b7f на чипе #edeef4: '+R('#686b7f','#edeef4').toFixed(2)+':1');
