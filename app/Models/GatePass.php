<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class GatePass extends Model
{
    /** Approval chain: status waiting on the stage => [stage key, role, label, next status]. */
    public const FLOW = [
        'pending_hod' => ['hod', 'dept_head', 'Head of Dept.', 'pending_hr'],
        'pending_hr' => ['hr', 'hr', 'HR', 'pending_security'],
        'pending_security' => ['security', 'security', 'Security', 'approved'],
    ];

    /** status => [label, badge classes] */
    public const STATUSES = [
        'pending_hod' => ['Awaiting Dept. Head', 'bg-amber-100 text-amber-700'],
        'pending_hr' => ['Awaiting HR', 'bg-amber-100 text-amber-700'],
        'pending_security' => ['Awaiting Security', 'bg-amber-100 text-amber-700'],
        'approved' => ['Approved', 'bg-sky-100 text-sky-700'],
        'out' => ['Out', 'bg-violet-100 text-violet-700'],
        'completed' => ['Completed', 'bg-emerald-100 text-emerald-700'],
        'rejected' => ['Rejected', 'bg-red-100 text-red-700'],
        'cancelled' => ['Cancelled', 'bg-gray-100 text-gray-600'],
    ];

    protected $fillable = [
        'pass_number', 'pass_date', 'employee_name', 'father_name', 'department', 'out_from', 'out_to', 'purpose', 'status',
        'hod_by', 'hod_at', 'hr_by', 'hr_at', 'security_by', 'security_at',
        'rejected_by', 'rejected_at', 'rejected_stage', 'reject_reason',
        'out_time', 'in_time', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'pass_date' => 'date',
            'hod_at' => 'datetime', 'hr_at' => 'datetime', 'security_at' => 'datetime',
            'rejected_at' => 'datetime', 'out_time' => 'datetime', 'in_time' => 'datetime',
        ];
    }

    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function hodBy(): BelongsTo { return $this->belongsTo(User::class, 'hod_by'); }
    public function hrBy(): BelongsTo { return $this->belongsTo(User::class, 'hr_by'); }
    public function securityBy(): BelongsTo { return $this->belongsTo(User::class, 'security_by'); }
    public function rejectedBy(): BelongsTo { return $this->belongsTo(User::class, 'rejected_by'); }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function statusClass(): string
    {
        return self::STATUSES[$this->status][1] ?? 'bg-gray-100 text-gray-600';
    }

    /** "02:30 PM" from a stored H:i[:s] time. */
    public static function clock(?string $time): string
    {
        return $time ? Carbon::parse($time)->format('h:i A') : '';
    }

    /** [stage key, role, label, next status] for the stage this pass is waiting on, if any. */
    public function stage(): ?array
    {
        return self::FLOW[$this->status] ?? null;
    }

    private static function sameDepartment(?string $a, ?string $b): bool
    {
        return strcasecmp(trim((string) $a), trim((string) $b)) === 0 && trim((string) $a) !== '';
    }

    /** Admins can act at any stage; otherwise the role for the stage (a Dept. Head only for their own department). */
    public function canApprove(User $user): bool
    {
        if (! $stage = $this->stage()) {
            return false;
        }

        return $user->hasRole('admin')
            || ($user->hasRole($stage[1]) && ($stage[1] !== 'dept_head' || self::sameDepartment($user->department, $this->department)));
    }

    public function canRecordMovement(User $user): bool
    {
        return in_array($this->status, ['approved', 'out'], true) && $user->hasAnyRole(['admin', 'security']);
    }

    private function isOwnerOrAdmin(User $user): bool
    {
        return $user->hasRole('admin') || $this->created_by === $user->id;
    }

    public function canEdit(User $user): bool
    {
        return $user->can('gate-passes.edit') && $this->status === 'pending_hod' && $this->isOwnerOrAdmin($user);
    }

    public function canCancel(User $user): bool
    {
        return (isset(self::FLOW[$this->status]) || $this->status === 'approved') && $this->isOwnerOrAdmin($user);
    }

    public function canDelete(User $user): bool
    {
        return $user->can('gate-passes.delete')
            && ($user->hasRole('admin') || ($this->status === 'pending_hod' && $this->created_by === $user->id));
    }

    /** Passes the user can currently act on (approve / reject, or record out and in times). */
    public function scopeActionableBy(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query->whereIn('status', ['pending_hod', 'pending_hr', 'pending_security', 'approved', 'out']);
        }

        $roles = array_filter([
            $user->hasRole('dept_head'), $user->hasRole('hr'), $user->hasRole('security'),
        ]);
        if (! $roles) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $w) use ($user) {
            if ($user->hasRole('dept_head')) {
                $w->orWhere(fn (Builder $x) => $x->where('status', 'pending_hod')
                    ->whereRaw('LOWER(TRIM(department)) = ?', [strtolower(trim((string) $user->department))]));
            }
            if ($user->hasRole('hr')) {
                $w->orWhere('status', 'pending_hr');
            }
            if ($user->hasRole('security')) {
                $w->orWhereIn('status', ['pending_security', 'approved', 'out']);
            }
        });
    }

    /** Next sequential number for the year, e.g. GP-2026-0001. */
    public static function nextNumber(): string
    {
        $prefix = 'GP-'.now()->year.'-';
        $last = static::where('pass_number', 'like', $prefix.'%')->orderByDesc('pass_number')->value('pass_number');

        return $prefix.str_pad(($last ? (int) substr($last, -4) : 0) + 1, 4, '0', STR_PAD_LEFT);
    }
}
