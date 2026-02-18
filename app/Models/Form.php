<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'department',
        'description',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Form fields (EAV attributes).
     */
    public function fields(): HasMany
    {
        return $this->hasMany(Attribute::class)->orderBy('sort_order');
    }

    /**
     * Form submissions (entities).
     */
    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    /**
     * Get form structure for API (id as formId, fields array with validation/options/showIf).
     *
     * @return array<string, mixed>
     */
    public function toFormStructure(): array
    {
        $fields = $this->fields->map(function (Attribute $attr) {
            $field = [
                'id' => $attr->name,
                'label' => $attr->label,
                'type' => $attr->fieldType->slug,
                'required' => $attr->is_required,
            ];
            if ($attr->placeholder) {
                $field['placeholder'] = $attr->placeholder;
            }
            if ($attr->help_text) {
                $field['helpText'] = $attr->help_text;
            }
            if (! empty($attr->validation_config)) {
                $field['validation'] = $attr->validation_config;
            }
            if (! empty($attr->conditional_logic)) {
                $field['showIf'] = $attr->conditional_logic;
            }
            if ($attr->fieldType->supports_options && $attr->options->isNotEmpty()) {
                $field['options'] = $attr->options->map(fn ($o) => [
                    'value' => $o->value,
                    'label' => $o->label,
                ])->values()->all();
            }
            return $field;
        })->values()->all();

        return [
            'formId' => $this->slug,
            'title' => $this->title,
            'department' => $this->department,
            'fields' => $fields,
        ];
    }
}
