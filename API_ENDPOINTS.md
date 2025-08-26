# SmartChef API Endpoints

This document describes the API endpoints available for querying SmartChef data.

## Base URL
```
http://smartchef.test/api
```

## Available Endpoints

### 1. Products

#### Get Product by ID
**GET** `/products/{id}`

Returns detailed information about a specific product by its ID.

**Response includes:**
- Basic product information (name, code, SKU, brand, status)
- Group product details
- Product details (specifications, nutritional info, packaging, etc.)
- Product images (active and ordered)
- Associated recipes
- Associated contents

**Example:**
```bash
curl "http://smartchef.test/api/products/1"
```

#### Search Products
**POST** `/products/search`

Performs semantic search on products using natural language queries.

**Request Body:**
```json
{
    "query": "tomato sauce base",
    "limit": 10
}
```

**Response includes:**
- Search query used
- Results with similarity scores
- Total count of results

**Example:**
```bash
curl -X POST "http://smartchef.test/api/products/search" \
     -H "Content-Type: application/json" \
     -d '{"query": "tomato sauce base", "limit": 5}'
```

### 2. Contents

#### Get Content by ID
**GET** `/contents/{id}`

Returns detailed information about a specific content by its ID.

**Response includes:**
- Content information (name, code, description, type, pillars, channel)
- Content details (nutritional info, ingredients, preparation methods, yields)
- Associated images
- Associated recipes
- Associated products

**Example:**
```bash
curl "http://smartchef.test/api/contents/1"
```

#### Search Contents
**POST** `/contents/search`

Performs semantic search on contents using natural language queries.

**Request Body:**
```json
{
    "query": "pasta making guide",
    "limit": 10
}
```

**Response includes:**
- Search query used
- Results with similarity scores
- Total count of results

**Example:**
```bash
curl -X POST "http://smartchef.test/api/contents/search" \
     -H "Content-Type: application/json" \
     -d '{"query": "pasta making guide", "limit": 5}'
```

### 3. Recipes

#### Get Recipe by ID
**GET** `/recipes/{id}`

Returns detailed information about a specific recipe by its ID.

**Response includes:**
- Recipe information (name, code, cuisine, type, difficulty, preparation time)
- Recipe details (description, ingredients, preparation method)
- Associated products with quantities and preparation notes

**Example:**
```bash
curl "http://smartchef.test/api/recipes/1"
```

#### Search Recipes
**POST** `/recipes/search`

Performs semantic search on recipes using natural language queries.

**Request Body:**
```json
{
    "query": "italian bolognese sauce",
    "limit": 10
}
```

**Response includes:**
- Search query used
- Results with similarity scores
- Total count of results

**Example:**
```bash
curl -X POST "http://smartchef.test/api/recipes/search" \
     -H "Content-Type: application/json" \
     -d '{"query": "italian bolognese sauce", "limit": 5}'
```

## Notes

- All endpoints return JSON responses
- Responses include all related data (e.g., products include their recipes and contents)
- The `{id}` parameter should be replaced with the actual numeric ID of the resource
- No authentication is required for these read-only endpoints
- All endpoints return a 404 error if the requested ID doesn't exist

## Error Responses

**404 Not Found:**
```json
{
    "message": "No query results for model [App\\Models\\Product] 999"
}
```

**500 Internal Server Error:**
```json
{
    "message": "Server error occurred"
}
```
