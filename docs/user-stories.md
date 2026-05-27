# User Stories — AI-Powered Product and Packaging Search

## Overview

This document contains user stories for the AI-powered product and packaging catalog search feature.

**Architectural decisions recorded here:**

- XLS data is imported into new dedicated `CatalogProduct` and `CatalogPackage` models, separate from the existing `Product` and `ProductPackaging` models used for manually-managed content.
- Import idempotency for products uses the XLS `product_id` field matched against `CatalogProduct.codigo_padrao`.
- Embeddings are generated at the product level only. Package/SKU data is included in the product's searchable text.
- Taxonomy fields (`category_name`, `sub_category_name`, `line_name`, `brand_name`) are stored as plain string columns on `CatalogProduct`.
- `CatalogProduct` is tenant-scoped (`tenant_id`), consistent with the rest of the platform.
- The new API endpoints follow the existing Sanctum token authentication pattern.

---

**User Types:**

- **Admin** — Manages catalog data, imports, embeddings, and monitoring.
- **External System** — Consumes product/package search and AI answer APIs using Sanctum tokens.
- **Final User** — Receives AI-generated answers based on stored catalog data.
- **Platform** — Processes imports, embeddings, semantic search, RAG context, and API responses.

---

## 1. Catalog Data Structure

### US-1.1: Define CatalogProduct and CatalogPackage Models

**As the** Platform  
**I want to** store XLS catalog data in dedicated `CatalogProduct` and `CatalogPackage` models  
**So that** imported catalog data stays isolated from manually-managed product content

**Acceptance Criteria:**

- [ ] `CatalogProduct` stores product-level fields: `tenant_id`, `codigo_padrao` (source: XLS `product_id`), `product_name`, `product_description`, `product_img_url`, `category_name`, `sub_category_name`, `line_name`, `brand_name`, `embedding`
- [ ] `CatalogPackage` stores package-level fields: `catalog_product_id`, `sku_package` (source key), `sku_package_name`, `package_description`, `gross_weight`, `net_weight`, `ean`, `package_img_url`
- [ ] One `CatalogProduct` can have many `CatalogPackage` records
- [ ] `CatalogProduct` is scoped to a tenant via `tenant_id`
- [ ] Migrations, models with correct `$fillable`, casts, and relationships are created
- [ ] Factories exist for both models
- [ ] Pest tests cover model relationships, fillable attributes, and tenant scoping

**Expected Result:** The platform has isolated data structures ready to receive XLS catalog data.

---

### US-1.2: Map XLS Fields to CatalogProduct and CatalogPackage

**As the** Platform  
**I want to** have a clear field mapping between XLS columns and model attributes  
**So that** the import is consistent and no XLS data is lost or misassigned

**Accepted Field Mapping:**

| XLS Field             | Model             | Column              |
|-----------------------|-------------------|---------------------|
| `product_id`          | CatalogProduct    | `codigo_padrao`     |
| `product_name`        | CatalogProduct    | `product_name`      |
| `product_description` | CatalogProduct    | `product_description` |
| `product_img_url`     | CatalogProduct    | `product_img_url`   |
| `category_name`       | CatalogProduct    | `category_name`     |
| `sub_category_name`   | CatalogProduct    | `sub_category_name` |
| `line_name`           | CatalogProduct    | `line_name`         |
| `brand_name`          | CatalogProduct    | `brand_name`        |
| `sku_package`         | CatalogPackage    | `sku_package`       |
| `sku_package_name`    | CatalogPackage    | `sku_package_name`  |
| `package_description` | CatalogPackage    | `package_description` |
| `gross_weight`        | CatalogPackage    | `gross_weight`      |
| `net_weight`          | CatalogPackage    | `net_weight`        |
| `ean`                 | CatalogPackage    | `ean`               |
| `package_img_url`     | CatalogPackage    | `package_img_url`   |

**Acceptance Criteria:**

- [ ] All XLS columns listed above have a corresponding model column
- [ ] No XLS column is silently discarded without documentation
- [ ] Product-level fields and package-level fields are correctly separated

**Expected Result:** A documented and implemented field mapping exists between XLS source and application models.

---

## 2. XLS Import and Data Quality

### US-2.1: Import CatalogProduct and CatalogPackage Records from XLS

**As an** Admin  
**I want to** run an Artisan command that reads the XLS file and imports catalog records  
**So that** the searchable catalog can be populated from the provided source

