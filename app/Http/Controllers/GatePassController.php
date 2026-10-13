<?php

namespace App\Http\Controllers;

use App\Models\GatePass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GatePassController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        // Approving / rejecting / recording times (status) is further limited by the approval-chain roles.
        return [
            new Middleware('permission:gate-passes.view', only: ['index', 'show', 'print', 'status']),
            new Middleware('permission:gate-passes.create', only: ['create', 'store']),
            new Middleware('permission:gate-passes.edit', only: ['edit', 'update']),
            new Middleware('permission:gate-passes.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $passes = GatePass::query()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('pass_number', 'like', "%$s%")
                ->orWhere('employee_name', 'like', "%$s%")
                ->orWhere('father_name', 'like', "%$s%")
                ->orWhere('department', 'like', "%$s%")
                ->orWhere('purpose', 'like', "%$s%")))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->scope === 'action', fn ($q) => $q->actionableBy($user))
            ->latest('id')->paginate(15)->withQueryString();

        return view('gate-passes.index', [
            'passes' => $passes,
            'actionCount' => GatePass::actionableBy($user)->count(),
        ]);
    }

    public function create(): View
    {
        return view('gate-passes.form', [
            'pass' => new GatePass(['pass_date' => today()]),
            'departments' => $this->departments(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pass = GatePass::create($this->validated($request) + [
            'pass_number' => GatePass::nextNumber(),
            'status' => 'pending_hod',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('gate-passes.show', $pass)->with('status', 'Gate pass submitted to the Head of Department for approval.');
    }

    public function show(GatePass $gatePass): View
    {
        return view('gate-passes.show', ['pass' => $gatePass->load('creator', 'hodBy', 'hrBy', 'securityBy', 'rejectedBy')]);
    }

    public function edit(Request $request, GatePass $gatePass): View
    {
        abort_unless($gatePass->canEdit($request->user()), 403, 'Only a pass still awaiting the Head of Department can be edited.');

        return view('gate-passes.form', ['pass' => $gatePass, 'departments' => $this->departments()]);
    }

    public function update(Request $request, GatePass $gatePass): RedirectResponse
    {
        abort_unless($gatePass->canEdit($request->user()), 403, 'Only a pass still awaiting the Head of Department can be edited.');

        $gatePass->update($this->validated($request));

        return redirect()->route('gate-passes.show', $gatePass)->with('status', 'Gate pass updated.');
    }

    public function destroy(Request $request, GatePass $gatePass): RedirectResponse
    {
        abort_unless($gatePass->canDelete($request->user()), 403);

        $gatePass->delete();

        return redirect()->route('gate-passes.index')->with('status', 'Gate pass deleted.');
    }

    public function print(GatePass $gatePass): View
    {
        return view('gate-passes.print', ['pass' => $gatePass->load('hodBy', 'hrBy', 'securityBy')]);
    }

    /** Approve / reject at the current stage, record the actual out and in times, or cancel. */
    public function status(Request $request, GatePass $gatePass): RedirectResponse
    {
        $user = $request->user();
        $action = $request->validate(['action' => ['required', Rule::in(['approve', 'reject', 'out', 'in', 'cancel'])]])['action'];

        $message = match ($action) {
            'approve' => $this->approve($gatePass, $user),
            'reject' => $this->reject($request, $gatePass, $user),
            'out' => $this->recordMovement($gatePass, $user, 'approved', ['status' => 'out', 'out_time' => now()], 'Out time recorded.'),
            'in' => $this->recordMovement($gatePass, $user, 'out', ['status' => 'completed', 'in_time' => now()], 'In time recorded — pass completed.'),
            'cancel' => $this->cancel($gatePass, $user),
        };

        return back()->with('status', $message);
    }

    private function approve(GatePass $pass, User $user): string
    {
        abort_unless($pass->canApprove($user), 403, 'You cannot approve this pass at its current stage.');

        [$key, , $label, $next] = $pass->stage();
        $pass->update(["{$key}_by" => $user->id, "{$key}_at" => now(), 'status' => $next]);

        return "Approved as {$label}.";
    }

    private function reject(Request $request, GatePass $pass, User $user): string
    {
        abort_unless($pass->canApprove($user), 403, 'You cannot reject this pass at its current stage.');
        $reason = $request->validate(['reason' => ['required', 'string', 'max:500']])['reason'];

        $pass->update([
            'rejected_stage' => $pass->status, 'status' => 'rejected',
            'rejected_by' => $user->id, 'rejected_at' => now(), 'reject_reason' => $reason,
        ]);

        return 'Gate pass rejected.';
    }

    private function recordMovement(GatePass $pass, User $user, string $from, array $changes, string $message): string
    {
        abort_unless($pass->status === $from && $pass->canRecordMovement($user), 403);
        $pass->update($changes);

        return $message;
    }

    private function cancel(GatePass $pass, User $user): string
    {
        abort_unless($pass->canCancel($user), 403);
        $pass->update(['status' => 'cancelled']);

        return 'Gate pass cancelled.';
    }

    /** Departments that have a Head of Department who can approve. */
    private function departments(): array
    {
        return User::role('dept_head')->whereNotNull('department')->distinct()->orderBy('department')->pluck('department')->all();
    }

    private function validated(Request $request): array
    {
        $departments = $this->departments();

        return $request->validate([
            'pass_date' => ['required', 'date'],
            'employee_name' => ['required', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255', $departments ? Rule::in($departments) : 'string'],
            'out_from' => ['required', 'date_format:H:i'],
            'out_to' => ['required', 'date_format:H:i', 'after:out_from'],
            'purpose' => ['required', 'string', 'max:255'],
        ]);
    }
}
