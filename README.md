# Printel Enterprise

Printel's public website and administration panel run on Netlify Functions with the original branding, responsive layouts, product photographs, and catalogue. Netlify Database (Postgres with Drizzle) stores settings, categories, quotes, contact messages, quote baskets, and submission limits. Netlify Blobs stores images uploaded by administrators. Netlify Identity handles administrator accounts and password recovery.

## Deployment

Connect this repository to the existing Printel Netlify project. The repository's `netlify.toml` publishes only `public/`, packages the TypeScript function, and includes its server-rendered templates. No application build command is required. Netlify installs the npm dependencies and applies the SQL migrations in `netlify/database/migrations/` before publishing. The initial migrations preserve the existing business settings, twelve product categories, and existing customer records from the SQLite site.

The old PHP files and SQLite source remain outside the published directory for reference. They are not served or executed. SQLite is not used by the deployed application. Database changes and uploaded images persist across deployments; edits in the admin panel take effect without a rebuild.

## Administrator setup

1. Open the project's Netlify dashboard and select Identity.
2. Invite the administrator by email. No public administrator signup or first-visitor setup is available.
3. Follow the invitation link to set a password.
4. Open that user in Identity and assign the `admin` role.
5. Visit `/admin/` and log in with the invited email and password.

Invitation and password recovery links work from the home page or `/admin/login.php`. Password resets are available from the login page and admin settings. Accounts without the server-controlled `admin` role cannot access administration pages or CSV exports.

## Features

The public pages are available at their existing addresses: `/`, `/about.php`, `/products.php`, `/product.php?slug=…`, `/services.php`, `/quote.php`, and `/contact.php`. Product search, product images, WhatsApp links, and mobile navigation are retained. Quote lists persist in the database under an anonymous browser cookie. Quote requests receive a reference number after being saved. Contact and quote submissions appear in the admin panel.

Administrators can review, filter, annotate, update, delete, and export quotes; read, mark, delete, and export messages; manage category descriptions and item lists; publish, hide, and reorder categories; upload JPG, PNG, or WebP images up to 3 MB; and edit business settings, contact information, and optional banking details. The original optional PHP `mail()` notification integration is not used: new submissions are reviewed in the dashboard.

Forms validate data on the server and require an origin and CSRF token. Public submissions use a honeypot and database-backed submission limits. All database queries are parameterized, template output is escaped, uploaded image formats are checked from their file signatures, and CSV exports neutralize spreadsheet formulas. Private responses are not cached. Only public assets are published, so configuration, source files, database exports, and customer records are not exposed as static downloads.

## Local development and checks

Use Node.js 22.12 or newer and install dependencies with `npm ci`. Use `netlify dev --port 8889` for the Netlify runtime, Identity, and storage integration. Database migrations are applied by Netlify's deployment lifecycle; do not apply them manually.

Run `npm run check` for TypeScript checks and `npm test` for template rendering, validation, CSV escaping, CSRF, and unauthenticated administration checks. For a schema change, update `db/schema.ts` and generate a named migration with `npx drizzle-kit generate --name add_descriptive_change`.
