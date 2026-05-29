Look at the file `@docs/project-description.md`, the uploaded XLS file `Product_packages_f(1).xlsx`, and the current project structure.

Work out user stories for the next feature of the project.

The feature is: AI-powered product and packaging search using the product/package data from the XLS file, embeddings, RAG, and API access.

Ask me any clarifying questions using the `AskUserQuestion` tool before writing the final user stories if a business rule, data structure, or expected behavior is unclear.

Save the result in the file `@docs/user-stories.md`.

Do not implement all user stories at once. After saving the user stories, ask which phase should be implemented first.

---

# Feature Context

The project is a Laravel-based platform that stores structured product-related knowledge and exposes it through semantic search and APIs.

The uploaded XLS file is the reference for this feature. It contains product and package/SKU data using fields such as:

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

The data represents a product catalog where one product can have multiple package/SKU variations.

The goal is to store product and packaging data, generate embeddings from relevant searchable fields, retrieve related records using RAG, and expose API endpoints so other systems can search products/packages and request AI-generated answers.

This feature is backend-first. Prioritize data structure, import/synchronization, embeddings, semantic retrieval, RAG context preparation, APIs, and Pest tests.

Internal Inertia/Vue screens are optional and should only be proposed if useful for testing or monitoring the feature. UI work should not be the main focus.

---

# Expected User Stories

## Overview

This document contains user stories for the AI-powered product and packaging search feature.

**User Types:**

- **Admin** - Manages product/package data, imports, embeddings, and monitoring.
- **External System** - Consumes product/package search and AI answer APIs.
- **Final User** - Receives AI-generated answers based on stored product/package knowledge.
- **Platform** - Processes imports, embeddings, semantic search, RAG context, and API responses.

---

## 1. Product and Package Structure

### US-1.1: Map XLS Fields to Product and Package Data

**As the** Platform  
**I want to** map the XLS fields to product and package structures  
**So that** the implementation reflects the real catalog data

**Acceptance Criteria:**

- [ ] Product-level fields are identified from the XLS
- [ ] Package/SKU-level fields are identified from the XLS
- [ ] Searchable fields are identified
- [ ] Display/API-only fields are identified
- [ ] Ambiguous fields are clarified using `AskUserQuestion`
- [ ] No unsupported fields are invented

**Expected Result:** The project has a clear mapping between XLS data and application data structures.

---

### US-1.2: Store Products with Multiple Package Variations

**As an** Admin  
**I want to** store products and their package/SKU variations  
**So that** the catalog supports one product with many packaging options

**Acceptance Criteria:**

- [ ] `product_id` is treated as the source product identifier
- [ ] `sku_package` is treated as the source package/SKU identifier
- [ ] One product can have multiple packages
- [ ] Repeated product rows do not create duplicated products
- [ ] Each package/SKU is stored once
- [ ] Product and package relationships are covered by Pest tests

**Expected Result:** Products and package variations are stored with the correct relationship.

---

## 2. XLS Import and Data Quality

### US-2.1: Import Product and Package Records from XLS

**As an** Admin  
**I want to** import product and package records from the XLS  
**So that** the searchable catalog can be populated from the provided source

**Acceptance Criteria:**

- [ ] The import reads all supported XLS columns
- [ ] Products are created or updated by `product_id`
- [ ] Packages are created or updated by `sku_package`
- [ ] Multiple package rows for the same product are linked correctly
- [ ] Invalid rows are handled safely
- [ ] Import result includes created, updated, skipped, and failed counts
- [ ] Pest tests cover successful import and invalid row handling

**Expected Result:** XLS data can be imported safely and consistently.

---

### US-2.2: Keep Import Idempotent

**As the** Platform  
**I want to** prevent duplicated records during repeated imports  
**So that** running the same import more than once does not corrupt the catalog

**Acceptance Criteria:**

- [ ] Re-importing the same XLS does not duplicate products
- [ ] Re-importing the same XLS does not duplicate packages
- [ ] Existing records are updated when source values change
- [ ] Relationships remain stable after re-import
- [ ] Pest tests cover idempotent import behavior

