<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorPass extends Model
{
    public const STATUSES = ['expected' => 'Expected', 'in' => 'Checked In', 'out' => 'Checked Out', 'cancelled' => 'Cancelled'];

    public const ID_PROOFS = ['Aadhaar', 'Driving Licence', 'PAN', 'Passport', 'Voter ID', 'Company ID', 'Other'];

    protected $fillable = [
        'pass_number', 'visitor_name', 'company', 'phone', 'email', 'id_proof_type', 'id_proof_number',
        'person_to_meet', 'department', 'purpose', 'persons', 'vehicle_number', 'belongings',
        'expected_at', 'check_in_at', 'check_out_at', 'status', 'remarks', 'created_by',
    ];

    protected function casts(): array
    {
        return ['expected_at' => 'datetime', 'check_in_at' => 'datetime', 'check_out_at' => 'datetime'];
    }

    /** Still on site after the day they arrived. */
    public function isStale(): bool
    {
        return $this->status === 'in' && $this->check_in_at?->isBefore(today());
    }

    /** Pre-registered visitor whose expected arrival time has passed. */
    public function isLate(): bool
    {
        return $this->status === 'expected' && $this->expected_at?->isPast();
    }

    /** When the visitor arrived, or is due to arrive. */
    public function visitAt(): ?\Illuminate\Support\Carbon
    {
        return $this->check_in_at ?? $this->expected_at;
    }

    /** Next sequential number for the year, e.g. VP-2026-0001. */
    public static function nextNumber(): string
    {
        $prefix = 'VP-'.now()->year.'-';
        $last = static::where('pass_number', 'like', $prefix.'%')->orderByDesc('pass_number')->value('pass_number');

        return $prefix.str_pad(($last ? (int) substr($last, -4) : 0) + 1, 4, '0', STR_PAD_LEFT);
    }
}
