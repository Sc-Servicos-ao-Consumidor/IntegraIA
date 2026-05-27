# Prompt: Database Schema Generation

Create a suggested database schema in DBML format and save it in `@docs/database-schema.md`.

Read and use as context:

- `@docs/project-description.md`
- `@docs/user-stories.md`
- the uploaded XLS file `Product_packages_f(1).xlsx`
- the current project structure

The schema must support the product/package AI search feature: product catalog storage, package/SKU variations, XLS import, embeddings, RAG context retrieval, and API access.

Ask me clarifying questions using the `AskUserQuestion` tool before creating the final schema if any relationship, field type, or persistence strategy is unclear.

---

## XLS Structure

The XLS includes:

- `product_id`
- `product_name`
- `product_description`
- `product_img_url`
- `sku_package`
- `sku_package_name`
- `package_description`
- `gross_weight`
- `net_weight`
- `ean`
- `package_img_url`
- `category_name`
- `sub_category_name`
- `line_name`
- `brand_name`

Treat `product_id` as the source product identifier.

Treat `sku_package` as the source package/SKU identifier.

One product can have multiple package/SKU variations.

Repeated product rows must not create duplicated products.

---

## Laravel Database Guidelines

- Follow Laravel 13 and PostgreSQL conventions.
- Use plural snake_case table names.
- Use `id` as primary key unless there is a strong reason not to.
- Use foreign keys with `_id` suffix.
- Add `created_at` and `updated_at` timestamps.
- Add indexes for imports, joins, search, and API lookups.
- Add unique constraints to keep XLS imports idempotent.
- Keep the schema focused on this feature only.
- Do not redesign unrelated existing modules.

---

## Lookup Tables

Do not use DB enum fields or string-based enum/status columns.

For predefined values, use lookup tables with foreign keys.

For the XLS fields below, create or reuse lookup-style tables if the current project does not already have them:

- `category_name`
- `sub_category_name`
- `line_name`
- `brand_name`

Suggested lookup tables:

- `product_categories`
- `product_subcategories`
- `product_lines`
- `brands`

Lookup tables should usually include:

- `id`
- `name`
- `slug`
- `description` when useful
- `is_active` when useful
- timestamps

---

## Required Schema Areas

Design only the tables needed for:

1. **Products**
   - product source identifier
   - name
   - description
   - image URL
   - category/subcategory/line/brand relationships

2. **Product Packages**
   - package/SKU source identifier
   - package name
   - package description
   - gross weight
   - net weight
   - EAN
   - package image URL
   - product relationship

3. **XLS Import Tracking**
   - import runs
   - processed/created/updated/skipped/failed counts
   - import errors when useful

4. **Searchable Text**
   - generated searchable text or search hash
   - ability to detect outdated searchable content

5. **Embeddings**
   - source product/package relationship
   - provider/model metadata
   - dimensions
   - vector field if using pgvector
   - status via lookup table
   - failure metadata when useful

6. **RAG / AI / API**
   - minimal tables only if useful for tracking search requests, AI answer requests, or returned sources
   - do not over-engineer logs or API client tables unless clearly needed

---

## File/Image Fields

For external URLs from the XLS, use `_url` suffix:

- `product_img_url`
- `package_img_url`

For uploaded files stored by the platform, use `_path` suffix.

Do not confuse external URLs with local uploaded file paths.

---

## Expected Output

Generate DBML inside `@docs/database-schema.md`.

The DBML must include:

- tables
- columns
- primary keys
- foreign keys
- indexes
- unique constraints
- nullable fields where appropriate
- short notes for important design decisions

Keep the schema simple, maintainable, and Laravel-friendly.