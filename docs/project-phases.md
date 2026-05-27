# Backend Implementation Phases — AI-Powered Catalog Search

> **Scope:** Backend only. No frontend, Inertia, Vue, Blade, or UI tasks.
> **Source documents:** `user-stories.md`, `database-schema.md`, `project-description.md`, `Product_packages_f(1).xlsx`
> **Status key:** `[x]` completed · `[ ]` pending

---

## Architectural Decisions

| Decision | Choice |
|---|---|
| XLS parsing library | `maatwebsite/excel` (Laravel Excel) |
| Tenant resolution in CLI | `--tenant=ID` flag; bypass `BelongsToTenant` global scopes |
| Embedding SDK | `laravel/ai` directly (no new EmbeddingService wrapper) |
| Embedding execution | Queued jobs per record (`GenerateCatalogProductEmbedding`) |
| Taxonomy storage | Plain string columns on `catalog_products` (no FK tables) |
| Import status | `catalog_import_statuses` lookup table (seeded values) |
| Idempotency keys | Products: `(tenant_id, codigo_padrao)` · Packages: `(catalog_product_id, sku_package)` |

---

## Phase 1 — Data Structure

> **User Stories:** US-1.1, US-1.2

---

### Phase 1.1 — Migrations

#### Phase 1.1.1 — `catalog_import_statuses` migration and seeder

- [ ] Create migration for `catalog_import_statuses` table (`id`, `name` unique, timestamps)
- [ ] Create `CatalogImportStatusSeeder` seeding four rows: `pending`, `running`, `completed`, `failed`
- [ ] Register seeder in `DatabaseSeeder`

**Pest tests:**
```
- it seeds the four expected import status names
- it enforces uniqueness on the name column
```

---

#### Phase 1.1.2 — `catalog_import_runs` migration

- [ ] Create migration for `catalog_import_runs` (`id`, `tenant_id` FK, `catalog_import_status_id` FK, `file_name` nullable, `total_rows`, `created_count`, `updated_count`, `skipped_count`, `failed_count`, `started_at` nullable, `completed_at` nullable, timestamps)
- [ ] Add indexes: `catalog_import_runs_tenant_id_idx`, `catalog_import_runs_tenant_status_idx`

**Pest tests:**
```
- it has the correct columns and nullable constraints
- it enforces foreign key to tenants
- it enforces foreign key to catalog_import_statuses
```

---

#### Phase 1.1.3 — `catalog_import_errors` migration

- [ ] Create migration for `catalog_import_errors` (`id`, `catalog_import_run_id` FK, `row_number` nullable int, `row_data` nullable JSON, `error_message` text, timestamps)
- [ ] Add index: `catalog_import_errors_run_id_idx`

**Pest tests:**
```
- it has the correct columns and nullable constraints
- it enforces foreign key to catalog_import_runs
```

---

#### Phase 1.1.4 — `catalog_products` migration

- [ ] Create migration for `catalog_products` (`id`, `tenant_id` FK, `codigo_padrao` varchar, `sku` nullable varchar, `product_name` varchar, `product_description` nullable text, `product_img_url` nullable varchar, `category_name` nullable varchar, `sub_category_name` nullable varchar, `line_name` nullable varchar, `brand_name` nullable varchar, `searchable_text` nullable text, `embedding vector(3072)` nullable, `embedded_at` nullable timestamp, timestamps)
- [ ] Add unique index on `(tenant_id, codigo_padrao)`
- [ ] Add indexes: `tenant_id`, `embedded_at`, `category_name`, `brand_name`

**Pest tests:**
```
- it has the correct columns and nullable constraints
- it enforces the unique constraint on (tenant_id, codigo_padrao)
- it enforces foreign key to tenants
```

---

#### Phase 1.1.5 — `catalog_packages` migration

- [ ] Create migration for `catalog_packages` (`id`, `catalog_product_id` FK, `sku_package` varchar, `sku_package_name` nullable varchar, `package_description` nullable text, `gross_weight` nullable decimal(10,4), `net_weight` nullable decimal(10,4), `ean` nullable varchar, `package_img_url` nullable varchar, timestamps)
- [ ] Add unique index on `(catalog_product_id, sku_package)`
- [ ] Add indexes: `catalog_product_id`, `ean`

