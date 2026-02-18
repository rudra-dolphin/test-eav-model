<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'attribute_id',
        'value_text',
        'value_int',
        'value_decimal',
        'value_date',
        'value_boolean',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value_int' => 'integer',
            'value_decimal' => 'decimal:6',
            'value_date' => 'date',
            'value_boolean' => 'boolean',
        ];
    }

    /**
     * Entity (form submission) this value belongs to.
     */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /**
     * Attribute this value is for.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Get the resolved value from the appropriate typed column.
     */
    public function getValueAttribute(): mixed
    {
        return $this->value_text
            ?? $this->value_int
            ?? $this->value_decimal
            ?? $this->value_date?->format('Y-m-d')
            ?? $this->value_boolean;
    }
}