**Acceptance Criteria:**

- [ ] An Artisan command (e.g., `catalog:import`) accepts the XLS file path and a `--tenant` option
- [ ] The command reads all XLS columns defined in US-1.2
- [ ] `CatalogProduct` records are created or updated by `codigo_padrao` (XLS `product_id`)
- [ ] `CatalogPackage` records are created or updated by `sku_package`
- [ ] Multiple package rows for the same product are linked correctly to the same `CatalogProduct`
- [ ] Invalid or empty rows are skipped safely without stopping the import
- [ ] The command outputs a summary: total rows, created, updated, skipped, failed
- [ ] Pest tests cover successful import, re-import idempotency, and skipped invalid rows

**Expected Result:** XLS data can be imported into `CatalogProduct` and `CatalogPackage` records safely and consistently.

---

### US-2.2: Keep Import Idempotent

**As the** Platform  
**I want to** prevent duplicated records when the same XLS is imported more than once  
**So that** re-running the import is safe and does not corrupt the catalog

**Acceptance Criteria:**

- [ ] Re-importing the same XLS does not create duplicate `CatalogProduct` records (keyed by `codigo_padrao` + `tenant_id`)
- [ ] Re-importing the same XLS does not create duplicate `CatalogPackage` records (keyed by `sku_package` + `catalog_product_id`)
- [ ] Existing records are updated when source values change (upsert behavior)
- [ ] Relationships between `CatalogProduct` and `CatalogPackage` remain stable after re-import
- [ ] Pest tests cover idempotent import behavior with changed field values

**Expected Result:** The import can be safely re-run at any time.

---

### US-2.3: Normalize Imported Catalog Data

**As the** Platform  
**I want to** normalize text and numeric fields during import  
**So that** searchable text, embeddings, and API responses use clean, consistent data

**Acceptance Criteria:**

- [ ] String fields are trimmed of leading/trailing whitespace
- [ ] Empty or null string values are stored as `null`, not as empty strings
- [ ] Line breaks and special characters in description fields are preserved but not escaped incorrectly
- [ ] `gross_weight` and `net_weight` are stored as decimals
- [ ] `category_name`, `sub_category_name`, `line_name`, `brand_name` are stored as trimmed strings
- [ ] Pest tests cover normalization behavior for edge-case inputs

**Expected Result:** Imported catalog data is clean and consistent enough for search, embeddings, and API usage.

---

## 3. Searchable Text and Embeddings

### US-3.1: Build Searchable Text for CatalogProduct Records

**As the** Platform  
**I want to** build a searchable text representation from each CatalogProduct's fields  
**So that** the embedding captures the full semantic context of the product and its packages

**Searchable text should include (when present):**

- `product_name`
- `product_description`
- `brand_name`
- `category_name`
- `sub_category_name`
- `line_name`
- All related `CatalogPackage` fields: `sku_package_name`, `package_description`, `ean`

**Acceptance Criteria:**

- [ ] A service method builds the searchable text for a given `CatalogProduct`
- [ ] Fields that are null or empty are omitted from the text
- [ ] Package data from all related `CatalogPackage` records is included
- [ ] The output is a single, consistently structured string
- [ ] Pest tests cover searchable text generation with full data, partial data, and no packages

**Expected Result:** Each `CatalogProduct` has a complete searchable text ready for embedding generation.

---

### US-3.2: Generate and Refresh Embeddings for CatalogProduct Records

**As the** Platform  
**I want to** generate and refresh embeddings for `CatalogProduct` records  
**So that** semantic search uses current catalog information

**Acceptance Criteria:**

- [ ] An Artisan command (e.g., `catalog:embed`) generates embeddings for all `CatalogProduct` records without an embedding
- [ ] Embeddings are generated from the searchable text defined in US-3.1
- [ ] A `CatalogProduct` with empty searchable text is skipped without error
- [ ] An `--refresh` flag allows regenerating embeddings for all records, including those already embedded
- [ ] Embedding generation failures are logged and do not stop the batch
- [ ] The `CatalogProduct` model uses the existing `pgvector` infrastructure (`HasNeighbors`, `vector` column)
- [ ] Pest tests cover embedding generation, skipping empty text, and failure handling

**Expected Result:** `CatalogProduct` records can be searched semantically using their stored embeddings.

---

## 4. Semantic Search and RAG

