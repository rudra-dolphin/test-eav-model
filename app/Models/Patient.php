<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_number',
        'name',
        'department',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'address',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    /**
     * Form submissions (entities) for this patient.
     */
    public function entities(): HasMany
    {
        return $this->hasMany(Entity::class);
    }

    /**
     * Display label for dropdowns (e.g. "Name (MRN)").
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = [$this->name];
        if ($this->patient_number) {
            $parts[] = "({$this->patient_number})";
        }
        return implode(' ', $parts);
    }
}
