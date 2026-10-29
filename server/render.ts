import ejs from 'ejs';
import { join } from 'node:path';
import { icons } from './icons.js';
export const escape = (v: unknown) => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]!));
export const lines = (v: string) => v.split(/\r?\n/).map(x=>x.trim()).filter(Boolean);
export function helpers(values: Record<string,string>, csrf: string) {
 const setting = (key: string) => values[key] ?? '';
 const digits = (v: string) => v.replace(/\D/g,'');
 const icon = (name: string, size = 28) => `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${icons[name] || icons.box || ''}</svg>`;
 return {csrfToken:csrf,e:escape, setting, icon, phone_digits:digits, lines,
 url:(p='')=>'/'+p.replace(/^\//,''), count:(a: unknown[])=>a.length, slice:(a: unknown[],start:number,end:number)=>a.slice(start,start+end),
 lower:(s:string)=>s.toLowerCase(), replace:(a:string,b:string,s:string)=>s.replaceAll(a,b),
 nl2br:(s:string)=>s.replace(/\r?\n/g,'<br>'),
 paragraphs:(s:string)=>s.split(/\r?\n\s*\r?\n/).filter(Boolean).map(p=>'<p>'+escape(p).replace(/\r?\n/g,'<br>')+'</p>').join(''),
 rawurlencode:encodeURIComponent, substr:(s: unknown,start:number,end:number)=>String(s instanceof Date ? s.toISOString().replace('T',' ') : s).slice(start,start+end),
 ucfirst:(s:string)=>s.charAt(0).toUpperCase()+s.slice(1),
 icon_choices:()=>Object.fromEntries(Object.keys(icons).map(x=>[x,x])),
 csrf_field:()=>`<input type="hidden" name="_csrf" value="${escape(csrf)}">`,
 img_srcset:(p:string,sizes='(max-width: 700px) 100vw, 400px')=>p.startsWith('assets/img/products/') ? ` srcset="/${escape(p.replace('.jpg','-800.jpg'))} 800w, /${escape(p.replace('.jpg','-1600.jpg'))} 1600w, /${escape(p)} 3840w" sizes="${escape(sizes)}"` : '',
 tel:digits(setting('phone')), wa:digits(setting('whatsapp')), showBank:setting('show_bank')==='1', siteName:setting('site_name'),
 nav:{'index.php':'Home','about.php':'About','products.php':'Products','services.php':'Services','contact.php':'Contact'},
 why:lines(setting('why_us')), creds:lines(setting('credentials')), clients:lines(setting('clients')),
 };
}
export async function render(view: string, data: Record<string,unknown>) {
 return ejs.renderFile(join(process.cwd(),'server','views',view+'.ejs'), data, {cache:false});
}
