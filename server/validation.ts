export class ValidationError extends Error {}
export function text(form: FormData, key: string, max=4000) {
 const raw=form.get(key); const value=typeof raw==='string' ? raw.trim() : '';
 if(value.length>max) throw new ValidationError(`${key.replaceAll('_',' ')} is too long (maximum ${max} characters).`);
 return value;
}
export function email(value:string) {return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) && value.length<=190;}
export function publicSubmission(form:FormData, quote:boolean) {
 const data:Record<string,string>={};
 const fields=quote ? {name:120,email:190,phone:40,organisation:160,location:160,needed_by:80,items:6000,details:4000} : {name:120,email:190,phone:40,subject:160,message:5000};
 for(const [key,max] of Object.entries(fields)) data[key]=text(form,key,max!);
 if(!data.name)throw new ValidationError('Please enter your name.');
 if(!email(data.email))throw new ValidationError('Please enter a valid email address.');
 if(data.phone && !/^[0-9+()\-\s]{6,40}$/.test(data.phone))throw new ValidationError('Please enter a valid phone number, or leave it blank.');
 if(quote && (data.items.length+data.details.length)<5)throw new ValidationError('Please tell us what you need in the item list or details box.');
 if(!quote && data.message.length<10)throw new ValidationError('Please write a message between 10 and 5000 characters.');
 return data;
}
export function csv(rows:Record<string,unknown>[]) {
 if(!rows.length)return '';
 const cell=(v:unknown)=>'"'+String(v??'').replace(/^[=+@\-\t\r]/,"'$&").replaceAll('"','""')+'"';
 const keys=Object.keys(rows[0]);
 return '\uFEFF'+[keys.map(cell).join(','),...rows.map(r=>keys.map(k=>cell(r[k] instanceof Date ? (r[k] as Date).toISOString() : r[k])).join(','))].join('\r\n');
}
export function validId(v:unknown) { const n=Number(v); if(!Number.isSafeInteger(n)||n<1)throw new ValidationError('Invalid record.'); return n; }
