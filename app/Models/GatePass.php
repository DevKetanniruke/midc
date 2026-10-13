<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GatePass extends Model
{
    public const TYPES = ['returnable' => 'Returnable', 'non_returnable' => 'Non-Returnable'];

    public const STATUSES = ['open' => 'Open', 'returned' => 'Returned', 'cancelled' => 'Cancelled'];

    protected $fillable = [
        'pass_number', 'type', 'party_name', 'purpose', 'vehicle_number', 'driver_name', 'driver_phone',
        'items', 'issued_at', 'expected_return_on', 'returned_at', 'status', 'remarks', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'issued_at' => 'datetime',
            'expected_return_on' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    public function isReturnable(): bool
    {
        return $this->type === 'returnable';
    }

    public function isOverdue(): bool
    {
        return $this->isReturnable() && $this->status === 'open'
            && $this->expected_return_on && $this->expected_return_on->isBefore(today());
    }

    /** Next sequential number for the year, e.g. GP-2026-0001. */
    public static function nextNumber(): string
    {
        $prefix = 'GP-'.now()->year.'-';
        $last = static::where('pass_number', 'like', $prefix.'%')->orderByDesc('pass_number')->value('pass_number');

        return $prefix.str_pad(($last ? (int) substr($last, -4) : 0) + 1, 4, '0', STR_PAD_LEFT);
    }
}
