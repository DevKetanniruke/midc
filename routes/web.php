<?php

use App\Http\Controllers\GatePassController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VisitorPassController;
use App\Http\Controllers\LabelController;
use App\Models\Label;
use Illuminate\Support\Facades\Route;

// Guests are sent on to the login page by the auth middleware; signed-in users land on the dashboard.
Route::redirect('/', '/dashboard');

Route::get('/dashboard', function () {
    return view('dashboard', [
        'stats' => [
            'total' => Label::count(),
            'today' => Label::whereDate('created_at', today())->count(),
            'month' => Label::where('created_at', '>=', now()->startOfMonth())->count(),
            'units' => (int) Label::sum('quantity'),
        ],
        'recent' => Label::latest()->limit(6)->get(),
        'daily' => collect(range(6, 0))->mapWithKeys(fn ($i) => [today()->subDays($i)->toDateString() => 0])
            ->merge(Label::where('created_at', '>=', today()->subDays(6))
                ->selectRaw('DATE(created_at) as d, COUNT(*) as c')->groupBy('d')->pluck('c', 'd')),
        'top' => Label::selectRaw('part_number, SUM(quantity) as units')
            ->groupBy('part_number')->orderByDesc('units')->limit(5)->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('labels-print', [LabelController::class, 'printMany'])->name('labels.print.many');
    Route::get('labels/{label}/print', [LabelController::class, 'print'])->name('labels.print');
    Route::resource('labels', LabelController::class);

    Route::get('gate-passes/{gate_pass}/print', [GatePassController::class, 'print'])->name('gate-passes.print');
    Route::post('gate-passes/{gate_pass}/status', [GatePassController::class, 'status'])->name('gate-passes.status');
    Route::resource('gate-passes', GatePassController::class)->parameters(['gate-passes' => 'gate_pass']);

    Route::get('visitor-passes/{visitor_pass}/print', [VisitorPassController::class, 'print'])->name('visitor-passes.print');
    Route::post('visitor-passes/{visitor_pass}/status', [VisitorPassController::class, 'status'])->name('visitor-passes.status');
    Route::resource('visitor-passes', VisitorPassController::class)->parameters(['visitor-passes' => 'visitor_pass']);
});

require __DIR__.'/auth.php';
