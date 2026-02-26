<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeCondition extends Model
{
    protected $fillable = [
        'attribute_id',
        'parent_attribute_id',
        'operator',
        'trigger_value',
    ];

    /**
     * The dependent attribute (this field is shown when condition matches).
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * The parent attribute (the field we watch for the trigger value).
     */
    public function parentAttribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class, 'parent_attribute_id');
    }
}
