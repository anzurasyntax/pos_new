<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeds the Caps & handles catalog: parent category, sub-groups, and one product per line item.
 */
class CapsHandlesCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $root = Category::query()->updateOrCreate(
            ['slug' => 'caps-handles'],
            [
                'name' => 'Caps & handles',
                'parent_id' => null,
                'sort_order' => 10,
            ]
        );

        $groups = [
            [
                'slug' => 'caps-handles-19-litter',
                'name' => '19 Litter',
                'sort_order' => 1,
                'sku_prefix' => '19L',
                'products' => [
                    'Short Blue Tiki Cap',
                    'Full Blue Tiki Cap',
                    'Blue Seal Cap',
                    'Short White Tiki Cap',
                    'Full White Tiki Cap',
                    'White seal Cap',
                    'Smart Blue Cap',
                    'Single Handle',
                    'Double Handle',
                    'Tiki',
                ],
            ],
            [
                'slug' => 'caps-handles-6-litter',
                'name' => '6 Litter',
                'sort_order' => 2,
                'sku_prefix' => '06L',
                'products' => [
                    'Handle Blue',
                    'Handle White',
                    'Cap Blue',
                    'Cap White',
                ],
            ],
            [
                'slug' => 'caps-handles-30mm',
                'name' => '30mm',
                'sort_order' => 3,
                'sku_prefix' => '30M',
                'products' => [
                    'Light Blue',
                    'Dark Blue',
                ],
            ],
            [
                'slug' => 'caps-handles-minerals',
                'name' => 'Minerals',
                'sort_order' => 4,
                'sku_prefix' => 'MIN',
                'products' => [
                    'Calcium',
                    'Magnesium',
                    'Sodium',
                    'Anti Scalent UK',
                    'Anti Scalent China',
                ],
            ],
            [
                'slug' => 'caps-handles-shopper',
                'name' => 'Shopper',
                'sort_order' => 5,
                'sku_prefix' => 'SHP',
                'products' => [
                    '16/17',
                    '17/20',
                    '30/56',
                ],
            ],
            [
                'slug' => 'caps-handles-bottle',
                'name' => 'Bottle',
                'sort_order' => 6,
                'sku_prefix' => 'BTL',
                'products' => [
                    '1.5 Litter Bottle',
                    '6 Litter Bottle',
                    '19 Litter Bottle Spell',
                    '19 Litter Bottle Care Plastic',
                ],
            ],
        ];

        foreach ($groups as $group) {
            $category = Category::query()->updateOrCreate(
                ['slug' => $group['slug']],
                [
                    'name' => $group['name'],
                    'parent_id' => $root->id,
                    'sort_order' => $group['sort_order'],
                ]
            );

            foreach ($group['products'] as $productName) {
                $sku = $this->uniqueSku($group['sku_prefix'], $productName);

                Product::query()->updateOrCreate(
                    ['sku' => $sku],
                    [
                        'category_id' => $category->id,
                        'name' => $productName,
                        'stock_quantity' => 0,
                        'purchase_price' => 0,
                        'sale_price' => 0,
                        'unit_type' => null,
                        'unit_value' => null,
                        'base_sku_prefix' => $group['sku_prefix'],
                    ]
                );
            }
        }
    }

    /**
     * Build a stable, unique SKU (prefix + slug from name), truncated to fit DB.
     */
    private function uniqueSku(string $prefix, string $productName): string
    {
        $slug = Str::upper(Str::slug($productName, '-'));
        $slug = $slug !== '' ? $slug : 'ITEM';

        $base = $prefix.'-'.$slug;
        if (strlen($base) <= 100) {
            return $base;
        }

        return substr($base, 0, 100);
    }
}
