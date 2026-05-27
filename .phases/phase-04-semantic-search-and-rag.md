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

> **Note:** In the pipeline, search runs in a separate job (`CatalogSearchJob`) before this service is called. This service must accept pre-loaded RAG context — it must NOT call `CatalogSearchService` internally. This keeps each pipeline stage independently testable and prevents double embedding generation.

- [ ] Create `app/Services/Catalog/CatalogAnswerService.php`
- [ ] No constructor injection of `CatalogSearchService`
- [ ] Method `answerFromContext(string $question, array $ragContext): array`
  - Accepts pre-loaded RAG context (output of `CatalogSearchService::searchAsRagContext()`)
  - If `$ragContext` is empty, returns: `['answer' => '<safe fallback message>', 'products' => []]`
  - Builds a system prompt instructing the model to answer using only the provided catalog context
  - Calls `laravel/ai` SDK to generate a text response
  - Returns:
    ```php
    [
        'answer'   => string,
        'products' => [
            [
                'codigo_padrao', 'product_name', 'brand_name', 'category_name',
                'sub_category_name', 'line_name', 'product_img_url',
                'packages' => [['sku_package', 'sku_package_name', 'package_description',
                                'gross_weight', 'net_weight', 'ean', 'package_img_url']]
            ]
        ]
    ]
    ```
  - Never exposes raw embeddings, vector values, or internal model metadata in the return value

**Pest tests:**
```
- it returns an answer and products array when ragContext is non-empty
- it returns a safe fallback answer and empty products array when ragContext is empty (not an exception)
- it includes all product fields and nested package fields in the products array
- it never exposes embedding values in the returned array
- it calls the AI model with a prompt that contains the ragContext data
```

---