### US-4.1: Search Catalog Products Using Natural Language

**As an** External System  
**I want to** search catalog products and packages using natural language  
**So that** I can find relevant records without knowing exact product names, codes, or package descriptions

**Acceptance Criteria:**

- [ ] A `CatalogSearchService` accepts a natural language query string and `tenant_id`
- [ ] The service generates an embedding for the query and performs a neighbor search on `CatalogProduct.embedding`
- [ ] Results are ordered by semantic similarity (closest first)
- [ ] Each result includes: `codigo_padrao`, `product_name`, `brand_name`, `category_name`, `line_name`, and associated packages
- [ ] Empty query strings are rejected before embedding generation
- [ ] The result set is limited to a configurable number (default: 5)
- [ ] Pest tests cover successful search, empty query rejection, and no-result scenarios

**Expected Result:** The platform can find semantically relevant catalog products and packages from a natural language query.

---

### US-4.2: Retrieve RAG Context from Catalog Records

**As the** Platform  
**I want to** retrieve relevant `CatalogProduct` and `CatalogPackage` data as structured context for a given query  
**So that** AI answers are grounded in stored catalog data

**Acceptance Criteria:**

- [ ] The `CatalogSearchService` can return a structured RAG context array from search results
- [ ] Context includes: product name, description, brand, category, sub_category, line, and all associated packages (SKU, name, description, weights, EAN)
- [ ] Context is limited to avoid excessive prompt size (configurable max records)
- [ ] Relevance scores are included internally but raw embedding vectors are never exposed
- [ ] Pest tests cover context structure and field completeness

**Expected Result:** The platform prepares structured, grounded context for AI answer generation.

---

### US-4.3: Generate AI Answer from Catalog RAG Context

**As a** Final User  
**I want to** receive an AI-generated answer based on stored catalog product and package data  
**So that** I can understand matching products and available packaging options

**Acceptance Criteria:**

- [ ] A service method accepts a query and RAG context and calls the AI model (using existing `laravel/ai` infrastructure)
- [ ] The AI prompt instructs the model to answer using only the provided catalog context
- [ ] The answer can mention product name, brand, category, SKU, package name, description, weights, and EAN when relevant
- [ ] When no context is available, a safe fallback response is returned instead of an error
- [ ] Raw embeddings are never exposed in the answer or response
- [ ] Pest tests cover answer generation with context and with no context

**Expected Result:** The final answer is grounded in stored catalog data and useful to the final user.

---

## 5. API Access

### US-5.1: Catalog Search API

**As an** External System  
**I want to** search catalog products and packages through an authenticated API endpoint  
**So that** another application can consume semantic catalog search

**Endpoint:** `GET /api/catalog/search`  
**Auth:** Sanctum token (`auth:sanctum` middleware)

**Acceptance Criteria:**

- [ ] Accepts query parameter `q` (required, non-empty string)
- [ ] Returns up to N results (configurable, default 5), ordered by relevance
- [ ] Each result includes: `codigo_padrao`, `product_name`, `brand_name`, `category_name`, `sub_category_name`, `line_name`, `product_img_url`, and an array of packages
- [ ] Each package includes: `sku_package`, `sku_package_name`, `package_description`, `gross_weight`, `net_weight`, `ean`, `package_img_url`
- [ ] Empty or missing `q` returns 422 with validation error
- [ ] No results returns 200 with an empty array
- [ ] Unauthenticated requests return 401
- [ ] Pest API tests cover search success, validation failure, empty results, and 401

**Expected Result:** External systems can search the catalog through a versioned, authenticated API.

---

### US-5.2: Catalog AI Answer API

**As an** External System  
**I want to** send a product or packaging question to an API and receive an AI-supported answer  
**So that** I can retrieve AI-generated responses grounded in catalog data

**Endpoint:** `POST /api/catalog/ask`  
**Auth:** Sanctum token (`auth:sanctum` middleware)

**Acceptance Criteria:**

- [ ] Accepts JSON body with `question` (required, non-empty string)
- [ ] The API retrieves RAG context from catalog records and generates an AI answer
- [ ] Response includes: `answer` (string) and optionally `sources` (array of matched product references)
- [ ] When no relevant context is found, a graceful no-context answer is returned (not an error)
- [ ] Internal implementation details (embeddings, vectors, model names) are not exposed in the response
- [ ] Unauthenticated requests return 401
- [ ] Pest API tests cover successful answer, no-context scenario, validation failure, and 401

