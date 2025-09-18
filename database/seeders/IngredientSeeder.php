<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            // Vegetables
            'Onion', 'Garlic', 'Tomato', 'Carrot', 'Potato', 'Bell Pepper', 'Cucumber', 'Lettuce',
            'Spinach', 'Kale', 'Broccoli', 'Cauliflower', 'Zucchini', 'Eggplant', 'Mushroom',
            'Celery', 'Parsley', 'Cilantro', 'Basil', 'Oregano', 'Thyme', 'Rosemary',
            
            // Meats
            'Chicken Breast', 'Ground Beef', 'Pork Chop', 'Bacon', 'Ham', 'Turkey',
            'Lamb', 'Sausage', 'Salmon', 'Tuna', 'Shrimp', 'Cod', 'Tilapia',
            
            // Dairy & Eggs
            'Milk', 'Cheese', 'Butter', 'Eggs', 'Yogurt', 'Cream', 'Sour Cream',
            'Cheddar', 'Mozzarella', 'Parmesan', 'Ricotta', 'Cottage Cheese',
            
            // Grains & Breads
            'Rice', 'Pasta', 'Bread', 'Flour', 'Oats', 'Quinoa', 'Barley',
            'Tortilla', 'Naan', 'Baguette', 'Whole Wheat Bread',
            
            // Legumes
            'Black Beans', 'Kidney Beans', 'Chickpeas', 'Lentils', 'Peas',
            'Pinto Beans', 'Navy Beans', 'Split Peas',
            
            // Fruits
            'Apple', 'Banana', 'Orange', 'Lemon', 'Lime', 'Strawberry',
            'Blueberry', 'Raspberry', 'Pineapple', 'Mango', 'Avocado',
            
            // Nuts & Seeds
            'Almonds', 'Walnuts', 'Pecans', 'Cashews', 'Peanuts', 'Sunflower Seeds',
            'Chia Seeds', 'Flax Seeds', 'Pumpkin Seeds',
            
            // Oils & Fats
            'Olive Oil', 'Vegetable Oil', 'Coconut Oil', 'Sesame Oil',
            'Canola Oil', 'Avocado Oil',
            
            // Condiments & Sauces
            'Salt', 'Black Pepper', 'Soy Sauce', 'Ketchup', 'Mustard',
            'Mayonnaise', 'Hot Sauce', 'Worcestershire Sauce', 'Vinegar',
            'Balsamic Vinegar', 'Apple Cider Vinegar',
            
            // Sweeteners
            'Sugar', 'Brown Sugar', 'Honey', 'Maple Syrup', 'Agave Nectar',
            'Stevia', 'Powdered Sugar',
            
            // Spices & Herbs
            'Cinnamon', 'Nutmeg', 'Ginger', 'Turmeric', 'Cumin', 'Paprika',
            'Chili Powder', 'Cayenne Pepper', 'Bay Leaves', 'Sage',
            'Dill', 'Mint', 'Chives', 'Tarragon',
            
            // Baking
            'Baking Soda', 'Baking Powder', 'Vanilla Extract', 'Yeast',
            'Cornstarch', 'Cocoa Powder', 'Chocolate Chips',
            
            // Broths & Stocks
            'Chicken Broth', 'Beef Broth', 'Vegetable Broth', 'Fish Stock',
            
            // Other
            'Tomato Paste', 'Tomato Sauce', 'Coconut Milk', 'Evaporated Milk',
            'Condensed Milk', 'Breadcrumbs', 'Panko', 'Cornmeal'
        ];

        foreach ($ingredients as $ingredientName) {
            Ingredient::create([
                'name' => $ingredientName,
            ]);
        }
    }
}
