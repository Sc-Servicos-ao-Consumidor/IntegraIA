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

