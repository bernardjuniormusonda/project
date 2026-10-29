CREATE TABLE "categories" (
	"id" serial PRIMARY KEY,
	"slug" text NOT NULL UNIQUE,
	"title" text NOT NULL,
	"summary" text DEFAULT '' NOT NULL,
	"body" text DEFAULT '' NOT NULL,
	"items" text DEFAULT '' NOT NULL,
	"tagline" text DEFAULT '' NOT NULL,
	"icon" text DEFAULT 'box' NOT NULL,
	"image" text DEFAULT '' NOT NULL,
	"status" text DEFAULT 'published' NOT NULL,
	"sort_order" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "messages" (
	"id" serial PRIMARY KEY,
	"name" text NOT NULL,
	"email" text NOT NULL,
	"phone" text DEFAULT '' NOT NULL,
	"subject" text DEFAULT '' NOT NULL,
	"message" text NOT NULL,
	"is_read" integer DEFAULT 0 NOT NULL,
	"created_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "quotes" (
	"id" serial PRIMARY KEY,
	"ref" text DEFAULT '' NOT NULL,
	"name" text NOT NULL,
	"organisation" text DEFAULT '' NOT NULL,
	"email" text NOT NULL,
	"phone" text DEFAULT '' NOT NULL,
	"location" text DEFAULT '' NOT NULL,
	"items" text DEFAULT '' NOT NULL,
	"details" text DEFAULT '' NOT NULL,
	"needed_by" text DEFAULT '' NOT NULL,
	"status" text DEFAULT 'new' NOT NULL,
	"admin_note" text DEFAULT '' NOT NULL,
	"created_at" timestamp DEFAULT now() NOT NULL
);
--> statement-breakpoint
CREATE TABLE "rate_limits" (
	"bucket" text PRIMARY KEY,
	"hits" integer DEFAULT 1 NOT NULL,
	"expires_at" timestamp NOT NULL
);
--> statement-breakpoint
CREATE TABLE "settings" (
	"k" text PRIMARY KEY,
	"v" text DEFAULT '' NOT NULL
);
