import test from 'node:test';
import assert from 'node:assert/strict';
import { helpers, render } from '../server/render.js';
import { publicSubmission, csv, validId } from '../server/validation.js';
import { groups } from '../server/settings-fields.js';
const category={id:1,slug:'office-supplies',title:'Office <supplies>',tagline:'For every office',summary:'Quality products',body:'A paragraph.\n\nAnother paragraph.',items:'Paper\nPens',icon:'box',image:'assets/img/products/office-school.jpg',status:'published',sort_order:1};
const quote={id:1,ref:'PE-261029-0001',name:'Example <customer>',email:'example@example.com',phone:'',organisation:'Test company',location:'Lusaka',needed_by:'Next week',items:'2 x paper',details:'Details',status:'new',admin_note:'',created_at:new Date()};
const message={id:1,name:'Example customer',email:'example@example.com',phone:'',subject:'',message:'Please contact us.',is_read:0,created_at:new Date()};
const settings={site_name:'Printel Enterprise',hero_title:'Quality products',hero_text:'Business supplies',tagline:'Connecting needs.',phone:'+260 962 342 018',whatsapp:'260962342018',why_us:'Quality\nReliability',credentials:'Registered business',clients:'Example business'};
const context={...helpers(settings,'a'.repeat(64)),fullTitle:'Printel Enterprise',pageTitle:'Products',metaDesc:'Business supplies',current:'index.php',flash:null,errors:[],old:Object.fromEntries(['name','email','phone','subject','message','organisation','location','needed_by','items','details'].map(k=>[k,''])),done:null,cats:[category],featured:[category],c:category,others:[],items:['Paper','Pens'],services:[['box','Procurement','Reliable supply']],adminTitle:'Dashboard',adminName:'Admin',cur:'index.php',newQuotes:1,unreadMsg:1,counts:{all:1,new:1},filter:'all',QUOTE_STATUSES:{new:'New',contacted:'Contacted',quoted:'Quoted',won:'Won',closed:'Closed'},groups,posted:false,id:1,rows:[quote],quotes:[quote],msgs:[message],kpis:{'New quotes':1},recover:false};
for(const view of ['index','about','products','product','services','contact','quote','login','not-found'])test(`renders public ${view}`,async()=>{
 const html=await render(view,context);assert.match(html,/<!DOCTYPE html>/);assert.doesNotMatch(html,/<\?php|<%/);assert.match(html,/<main id="main">/);
 if(view==='product') {assert.match(html,/Office &lt;supplies&gt;/);assert.match(html,/data-item="Paper"/);}
});
for(const name of ['index','quotes','messages','categories','category-edit','settings'])test(`renders admin ${name}`,async()=>{
 const html=await render('admin-'+name,{...context,rows:name==='messages'?[message]:name==='categories'?[category]:[quote],items:{'index.php':['Dashboard',0],'quotes.php':['Quote requests',1]}});
 assert.match(html,/<main class="admin-main"/);assert.doesNotMatch(html,/<\?php|<%/);
});
test('quote confirmation and contact success render without exposing customer details',async()=>{
 const html=await render('quote',{...context,done:'PE-261029-0002'});assert.match(html,/id="quote-done"/);assert.doesNotMatch(html,/id="quote-form"/);
});
test('empty catalogue and validation failures render',async()=>{
 const html=await render('products',{...context,cats:[]});assert.match(html,/Products will be listed here soon/);
 const contact=await render('contact',{...context,errors:['Invalid email']});assert.match(contact,/Invalid email/);
});
test('validates quote and contact submissions on the server',()=>{
 const f=new FormData();f.set('name','Ada');f.set('email','ada@example.com');f.set('items','10 reams of paper');
 assert.equal(publicSubmission(f,true).items,'10 reams of paper');
 f.set('email','bad');assert.throws(()=>publicSubmission(f,true),/valid email/);
 f.set('email','ada@example.com');f.set('message','short');assert.throws(()=>publicSubmission(f,false),/10 and 5000/);
 f.set('message','A message long enough');assert.equal(publicSubmission(f,false).name,'Ada');
 f.set('name','x'.repeat(121));assert.throws(()=>publicSubmission(f,false),/too long/);
});
test('CSV export neutralizes formulas and escapes quotes and newlines',()=>{
 const output=csv([{name:'=1+1',note:'"Hello"\nWorld'}]);assert.ok(output.includes('"\'=1+1"'));assert.ok(output.includes('""Hello""\nWorld'));
});
test('rejects unsafe record IDs',()=>{for(const value of ['1 OR 1=1',-1,0,1.5,'NaN'])assert.throws(()=>validId(value));assert.equal(validId('10'),10);});

test('rejects cross-site and missing-CSRF writes before database access',async()=>{
 const {default:handler}=await import('../netlify/functions/site.mts');
 const context={} as import('@netlify/functions').Context;
 const crossSite=await handler(new Request('https://printel.netlify.app/admin/quotes.php',{method:'POST',headers:{Origin:'https://other.example'},body:new URLSearchParams({action:'delete',id:'1'})}),context);
 assert.equal(crossSite.status,403);
 const missingToken=await handler(new Request('https://printel.netlify.app/contact.php',{method:'POST',headers:{Origin:'https://printel.netlify.app'},body:new URLSearchParams({name:'Ada'})}),context);
 assert.equal(missingToken.status,403);
});
test('unauthenticated admin and export requests redirect to login',async()=>{
 const {default:handler}=await import('../netlify/functions/site.mts');
 for(const path of ['/admin/','/admin/export.php?type=quotes']) {
  const result=await handler(new Request('https://printel.netlify.app'+path),{} as import('@netlify/functions').Context);
  assert.equal(result.status,303);assert.equal(result.headers.get('Location'),'/admin/login.php');
  assert.match(result.headers.get('Cache-Control')||'',/no-store/);
 }
});
