## Phase 5 — Pipeline Infrastructure

> This phase builds the database tables, models, jobs, orchestrator service, and entry-point controller that implement the WhatsApp → Search → AI → WhatsApp pipeline.

---

### Phase 5.1 — `catalog_pipeline_statuses` migration and seeder

- [ ] Create migration for `catalog_pipeline_statuses` (`id`, `name` unique, timestamps)
- [ ] Create `CatalogPipelineStatusSeeder` seeding: `pending`, `processing`, `completed`, `failed`
- [ ] Register seeder in `DatabaseSeeder`

**Pest tests:**
```
- it seeds the four expected pipeline status names
- it enforces uniqueness on the name column
```

---

### Phase 5.2 — `catalog_requests` migration

- [ ] Create migration for `catalog_requests`:
  - `id` bigint pk
  - `tenant_id` bigint FK → `tenants`
  - `catalog_pipeline_status_id` bigint FK → `catalog_pipeline_statuses`
  - `contact_id` varchar not null (WhatsApp contact identifier for routing the response)
  - `question` text not null (original user query)
  - `search_results` json nullable (written by `CatalogSearchJob`)
  - `ai_answer` json nullable (written by `GenerateCatalogAnswerJob`)
  - `started_at` timestamp nullable
  - `completed_at` timestamp nullable
  - timestamps
- [ ] Add indexes: `tenant_id`, `(tenant_id, catalog_pipeline_status_id)`

**Pest tests:**
```
- it has the correct columns and nullable constraints
- it enforces foreign key to tenants
- it enforces foreign key to catalog_pipeline_statuses
```

---

### Phase 5.3 — `CatalogPipelineStatus` and `CatalogRequest` models

- [ ] Create `app/Models/CatalogPipelineStatus.php`
  - `$fillable`: `name`
  - `runs(): HasMany` → `CatalogRequest`

- [ ] Create `app/Models/CatalogRequest.php`
  - `$fillable`: `tenant_id`, `catalog_pipeline_status_id`, `contact_id`, `question`, `search_results`, `ai_answer`, `started_at`, `completed_at`
  - `$casts`: `search_results` → `array`, `ai_answer` → `array`, `started_at` → `datetime`, `completed_at` → `datetime`
  - `status(): BelongsTo` → `CatalogPipelineStatus`
  - `tenant(): BelongsTo` → `Tenant`
  - Do NOT use `BelongsToTenant` trait — jobs run without a session context

- [ ] Create `database/factories/CatalogRequestFactory.php`
  - Default state: `pending` status, realistic fake `question` and `contact_id`
  - States: `processing()`, `completed()`, `failed()`

**Pest tests:**
```
- it has the expected fillable attributes
- it casts search_results and ai_answer as arrays
- it casts started_at and completed_at as datetimes
- it defines a belongsTo relationship to CatalogPipelineStatus
- it defines a belongsTo relationship to Tenant
- the factory creates a CatalogRequest with pending status by default
- the completed() factory state sets completed_at
```

---

### Phase 5.4 — `WhatsAppTypingJob` (stub infrastructure)

- [ ] Create `app/Jobs/Catalog/WhatsAppTypingJob.php` implementing `ShouldQueue`
- [ ] Constructor: `public function __construct(public string $contactId, public int $tenantId)`
- [ ] `handle()` method: contains a `// TODO: call WhatsApp typing API` comment with a clear method signature ready to be filled in; does not throw
- [ ] Set `$tries = 1` — typing indicators are best-effort, do not retry

**Pest tests:**
```
- it can be instantiated with contactId and tenantId
- it does not throw during handle() execution
```

---

### Phase 5.5 — `CatalogSearchJob`

- [ ] Create `app/Jobs/Catalog/CatalogSearchJob.php` implementing `ShouldQueue`
- [ ] Constructor: `public function __construct(public int $catalogRequestId)`
- [ ] `handle(CatalogSearchService $searchService)` method:
  - Loads `CatalogRequest` by ID; returns early if not found
  - Sets `started_at` and transitions status to `processing`
  - Calls `$searchService->searchAsRagContext($request->question, $request->tenant_id)`
  - Saves result to `$request->search_results`
  - On failure: catches exception, transitions status to `failed`, sets `completed_at`, re-throws for queue retry
- [ ] Set `$tries = 3`, `$backoff = [30, 60]`

**Pest tests:**
```
- it loads the CatalogRequest and runs the search
- it saves the search_results array on the CatalogRequest
- it sets started_at when the job runs
- it transitions status to processing at the start
- it transitions status to failed and sets completed_at on exception
- it returns early without error when CatalogRequest does not exist
```