**Pest tests:**
```
- it has the correct columns and nullable constraints
- it enforces the unique constraint on (catalog_product_id, sku_package)
- it enforces foreign key to catalog_products
```

---

### Phase 1.2 — Models

#### Phase 1.2.1 — `CatalogProduct` model

- [ ] Create `app/Models/CatalogProduct.php`
- [ ] Add `$fillable`: `tenant_id`, `codigo_padrao`, `sku`, `product_name`, `product_description`, `product_img_url`, `category_name`, `sub_category_name`, `line_name`, `brand_name`, `searchable_text`, `embedding`, `embedded_at`
- [ ] Add `$casts`: `embedded_at` → `datetime`
- [ ] Use `HasNeighbors` and `HasFactory` traits
- [ ] Do NOT use `BelongsToTenant` trait (CLI commands bypass session-based scoping; tenant filtering is handled explicitly)
- [ ] Define `packages(): HasMany` → `CatalogPackage`
- [ ] Define `tenant(): BelongsTo` → `Tenant`
- [ ] Define scope `scopeWithoutEmbedding(Builder $query)` → `whereNull('embedding')`
- [ ] Define scope `scopeStale(Builder $query)` → `whereNull('embedded_at')->orWhereColumn('embedded_at', '<', 'updated_at')`

**Pest tests:**
```
- it has the expected fillable attributes
- it casts embedded_at to a datetime
- it defines a hasMany relationship to CatalogPackage
- it defines a belongsTo relationship to Tenant
- scopeWithoutEmbedding returns only records where embedding is null
- scopeStale returns records with null embedded_at or embedded_at < updated_at
```

---

#### Phase 1.2.2 — `CatalogPackage` model

- [ ] Create `app/Models/CatalogPackage.php`
- [ ] Add `$fillable`: `catalog_product_id`, `sku_package`, `sku_package_name`, `package_description`, `gross_weight`, `net_weight`, `ean`, `package_img_url`
- [ ] Add `$casts`: `gross_weight` → `decimal:4`, `net_weight` → `decimal:4`
- [ ] Use `HasFactory` trait
- [ ] Define `catalogProduct(): BelongsTo` → `CatalogProduct`

**Pest tests:**
```
- it has the expected fillable attributes
- it casts gross_weight and net_weight as decimals
- it defines a belongsTo relationship to CatalogProduct
```

---

#### Phase 1.2.3 — `CatalogImportStatus` model

- [ ] Create `app/Models/CatalogImportStatus.php`
- [ ] Add `$fillable`: `name`
- [ ] Use `HasFactory` trait
- [ ] Define `runs(): HasMany` → `CatalogImportRun`

**Pest tests:**
```
- it has the expected fillable attributes
- it defines a hasMany relationship to CatalogImportRun
```

---

#### Phase 1.2.4 — `CatalogImportRun` model

- [ ] Create `app/Models/CatalogImportRun.php`
- [ ] Add `$fillable`: `tenant_id`, `catalog_import_status_id`, `file_name`, `total_rows`, `created_count`, `updated_count`, `skipped_count`, `failed_count`, `started_at`, `completed_at`
- [ ] Add `$casts`: `started_at` → `datetime`, `completed_at` → `datetime`
- [ ] Define `status(): BelongsTo` → `CatalogImportStatus`
- [ ] Define `errors(): HasMany` → `CatalogImportError`
- [ ] Define `tenant(): BelongsTo` → `Tenant`

**Pest tests:**
```
- it has the expected fillable attributes
- it casts started_at and completed_at as datetimes
- it defines a belongsTo relationship to CatalogImportStatus
- it defines a hasMany relationship to CatalogImportError
```

---

#### Phase 1.2.5 — `CatalogImportError` model

- [ ] Create `app/Models/CatalogImportError.php`
- [ ] Add `$fillable`: `catalog_import_run_id`, `row_number`, `row_data`, `error_message`
- [ ] Add `$casts`: `row_data` → `array`
- [ ] Define `run(): BelongsTo` → `CatalogImportRun`

