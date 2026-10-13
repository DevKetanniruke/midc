<?php

namespace App\Http\Controllers;

use App\Models\GatePass;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GatePassController extends Controller
{
    public function index(Request $request): View
    {
        $passes = GatePass::query()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('pass_number', 'like', "%$s%")
                ->orWhere('party_name', 'like', "%$s%")
                ->orWhere('vehicle_number', 'like', "%$s%")
                ->orWhere('purpose', 'like', "%$s%")))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->when($request->type, fn ($q, $v) => $q->where('type', $v))
            ->latest('id')->paginate(15)->withQueryString();

        return view('gate-passes.index', ['passes' => $passes]);
    }

    public function create(): View
    {
        return view('gate-passes.form', ['pass' => new GatePass(['type' => 'non_returnable', 'issued_at' => now()])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pass = GatePass::create($this->validated($request) + [
            'pass_number' => GatePass::nextNumber(),
            'status' => 'open',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('gate-passes.print', $pass);
    }

    public function show(GatePass $gatePass): View
    {
        return view('gate-passes.show', ['pass' => $gatePass, 'qr' => $this->qr($gatePass)]);
    }

    public function edit(GatePass $gatePass): View
    {
        return view('gate-passes.form', ['pass' => $gatePass]);
    }

    public function update(Request $request, GatePass $gatePass): RedirectResponse
    {
        $gatePass->update($this->validated($request));

        return redirect()->route('gate-passes.show', $gatePass)->with('status', 'Gate pass updated.');
    }

    public function destroy(GatePass $gatePass): RedirectResponse
    {
        $gatePass->delete();

        return redirect()->route('gate-passes.index')->with('status', 'Gate pass deleted.');
    }

    public function print(GatePass $gatePass): View
    {
        return view('gate-passes.print', ['pass' => $gatePass, 'qr' => $this->qr($gatePass)]);
    }

    /** Mark a returnable pass as returned, cancel it, or reopen it. */
    public function status(Request $request, GatePass $gatePass): RedirectResponse
    {
        $action = $request->validate(['action' => ['required', Rule::in(['return', 'cancel', 'reopen'])]])['action'];

        match ($action) {
            'return' => $gatePass->isReturnable() && $gatePass->update(['status' => 'returned', 'returned_at' => now()]),
            'cancel' => $gatePass->update(['status' => 'cancelled', 'returned_at' => null]),
            'reopen' => $gatePass->update(['status' => 'open', 'returned_at' => null]),
        };

        return back()->with('status', 'Gate pass status updated.');
    }

    private function qr(GatePass $pass): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle(160, 0), new SvgImageBackEnd()));

        return preg_replace('/^<\?xml[^>]*\?>\s*/', '', $writer->writeString($pass->pass_number));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(GatePass::TYPES))],
            'party_name' => ['required', 'string', 'max:255'],
            'purpose' => ['required', 'string', 'max:255'],
            'vehicle_number' => ['nullable', 'string', 'max:50'],
            'driver_name' => ['nullable', 'string', 'max:255'],
            'driver_phone' => ['nullable', 'string', 'max:30'],
            'issued_at' => ['required', 'date'],
            'expected_return_on' => ['nullable', 'date', 'required_if:type,returnable'],
            'remarks' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit' => ['nullable', 'string', 'max:20'],
        ]);

        $data['items'] = array_values($data['items']);
        if ($data['type'] !== 'returnable') {
            $data['expected_return_on'] = null;
        }

        return $data;
    }
}
