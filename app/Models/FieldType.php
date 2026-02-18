<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FieldType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'supports_options',
        'allows_multiple',
        'value_column',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'supports_options' => 'boolean',
            'allows_multiple' => 'boolean',
        ];
    }

    /**
     * Attributes that use this field type.
     */
    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class, 'field_type_id');
    }
}