**Pest tests:**
```
- it has the expected fillable attributes
- it casts row_data as array
- it defines a belongsTo relationship to CatalogImportRun
```

---

### Phase 1.3 — Factories

#### Phase 1.3.1 — `CatalogProductFactory`

- [ ] Create `database/factories/CatalogProductFactory.php`
- [ ] Generate realistic fake values for all fillable fields
- [ ] Leave `embedding` and `embedded_at` as `null` by default
- [ ] Add state `embedded()` that sets a fake 3072-dim float array as `embedding` and `embedded_at` as `now()`

**Pest tests:**
```
- it creates a CatalogProduct with the factory
- the embedded() state sets a non-null embedding and embedded_at
```

---

#### Phase 1.3.2 — `CatalogPackageFactory`

- [ ] Create `database/factories/CatalogPackageFactory.php`
- [ ] Generate realistic fake values for all fillable fields including decimal weights

**Pest tests:**
```
- it creates a CatalogPackage associated with a CatalogProduct
```

---

#### Phase 1.3.3 — `CatalogImportRunFactory`

- [ ] Create `database/factories/CatalogImportRunFactory.php`
- [ ] Default state uses `pending` status
- [ ] Add states: `running()`, `completed()`, `failed()`

**Pest tests:**
```
- it creates a CatalogImportRun with default pending status
- the completed() state sets completed_at and the completed status
```

---

## Phase 2 — XLS Import and Data Quality

> **User Stories:** US-2.1, US-2.2, US-2.3

---

### Phase 2.1 — Install Laravel Excel

- [ ] Add `maatwebsite/excel` to `composer.json` via Sail: `./vendor/bin/sail composer require maatwebsite/excel`
- [ ] Verify the package is registered (Laravel auto-discovers it)

**Pest tests:** _(no application-level tests for package installation)_

---

### Phase 2.2 — XLS Field Normalizer

- [ ] Create `app/Services/Catalog/CatalogFieldNormalizer.php`
- [ ] Implement `normalize(array $row): array` method that:
  - Trims all string fields
  - Converts empty strings to `null`
  - Preserves line breaks in description fields
  - Casts `gross_weight` and `net_weight` to float/null
  - Applies to all XLS columns from the field mapping in US-1.2

**Pest tests:**
```
- it trims leading and trailing whitespace from string fields
- it converts empty strings to null
- it preserves newlines in product_description and package_description
- it casts gross_weight and net_weight to float values
- it returns null for empty gross_weight and net_weight
- it handles a fully populated row correctly
- it handles a fully empty row correctly
```

---

### Phase 2.3 — Maatwebsite Import Class

- [ ] Create `app/Imports/CatalogImport.php` implementing `ToCollection`, `WithHeadingRow`, `SkipsOnError`, `SkipsOnFailure`
- [ ] Map XLS column names to model fields using the field mapping from US-1.2
- [ ] Inject `CatalogFieldNormalizer` for row normalization
- [ ] Skip rows missing `product_id` or `sku_package` (required keys)
- [ ] Track per-row failures via `onError()` and `onFailure()` callbacks

**Pest tests:**
```
- it reads all expected columns from a valid XLS file
- it skips rows with empty product_id
- it skips rows with empty sku_package
- it normalizes fields on each valid row
```

---

### Phase 2.4 — `CatalogImportService`

- [ ] Create `app/Services/Catalog/CatalogImportService.php`
- [ ] Constructor accepts `int $tenantId`
- [ ] Method `import(UploadedFile|string $file, CatalogImportRun $run): void`
  - Opens import run, sets `started_at`, transitions status to `running`
  - Iterates collection from `CatalogImport`
  - Groups rows by `product_id` (XLS `product_id` → `codigo_padrao`)
  - Upserts `CatalogProduct` records by `(tenant_id, codigo_padrao)` — bypasses `BelongsToTenant` global scope by using `CatalogProduct::withoutGlobalScopes()`
  - For each product, upserts related `CatalogPackage` records by `(catalog_product_id, sku_package)`
  - Logs failed rows as `CatalogImportError` records on the run
  - Updates run counters: `total_rows`, `created_count`, `updated_count`, `skipped_count`, `failed_count`
  - Marks run as `completed` or `failed`, sets `completed_at`

