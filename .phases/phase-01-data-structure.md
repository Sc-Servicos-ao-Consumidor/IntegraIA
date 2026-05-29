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

