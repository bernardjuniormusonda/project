-- Existing business content and customer records; no PHP administrator credentials.
INSERT INTO "settings" ("k","v") VALUES ('site_name','Printel Enterprise') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('tagline','Connecting needs. Delivering solutions.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('hero_kicker','Non-specialised wholesale trade') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('hero_title','Your one-stop source for quality products and solutions.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('hero_text','From printing and branding to computers, office supplies, workwear and cleaning products, Printel Enterprise supplies businesses, institutions and communities across Zambia in bulk, at competitive prices.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('motto','Creativity is nothing without purpose. We brand anything.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('about_who','Printel Enterprise is a trusted wholesale supplier of diverse products for businesses, industries, institutions and communities.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('about_what','We provide quality products in bulk at competitive prices, ensuring reliability, consistency and value for our customers.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('mission','To deliver a wide range of quality products and solutions that support businesses, industries, institutions and communities across Zambia.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('proposition_network','Partnering with leading suppliers and brands to bring you the best.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('proposition_dedication','Committed to quality, reliability and customer satisfaction in every transaction.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('why_us','Wide range of products
Competitive prices
Quality guaranteed
Reliable supply & service') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('institutions_note','We provide affordable procurement services to academic institutions.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('credentials','Registered business (PACRA)
ZRA tax clearance') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('clients','Micro-Pro Innovations Limited
Meanwood General Insurance Company
Visberg Construction
Finchgold General Dealers') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('phone','+260 962 342 018') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('phone2','+260 973 383 435') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('whatsapp','260962342018') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('email','Printelzm@gmail.com') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('address','Off Burma Road, Kabwata
Lusaka, Zambia') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('hours','Mon to Fri, 08:00 to 17:00') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('facebook','') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('facebook_name','Printel Enterprises') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('show_bank','0') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('bank_name','First National Bank (FNB) Zambia') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('bank_account_name','Printel Enterprises') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('bank_account_no','63001218783') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('bank_branch','Commercial Suite') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('bank_branch_no','260001') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('bank_swift','FIRNZMLX') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('footer_note','Registered business in Zambia.') ON CONFLICT DO NOTHING;
INSERT INTO "settings" ("k","v") VALUES ('seeded','1') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (1,'printing-branding','Printing & Branding','Bulk photocopying, printing, banners, ID and business cards, and printed stationery.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Bulk photocopying
Printing
ID cards
Business cards
Printed stationery (receipts, invoices, delivery notes)
Banners and roll-up banners
Name tags
Screen printing and DTG (direct to garment)
Embroidery
Coroplast yard signs
Branded mugs','We brand anything.','print','assets/img/products/digital-printing.jpg','published',1,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (2,'promotional-products','Promotional Products','Branded caps, mugs, bottles, bags, notebooks, pens and USB drives for your business or event.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Branded caps
Branded mugs
Water bottles
Bags
Notebooks and diaries
Pens
Keyrings
USB flash drives','Put your logo everywhere.','gift','assets/img/products/promotional.jpg','published',2,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (3,'printers-copiers','Printers & Copiers','Multifunction printers, photocopiers, scanners and large-format printers, with parts and maintenance.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Multifunction printers
Photocopiers and scanners
Large-format printers
Printer accessories and parts
Maintenance and spare parts','Print. Copy. Scan. Perform.','print','assets/img/products/printers-copiers.jpg','published',3,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (4,'ink-toners-consumables','Ink, Toners & Consumables','Original and compatible toners, ink cartridges and other bulk printing consumables.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Original and compatible toners
Ink cartridges
Drums and imaging units
Bulk printing consumables','Quality. Compatibility. Value.','drop','assets/img/products/ink-toners.jpg','published',4,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (5,'it-equipment-computer-accessories','IT Equipment & Computer Accessories','Laptops, desktops, monitors, networking devices, storage and computer peripherals.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Computers and laptops
Monitors and accessories
Printers and scanners
Networking devices
Storage and memory
Computer peripherals','Technology for every business.','laptop','assets/img/products/it-equipment.jpg','published',5,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (6,'cell-phones-gadgets-accessories','Cell Phones, Gadgets & Accessories','Cell phones and gadgets with chargers, cables, earphones, power banks, cases and more.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Cell phones and gadgets
Chargers and power adapters
Cables and connectors
Earphones and headsets
Power banks
Phone cases and covers
Screen protectors
Car holders and mounts
Memory cards and USB drives','Connect. Protect. Power.','phone','assets/img/products/phones.jpg','published',6,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (7,'office-school-supplies','Office & School Supplies','Binders, files, folders, desk accessories and organisers for offices and schools.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Binders, files and folders
Desk accessories
Office furniture and organisers
Staplers, tape and sticky notes
Calculators','Everyday items. Exceptional quality.','clip','assets/img/products/office-school.jpg','published',7,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (8,'stationery-paper','Stationery & Paper','Paper, notebooks, pens, markers and packaging materials, in bulk.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','A4 paper and other paper sizes
Notebooks
Pens and markers
Packaging materials','Everything you write and print on.','book','assets/img/products/stationery-paper.jpg','published',8,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (9,'workwear-ppe','Workwear & PPE','Overalls, reflective jackets, helmets, gloves and eye protection for teams and sites.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Overalls and workwear
Reflective jackets
Safety helmets
Gloves
Safety glasses
Safety boots','Work safe. Look professional.','shield','assets/img/products/workwear-ppe.jpg','published',9,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (10,'safety-signs-systems','Safety Signs & Systems','Safety and warning signs, first aid kits, fire extinguishers and detection equipment.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Safety and warning signs
First aid kits
Fire extinguishers
Fire and smoke detection
Emergency exit signs','Clear signs. Safer places.','sign','assets/img/products/safety-signs.jpg','published',10,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (11,'cleaning-janitorial-supplies','Cleaning & Janitorial Supplies','Detergents, cleaning tools, paper products and hygiene supplies for any premises.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Detergents and chemicals
Cleaning tools and equipment
Paper products
Waste management solutions
Hygiene and sanitation products','Clean spaces. Safer places.','spark','assets/img/products/cleaning.jpg','published',11,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
INSERT INTO "categories" ("id","slug","title","summary","body","items","tagline","icon","image","status","sort_order","created_at") VALUES (12,'tools-equipment','Tools & Equipment','Hand tools, power tools, tool boxes and measuring equipment.','Sample description: tell customers what you stock in this category, the brands you carry, minimum order sizes and how quickly you can deliver.

Edit or replace this text from the admin panel (Categories).','Hand tools
Power tools
Tool boxes
Measuring tools','The right tool for the job.','wrench','assets/img/products/tools.jpg','published',12,'2026-09-19 11:42:50') ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('categories', 'id'), COALESCE((SELECT MAX(id) FROM categories), 1), EXISTS(SELECT 1 FROM categories));
INSERT INTO "quotes" ("id","ref","name","organisation","email","phone","location","items","details","needed_by","status","admin_note","created_at") VALUES (1,'PE-261026-0001','Bernard Musonda','ministry of health','bernardjuniormusonda@gmail.com','+260973383435','Lusaka','10 x A4 bond paper','just the main package at','30 november','new','','2026-10-26 08:40:03') ON CONFLICT DO NOTHING;
SELECT setval(pg_get_serial_sequence('quotes', 'id'), COALESCE((SELECT MAX(id) FROM quotes), 1), EXISTS(SELECT 1 FROM quotes));
SELECT setval(pg_get_serial_sequence('messages', 'id'), COALESCE((SELECT MAX(id) FROM messages), 1), EXISTS(SELECT 1 FROM messages));