**Pest tests:**
```
- it creates CatalogProduct records for new product_ids
- it updates existing CatalogProduct records on re-import (idempotency)
- it creates CatalogPackage records linked to the correct CatalogProduct
- it updates existing CatalogPackage records on re-import (idempotency)
- it links multiple packages to the same product when product_id repeats
- it skips rows with missing product_id and increments skipped_count
- it records failed rows as CatalogImportError with error_message and row_data
- it sets the run status to completed after a successful import
- it sets the run status to failed when all rows fail
- it updates run counters correctly (created, updated, skipped, failed)
- it sets started_at when the import begins
- it sets completed_at when the import finishes
- re-importing the same file does not create duplicate CatalogProduct records
- re-importing the same file does not create duplicate CatalogPackage records
- re-importing with changed values updates the existing records
```

---

### Phase 2.5 — `catalog:import` Artisan Command

- [ ] Create `app/Console/Commands/Catalog/ImportCatalogCommand.php`
- [ ] Signature: `catalog:import {file : Path to the XLS file} {--tenant= : Tenant ID (required)}`
- [ ] Validates that `--tenant` is provided and the tenant exists; exits with error if not
- [ ] Validates that the file path exists and is readable; exits with error if not
- [ ] Creates a `CatalogImportRun` for the tenant with status `pending` before starting
- [ ] Calls `CatalogImportService::import()` passing file path and run
- [ ] Outputs summary table: `Total rows`, `Created`, `Updated`, `Skipped`, `Failed`
- [ ] Exits with code 0 on success, 1 if any rows failed

**Pest tests:**
```
- it fails with an error when --tenant is not provided
- it fails with an error when the tenant does not exist
- it fails with an error when the file path does not exist
- it creates a CatalogImportRun record before importing
- it prints the import summary after completion
- it exits with code 0 when all rows succeed
- it exits with code 1 when at least one row fails
```

---

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

## Phase 4 — Semantic Search and RAG

> **User Stories:** US-4.1, US-4.2, US-4.3

---

### Phase 4.1 — `CatalogSearchService`

- [ ] Create `app/Services/Catalog/CatalogSearchService.php`
- [ ] Method `search(string $query, int $tenantId, int $limit = 5): Collection`
  - Throws `InvalidArgumentException` if `$query` is empty
  - Calls `laravel/ai` SDK to generate an embedding for `$query`
  - Performs neighbor search on `CatalogProduct` scoped to `$tenantId` using `nearestNeighbors('embedding', $vector)->limit($limit)`
  - Eager-loads `packages` on results
  - Returns collection of `CatalogProduct` models (with packages loaded)
- [ ] Method `searchAsRagContext(string $query, int $tenantId, int $limit = 5): array`
  - Calls `search()` internally
  - Transforms each result into a structured array:
    ```php
    [
        'product_name', 'product_description', 'brand_name',
        'category_name', 'sub_category_name', 'line_name',
        'packages' => [['sku_package', 'sku_package_name', 'package_description', 'gross_weight', 'net_weight', 'ean']]
    ]
    ```
  - Never includes `embedding` vectors in the output
  - Returns empty array when no results are found

**Pest tests:**
```
- it throws InvalidArgumentException for an empty query string
- it returns CatalogProduct records ordered by semantic similarity (mock neighbor search)
- it limits results to the configured limit
- it eager-loads packages on search results
- it returns an empty collection when no results match
- searchAsRagContext returns the expected array structure
- searchAsRagContext never includes embedding values in output
- searchAsRagContext returns an empty array when no results are found
- searchAsRagContext includes all package fields for each product
```

---

### Phase 4.2 — `CatalogAnswerService`

