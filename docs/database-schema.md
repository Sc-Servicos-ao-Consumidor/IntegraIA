# Database Schema — AI-Powered Product & Packaging Search

> **Feature scope:** Tables for `CatalogProduct`, `CatalogPackage`, XLS import tracking, searchable text, and pgvector embeddings. Existing modules (`products`, `recipes`, `contents`, etc.) are not modified.

---

## Design Decisions

| Decision | Choice | Reason |
|---|---|---|
| Taxonomy fields | Plain strings on `catalog_products` | Explicit architectural decision in `user-stories.md` — avoids joins for a read-heavy, search-first feature |
| Embedding storage | `vector(3072)` on `catalog_products` | Matches existing infrastructure (`products`, `product_chunks`); keeps queries simple |
| Embedding scope | Product level only | Packages are included in the product's searchable text per US-3.1 |
| Staleness detection | `embedded_at` vs `updated_at` | No hash needed — comparison is idiomatic and sufficient |
| Import status | Lookup table (`catalog_import_statuses`) | Project guideline: no DB enums or string-based status columns |
| Idempotency (products) | Unique on `(tenant_id, codigo_padrao)` | Enables safe upsert on re-import |
| Idempotency (packages) | Unique on `(catalog_product_id, sku_package)` | Enables safe upsert on re-import |
| Tenant scoping | `tenant_id` on `catalog_products` only | Packages inherit scope through the product relationship |
| RAG / search logging | No new tables | Existing `assistant_logs` infrastructure is sufficient |
| Image fields | `_url` suffix | External URLs from XLS; not local uploaded file paths |

---

## DBML

```dbml
// ============================================================
// catalog_import_statuses — lookup table for import run states
// Valid values seeded: pending, running, completed, failed
// ============================================================

Table catalog_import_statuses {
  id         bigint      [pk, increment]
  name       varchar     [not null, unique, note: 'pending | running | completed | failed']
  created_at timestamp
  updated_at timestamp
}

// ============================================================
// catalog_import_runs — one record per XLS import execution
// ============================================================

Table catalog_import_runs {
  id                          bigint    [pk, increment]
  tenant_id                   bigint    [not null, ref: > tenants.id]
  catalog_import_status_id    bigint    [not null, ref: > catalog_import_statuses.id]
  file_name                   varchar   [null, note: 'Original XLS filename for traceability']
  total_rows                  int       [not null, default: 0]
  created_count               int       [not null, default: 0]
  updated_count               int       [not null, default: 0]
  skipped_count               int       [not null, default: 0]
  failed_count                int       [not null, default: 0]
  started_at                  timestamp [null]
  completed_at                timestamp [null]
  created_at                  timestamp
  updated_at                  timestamp

  indexes {
    tenant_id                               [name: 'catalog_import_runs_tenant_id_idx']
    (tenant_id, catalog_import_status_id)   [name: 'catalog_import_runs_tenant_status_idx']
  }
}

// ============================================================
// catalog_import_errors — row-level errors for a given import run
// Optional: only created when a row fails processing
// ============================================================

Table catalog_import_errors {
  id                      bigint  [pk, increment]
  catalog_import_run_id   bigint  [not null, ref: > catalog_import_runs.id]
  row_number              int     [null, note: 'XLS row index for post-mortem debugging']
  row_data                json    [null, note: 'Raw XLS row snapshot for debugging']
  error_message           text    [not null]
  created_at              timestamp
  updated_at              timestamp

  indexes {
    catalog_import_run_id   [name: 'catalog_import_errors_run_id_idx']
  }
}

// ============================================================
// catalog_products — one row per unique XLS product_id per tenant
// ============================================================

Table catalog_products {
  id                   bigint      [pk, increment]
  tenant_id            bigint      [not null, ref: > tenants.id]

  // Source identifier — drives idempotency on import
  codigo_padrao        varchar     [not null, note: 'Source: XLS product_id']
  sku                  varchar     [null] 
  product_name         varchar     [not null]
  product_description  text        [null]
  product_img_url      varchar     [null, note: 'External URL from XLS; not a local file path']

  // Taxonomy stored as plain strings per user-stories.md architectural decision.
  // Avoids joins for a search-first feature; values come directly from the XLS.
  category_name        varchar     [null]
  sub_category_name    varchar     [null]
  line_name            varchar     [null]
  brand_name           varchar     [null]

  // Searchable text built from product fields + all related package fields (US-3.1).
  // Stored to support auditing and to detect when content changed after embedding.
  searchable_text      text        [null]

  // pgvector embedding — 3072 dimensions matches existing infrastructure.
  // NULL means the record has not been embedded yet (US-6.1: whereNull('embedding')).
  // Compare embedded_at with updated_at to detect stale embeddings.
  embedding            "vector(3072)" [null]
  embedded_at          timestamp   [null, note: 'When embedding was last generated']

  created_at           timestamp
  updated_at           timestamp

  indexes {
    (tenant_id, codigo_padrao)  [unique, name: 'catalog_products_tenant_codigo_unique']
    tenant_id                   [name: 'catalog_products_tenant_id_idx']
    embedded_at                 [name: 'catalog_products_embedded_at_idx', note: 'Locate unembedded or stale records']
    category_name               [name: 'catalog_products_category_name_idx']
    brand_name                  [name: 'catalog_products_brand_name_idx']
  }
}

// ============================================================
// catalog_packages — one row per unique sku_package per product
// ============================================================

Table catalog_packages {
  id                   bigint          [pk, increment]
  catalog_product_id   bigint          [not null, ref: > catalog_products.id]

  // Source identifier — drives idempotency on import
  sku_package          varchar         [not null, note: 'Source: XLS sku_package']

  sku_package_name     varchar         [null]
  package_description  text            [null]
  gross_weight         decimal(10, 4)  [null, note: 'Stored as decimal to avoid float precision issues']
  net_weight           decimal(10, 4)  [null]
  ean                  varchar         [null]
  package_img_url      varchar         [null, note: 'External URL from XLS; not a local file path']

  created_at           timestamp
  updated_at           timestamp

  indexes {
    (catalog_product_id, sku_package)   [unique, name: 'catalog_packages_product_sku_unique']
    catalog_product_id                  [name: 'catalog_packages_product_id_idx']
    ean                                 [name: 'catalog_packages_ean_idx']
  }
}
```

