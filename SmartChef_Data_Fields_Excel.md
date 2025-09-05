# SmartChef Data Fields - Excel File Structure

## 📊 Excel File Organization

This document describes the structure of the Excel file with multiple sheets for easy data entry.

## 🗂️ Sheet 1: PRODUCTS

| Field Name | Data Type | Required | Description | Example Value |
|------------|-----------|----------|-------------|---------------|
| ulid | Char(36) | Yes | Unique identifier (ULID format) | 01H9X8Y7Z6W5V4U3T2S1R0Q |
| slug | String | Yes | URL-friendly identifier | arroz-integral-organico |
| codigo_padrao | String | No | Standard product code | ARZ-001 |
| sku | String | No | Stock keeping unit | ARZ-ORG-1KG |
| group_product_id | Integer | No | Reference to product group | 1 |
| marca | String | No | Brand name | Orgânica |
| descricao | String | Yes | Product description | Arroz integral orgânico de alta qualidade |
| status | Boolean | No | Active status | true |

## 🗂️ Sheet 2: PRODUCT_DETAILS

| Field Name | Data Type | Required | Description | Example Value |
|------------|-----------|----------|-------------|---------------|
| product_id | Integer | Yes | Reference to product | 1 |
| marca | String | No | Brand (can override product brand) | Orgânica |
| escolha_embalagem | String | No | Packaging choice | Saco de papel |
| prompt_especificacao_embalagens | Text | No | Packaging specification prompt | Descreva as especificações da embalagem |
| prompt_uso_informacoes_produto | Text | No | Product usage information prompt | Como usar este produto |
| especificacao_produto | Text | No | Product specification | Arroz integral orgânico certificado |
| perfil_sabor | String | No | Flavor profile | Suave e natural |
| descricao_tabela_nutricional | Text | No | Nutritional table description | Rica em fibras e minerais |
| descricao_lista_ingredientes | Text | No | Ingredients list description | 100% arroz integral orgânico |
| descricao_modos_preparo | Text | No | Preparation methods description | Cozinhar em água fervente |
| descricao_rendimentos | Text | No | Yield description | 1 xícara rende 3 xícaras |
| embalagem_tipo | String | No | Packaging type | kg |
| embalagem_descricao | String | No | Packaging description | Saco de papel kraft 1kg |
| quantidade_caixa | String | No | Quantity per box | 12 unidades |
| peso_liquido | Decimal | No | Net weight | 1.00 |
| peso_bruto | Decimal | No | Gross weight | 1.05 |
| validade | String | No | Expiration date | 2025-12-31 |
| valor | Decimal | No | Price | 8.90 |
| desconto | Decimal | No | Discount percentage | 0.00 |
| informacao_adicional | Text | No | Additional information | Produto certificado orgânico |
| ean | String | No | EAN barcode | 7891234567890 |
| qr_code | String | No | QR code | https://qr.example.com/123 |
| url_rede_social | String | No | Social media URL | https://instagram.com/brand |
| catalogo | Boolean | No | Catalog inclusion | true |
| lancamento | Boolean | No | New product flag | false |
| status | Boolean | No | Active status | true |

## 🗂️ Sheet 3: GROUP_PRODUCTS

| Field Name | Data Type | Required | Description | Example Value |
|------------|-----------|----------|-------------|---------------|
| ulid | Char(36) | Yes | Unique identifier (ULID format) | 01H9X8Y7Z6W5V4U3T2S1R0Q |
| codigo_padrao | String | No | Standard group code | GRP-001 |
| descricao | String | Yes | Group description (unique) | Grãos e Cereais |
| observacao | String | No | Group observations | Produtos básicos da alimentação |
| status | Boolean | No | Active status | true |

## 🗂️ Sheet 4: RECIPES

