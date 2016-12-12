SELECT * FROM proteinpos_legacy.lkpbrand;
SELECT * FROM proteinpos_legacy.txninventory;
SELECT * FROM proteinpos_legacy.lkpitemclass;
SELECT * FROM proteinpos_legacy.lkpitemsubclass;
SELECT * FROM proteinpos.brands;
SELECT * FROM proteinpos.product_categories;
SELECT * FROM proteinpos.products;
SELECT * FROM proteinpos.login_sessions;
SELECT * FROM proteinpos.users;

-- Brands Seeder
INSERT INTO proteinpos.brands(created_at, updated_at, created_by, updated_by, name)
SELECT
    NOW(), NOW(), 1, 1, description
FROM
    proteinpos_legacy.lkpbrand
        LEFT JOIN
    proteinpos.brands ON lkpbrand.description = brands.name COLLATE utf8_unicode_ci
WHERE
    brands.id IS NULL;

-- Category Seeder
INSERT INTO proteinpos.product_categories(created_at, updated_at, created_by, updated_by, name)
SELECT
    NOW(), NOW(), 1, 1, description
FROM
    proteinpos_legacy.lkpitemclass
        LEFT JOIN
    proteinpos.product_categories ON lkpitemclass.description = product_categories.name COLLATE utf8_unicode_ci
WHERE
    product_categories.id IS NULL;
INSERT INTO proteinpos.product_categories(created_at, updated_at, created_by, updated_by, name, parent_id)
SELECT
    NOW(), NOW(), 1, 1, lkpitemsubclass.description, parent.id
FROM
    proteinpos_legacy.lkpitemsubclass
        LEFT JOIN
    proteinpos.product_categories ON lkpitemsubclass.description = product_categories.name COLLATE utf8_unicode_ci
        JOIN
    proteinpos_legacy.lkpitemclass ON lkpitemsubclass.subclass_id = lkpitemclass.id
        JOIN
    proteinpos.product_categories parent ON lkpitemclass.description = parent.name COLLATE utf8_unicode_ci
WHERE
    product_categories.id IS NULL;

-- Product Seeder
INSERT INTO proteinpos.products(created_at, updated_at, created_by, updated_by, product_category_id, brand_id, name, price)
SELECT
    NOW(), NOW(), 1, 1, COALESCE(child.id, parent.id), brands.id, txninventory.item_name, 0
FROM
    proteinpos_legacy.txninventory
        LEFT JOIN
    proteinpos_legacy.lkpitemsubclass ON txninventory.item_subclass_id = lkpitemsubclass.id
        LEFT JOIN
    proteinpos_legacy.lkpitemclass ON txninventory.item_class_id = lkpitemclass.id
        LEFT JOIN
    proteinpos.product_categories child ON lkpitemsubclass.description = child.name COLLATE utf8_unicode_ci
        LEFT JOIN
    proteinpos.product_categories parent ON lkpitemclass.description = parent.name COLLATE utf8_unicode_ci
        LEFT JOIN
    proteinpos_legacy.lkpbrand ON txninventory.manufacturer_id = lkpbrand.id
        LEFT JOIN
    proteinpos.brands ON lkpbrand.description = brands.name COLLATE utf8_unicode_ci
        LEFT JOIN
    proteinpos.products ON txninventory.item_name = products.name COLLATE utf8_unicode_ci
WHERE
    products.id IS NULL;