# SmartChef AI Integration Flow - Visual Representation

## 🚀 AI Call Flow Overview

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   User Input    │    │   Frontend      │    │   Backend       │
│                 │    │   (Vue.js)      │    │   (Laravel)     │
└─────────┬───────┘    └─────────┬───────┘    └─────────┬───────┘
          │                       │                       │
          │                       ▼                       │
          │              ┌─────────────────┐              │
          │              │   API Request   │              │
          │              │   (HTTP/JSON)   │              │
          │              └─────────┬───────┘              │
          │                        │                       │
          │                        ▼                       │
          │              ┌─────────────────┐              │
          │              │  Controller     │              │
          │              │  (Recipe, etc.) │              │
          │              └─────────┬───────┘              │
          │                        │                       │
          │                        ▼                       │
          │              ┌─────────────────┐              │
          │              │  Service Layer  │              │
          │              │                 │              │
          │              │ • PrismService  │              │
          │              │ • EmbeddingSvc  │              │
          │              │ • AIToolService │              │
          │              └─────────┬───────┘              │
          │                        │                       │
          │                        ▼                       │
          │              ┌─────────────────┐              │
          │              │  Prism API      │              │
          │              │  Gateway        │              │
          │              │                 │              │
          │              │ https://openapi │              │
          │              │ .test/api/ai    │              │
          │              └─────────┬───────┘              │
          │                        │                       │
          │                        ▼                       │
          │              ┌─────────────────┐              │
          │              │  AI Providers   │              │
          │              │                 │              │
          │              │ ┌─────────────┐ │              │
          │              │ │   OpenAI    │ │              │
          │              │ │   GPT-4     │ │              │
          │              │ │   Embedding │ │              │
          │              │ └─────────────┘ │              │
          │              │ ┌─────────────┐ │              │
          │              │ │  Anthropic  │ │              │
          │              │ │   Claude    │ │              │
          │              │ └─────────────┘ │              │
          │              │ ┌─────────────┐ │              │
          │              │ │   Mistral   │ │              │
          │              │ │     AI      │ │              │
          │              │ └─────────────┘ │              │
          │              │ ┌─────────────┐ │              │
          │              │ │    Groq     │ │              │
          │              │ └─────────────┘ │              │
          │              │ ┌─────────────┐ │              │
          │              │ │   Gemini    │ │              │
          │              │ └─────────────┘ │              │
          │              └─────────┬───────┘              │
          │                        │                       │
          │                        ▼                       │
          │              ┌─────────────────┐              │
          │              │  AI Response    │              │
          │              │                 │              │
          │              │ • Text Content  │              │
          │              │ • Embeddings    │              │
          │              │ • Tool Results  │              │
          │              └─────────┬───────┘              │
          │                        │                       │
          │                        ▼                       │
          │              ┌─────────────────┐              │
          │              │  Processing     │              │
          │              │                 │              │
          │              │ • Parse Response│              │
          │              │ • Store Data    │              │
          │              │ • Generate      │              │
          │              │   Embeddings    │              │
          │              └─────────┬───────┘              │
          │                        │                       │
          │                        ▼                       │
          │              ┌─────────────────┐              │
          │              │  Vector DB      │              │
          │              │                 │              │
          │              │ PostgreSQL +    │              │
          │              │ pgvector        │              │
          │              │                 │              │
          │              │ • Recipe vectors│              │
          │              │ • Product       │              │
          │              │   vectors       │              │
          │              │ • Content       │              │
          │              │   vectors       │              │
          │              └─────────┬───────┘              │
          │                        │                       │
          │                        ▼                       │
          │              ┌─────────────────┐              │
          │              │  User Response  │              │
          │              │                 │              │
          │              │ • AI Generated  │              │
          │              │   Content       │              │
          │              │ • Search Results│              │
          │              │ • Recommendations│              │
          │              └─────────────────┘              │
```

## 🔄 Key AI Workflows

### 1. Recipe Generation Flow
```
User Request → GenerateRecipes Command → PrismService → AI Provider → 
Parse Response → Store Recipe → Generate Embedding → Vector DB
```

### 2. Semantic Search Flow
```
User Query → Convert to Embedding → Vector Similarity Search → 
Rank Results → Return Matches
```

### 3. AI Assistant Flow
```
User Message → Context Analysis → Tool Selection → Execute Tools → 
AI Response → Return Answer
```

## 🛠️ Service Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    SmartChef Application                    │
├─────────────────────────────────────────────────────────────┤
│  Controllers                                               │
│  ├── RecipeController                                      │
│  ├── Auth Controllers                                      │
│  └── Settings Controllers                                  │
├─────────────────────────────────────────────────────────────┤
│  Services                                                  │
│  ├── PrismService (AI Gateway)                            │
│  ├── EmbeddingService (Vector Management)                 │
│  ├── RecipeEmbeddingService (Recipe-specific)             │
│  └── AIToolService (Tool Execution)                       │
├─────────────────────────────────────────────────────────────┤
│  Models                                                    │
│  ├── Recipe (with embedding)                              │
│  ├── Product (with embedding)                             │
│  ├── Content (with embedding)                             │
│  └── User                                                 │
├─────────────────────────────────────────────────────────────┤
│  Commands                                                  │
│  ├── GenerateRecipes                                       │
│  ├── GenerateRecipeEmbeddings                             │
│  ├── GenerateProductEmbeddings                            │
│  └── GenerateContentEmbeddings                            │
└─────────────────────────────────────────────────────────────┘
```

## 🌐 External AI Integration

```
SmartChef ←→ Prism API Gateway ←→ Multiple AI Providers
    │              │                      │
    │              │                      ├── OpenAI (GPT-4, Embeddings)
    │              │                      ├── Anthropic (Claude)
    │              │                      ├── Mistral AI
    │              │                      ├── Groq
    │              │                      ├── Gemini
    │              │                      ├── DeepSeek
    │              │                      ├── VoyageAI
    │              │                      └── Ollama (Local)
    │              │
    │              └── Unified API Interface
    │                     │
    │                     ├── Authentication
    │                     ├── Rate Limiting
    │                     ├── Provider Selection
    │                     ├── Response Formatting
    │                     └── Error Handling
    │
    └── Vector Database (PostgreSQL + pgvector)
```

## 📊 Data Flow Example

### Recipe Creation with AI
```
1. User submits recipe request
   ↓
2. RecipeController receives request
   ↓
3. PrismService calls AI provider
   ↓
4. AI generates recipe content
   ↓
5. Content is parsed and stored
   ↓
6. EmbeddingService generates vector
   ↓
7. Vector stored in database
   ↓
8. User receives AI-generated recipe
```

### Semantic Search
```
1. User types search query
   ↓
2. Query converted to embedding
   ↓
3. Vector similarity search
   ↓
4. Ranked results returned
   ↓
5. User sees relevant recipes
```

## 🔧 Configuration Points

- **Environment Variables**: Control AI provider selection
- **Service Configuration**: Manage API endpoints and tokens
- **Model Selection**: Choose appropriate AI models per use case
- **Vector Database**: Configure PostgreSQL with pgvector extension
- **Rate Limiting**: Manage API call limits and costs

This architecture provides a robust, scalable, and maintainable way to integrate AI capabilities into your SmartChef application while supporting multiple providers and use cases.
