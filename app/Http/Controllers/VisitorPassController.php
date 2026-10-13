<?php

namespace App\Http\Controllers;

use App\Models\VisitorPass;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VisitorPassController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        // Checking visitors in / out and cancelling count as "edit".
        return [
            new Middleware('permission:visitor-passes.view', only: ['index', 'show', 'print']),
            new Middleware('permission:visitor-passes.create', only: ['create', 'store']),
            new Middleware('permission:visitor-passes.edit', only: ['edit', 'update', 'status']),
            new Middleware('permission:visitor-passes.delete', only: ['destroy']),
        ];
    }

    private const PER_PAGE = [10, 25, 50, 100];

    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->per_page, self::PER_PAGE, true) ? (int) $request->per_page : self::PER_PAGE[0];

        $passes = VisitorPass::query()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('pass_number', 'like', "%$s%")
                ->orWhere('visitor_name', 'like', "%$s%")
                ->orWhere('phone', 'like', "%$s%")
                ->orWhere('address', 'like', "%$s%")
                ->orWhere('purpose', 'like', "%$s%")
                ->orWhere('person_to_meet', 'like', "%$s%")))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->date_from, fn ($q, $v) => $q->whereDate(DB::raw('COALESCE(check_in_at, expected_at)'), '>=', $v))
            ->when($request->date_to, fn ($q, $v) => $q->whereDate(DB::raw('COALESCE(check_in_at, expected_at)'), '<=', $v))
            ->latest('id')->paginate($perPage)->withQueryString();

        return view('visitor-passes.index', [
            'passes' => $passes,
            'inside' => VisitorPass::where('status', 'in')->count(),
            'perPage' => $perPage,
            'perPageOptions' => self::PER_PAGE,
            'expectedToday' => VisitorPass::where('status', 'expected')->whereDate('expected_at', today())->count(),
        ]);
    }

    public function create(): View
    {
        return view('visitor-passes.form', ['pass' => new VisitorPass(['check_in_at' => now(), 'expected_at' => now()->addHour()->startOfHour(), 'persons' => 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $preRegister = $request->input('mode') === 'pre_register';
        $pass = VisitorPass::create($this->validated($request, $preRegister) + [
            'pass_number' => VisitorPass::nextNumber(),
            'status' => $preRegister ? 'expected' : 'in',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('visitor-passes.print', $pass);
    }

    public function show(VisitorPass $visitorPass): View
    {
        return view('visitor-passes.show', ['pass' => $visitorPass, 'qr' => $this->qr($visitorPass)]);
    }

    public function edit(VisitorPass $visitorPass): View
    {
        return view('visitor-passes.form', ['pass' => $visitorPass]);
    }

    public function update(Request $request, VisitorPass $visitorPass): RedirectResponse
    {
        $visitorPass->update($this->validated($request, $visitorPass->check_in_at === null));

        return redirect()->route('visitor-passes.show', $visitorPass)->with('status', 'Visitor pass updated.');
    }

    public function destroy(VisitorPass $visitorPass): RedirectResponse
    {
        $visitorPass->delete();

        return redirect()->route('visitor-passes.index')->with('status', 'Visitor pass deleted.');
    }

    public function print(VisitorPass $visitorPass): View
    {
        return view('visitor-passes.print', ['pass' => $visitorPass, 'qr' => $this->qr($visitorPass)]);
    }

    /** Check the visitor in or out, cancel the pass, or reopen it. */
    public function status(Request $request, VisitorPass $visitorPass): RedirectResponse
    {
        $action = $request->validate(['action' => ['required', Rule::in(['checkin', 'checkout', 'cancel', 'reopen'])]])['action'];

        match ($action) {
            'checkin' => $visitorPass->update(['status' => 'in', 'check_in_at' => now(), 'check_out_at' => null]),
            'checkout' => $visitorPass->update(['status' => 'out', 'check_out_at' => now()]),
            'cancel' => $visitorPass->update(['status' => 'cancelled', 'check_out_at' => null]),
            'reopen' => $visitorPass->update($visitorPass->check_in_at
                ? ['status' => 'in', 'check_out_at' => null]
                : ['status' => 'expected']),
        };

        return back()->with('status', 'Visitor pass status updated.');
    }

    private function qr(VisitorPass $pass): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(160, 0), new SvgImageBackEnd()));

        return preg_replace('/^<\?xml[^>]*\?>\s*/', '', $writer->writeString($pass->pass_number));
    }

    /** Pre-registered visits need an expected arrival time; the rest need a check-in time. */
    private function validated(Request $request, bool $preRegister): array
    {
        $data = $request->validate([
            'visitor_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'purpose' => ['required', 'string', 'max:255'],
            'person_to_meet' => ['required', 'string', 'max:255'],
            'check_in_at' => [$preRegister ? 'nullable' : 'required', 'date'],
            'expected_at' => [$preRegister ? 'required' : 'nullable', 'date'],
        ]);

        // Only the time that applies to this visit is stored; the other input is ignored.
        unset($data[$preRegister ? 'check_in_at' : 'expected_at']);

        return $data;
    }
}
