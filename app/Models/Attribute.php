<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    /**
     * Condition for when this field is shown (dependent on another field's value).
     */
    public function attributeCondition(): HasOne
    {
        return $this->hasOne(AttributeCondition::class);
    }

    /**
     * Get showIf rule for end-user forms (from attribute_conditions or conditional_logic).
     *
     * @return array{field: string, value: string}|null
     */
    public function getShowIf(): ?array
    {
        $cond = $this->relationLoaded('attributeCondition') ? $this->attributeCondition : $this->attributeCondition()->with('parentAttribute')->first();
        if ($cond && $cond->parentAttribute) {
            return ['field' => $cond->parentAttribute->name, 'value' => $cond->trigger_value];
        }
        if (! empty($this->conditional_logic)) {
            $l = $this->conditional_logic;
            $field = $l['field'] ?? $l['showIf']['field'] ?? null;
            $value = $l['value'] ?? $l['showIf']['value'] ?? null;
            if ($field !== null || $value !== null) {
                return ['field' => (string) $field, 'value' => (string) $value];
            }
        }
        return null;
    }
}