**Expected Result:** The import can be safely re-run.

---

### US-2.3: Normalize Imported Data

**As the** Platform  
**I want to** normalize imported product and package fields  
**So that** search, embeddings, and APIs use consistent data

**Acceptance Criteria:**

- [ ] Text fields are trimmed
- [ ] Empty values are handled safely
- [ ] Line breaks and escaped characters are handled
- [ ] Weight fields are stored consistently
- [ ] Category, subcategory, line, and brand values are normalized consistently
- [ ] Pest tests cover normalization rules

**Expected Result:** Imported catalog data is clean enough for search and API usage.

---

## 3. Embeddings and Searchable Text

### US-3.1: Build Searchable Text for Products and Packages

**As the** Platform  
**I want to** build searchable text from product and package fields  
**So that** embeddings represent useful catalog context

**Acceptance Criteria:**

- [ ] Product searchable text includes relevant product fields
- [ ] Package searchable text includes relevant package/SKU fields
- [ ] Empty fields are ignored
- [ ] Searchable text is generated consistently
- [ ] Pest tests cover searchable text generation

**Expected Result:** Product and package records have consistent text ready for embeddings.

---

### US-3.2: Generate and Refresh Embeddings

**As the** Platform  
**I want to** generate and refresh embeddings for product/package records  
**So that** semantic search uses current catalog information

**Acceptance Criteria:**

- [ ] Embeddings can be generated from searchable text
- [ ] Embeddings are associated with the correct product or package source
- [ ] Empty searchable text is not sent for embedding generation
- [ ] Changed searchable content can mark embeddings as outdated
- [ ] Embeddings can be regenerated after updates
- [ ] Failures are handled without breaking the source record
- [ ] Pest tests cover generation, regeneration, and failure handling

**Expected Result:** Product and package records can be searched semantically.

---

## 4. Semantic Search and RAG

### US-4.1: Search Products and Packages Using Natural Language

**As an** External System  
**I want to** search products and packages using natural language  
**So that** I can find relevant records without knowing exact product names, codes, or package descriptions

**Acceptance Criteria:**

- [ ] Search query is validated
- [ ] Query embedding is generated or reused
- [ ] Product and package embeddings are searched
- [ ] Results are ordered by relevance
- [ ] Results include product and package context
- [ ] Empty queries are rejected
- [ ] Pest tests cover search success and validation errors

**Expected Result:** Relevant products and package variations are returned by semantic meaning.

---

### US-4.2: Retrieve RAG Context

**As the** Platform  
**I want to** retrieve relevant product/package context for a query  
**So that** AI answers are grounded in stored catalog data

**Acceptance Criteria:**

- [ ] Relevant products and packages are retrieved
- [ ] Context includes product fields, package fields, category, subcategory, line, and brand when available
- [ ] Retrieved context is limited to avoid excessive prompt size
- [ ] Relevance scores are included when available
- [ ] Raw embedding vectors are not exposed
- [ ] Pest tests cover context retrieval and ordering

**Expected Result:** The platform prepares structured context for AI answer generation.

---

### US-4.3: Generate AI Answer from RAG Context

**As a** Final User  
**I want to** receive an AI-generated answer based on product/package data  
**So that** I can understand matching products and available packaging options

**Acceptance Criteria:**

- [ ] AI answer uses retrieved product/package context
- [ ] Answer prioritizes stored platform data
- [ ] Answer can mention SKU, package name, description, weights, EAN, category, line, and brand when relevant
- [ ] No-context scenarios are handled safely
- [ ] Raw embeddings are not exposed
- [ ] Pest tests cover answer generation with and without context

**Expected Result:** The final answer is grounded in stored catalog data.

---

## 5. API Access

### US-5.1: Product and Package Search API

**As an** External System  
**I want to** search products and packages through an API  
**So that** another application can consume semantic catalog search

**Acceptance Criteria:**

