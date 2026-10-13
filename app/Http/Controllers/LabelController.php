<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Services\LabelBarcodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabelController extends Controller
{
    private const PER_PAGE = [10, 25, 50, 100];

    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->per_page, self::PER_PAGE, true) ? (int) $request->per_page : self::PER_PAGE[0];

        $labels = Label::query()
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('part_number', 'like', "%$s%")
                ->orWhere('vendor_batch_lot', 'like', "%$s%")
                ->orWhere('delivery_note', 'like', "%$s%")
                ->orWhere('part_description', 'like', "%$s%")))
            ->when($request->part_number, fn ($q, $v) => $q->where('part_number', 'like', "%$v%"))
            ->when($request->supplier_number, fn ($q, $v) => $q->where('supplier_number', 'like', "%$v%"))
            ->when($request->vendor_batch_lot, fn ($q, $v) => $q->where('vendor_batch_lot', 'like', "%$v%"))
            ->when($request->qty_min, fn ($q, $v) => $q->where('quantity', '>=', (int) $v))
            ->when($request->qty_max, fn ($q, $v) => $q->where('quantity', '<=', (int) $v))
            ->when($request->date_from, fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to, fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest('id')->paginate($perPage)->withQueryString();

        return view('labels.index', ['labels' => $labels, 'perPage' => $perPage, 'perPageOptions' => self::PER_PAGE]);
    }

    public function create(Request $request): View
    {
        // "Same": prefill from a previous label, bumping the trailing number of the batch lot.
        $label = new Label();
        if ($source = Label::find($request->same)) {
            $label->fill($source->only(array_keys(Label::FIELDS)));
            $label->vendor_batch_lot = preg_replace_callback(
                '/(\d+)(\D*)$/',
                fn ($m) => str_pad((int) $m[1] + 1, strlen($m[1]), '0', STR_PAD_LEFT).$m[2],
                $source->vendor_batch_lot
            );
        }

        return view('labels.form', compact('label'));
    }

    public function store(Request $request): RedirectResponse
    {
        $label = Label::create($this->validated($request) + ['created_by' => $request->user()->id]);

        return redirect()->route('labels.print', $label);
    }

    public function show(Label $label, LabelBarcodeService $service): View
    {
        return view('labels.show', [
            'label' => $label,
            'barcodes' => $service->barcodes($label),
            'qr' => $service->qr($label),
        ]);
    }

    public function edit(Label $label): View
    {
        return view('labels.form', compact('label'));
    }

    public function update(Request $request, Label $label): RedirectResponse
    {
        $label->update($this->validated($request));

        return redirect()->route('labels.print', $label);
    }

    public function destroy(Label $label): RedirectResponse
    {
        $label->delete();

        return redirect()->route('labels.index')->with('status', 'Label deleted.');
    }

    /** Print one label; ?copies=2 repeats it so two fit on one A4 sheet. */
    public function print(Request $request, Label $label, LabelBarcodeService $service): View
    {
        $copies = $request->integer('copies', 1) === 2 ? 2 : 1;

        return view('labels.print', ['items' => array_fill(0, $copies, $this->printItem($label, $service))]);
    }

    /** Print the selected labels, two per A4 sheet. */
    public function printMany(Request $request, LabelBarcodeService $service): View|RedirectResponse
    {
        $ids = array_filter((array) $request->query('ids'), 'is_numeric');
        $labels = Label::whereIn('id', $ids)->orderBy('id')->get();

        if ($labels->isEmpty()) {
            return redirect()->route('labels.index')->with('status', 'Select at least one label to print.');
        }

        return view('labels.print', ['items' => $labels->map(fn ($l) => $this->printItem($l, $service))->all()]);
    }

    private function printItem(Label $label, LabelBarcodeService $service): array
    {
        return ['label' => $label, 'barcodes' => $service->barcodes($label), 'qr' => $service->qr($label)];
    }

    private function validated(Request $request): array
    {
        $rules = [];
        foreach (Label::FIELDS as $field => [, $required]) {
            $rules[$field] = [$required ? 'required' : 'nullable', $field === 'quantity' ? 'integer' : 'string', $field === 'quantity' ? 'min:1' : 'max:255'];
        }

        return $request->validate($rules);
    }
}
