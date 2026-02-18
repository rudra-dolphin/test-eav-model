<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'field_type_id',
        'name',
        'label',
        'placeholder',
        'help_text',
        'sort_order',
        'is_required',
        'validation_config',
        'conditional_logic',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'validation_config' => 'array',
            'conditional_logic' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Form this attribute belongs to.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * Field type (text, number, date, radio, checkbox, dropdown).
     */
    public function fieldType(): BelongsTo
    {
        return $this->belongsTo(FieldType::class, 'field_type_id');
    }

    /**
     * Options for dropdown, radio, checkbox.
     */
    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class, 'attribute_id')->orderBy('sort_order');
    }

    /**
     * Stored values for this attribute across entities.
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id');
    }
}
