# Printel Enterprise website

A complete website with a front end (HTML, CSS, JavaScript) and a back end (PHP + SQLite database + admin panel), built from the Printel company profile.

## What is included

**Public website**
- Home: hero, trust strip, featured product categories, who we are, services, organisations served
- About: who we are, what we do, mission, why choose us, unique proposition
- Products: 12 categories with photos and live search, and a page for each category listing what you supply
- Services: procurement, wholesale distribution, corporate branding, sourcing, web design, after-sales
- Request a quote: customers tick items on category pages ("Add"), set quantities, and send one quote request. They get a reference number such as PE-260919-0001
- Contact: message form, phone numbers, email, address, hours, and (optional) banking details
- WhatsApp button, mobile menu, works on phones and desktops, no external dependencies

**Admin panel (`/admin/`)**
- Dashboard with new quotes and unread messages
- Quote requests: filter by status (New, Contacted, Quoted, Won, Closed), add an internal note, click-to-call and click-to-email, CSV download
- Messages: read, mark, delete, CSV download
- Product categories: add, edit, delete, publish/unpublish, reorder, upload images, edit the item list customers can add to a quote
- Settings: business name, home/about text, contact details, WhatsApp, Facebook, credentials, organisations served, banking details (with a show/hide switch), change password

**Security:** CSRF protection on all forms, prepared SQL statements, escaped output, password hashing, login rate limiting, spam honeypot and form rate limiting, image upload checks, security headers.

## Requirements

PHP 8.1 or newer with `pdo_sqlite` and `fileinfo` (standard on most hosts). No separate database server is needed.

## Try it on your computer

```
cd printel-site
php -S localhost:8000
```

Open http://localhost:8000

## Put it on a web host

1. Upload everything inside `printel-site` to your hosting (for example into `public_html`).
2. Make sure the `data/` and `uploads/` folders are writable by the web server.
3. Visit `yourdomain/admin/`. The first time, you create the administrator account.
4. Check **Settings** and **Product categories** and adjust anything you want to change.

## Before you go live: please check

- **Banking details** are hidden by default. Turn them on in Settings only if you want them public.
- **Organisations you serve** lists four client names from the profile. Keep only those who agreed to be named (edit in Settings).
- **Credentials** shows "Registered business (PACRA)" and "ZRA tax clearance". Only keep what is currently valid. The ZPPA certificate in the profile showed an expiry of 06/11/2025, so it is not listed.
- **WhatsApp** uses +260 962 342 018. Change or clear it in Settings.
- The category photos were cut from your company profile PDF. Replace them with sharper photos in Admin > Product categories when you have them.
- Add your **Facebook page link** in Settings (the profile only gave the page name).
- Use HTTPS on your domain, and back up `data/site.sqlite` and `uploads/` regularly.
- Optional: set `notify_email` in `config.php` to be emailed for each new quote or message (needs PHP `mail()` on your host).

### Nginx users
The `.htaccess` files only work on Apache. On Nginx, deny access to `/data/`, `/includes/` and `config.php`, and block PHP execution inside `/uploads/`.

## Folder map

```
index.php, about.php, products.php, product.php,
services.php, quote.php, contact.php     public pages
includes/                                shared code (database, helpers, header, footer)
admin/                                   admin panel
assets/css, assets/js, assets/img        styles, scripts, logo and product photos
data/                                    SQLite database (created automatically)
uploads/                                 images uploaded from the admin
config.php                               technical settings
```
