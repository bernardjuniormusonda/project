import type { Context, Config } from '@netlify/functions';
import { getUser, refreshSession, login, logout, requestPasswordRecovery, acceptInvite, recoverPassword } from '@netlify/identity';
import { getStore } from '@netlify/blobs';
import { fileTypeFromBuffer } from 'file-type';
import { randomBytes, timingSafeEqual, createHash } from 'node:crypto';
import { eq, asc, desc, sql, lt } from 'drizzle-orm';
import { database } from '../../db/index.js';
import { settings, categories, quotes, messages, rateLimits, baskets } from '../../db/schema.js';
import { render, helpers, lines, escape } from '../../server/render.js';
import { text, email, publicSubmission, csv, validId, ValidationError } from '../../server/validation.js';
import { groups } from '../../server/settings-fields.js';
import homeServices from '../../server/index-services.js';
import allServices from '../../server/services-services.js';

export default async (req:Request, context:Context) => {
 const u=new URL(req.url); const path=u.pathname;
 const csrfCookie=req.headers.get('cookie')?.match(/(?:^|;\s*)printel_csrf=([a-f0-9]{64})(?:;|$)/)?.[1];
 const csrf=csrfCookie || randomBytes(32).toString('hex');
 const basketCookie=req.headers.get('cookie')?.match(/(?:^|;\s*)printel_basket=([a-f0-9]{64})(?:;|$)/)?.[1];
 const basketId=basketCookie||randomBytes(32).toString('hex');
 const headers=new Headers({'Cache-Control':'private, no-store','Content-Type':'text/html; charset=utf-8'});
 if(!csrfCookie)headers.append('Set-Cookie',`printel_csrf=${csrf}; Path=/; HttpOnly; SameSite=Lax${u.protocol==='https:' ? '; Secure' : ''}`);
 if(!basketCookie)headers.append('Set-Cookie',`printel_basket=${basketId}; Path=/; HttpOnly; SameSite=Lax; Max-Age=2592000${u.protocol==='https:' ? '; Secure' : ''}`);
 const response=(body:string,status=200)=>new Response(body,{status,headers});
 const redirect=(to:string)=>{ headers.set('Location',to);return response('',303); };
 let form=new FormData();
 if(!['GET','HEAD','POST'].includes(req.method))return response('Method not allowed',405);
 if(req.method==='POST') {
  if(req.headers.get('origin')!==u.origin)return response('Invalid request origin. Reload this page and try again.',403);
  if(Number(req.headers.get('content-length')||0)>4_000_000)return response('Upload exceeds the 3 MB image limit.',413);
  try { form=await req.formData(); } catch { return response('Invalid form submission.',400); }
  const sent=form.get('_csrf');
  if(!csrfCookie || typeof sent!=='string' || sent.length!==csrf.length || !timingSafeEqual(Buffer.from(sent),Buffer.from(csrf)))return response('Your session expired. Reload this page and try again.',403);
 }
 const isAdminPath=path.startsWith('/admin');
 const loginPage=['/admin/login.php','/admin/setup.php'].includes(path);
 try {
  if(path.startsWith('/media/')) {
   if(req.method!=='GET')return response('Method not allowed',405);
   const key=path.slice(7);
   if(!/^[a-f0-9]{32}\.(jpg|png|webp)$/.test(key))return response('Not found',404);
   const blob=await getStore('product-images').get(key,{type:'arrayBuffer'});
   if(!blob)return response('Not found',404);
   return new Response(blob,{headers:{'Content-Type':key.endsWith('.jpg')?'image/jpeg':key.endsWith('.png')?'image/png':'image/webp','X-Content-Type-Options':'nosniff','Cache-Control':'public, max-age=31536000, immutable'}});
  }
  if(isAdminPath)await refreshSession();
  const user=isAdminPath ? await getUser() : null;
  if(isAdminPath && !loginPage && !user)return redirect('/admin/login.php');
  if(isAdminPath && !loginPage && !user?.roles?.includes('admin'))return response('Administrator access required. Ask the site owner to assign your account the admin role in Netlify Identity.',403);
  const db=database();
  if(path==='/api/basket') {
   headers.set('Content-Type','application/json; charset=utf-8');
   if(req.method==='POST') {
    let items:unknown;try {items=JSON.parse(text(form,'items',40000));}catch{return response(JSON.stringify({error:'Invalid basket'}),400);}
    if(!Array.isArray(items)||items.length>100||items.some(x=>!x||typeof x.item!=='string'||!x.item.trim()||x.item.length>300||typeof x.cat!=='string'||x.cat.length>160||!Number.isInteger(x.qty)||x.qty<1||x.qty>100000))return response(JSON.stringify({error:'Basket items or quantities are invalid.'}),400);
    await db.insert(baskets).values({id:basketId,items,updated_at:new Date()}).onConflictDoUpdate({target:baskets.id,set:{items,updated_at:new Date()}});
    return response(JSON.stringify({ok:true}));
   }
   const [basket]=await db.select().from(baskets).where(eq(baskets.id,basketId));
   return response(JSON.stringify({items:basket?.items||[]}));
  }
  const values=Object.fromEntries((await db.select().from(settings)).map(r=>[r.k,r.v]));
  const h=helpers(values,csrf);
  const titles:Record<string,string>={'index':'','about':'About us','products':'Products','product':'Products','services':'Services','contact':'Contact us','quote':'Request a quote'};
  let view=path==='/' ? 'index' : path.slice(1).replace(/\.php$/,'').replace(/\/$/,'');
  const base:Record<string,unknown>={...h,errors:[],old:Object.fromEntries(['name','email','phone','organisation','location','needed_by','items','details','subject','message'].map(k=>[k,''])),done:null,flash:null,metaDesc:values.hero_text,recover:u.searchParams.has('recover'),posted:false};
  const page=async (name:string,title:string,data:Record<string,unknown>={},status=200)=>response(await render(name,{...base,pageTitle:title,fullTitle:title ? `${title} | ${values.site_name}`:`${values.site_name} | ${values.tagline}`,current:view==='product'?'products.php':`${view}.php`,...data}),status);
  if(loginPage) {
   let authError='';
   if(req.method==='POST') {
    try {
     const action=text(form,'action',40);
     if(action==='login')await login(text(form,'email',190),passwordInput());
     else if(action==='request-recovery') {
      const address=text(form,'email',190);if(!email(address))throw new ValidationError('Invalid email');
      await requestPasswordRecovery(address);
      return page('login','Administrator access',{flash:{msg:'If that account exists, a recovery email has been sent.',type:'success'}});
     } else if(action==='accept-invite') {
      const password=passwordInput();if(password.length<10)throw new ValidationError('Password too short');
      await acceptInvite(text(form,'token',4096),password);
      return redirect('/admin/login.php?updated=1');
     } else if(action==='recover') {
      const password=passwordInput();if(password.length<10)throw new ValidationError('Password too short');
      await recoverPassword(text(form,'token',4096),password);
      return redirect('/admin/login.php?updated=1');
     } else throw new ValidationError('Invalid action');
     return redirect('/admin/');
    } catch { authError='Unable to sign in or update your password. Check your details or request a new invitation or recovery email. New passwords must have at least 10 characters.'; }
   }
   return page('login','Administrator access',{errors:authError?[authError]:[],flash:u.searchParams.has('updated')?{msg:'Password saved. Please log in with your email and password.',type:'success'}:null});
  }
  if(isAdminPath) {
   if(path==='/admin/logout.php') {
    if(req.method!=='POST')return response('Method not allowed',405);
    await logout();return redirect('/admin/login.php');
   }
   return await adminRoutes();
  }
  if(!Object.hasOwn(titles,view))return page('not-found','Page not found',{},404);
  const cats=await db.select().from(categories).where(eq(categories.status,'published')).orderBy(asc(categories.sort_order),asc(categories.id));
  Object.assign(base,{cats,featured:cats.slice(0,6),services:view==='index'?homeServices:allServices});
  if(view==='product') {
   const c=cats.find(c=>c.slug===u.searchParams.get('slug'));
   if(!c)return page('not-found','Category not found',{},404);
   return page('product',c.title,{c,others:cats.filter(x=>x.id!==c.id),items:lines(c.items),metaDesc:c.summary});
  }
  if(view==='contact' || view==='quote') {
   if(req.method==='POST') {
    try {
     if(text(form,'website',300))return page(view,titles[view],{flash:{msg:'Thank you! Your request has been received.',type:'success'}});
     const data=publicSubmission(form,view==='quote');
     const now=new Date();const window=Math.floor(now.getTime()/3600000);
     const bucket=`${view}:${createHash('sha256').update(context.ip || 'unknown').digest('hex')}:${window}`;
     const allowed=await db.insert(rateLimits).values({bucket,expires_at:new Date((window+1)*3600000)}).onConflictDoUpdate({target:rateLimits.bucket,set:{hits:sql`${rateLimits.hits}+1`},where:sql`${rateLimits.hits}<5`}).returning();
     if(!allowed.length)throw new ValidationError('Too many requests from your connection. Please try again later or call us.');
     await db.delete(rateLimits).where(lt(rateLimits.expires_at,now));
     if(view==='quote') {
      const ref=await db.transaction(async tx=>{
       const [row]=await tx.insert(quotes).values({...data, name:data.name, email:data.email}).returning({id:quotes.id});
       const ref=`PE-${now.toISOString().slice(2,10).replaceAll('-','')}-${String(row.id).padStart(4,'0')}`;
       await tx.update(quotes).set({ref}).where(eq(quotes.id,row.id));
       await tx.delete(baskets).where(eq(baskets.id,basketId));return ref;
      });
      // The redirect carries only the public reference, never customer data.
      return redirect('/quote.php?sent='+encodeURIComponent(ref));
     }
     await db.insert(messages).values({...data,name:data.name,email:data.email,message:data.message});
     return redirect('/contact.php?sent=1');
    } catch(e) {
     const old=Object.fromEntries([...form.entries()].filter(([k,v])=>!['_csrf','website'].includes(k)&&typeof v==='string'));
     if(form.has('manual_items'))old.items=String(form.get('manual_items')).slice(0,6000);
     return page(view,titles[view],{old,errors:[e instanceof ValidationError ? e.message : 'Unable to save your request. Please try again.']},400);
    }
   }
   const ref=u.searchParams.get('sent');
   if(view==='quote' && ref && /^PE-\d{6}-\d{4,}$/.test(ref))base.done=ref;
   if(view==='contact' && ref==='1')base.flash={msg:'Thank you! Your message has been sent. We will reply as soon as we can.',type:'success'};
  } else if(req.method==='POST')return response('Method not allowed',405);
  return page(view,titles[view]);

  function passwordInput() {
   const value=form.get('password');
   if(typeof value!=='string'||value.length>1000)throw new ValidationError('Invalid password.');
   return value;
  }
  async function adminRoutes():Promise<Response> {
   const name=path==='/admin/'||path==='/admin'||path==='/admin/index.php'?'index':path.split('/').pop()!.replace('.php','');
   if(!['index','quotes','messages','categories','category-edit','settings','export'].includes(name))return page('not-found','Page not found',{},404);
   const statuses={new:'New',contacted:'Contacted',quoted:'Quoted',won:'Won',closed:'Closed'};
   let errors:string[]=[];
   if(req.method==='POST') {
    try {
     const action=text(form,'action',30);
     if(name==='quotes') {
      const id=validId(form.get('id'));
      if(action==='delete')await db.delete(quotes).where(eq(quotes.id,id));
      else if(action==='save') {
       const status=text(form,'status',30);if(!Object.hasOwn(statuses,status))throw new ValidationError('Invalid status.');
       await db.update(quotes).set({status,admin_note:text(form,'admin_note',3000)}).where(eq(quotes.id,id));
      }else throw new ValidationError('Invalid action.');
     } else if(name==='messages') {
      if(action==='read_all')await db.update(messages).set({is_read:1});
      else {
       const id=validId(form.get('id'));
       if(action==='delete')await db.delete(messages).where(eq(messages.id,id));
       else if(['read','unread'].includes(action))await db.update(messages).set({is_read:action==='read'?1:0}).where(eq(messages.id,id));
       else throw new ValidationError('Invalid action.');
      }
     } else if(name==='settings') {
      const edits:{k:string,v:string}[]=[];
      for(const fields of Object.values(groups))for(const [k,[label,type]] of Object.entries(fields)) {
       const v=type==='bool' ? (form.get(k)==='1'?'1':'0') : text(form,k,type==='textarea'?4000:300);
       if(type==='email'&&v&&!email(v))throw new ValidationError(`${label} must be a valid email address.`);
       if(type==='url'&&v&&!/^https?:\/\/[^\s]+$/i.test(v))throw new ValidationError(`${label} must start with https:// or http://.`);
       if(k==='whatsapp'&&v&&!/^\d{8,15}$/.test(v))throw new ValidationError('WhatsApp must contain 8–15 digits.');
       if(k==='site_name'&&!v)throw new ValidationError('Business name is required.');
       edits.push({k,v});
      }
      await db.transaction(async tx=>{for(const r of edits)await tx.insert(settings).values(r).onConflictDoUpdate({target:settings.k,set:{v:r.v}});});
     } else if(name==='categories') {
      const id=validId(form.get('id'));
      const [c]=await db.select().from(categories).where(eq(categories.id,id));if(!c)throw new ValidationError('Category not found.');
      if(action==='delete')await db.delete(categories).where(eq(categories.id,id));
      else if(action==='toggle')await db.update(categories).set({status:c.status==='published'?'draft':'published'}).where(eq(categories.id,id));
      else if(['up','down'].includes(action)) {
       await db.transaction(async tx=>{
        const list=await tx.select().from(categories).orderBy(asc(categories.sort_order),asc(categories.id)).for('update');
        const pos=list.findIndex(x=>x.id===id), other=pos+(action==='up'?-1:1);
        if(other>=0 && other<list.length)[list[pos],list[other]]=[list[other],list[pos]];
        for(const [i,row] of list.entries())await tx.update(categories).set({sort_order:i+1}).where(eq(categories.id,row.id));
       });
      }else throw new ValidationError('Invalid action.');
     } else if(name==='category-edit') {
      const id=Number(form.get('id')||0);
      if(!Number.isSafeInteger(id)||id<0)throw new ValidationError('Invalid category.');
      const c:Record<string,string>={};
      for(const [key,max] of Object.entries({title:160,slug:80,tagline:120,summary:300,body:8000,items:5000,icon:30,status:20}))c[key]=text(form,key,max);
      if(!c.title)throw new ValidationError('Category title is required.');
      c.slug=(c.slug||c.title).toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,'');
      if(!c.slug)throw new ValidationError('Please enter a URL name using letters and numbers.');
      if(!['draft','published'].includes(c.status))throw new ValidationError('Invalid category status.');
      const [existing]=id?await db.select().from(categories).where(eq(categories.id,id)):[];
      if(id&&!existing)throw new ValidationError('Category not found.');
      const duplicates=await db.select().from(categories).where(eq(categories.slug,c.slug));
      if(duplicates.some(x=>x.id!==id))throw new ValidationError('Another category uses that URL name.');
      c.image=form.get('remove_image')==='1'?'':existing?.image||'';
      const upload=form.get('image');
      if(upload instanceof File && upload.size) {
       if(upload.size>3*1024*1024)throw new ValidationError('Image must be at most 3 MB.');
       const bytes=await upload.arrayBuffer(); const type=await fileTypeFromBuffer(bytes);
       if(!type||!['image/jpeg','image/png','image/webp'].includes(type.mime))throw new ValidationError('Image must be a JPG, PNG or WebP file.');
       const key=`${randomBytes(16).toString('hex')}.${type.ext}`;
       await getStore('product-images').set(key,bytes);c.image='media/'+key;
      }
      if(id)await db.update(categories).set(c).where(eq(categories.id,id));
      else {
       const [max]=await db.select({n:sql<number>`coalesce(max(${categories.sort_order}),0)`}).from(categories);
       await db.insert(categories).values({...c,title:c.title,slug:c.slug,sort_order:Number(max.n)+1});
      }
      return redirect('/admin/categories.php?saved=1');
     } else return response('Method not allowed',405);
     return redirect(path+'?saved=1');
    } catch(e) {errors=[e instanceof ValidationError ? e.message : 'Unable to save. Please try again.'];}
   }
   const [allQuotes,allMessages,allCats,quoteTotals,messageTotals]=await Promise.all([
    db.select().from(quotes).orderBy(desc(quotes.id)).limit(500),
    db.select().from(messages).orderBy(desc(messages.id)).limit(500),
    db.select().from(categories).orderBy(asc(categories.sort_order),asc(categories.id)),
    db.select({status:quotes.status,n:sql<number>`count(*)::int`}).from(quotes).groupBy(quotes.status),
    db.select({n:sql<number>`count(*)::int`}).from(messages).where(eq(messages.is_read,0))
   ]);
   const counts:Record<string,number>={all:0};for(const row of quoteTotals){counts[row.status]=Number(row.n);counts.all+=Number(row.n);}
   const newQuotes=counts.new||0,unreadMsg=Number(messageTotals[0]?.n||0);
   const requestedFilter=u.searchParams.get('status')||'all';
   const filter=Object.hasOwn(statuses,requestedFilter)?requestedFilter:'all';
   const filteredQuotes=name==='quotes'&&filter!=='all'?await db.select().from(quotes).where(eq(quotes.status,filter)).orderBy(desc(quotes.id)).limit(500):allQuotes;
   const id=Number(u.searchParams.get('id')||form.get('id')||0);
   const c=allCats.find(c=>c.id===id)||{id:0,title:'',slug:'',tagline:'',summary:'',body:'',items:'',icon:'box',image:'',status:'published'};
   if(name==='category-edit'&&id&&!allCats.some(c=>c.id===id))return page('not-found','Category not found',{},404);
   if(name==='export') {
    if(req.method!=='GET')return response('Method not allowed',405);
    const type=u.searchParams.get('type');if(!['quotes','messages'].includes(type||''))return response('Invalid export type',400);
    const rows=type==='quotes'?await db.select().from(quotes).orderBy(desc(quotes.id)):await db.select().from(messages).orderBy(desc(messages.id));
    headers.set('Content-Type','text/csv; charset=utf-8');headers.set('Content-Disposition',`attachment; filename="${type}.csv"`);
    return response(csv(rows));
   }
   const adminTitle:Record<string,string>={index:'Dashboard',quotes:'Quote requests',messages:'Messages',categories:'Product categories','category-edit':id?'Edit category':'Add category',settings:'Settings'};
   const adminNav={'index.php':['Dashboard',0],'quotes.php':['Quote requests',newQuotes],'messages.php':['Messages',unreadMsg],'categories.php':['Product categories',0],'settings.php':['Settings',0]};
   // Always render submitted values again after validation failures.
   const old=Object.fromEntries([...form.entries()].filter(([k,v])=>k!=='_csrf'&&typeof v==='string'));
   return page('admin-'+name,adminTitle[name],{
    adminTitle:adminTitle[name],cur:name==='category-edit'?'categories.php':name+'.php',items:adminNav,
    adminName:user?.name||user?.email||'admin',newQuotes,unreadMsg,counts,filter,QUOTE_STATUSES:statuses,groups,
    rows:name==='quotes'?filteredQuotes:name==='messages'?allMessages:allCats,
    quotes:allQuotes.slice(0,6),msgs:allMessages.slice(0,6),
    kpis:{'New quote requests':newQuotes,'Open quotes':['new','contacted','quoted'].reduce((n,status)=>n+(counts[status]||0),0),'Unread messages':unreadMsg,'Live categories':allCats.filter(x=>x.status==='published').length},
    id,c:errors.length?{...c,...old}:c,errors,old,posted:req.method==='POST',
    flash:errors.length?{msg:errors.join(' '),type:'error'}:u.searchParams.has('saved')?{msg:'Changes saved.',type:'success'}:null
   },errors.length?400:200);
  }
 } catch {
  return response('<!doctype html><html lang="en"><meta charset="utf-8"><title>Temporarily unavailable</title><h1>Temporarily unavailable</h1><p>Please try again shortly.</p></html>',503);
 }
};
export const config:Config={path:['/','/index.php','/about.php','/products.php','/product.php','/services.php','/contact.php','/quote.php','/admin','/admin/*','/media/*','/api/basket'],preferStatic:true};