- [ ] Create `app/Services/Catalog/CatalogAnswerService.php`
- [ ] Constructor injects `CatalogSearchService`
- [ ] Method `answer(string $question, int $tenantId): array`
  - Calls `CatalogSearchService::searchAsRagContext()` with the question
  - If context is empty, returns: `['answer' => '<safe fallback message>', 'sources' => []]`
  - Builds a system prompt instructing the model to answer using only the provided catalog context
  - Calls `laravel/ai` SDK to generate a text response
  - Returns: `['answer' => string, 'sources' => [['codigo_padrao', 'product_name', 'brand_name']]]`
  - Never exposes raw embeddings, vector values, or internal model metadata in the return value

**Pest tests:**
```
- it returns an answer when relevant catalog context is found
- it returns a safe fallback answer when no context is found (not an exception)
- it includes sources array with codigo_padrao and product_name
- it never exposes embedding values in the returned array
- it calls the AI model with a prompt that includes the catalog context
```

---

## Phase 5 — API Endpoints

> **User Stories:** US-5.1, US-5.2, US-5.3

---

### Phase 5.1 — Catalog Search API

**Endpoint:** `GET /api/catalog/search`
**Auth:** `auth:sanctum`

- [ ] Create `app/Http/Controllers/Api/Catalog/CatalogSearchController.php`
- [ ] Method `__invoke(Request $request, CatalogSearchService $service): JsonResponse`
  - Validates `q` (required, string, min:1)
  - Resolves `tenant_id` from `$request->user()->tenant_id`
  - Calls `CatalogSearchService::search()`
  - Returns 200 JSON with array of products, each including: `codigo_padrao`, `product_name`, `brand_name`, `category_name`, `sub_category_name`, `line_name`, `product_img_url`, and `packages` array
  - Each package includes: `sku_package`, `sku_package_name`, `package_description`, `gross_weight`, `net_weight`, `ean`, `package_img_url`
  - Returns 200 with empty array when no results
  - Returns 422 with validation error when `q` is missing or empty
- [ ] Register route in `routes/api.php` under `auth:sanctum` group: `GET /catalog/search`

**Pest tests:**
```
- it returns 200 with matching products for a valid query
- it includes packages nested under each product result
- it returns 200 with an empty array when no products match
- it returns 422 when q is missing
- it returns 422 when q is an empty string
- it returns 401 for unauthenticated requests
- it never includes embedding values in the response
```

---

### Phase 5.2 — Catalog AI Answer API

**Endpoint:** `POST /api/catalog/ask`
**Auth:** `auth:sanctum`

- [ ] Create `app/Http/Controllers/Api/Catalog/CatalogAskController.php`
- [ ] Method `__invoke(Request $request, CatalogAnswerService $service): JsonResponse`
  - Validates `question` (required, string, min:1)
  - Resolves `tenant_id` from `$request->user()->tenant_id`
  - Calls `CatalogAnswerService::answer()`
  - Returns 200 JSON: `{ "answer": string, "sources": [...] }`
  - Returns 200 with fallback answer when no context is found (not a 404)
  - Returns 422 for invalid input
  - Returns 401 for unauthenticated
- [ ] Register route in `routes/api.php` under `auth:sanctum` group: `POST /catalog/ask`

**Pest tests:**
```
- it returns 200 with an answer and sources when context is found
- it returns 200 with a fallback answer when no catalog context is found
- it returns 422 when question is missing
- it returns 422 when question is an empty string
- it returns 401 for unauthenticated requests
- it never exposes embeddings or internal model names in the response
```

---

### Phase 5.3 — Catalog Product Detail API

**Endpoint:** `GET /api/catalog/products/{codigo_padrao}`
**Auth:** `auth:sanctum`

- [ ] Create `app/Http/Controllers/Api/Catalog/CatalogProductController.php`
- [ ] Method `show(string $codigoPadrao, Request $request): JsonResponse`
  - Resolves `tenant_id` from `$request->user()->tenant_id`
  - Queries `CatalogProduct::where('tenant_id', $tenantId)->where('codigo_padrao', $codigoPadrao)->with('packages')->firstOrFail()`
  - Returns 200 JSON with all product fields and `packages` array
  - Returns 404 when `codigo_padrao` is not found for the tenant
  - Returns 401 for unauthenticated
- [ ] Register route in `routes/api.php` under `auth:sanctum` group: `GET /catalog/products/{codigo_padrao}`