- [ ] API accepts a natural language query
- [ ] Request payload is validated
- [ ] API returns product/package matches ordered by relevance
- [ ] Response includes product identifiers and package identifiers
- [ ] Response includes relevant product and package fields
- [ ] API handles empty, invalid, and no-result scenarios
- [ ] Pest API tests cover success and error scenarios

**Expected Result:** External systems can search the catalog through an API.

---

### US-5.2: AI Answer API

**As an** External System  
**I want to** send a product or packaging question to an API  
**So that** I can receive an AI-supported answer based on platform data

**Acceptance Criteria:**

- [ ] API accepts a natural language question
- [ ] API retrieves RAG context
- [ ] API generates an AI-supported answer
- [ ] API returns the answer and optional source references
- [ ] API handles insufficient-context scenarios
- [ ] API does not expose internal implementation details
- [ ] Pest API tests cover success and insufficient-context scenarios

**Expected Result:** External systems can request AI-supported product/package answers.

---

### US-5.3: Product Package Detail API

**As an** External System  
**I want to** retrieve a product with its available package variations  
**So that** another application can display complete product and packaging information

**Acceptance Criteria:**

- [ ] API can return a product by internal identifier or source `product_id`
- [ ] Response includes product-level fields
- [ ] Response includes related package/SKU variations
- [ ] Package response includes SKU, name, description, weights, EAN, and image URL when available
- [ ] Missing products return a proper not found response
- [ ] Pest API tests cover success and not found responses

**Expected Result:** External systems can retrieve structured product details and package variations.

---

## 6. Operational Requirements

### US-6.1: Track Import and Embedding Status

**As an** Admin  
**I want to** know import and embedding status  
**So that** I can monitor whether catalog records are ready for semantic search

**Acceptance Criteria:**

- [ ] Import result includes total processed, created, updated, skipped, and failed rows
- [ ] Product/package records can identify whether embeddings exist
- [ ] Outdated embeddings can be identified
- [ ] Failures are logged safely
- [ ] Raw embeddings are never displayed to users
- [ ] Pest tests cover status and failure handling

**Expected Result:** The platform can monitor import and semantic search readiness.

---

## Implementation Phases

Do not implement all phases at once.

After saving `@docs/user-stories.md`, ask which phase should be implemented first.

Recommended phases:

1. **Phase 1 — Data Structure and XLS Import**
   - US-1.1
   - US-1.2
   - US-2.1
   - US-2.2
   - US-2.3

2. **Phase 2 — Searchable Text and Embeddings**
   - US-3.1
   - US-3.2

3. **Phase 3 — Semantic Search and RAG**
   - US-4.1
   - US-4.2
   - US-4.3

4. **Phase 4 — APIs**
   - US-5.1
   - US-5.2
   - US-5.3

5. **Phase 5 — Operational Monitoring**
   - US-6.1

---

## Appendix: User Story Status

| ID     | Story                                               | Priority | Status  |
| ------ | --------------------------------------------------- | -------- | ------- |
| US-1.1 | Map XLS Fields to Product and Package Data          | High     | Pending |
| US-1.2 | Store Products with Multiple Package Variations     | High     | Pending |
| US-2.1 | Import Product and Package Records from XLS         | High     | Pending |
| US-2.2 | Keep Import Idempotent                              | High     | Pending |
| US-2.3 | Normalize Imported Data                             | High     | Pending |
| US-3.1 | Build Searchable Text for Products and Packages     | High     | Pending |
| US-3.2 | Generate and Refresh Embeddings                     | High     | Pending |
| US-4.1 | Search Products and Packages Using Natural Language | High     | Pending |
| US-4.2 | Retrieve RAG Context                                | High     | Pending |
| US-4.3 | Generate AI Answer from RAG Context                 | High     | Pending |
| US-5.1 | Product and Package Search API                      | High     | Pending |
| US-5.2 | AI Answer API                                       | High     | Pending |
| US-5.3 | Product Package Detail API                          | Medium   | Pending |
| US-6.1 | Track Import and Embedding Status                   | Medium   | Pending |
