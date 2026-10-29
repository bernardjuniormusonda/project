CREATE TABLE "baskets" (
	"id" text PRIMARY KEY,
	"items" jsonb DEFAULT '[]' NOT NULL,
	"updated_at" timestamp DEFAULT now() NOT NULL
);