**Pest tests:**
```
- it returns 200 with the product and all its packages
- it returns 404 when the codigo_padrao does not exist for the tenant
- it returns 404 when the codigo_padrao belongs to a different tenant
- it returns 401 for unauthenticated requests
- it never includes embedding values in the response
```

---

## Phase 6 — Operational Monitoring

> **User Story:** US-6.1

---

### Phase 6.1 — Import Run Status Tracking

- [ ] Verify `CatalogImportRun` status transitions are applied correctly by `CatalogImportService`:
  - `pending` → `running` (on `started_at`)
  - `running` → `completed` (on `completed_at`, all rows processed)
  - `running` → `failed` (on `completed_at`, fatal import failure)
- [ ] Verify `CatalogImportError` records are created for each failed row with `row_number`, `row_data` (raw XLS row), and `error_message`
- [ ] Verify import command summary output matches the `CatalogImportRun` counters

**Pest tests:**
```
- it transitions import run from pending to running on start
- it transitions import run to completed after a successful import
- it transitions import run to failed when a fatal error occurs
- it stores row_number and row_data on CatalogImportError for failed rows
- it stores the original error message on CatalogImportError
```

---

### Phase 6.2 — Embedding Status Tracking

- [ ] Verify `embedded_at` is set correctly by `GenerateCatalogProductEmbedding` job
- [ ] Verify `CatalogProduct::scopeWithoutEmbedding()` returns only records where `embedding IS NULL`
- [ ] Verify `CatalogProduct::scopeStale()` returns records where `embedded_at IS NULL OR embedded_at < updated_at`
- [ ] Verify `catalog:embed` command outputs count of records queued
- [ ] Verify embedding failure logs are written via Laravel's logging infrastructure (not thrown to the end user)

**Pest tests:**
```
- it sets embedded_at after successful embedding generation
- scopeWithoutEmbedding returns only records with null embedding
- scopeStale returns records where embedded_at is null
- scopeStale returns records where embedded_at is older than updated_at
- scopeStale does not return records that are already current
- it logs embedding failures without crashing the job (log assertion)
```

---

## Task Summary

| Phase | Description | Status |
|---|---|---|
| 1.1.1 | `catalog_import_statuses` migration + seeder | [ ] |
| 1.1.2 | `catalog_import_runs` migration | [ ] |
| 1.1.3 | `catalog_import_errors` migration | [ ] |
| 1.1.4 | `catalog_products` migration | [ ] |
| 1.1.5 | `catalog_packages` migration | [ ] |
| 1.2.1 | `CatalogProduct` model | [ ] |
| 1.2.2 | `CatalogPackage` model | [ ] |
| 1.2.3 | `CatalogImportStatus` model | [ ] |
| 1.2.4 | `CatalogImportRun` model | [ ] |
| 1.2.5 | `CatalogImportError` model | [ ] |
| 1.3.1 | `CatalogProductFactory` | [ ] |
| 1.3.2 | `CatalogPackageFactory` | [ ] |
| 1.3.3 | `CatalogImportRunFactory` | [ ] |
| 2.1 | Install `maatwebsite/excel` | [ ] |
| 2.2 | `CatalogFieldNormalizer` service | [ ] |
| 2.3 | `CatalogImport` (Maatwebsite import class) | [ ] |
| 2.4 | `CatalogImportService` | [ ] |
| 2.5 | `catalog:import` Artisan command | [ ] |
| 3.1 | `CatalogSearchableTextService` | [ ] |
| 3.2 | `GenerateCatalogProductEmbedding` job | [ ] |
| 3.3 | `catalog:embed` Artisan command | [ ] |
| 4.1 | `CatalogSearchService` | [ ] |
| 4.2 | `CatalogAnswerService` | [ ] |
| 5.1 | `GET /api/catalog/search` endpoint | [ ] |
| 5.2 | `POST /api/catalog/ask` endpoint | [ ] |
| 5.3 | `GET /api/catalog/products/{codigo_padrao}` endpoint | [ ] |
| 6.1 | Import run status tracking (verification) | [ ] |
| 6.2 | Embedding status tracking (verification) | [ ] |
