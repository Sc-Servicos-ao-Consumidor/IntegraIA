## Phase 3 — Searchable Text and Embeddings

> **User Stories:** US-3.1, US-3.2

---

### Phase 3.1 — `CatalogSearchableTextService`

- [ ] Create `app/Services/Catalog/CatalogSearchableTextService.php`
- [ ] Method `build(CatalogProduct $product): string`
  - Eager-loads `packages` if not already loaded
  - Concatenates non-null fields in this order: `product_name`, `product_description`, `brand_name`, `category_name`, `sub_category_name`, `line_name`
  - Appends all related package fields (per package): `sku_package_name`, `package_description`, `ean`
  - Omits null or empty-string fields from the output
  - Returns a single trimmed string
- [ ] Method `buildAndStore(CatalogProduct $product): void`
  - Calls `build()` and saves result to `searchable_text` on the product

**Pest tests:**
```
- it includes product_name, product_description, brand_name, category_name, sub_category_name, and line_name when present
- it omits null fields from the output
- it omits empty-string fields from the output
- it appends sku_package_name, package_description, and ean from all related packages
- it returns an empty string when all fields are null and no packages exist
- it returns a non-empty string even when only product_name is present
- buildAndStore persists searchable_text on the CatalogProduct record
```

---

### Phase 3.2 — `GenerateCatalogProductEmbedding` Job

- [ ] Create `app/Jobs/GenerateCatalogProductEmbedding.php` implementing `ShouldQueue`
- [ ] Constructor accepts `int $catalogProductId`
- [ ] `handle()` method:
  - Loads `CatalogProduct` (returns early if not found)
  - Calls `CatalogSearchableTextService::buildAndStore()` to refresh `searchable_text`
  - Skips embedding generation if `searchable_text` is empty; logs a warning
  - Calls `laravel/ai` SDK to generate an embedding from `searchable_text`
  - Saves the vector to `embedding` and sets `embedded_at = now()`
  - Wraps the AI call in a try/catch; logs failures and re-throws so the queue retries
- [ ] Set `$tries = 3` and `$backoff = [30, 60]` on the job

**Pest tests:**
```
- it generates an embedding and sets embedded_at on the CatalogProduct
- it refreshes searchable_text before generating the embedding
- it skips embedding and logs a warning when searchable_text is empty
- it returns early without error when the CatalogProduct does not exist
- it marks the job as failed after exhausting retries (mock AI failure)
```

---

### Phase 3.3 — `catalog:embed` Artisan Command

- [ ] Create `app/Console/Commands/Catalog/EmbedCatalogCommand.php`
- [ ] Signature: `catalog:embed {--tenant= : Tenant ID (required)} {--refresh : Re-embed already-embedded records}`
- [ ] Validates `--tenant` option; exits with error if missing or tenant not found
- [ ] Without `--refresh`: dispatches `GenerateCatalogProductEmbedding` jobs for all records where `embedding IS NULL` for the tenant
- [ ] With `--refresh`: dispatches jobs for all stale records (`embedding IS NULL OR embedded_at < updated_at`) for the tenant
- [ ] Outputs: records queued count
- [ ] Does not wait for jobs to complete (fire-and-forget dispatch)

**Pest tests:**
```
- it dispatches jobs only for records without an embedding by default
- it dispatches jobs for stale records when --refresh is passed
- it does not dispatch jobs for already-embedded non-stale records without --refresh
- it fails with an error when --tenant is not provided
- it fails with an error when the tenant does not exist
- it outputs the count of queued jobs
```

---

