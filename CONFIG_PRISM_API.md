# Prism API Configuration

This document outlines the configuration needed for the refactored AI search and response system.

## Environment Variables

Add these variables to your `.env` file:

```env
# Prism API Configuration
PRISM_API_URL=https://openapi.test/api/ai
PRISM_API_TOKEN=your-api-token-here

# AI Model Configuration
PRISM_EMBEDDING_PROVIDER=openai
PRISM_EMBEDDING_MODEL=text-embedding-3-small
PRISM_CHAT_PROVIDER=openai
PRISM_CHAT_MODEL=gpt-5-nano
```

## Available Commands

### Generate Embeddings

```bash
# Generate embeddings for all models
php artisan embedding:generate-all

# Generate embeddings for specific models
php artisan embedding:generate-all --models=recipes,products
php artisan embedding:generate-all --models=content

# Generate embeddings with limits
php artisan embedding:generate-all --limit=100

# Force regenerate existing embeddings
php artisan embedding:generate-all --force

# Generate embeddings for individual model types
php artisan embedding:generate-recipes
php artisan embedding:generate-products
php artisan embedding:generate-content
```

### Run Migrations

```bash
# Add embedding columns to products and contents tables
php artisan migrate
```

## API Endpoints

### Enhanced Search Endpoint

```
GET /recipes/search?query=chicken&type=all&limit=5
```

Parameters:
- `query` (required): Search query text
- `type` (optional): Type of content to search (recipes, products, content, all)
- `limit` (optional): Number of results to return (default: 5, max: 50)

Response format:
```json
{
  "recipes": [...],
  "products": [...],
  "contents": [...]
}
```

### Enhanced Assistant Endpoint

```
POST /recipes/assistant
```

Request body:
```json
{
  "text": "Find me a recipe with chicken and vegetables",
  "context": "optional context",
  "use_tools": true
}
```

Response format:
```json
{
  "response": "AI response text",
  "tool_calls": [
    {
      "tool_call_id": "call_123",
      "function_name": "search_recipes",
      "result": {...}
    }
  ]
}
```

## Available AI Tools

The AI assistant now has access to these tools:

1. **search_recipes**: Search recipes using semantic search
2. **search_products**: Search products using semantic search
3. **search_content**: Search content using semantic search
4. **get_recipe_details**: Get detailed information about a specific recipe
5. **get_product_details**: Get detailed information about a specific product
6. **find_recipes_with_product**: Find recipes that use a specific product

## Features Added

1. **Unified Embedding System**: Single service handles all model types
2. **Enhanced Search**: Search across recipes, products, and content
3. **AI Tool Calling**: AI can access database information via tools
4. **Configuration Management**: Centralized API configuration
5. **Database Support**: Added embedding columns to all relevant tables
6. **Command Line Tools**: Easy embedding generation and management
7. **Robust Response Handling**: Handles different API response formats automatically
8. **Consistent Embedding Extraction**: Centralized logic for parsing embeddings
9. **Clean API Responses**: Automatically removes embedding data from all API responses
10. **Optimized Data Transfer**: Excludes large embedding arrays from frontend responses

## Migration Notes

1. Run migrations to add embedding columns
2. Update environment variables
3. Generate embeddings for existing data
4. Test search and assistant endpoints
