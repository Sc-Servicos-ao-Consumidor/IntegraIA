# SmartChef Semantic Platform — Project Description

## Overview

SmartChef Semantic Platform is a web-based content management and semantic search platform designed to store structured business information and make it available through AI-powered search and APIs. The platform allows administrators to register products, recipes, ingredients, and related content, then uses embeddings to retrieve approximate and relevant data when a user searches for a topic.

The core loop revolves around storing high-quality product and recipe information, generating embeddings from that content, and using semantic search to find the most relevant records based on meaning rather than exact keywords. When a user searches for a product, recipe, ingredient, preparation method, or commercial context, the platform compares the query against the stored embeddings and returns related data that can be used by AI to generate a useful response for the final user.

The platform includes modules for product registration, recipe registration, content management, associations between records, semantic search, and API access. Products can contain technical, commercial, nutritional, preparation, packaging, usage, and AI prompt information. Recipes can be connected to products, contents, and ingredients, allowing the system to understand how each item is used in practical scenarios.

The project is built to act as a centralized knowledge base for product-related content. Its purpose is to make product information easier to store, search, retrieve, and expose to other systems through APIs, while using AI to improve the way users interact with that information.

## Key Concepts

Products: Commercial and technical items stored in the platform. Products may include internal code, brand, product group, description, packaging details, nutritional information, yield, preparation methods, ingredient list, usage tips, flavor profile, and prompt guidance for AI responses.

Recipes: Structured culinary or usage-oriented records that describe how products and ingredients can be used. Recipes may include name, description, allergens, cuisine types, recipe type, service order, preparation time, difficulty level, yield, channels, usage group, technique, consumption occasion, ingredients, and preparation method.

Ingredients: Individual components that can be associated with recipes or product-related content. Ingredients help describe recipe composition and improve the semantic context used during search and AI response generation.

Contents: Additional knowledge records that can be linked to products or recipes. Contents may include FAQs, technical explanations, commercial arguments, usage suggestions, support information, educational content, or other searchable materials.

Associations: Relationships between products, recipes, ingredients, and contents. These connections allow the platform to retrieve richer context and understand how records relate to each other.

Embeddings: Vector representations generated from product, recipe, ingredient, and content data. Embeddings allow the platform to compare meaning between a user query and stored records.

Semantic Search: Search mechanism that retrieves records based on meaning and similarity, not only exact keyword matches. It helps users find relevant information even when they use different words or incomplete descriptions.

AI Responses: Final answers generated using the records retrieved by semantic search. Responses should be grounded in the stored platform data and should help the final user understand products, recipes, preparation methods, usage contexts, or related information.

APIs: External access points that allow other systems to consult stored data, semantic search results, and AI-supported responses.

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP, Laravel |
| Frontend | Inertia.js, Vue.js |
| Styling | Tailwind CSS |
| Database | PostgreSQL |
| Testing | Pest |
| Dev Environment | Laravel Sail |
| Code Formatting | Laravel Pint |
| Asset Bundling | Vite |
| AI Features | Embeddings, semantic search, AI-generated responses |
| API Layer | Laravel API routes and controllers |

## Core Workflows

### 1. Authentication

Administrators access the platform through an authenticated area. After logging in, they can manage products, recipes, ingredients, contents, associations, semantic search features, and API-related resources according to their permissions.

### 2. Product Registration

Administrators register and maintain product information inside the platform.

A product may include:

- Internal product code
- Brand
- Product group
- Product description
- Packaging details
- Flavor profile
- Nutritional table description
- Yield description
- Preparation methods
- Ingredient list
- Usage tips
- Prompt instructions for AI usage

This information is used for direct product consultation, semantic search, AI responses, and API access.

### 3. Recipe Registration

Administrators register recipes with detailed information about how products and ingredients are used.

A recipe may include:

- Recipe name
- Recipe description
- Allergens
- Cuisine types
- Recipe type
- Service order
- Preparation time
- Difficulty level
- Yield
- Channel
- Usage group
- Technique
- Consumption occasion
- Ingredient description
- Preparation method
- AI prompt guidance

Recipes can be connected to products, contents, and ingredients, allowing the platform to understand practical applications and return more contextual results.

### 4. Content and Associations

The platform allows products, recipes, ingredients, and contents to be connected to each other.

Recipes may be linked to the products used in their preparation. Products may be linked to technical content, commercial information, FAQs, usage tips, or other supporting materials. Ingredients may help describe recipes and improve search context.

These associations allow the system to retrieve not only isolated records, but also related information that makes the final response more complete and useful.

### 5. Embedding Generation

After product, recipe, ingredient, or content data is registered, the platform generates embeddings from the relevant text fields.

These embeddings represent the meaning of each record. When a user searches for something, the platform compares the search query with the stored embeddings and identifies the closest records based on semantic similarity.

This allows the platform to find approximate matches even when the user does not use the exact same words as the registered content.

### 6. Semantic Search and AI Responses

Users can search using natural language.

Example searches may include:

- Product usage questions
- Recipe ideas
- Preparation suggestions
- Ingredient-related questions
- Product comparison needs
- Commercial or technical product information
- Application ideas for specific channels or usage groups

The platform retrieves the most semantically relevant records and uses them as context for AI-generated responses. The AI response should prioritize the retrieved platform data and present useful information to the final user.

This workflow makes the platform useful both as a search tool and as an intelligent assistant for product discovery, recipe explanation, usage guidance, and business support.

### 7. API Access

The platform exposes APIs so external systems can consult stored data and semantic search results.

External applications may use these APIs to retrieve product information, recipe data, content, related records, or AI-supported responses. This allows the platform to act as a central knowledge service for other applications, assistants, websites, internal tools, or customer-facing channels.

### 8. Knowledge Management and Maintenance

Administrators can update products, recipes, contents, ingredients, and associations as the business knowledge evolves.

When records are updated, the related searchable content and embeddings should remain aligned with the latest stored information. This keeps semantic search and API responses consistent with the current knowledge base.