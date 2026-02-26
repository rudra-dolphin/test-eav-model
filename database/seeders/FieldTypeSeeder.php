<?php

namespace Database\Seeders;

use App\Models\FieldType;
use Illuminate\Database\Seeder;

class FieldTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Text', 'slug' => 'text', 'description' => 'Single line text', 'supports_options' => false, 'allows_multiple' => false, 'value_column' => 'value_text'],
            ['name' => 'Number', 'slug' => 'number', 'description' => 'Numeric input', 'supports_options' => false, 'allows_multiple' => false, 'value_column' => 'value_int'],
            ['name' => 'Decimal', 'slug' => 'decimal', 'description' => 'Decimal number', 'supports_options' => false, 'allows_multiple' => false, 'value_column' => 'value_decimal'],
            ['name' => 'Date', 'slug' => 'date', 'description' => 'Date picker', 'supports_options' => false, 'allows_multiple' => false, 'value_column' => 'value_date'],
            ['name' => 'Radio', 'slug' => 'radio', 'description' => 'Single choice from options', 'supports_options' => true, 'allows_multiple' => false, 'value_column' => 'value_text'],
            ['name' => 'Checkbox', 'slug' => 'checkbox', 'description' => 'Single or multiple checkboxes', 'supports_options' => true, 'allows_multiple' => true, 'value_column' => 'value_text'],
            ['name' => 'Dropdown', 'slug' => 'dropdown', 'description' => 'Select from options', 'supports_options' => true, 'allows_multiple' => false, 'value_column' => 'value_text'],
            ['name' => 'Section heading', 'slug' => 'heading', 'description' => 'Main section title (e.g. Lifestyle Factors)', 'supports_options' => false, 'allows_multiple' => false, 'value_column' => 'value_text'],
            ['name' => 'Subsection heading', 'slug' => 'heading_sub', 'description' => 'Middle/sub-section title (e.g. Comorbid conditions)', 'supports_options' => false, 'allows_multiple' => false, 'value_column' => 'value_text'],
        ];

        foreach ($types as $type) {
            FieldType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
