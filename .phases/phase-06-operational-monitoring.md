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
| 4.2 | `CatalogAnswerService` (accepts pre-loaded RAG context) | [ ] |
| 5.1 | `catalog_pipeline_statuses` migration + seeder | [ ] |
| 5.2 | `catalog_requests` migration | [ ] |
| 5.3 | `CatalogPipelineStatus` + `CatalogRequest` models + factory | [ ] |
| 5.4 | `WhatsAppTypingJob` (stub) | [ ] |
| 5.5 | `CatalogSearchJob` | [ ] |
| 5.6 | `GenerateCatalogAnswerJob` | [ ] |
| 5.7 | `SendWhatsAppMessageJob` (stub) | [ ] |
| 5.8 | `CatalogPipelineService` (orchestrator) | [ ] |
| 5.9 | `POST /api/catalog/pipeline` entry point | [ ] |
| 6.1 | Import run status tracking (verification) | [ ] |
| 6.2 | Embedding status tracking (verification) | [ ] |