---

### Phase 5.6 — `GenerateCatalogAnswerJob`

- [ ] Create `app/Jobs/Catalog/GenerateCatalogAnswerJob.php` implementing `ShouldQueue`
- [ ] Constructor: `public function __construct(public int $catalogRequestId)`
- [ ] `handle(CatalogAnswerService $answerService)` method:
  - Loads `CatalogRequest` by ID; returns early if not found
  - Reads `$request->search_results` (already populated by `CatalogSearchJob`)
  - Calls `$answerService->answerFromContext($request->question, $request->search_results ?? [])`
  - Saves result to `$request->ai_answer`
  - On failure: catches exception, transitions status to `failed`, sets `completed_at`, re-throws
- [ ] Set `$tries = 3`, `$backoff = [30, 60]`

**Pest tests:**
```
- it loads the CatalogRequest and generates the AI answer
- it passes the pre-loaded search_results to CatalogAnswerService (does not search again)
- it saves the ai_answer array on the CatalogRequest
- it handles empty search_results gracefully (fallback answer path)
- it transitions status to failed and sets completed_at on exception
- it returns early without error when CatalogRequest does not exist
```

---

### Phase 5.7 — `SendWhatsAppMessageJob` (stub infrastructure)

- [ ] Create `app/Jobs/Catalog/SendWhatsAppMessageJob.php` implementing `ShouldQueue`
- [ ] Constructor: `public function __construct(public int $catalogRequestId)`
- [ ] `handle()` method:
  - Loads `CatalogRequest` by ID; returns early if not found
  - Reads `$request->ai_answer`, `$request->contact_id`, `$request->tenant_id`
  - Contains `// TODO: call WhatsApp send message API` comment with clear parameter references
  - Transitions status to `completed`, sets `completed_at`
- [ ] Set `$tries = 3`, `$backoff = [30, 60]`

**Pest tests:**
```
- it can be instantiated with a catalogRequestId
- it transitions status to completed and sets completed_at after handle()
- it returns early without error when CatalogRequest does not exist
```

---

### Phase 5.8 — `CatalogPipelineService` (orchestrator)

- [ ] Create `app/Services/Catalog/CatalogPipelineService.php`
- [ ] Method `dispatch(string $question, string $contactId, int $tenantId): CatalogRequest`
  - Creates a `CatalogRequest` with status `pending`, `question`, `contact_id`, `tenant_id`
  - Dispatches `WhatsAppTypingJob($contactId, $tenantId)` immediately (fire-and-forget, outside chain)
  - Dispatches the chain:
    ```php
    Bus::chain([
        new CatalogSearchJob($request->id),
        new GenerateCatalogAnswerJob($request->id),
        new SendWhatsAppMessageJob($request->id),
    ])->dispatch();
    ```
  - Returns the created `CatalogRequest` record
- [ ] Validates that `$question` is not empty; throws `InvalidArgumentException` if so

**Pest tests:**
```
- it creates a CatalogRequest record with pending status
- it dispatches WhatsAppTypingJob as a standalone fire-and-forget job
- it dispatches CatalogSearchJob, GenerateCatalogAnswerJob, and SendWhatsAppMessageJob as a chain
- it returns the created CatalogRequest
- it throws InvalidArgumentException when question is empty
```

---

### Phase 5.9 — Entry Point Controller and Route

**Endpoint:** `POST /api/catalog/pipeline`
**Auth:** `auth:sanctum`

- [ ] Create `app/Http/Controllers/Api/Catalog/CatalogPipelineController.php`
- [ ] Method `__invoke(Request $request, CatalogPipelineService $pipeline): JsonResponse`
  - Validates: `question` (required, string, min:1), `contact_id` (required, string)
  - Resolves `tenant_id` from `$request->user()->tenant_id`
  - Calls `$pipeline->dispatch($question, $contactId, $tenantId)`
  - Returns 202 Accepted: `{ "request_id": int }`
  - Returns 422 for validation failures
  - Returns 401 for unauthenticated
- [ ] Register route in `routes/api.php` under `auth:sanctum` group: `POST /catalog/pipeline`

**Pest tests:**
```
- it returns 202 Accepted with a request_id when input is valid
- it creates a CatalogRequest and dispatches the pipeline jobs
- it returns 422 when question is missing
- it returns 422 when question is empty
- it returns 422 when contact_id is missing
- it returns 401 for unauthenticated requests
```

---

