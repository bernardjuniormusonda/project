import { pgTable, serial, text, integer, timestamp, jsonb } from 'drizzle-orm/pg-core';
export const settings = pgTable('settings', { k: text().primaryKey(), v: text().notNull().default('') });
export const categories = pgTable('categories', {
 id: serial().primaryKey(), slug: text().notNull().unique(), title: text().notNull(),
 summary: text().notNull().default(''), body: text().notNull().default(''), items: text().notNull().default(''),
 tagline: text().notNull().default(''), icon: text().notNull().default('box'), image: text().notNull().default(''),
 status: text().notNull().default('published'), sort_order: integer().notNull().default(0), created_at: timestamp().defaultNow().notNull()
});
export const quotes = pgTable('quotes', {
 id: serial().primaryKey(), ref: text().notNull().default(''), name: text().notNull(), organisation: text().notNull().default(''),
 email: text().notNull(), phone: text().notNull().default(''), location: text().notNull().default(''), items: text().notNull().default(''),
 details: text().notNull().default(''), needed_by: text().notNull().default(''), status: text().notNull().default('new'),
 admin_note: text().notNull().default(''), created_at: timestamp().defaultNow().notNull()
});
export const messages = pgTable('messages', {
 id: serial().primaryKey(), name: text().notNull(), email: text().notNull(), phone: text().notNull().default(''),
 subject: text().notNull().default(''), message: text().notNull(), is_read: integer().notNull().default(0), created_at: timestamp().defaultNow().notNull()
});
export const rateLimits = pgTable('rate_limits', { bucket: text().primaryKey(), hits: integer().notNull().default(1), expires_at: timestamp().notNull() });

export const baskets = pgTable('baskets', { id: text().primaryKey(), items: jsonb().$type<{item:string,cat:string,qty:number}[]>().notNull().default([]), updated_at: timestamp().notNull().defaultNow() });