| Field Name | Data Type | Required | Description | Example Value |
|------------|-----------|----------|-------------|---------------|
| title | String | Yes | Recipe title | Arroz Integral com Legumes |
| raw_text | Text | Yes | Raw recipe text | Receita completa de arroz integral com legumes... |
| metadata | JSON | No | Additional metadata | {"tempo_total": 60, "dificuldade": "medio"} |
| tags | JSON | No | Recipe tags | ["arroz", "integral", "legumes", "saudável"] |
| recipe_code | String | No | Recipe code | REC-001 |
| recipe_name | String | No | Recipe name | Arroz Integral com Legumes |
| cuisine | String | No | Cuisine type | Brasileira |
| recipe_type | String | No | Recipe type | Principal |
| service_order | String | No | Service order | SO-001 |
| preparation_time | Integer | No | Preparation time in minutes | 45 |
| difficulty_level | String | No | Difficulty | medio |
| yield | String | No | Number of servings | 4 |
| channel | String | No | Channel | website |
| recipe_description | Text | No | Recipe description | Receita saudável e nutritiva... |
| ingredients_description | Text | No | Ingredients description | Lista completa de ingredientes... |
| preparation_method | Text | No | Preparation method | Passo a passo detalhado... |
| main_ingredients | JSON | No | Main ingredients | ["arroz integral", "cenoura", "brócolis"] |
| supporting_ingredients | JSON | No | Supporting ingredients | ["cebola", "alho", "azeite"] |
| usage_groups | JSON | No | Usage groups | ["almoço", "jantar", "dieta"] |
| preparation_techniques | JSON | No | Preparation techniques | ["refogar", "cozinhar", "temperar"] |
| consumption_occasion | JSON | No | Consumption occasions | ["dia a dia", "fins de semana"] |
| general_images_link | String | No | General images link | https://example.com/images |
| product_code | String | No | Product code reference | ARZ-001 |
| content_code | String | No | Content code reference | CONT-001 |

## 🗂️ Sheet 5: CONTENTS

| Field Name | Data Type | Required | Description | Example Value |
|------------|-----------|----------|-------------|---------------|
| nome_conteudo | String | No | Content name | Guia Nutricional Arroz |
| content_code | String | No | Content code (unique) | CONT-001 |
| descricao_tabela_nutricional | Text | No | Nutritional table description | Informações nutricionais completas |
| descricao_lista_ingredientes | Text | No | Ingredients list description | Lista detalhada de ingredientes |
| descricao_modos_preparo | Text | No | Preparation methods description | Métodos de preparo variados |
| descricao_rendimentos | Text | No | Yield description | Diferentes rendimentos possíveis |
| imagem_tabela_nutricional | String | No | Nutritional table image URL | https://example.com/nutricional.jpg |
| imagem_lista_ingredientes | String | No | Ingredients list image URL | https://example.com/ingredientes.jpg |
| imagem_modos_preparo | String | No | Preparation methods image URL | https://example.com/preparo.jpg |
| imagem_rendimentos | String | No | Yield image URL | https://example.com/rendimentos.jpg |
| imagens_nutricionais_cadastradas | JSON | No | Registered nutritional images | ["img1.jpg", "img2.jpg"] |
| imagens_ingredientes_cadastradas | JSON | No | Registered ingredients images | ["img3.jpg", "img4.jpg"] |
| imagens_preparo_cadastradas | JSON | No | Registered preparation images | ["img5.jpg", "img6.jpg"] |
| imagens_rendimentos_cadastradas | JSON | No | Registered yield images | ["img7.jpg", "img8.jpg"] |
| tipo_conteudo | String | No | Content type | Nutricional |
| pilares | String | No | Content pillars | Saúde |
| canal | String | No | Channel | website |
| links_conteudo | JSON | No | Content links | ["link1.com", "link2.com"] |
| cozinheiro | Boolean | No | Chef access | true |
| comprador | Boolean | No | Buyer access | true |
| administrador | Boolean | No | Admin access | false |
| status | Boolean | No | Active status | true |
| descricao_conteudo | Text | No | Content description | Crie conteúdo sobre arroz integral |

## 🗂️ Sheet 6: RECIPE_PRODUCT_RELATIONSHIPS

| Field Name | Data Type | Required | Description | Example Value |
|------------|-----------|----------|-------------|---------------|
| recipe_id | Integer | Yes | Recipe reference | 1 |
| product_id | Integer | Yes | Product reference | 1 |
| quantity | Decimal | No | Product quantity in recipe | 200.00 |
| unit | String | No | Unit | g |
| ingredient_type | Enum | No | Ingredient type | main |
| preparation_notes | Text | No | Preparation notes | Lavar bem antes de usar |
| optional | Boolean | No | Whether ingredient is optional | false |
| order | Integer | No | Order of appearance in recipe | 1 |