---

## Table Summary

| Table | Rows grow | Purpose |
|---|---|---|
| `catalog_import_statuses` | Seeded (4 rows) | Lookup for import run state |
| `catalog_import_runs` | Per import execution | Tracks summary counts and status of each import run |
| `catalog_import_errors` | Per failed XLS row | Row-level error log for debugging import failures |
| `catalog_products` | Per unique product per tenant | Core product catalog; holds embedding and searchable text |
| `catalog_packages` | Per unique SKU per product | Package/SKU variations linked to a catalog product |

---

## XLS → Column Mapping

| XLS Field | Table | Column |
|---|---|---|
| `product_id` | `catalog_products` | `codigo_padrao` |
| `product_name` | `catalog_products` | `product_name` |
| `product_description` | `catalog_products` | `product_description` |
| `product_img_url` | `catalog_products` | `product_img_url` |
| `category_name` | `catalog_products` | `category_name` |
| `sub_category_name` | `catalog_products` | `sub_category_name` |
| `line_name` | `catalog_products` | `line_name` |
| `brand_name` | `catalog_products` | `brand_name` |
| `sku_package` | `catalog_packages` | `sku_package` |
| `sku_package_name` | `catalog_packages` | `sku_package_name` |
| `package_description` | `catalog_packages` | `package_description` |
| `gross_weight` | `catalog_packages` | `gross_weight` |
| `net_weight` | `catalog_packages` | `net_weight` |
| `ean` | `catalog_packages` | `ean` |
| `package_img_url` | `catalog_packages` | `package_img_url` |

---

## Staleness Detection Pattern

A `CatalogProduct` embedding is considered stale when:

```sql
embedded_at IS NULL
OR embedded_at < updated_at
```

This covers both unembedded records and records whose content changed after the last embedding run.

---

## Relationships

```
tenants
  └── catalog_products (tenant_id)
        └── catalog_packages (catalog_product_id)

catalog_import_statuses
  └── catalog_import_runs (catalog_import_status_id)
        └── catalog_import_errors (catalog_import_run_id)

catalog_import_runs
  └── tenant_id → tenants
```
