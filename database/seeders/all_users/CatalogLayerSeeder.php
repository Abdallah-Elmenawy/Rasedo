<?php

namespace Database\Seeders\all_users;

use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use Illuminate\Database\Seeder;

class CatalogLayerSeeder extends Seeder
{
    public function run(): void
    {
        $category = ProductCategory::query()->updateOrCreate(['name' => 'عام']);

        Product::query()->updateOrCreate(['sku' => 'P-001'], [
            'name' => 'منتج تجريبي',
            'category_id' => $category->id,
            'type' => 'product',
            'unit' => 'قطعة',
            'cost_price' => 50,
            'sale_price' => 80,
            'min_stock' => 5,
            'current_stock' => 40,
            'is_active' => true,
        ]);
    }
}