## 🗂️ Sheet 7: RECIPE_CONTENT_RELATIONSHIPS

| Field Name | Data Type | Required | Description | Example Value |
|------------|-----------|----------|-------------|---------------|
| recipe_id | Integer | Yes | Recipe reference | 1 |
| content_id | Integer | Yes | Content reference | 1 |
| top_dish | String | No | Top dish flag | sim |
| order | Integer | No | Order | 1 |

## 🗂️ Sheet 8: CONTENT_PRODUCT_RELATIONSHIPS

| Field Name | Data Type | Required | Description | Example Value |
|------------|-----------|----------|-------------|---------------|
| content_id | Integer | Yes | Content reference | 1 |
| product_id | Integer | Yes | Product reference | 1 |
| featured | String | No | Featured product flag | sim |
| order | Integer | No | Order | 1 |
| notes | Text | No | Notes | Produto destaque do mês |

## 🗂️ Sheet 9: DATA_ENTRY_TEMPLATE

### Product Entry Template
```
ulid: [Generate ULID]
slug: [product-name-slug]
codigo_padrao: [PROD-001]
sku: [PROD-BRAND-SIZE]
group_product_id: [1]
marca: [Brand Name]
descricao: [Product description]
status: [true/false]
```

### Recipe Entry Template
```
title: [Recipe Title]
raw_text: [Complete recipe text]
recipe_code: [REC-001]
recipe_name: [Recipe Name]
cuisine: [Cuisine Type]
recipe_type: [Recipe Type]
preparation_time: [45]
difficulty_level: [facil/medio/dificil/expert]
yield: [4]
channel: [website/youtube/instagram/blog]
recipe_description: [Recipe description]
ingredients_description: [Ingredients description]
preparation_method: [Preparation method]
main_ingredients: ["ingredient1", "ingredient2"]
supporting_ingredients: ["ingredient3", "ingredient4"]
usage_groups: ["group1", "group2"]
preparation_techniques: ["technique1", "technique2"]
consumption_occasion: ["occasion1", "occasion2"]
tags: ["tag1", "tag2", "tag3"]
```

### Content Entry Template
```
nome_conteudo: [Content Name]
content_code: [CONT-001]
tipo_conteudo: [Content Type]
pilares: [Content Pillars]
canal: [Channel]
cozinheiro: [true/false]
comprador: [true/false]
administrador: [true/false]
status: [true/false]
descricao_conteudo: [Content description]
```

## 🗂️ Sheet 10: FIELD_VALIDATION_RULES

### Required Fields
- **Products**: ulid, slug, descricao
- **Recipes**: title, raw_text
- **Group Products**: ulid, descricao

### Data Type Guidelines
- **JSON Arrays**: Use comma-separated values or JSON format
- **Boolean**: true/false, 1/0, or yes/no
- **Decimal**: Use dot as decimal separator (e.g., 8.90)
- **Integer**: Whole numbers only
- **String**: Text up to 255 characters
- **Text**: Long text content

### Enum Values
- **difficulty_level**: facil, medio, dificil, expert
- **ingredient_type**: main, supporting
- **embalagem_tipo**: kg, g, l, ml, c, cx, un, pct, sache, lata, frasco
- **channel**: website, youtube, instagram, blog

### Auto-Generated Fields (Do NOT fill)
- **id**: Auto-increment
- **embedding**: AI-generated vector
- **created_at**: Creation timestamp
- **updated_at**: Update timestamp

## 📋 Instructions for Users

1. **Start with Group Products**: Create product groups first
2. **Then Products**: Create products and assign them to groups
3. **Product Details**: Fill detailed information for each product
4. **Contents**: Create content items
5. **Recipes**: Create recipes with ingredients and methods
6. **Relationships**: Connect recipes with products and contents

## 🔗 ULID Generation

Use online ULID generators or generate programmatically:
- Format: 01H9X8Y7Z6W5V4U3T2S1R0Q
- Always starts with timestamp
- 26 characters long
- URL-safe

## 📊 Data Import Process

1. Fill the Excel sheets with your data
2. Validate required fields are completed
3. Check data types match specifications
4. Ensure referential integrity (IDs exist in related tables)
5. Import data using Laravel seeders or database import tools