**Expected Result:** External systems can request AI-supported catalog answers through an authenticated API.

---

### US-5.3: Catalog Product Detail API

**As an** External System  
**I want to** retrieve a catalog product with all its package variations  
**So that** another application can display complete product and packaging information

**Endpoint:** `GET /api/catalog/products/{codigo_padrao}`  
**Auth:** Sanctum token (`auth:sanctum` middleware)

**Acceptance Criteria:**

- [ ] Accepts `codigo_padrao` as the route parameter
- [ ] Response includes all product-level fields and an array of related packages
- [ ] Package array includes all `CatalogPackage` fields
- [ ] Unknown `codigo_padrao` returns 404
- [ ] Unauthenticated requests return 401
- [ ] Pest API tests cover success, 404, and 401

**Expected Result:** External systems can retrieve structured product details and all package variations.

---

## 6. Operational Requirements

### US-6.1: Track Import and Embedding Status

**As an** Admin  
**I want to** know the import and embedding status of catalog records  
**So that** I can monitor whether the catalog is ready for semantic search

**Acceptance Criteria:**

- [ ] Import command outputs a summary: total rows processed, created, updated, skipped, failed
- [ ] `CatalogProduct` records that have no embedding can be queried (e.g., `whereNull('embedding')`)
- [ ] Embedding command outputs: total processed, embedded, skipped (empty text), failed
- [ ] Failures during embedding generation are logged via Laravel's logging infrastructure
- [ ] Raw embedding vectors are never displayed to admins or returned by APIs
- [ ] Pest tests cover status reporting for both import and embedding commands

**Expected Result:** The platform provides enough visibility to monitor catalog import and search readiness.

---

## Implementation Phases

Do not implement all phases at once. Ask which phase to implement first after saving this document.

### Phase 1 — Data Structure and XLS Import
- US-1.1 Define CatalogProduct and CatalogPackage Models
- US-1.2 Map XLS Fields to CatalogProduct and CatalogPackage
- US-2.1 Import CatalogProduct and CatalogPackage Records from XLS
- US-2.2 Keep Import Idempotent
- US-2.3 Normalize Imported Catalog Data

### Phase 2 — Searchable Text and Embeddings
- US-3.1 Build Searchable Text for CatalogProduct Records
- US-3.2 Generate and Refresh Embeddings for CatalogProduct Records

### Phase 3 — Semantic Search and RAG
- US-4.1 Search Catalog Products Using Natural Language
- US-4.2 Retrieve RAG Context from Catalog Records
- US-4.3 Generate AI Answer from Catalog RAG Context

### Phase 4 — APIs
- US-5.1 Catalog Search API
- US-5.2 Catalog AI Answer API
- US-5.3 Catalog Product Detail API

### Phase 5 — Operational Monitoring
- US-6.1 Track Import and Embedding Status

---

## Appendix: User Story Status

| ID     | Story                                                  | Priority | Phase | Status  |
|--------|--------------------------------------------------------|----------|-------|---------|
| US-1.1 | Define CatalogProduct and CatalogPackage Models        | High     | 1     | Pending |
| US-1.2 | Map XLS Fields to CatalogProduct and CatalogPackage    | High     | 1     | Pending |
| US-2.1 | Import CatalogProduct and CatalogPackage from XLS      | High     | 1     | Pending |
| US-2.2 | Keep Import Idempotent                                 | High     | 1     | Pending |
| US-2.3 | Normalize Imported Catalog Data                        | High     | 1     | Pending |
| US-3.1 | Build Searchable Text for CatalogProduct Records       | High     | 2     | Pending |
| US-3.2 | Generate and Refresh Embeddings                        | High     | 2     | Pending |
| US-4.1 | Search Catalog Products Using Natural Language         | High     | 3     | Pending |
| US-4.2 | Retrieve RAG Context from Catalog Records              | High     | 3     | Pending |
| US-4.3 | Generate AI Answer from Catalog RAG Context            | High     | 3     | Pending |
| US-5.1 | Catalog Search API                                     | High     | 4     | Pending |
| US-5.2 | Catalog AI Answer API                                  | High     | 4     | Pending |
| US-5.3 | Catalog Product Detail API                             | Medium   | 4     | Pending |
| US-6.1 | Track Import and Embedding Status                      | Medium   | 5     | Pending |
