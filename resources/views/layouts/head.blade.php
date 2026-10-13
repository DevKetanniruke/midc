@php
    // Accent palettes (RGB triplets for shades 50-900), switched via <html data-accent="...">
    $accents = [
        'indigo'  => ['238 242 255', '224 231 255', '199 210 254', '165 180 252', '129 140 248', '99 102 241', '79 70 229', '67 56 202', '55 48 163', '49 46 129'],
        'emerald' => ['236 253 245', '209 250 229', '167 243 208', '110 231 183', '52 211 153', '16 185 129', '5 150 105', '4 120 87', '6 95 70', '6 78 59'],
        'rose'    => ['255 241 242', '255 228 230', '254 205 211', '253 164 175', '251 113 133', '244 63 94', '225 29 72', '190 18 60', '159 18 57', '136 19 55'],
        'amber'   => ['255 251 235', '254 243 199', '253 230 138', '252 211 77', '251 191 36', '245 158 11', '217 119 6', '180 83 9', '146 64 14', '120 53 15'],
        'sky'     => ['240 249 255', '224 242 254', '186 230 253', '125 211 252', '56 189 248', '14 165 233', '2 132 199', '3 105 161', '7 89 133', '12 74 110'],
        'violet'  => ['245 243 255', '237 233 254', '221 214 254', '196 181 253', '167 139 250', '139 92 246', '124 58 237', '109 40 217', '91 33 182', '76 29 149'],
    ];
    $shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];
@endphp
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

{{-- Apply saved mode/accent before first paint (light is the default) --}}
<script>
    (function () {
        try {
            if (localStorage.getItem('mode') === 'dark') document.documentElement.classList.add('dark');
            document.documentElement.dataset.accent = localStorage.getItem('accent') || 'indigo';
        } catch (e) {}
    })();
</script>

<script src="https://cdn.tailwindcss.com?plugins=forms"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: { extend: {
            fontFamily: { sans: ['Figtree', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
            colors: { accent: {
                @foreach($shades as $s) {{ $s }}: 'rgb(var(--a-{{ $s }}) / <alpha-value>)', @endforeach
            } },
        } },
    };
</script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
    // Replace native date / datetime-local inputs with Flatpickr (submits the same Y-m-d / Y-m-d H:i values).
    document.addEventListener('DOMContentLoaded', function () {
        if (!window.flatpickr) return;
        document.querySelectorAll('input[type="date"], input[type="datetime-local"]').forEach(function (el) {
            var time = el.type === 'datetime-local';
            flatpickr(el, {
                enableTime: time, time_24hr: true, allowInput: true,
                dateFormat: time ? 'Y-m-d H:i' : 'Y-m-d',
                altInput: true, altFormat: time ? 'd M Y, H:i' : 'd M Y',
            });
        });
    });
</script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
    [x-cloak] { display: none !important; }
    @foreach($accents as $name => $values)
    {{ $name === 'indigo' ? ':root, ' : '' }}[data-accent="{{ $name }}"] {
        @foreach($shades as $i => $s) --a-{{ $s }}: {{ $values[$i] }}; @endforeach
    }
    @endforeach

    /* Dark mode surface mapping (also covers the stock Breeze components) */
    .dark body { background: #0b1220; color: #e5e7eb; }
    .dark .bg-white { background-color: #111a2c; }
    .dark .bg-white\/90 { background-color: rgb(17 26 44 / 0.9); }
    .dark .bg-gray-50 { background-color: #0f1726; }
    .dark .bg-gray-100 { background-color: #0b1220; }
    .dark .hover\:bg-gray-50:hover, .dark .hover\:bg-gray-100:hover { background-color: #18233b; }
    .dark .border-gray-100, .dark .border-gray-200, .dark .border-gray-300 { border-color: #223050; }
    .dark .divide-y > :not([hidden]) ~ :not([hidden]) { border-color: #1b2742; }
    .dark .text-gray-900, .dark .text-gray-800, .dark .text-gray-700 { color: #e5e7eb; }
    .dark .text-gray-600 { color: #cbd5e1; }
    .dark .text-gray-500 { color: #94a3b8; }
    .dark input:not([type=checkbox]):not([type=radio]), .dark select, .dark textarea { background-color: #0b1220; border-color: #2a3654; color: #e5e7eb; }
    .dark input::placeholder { color: #64748b; }
    .dark .shadow-sm, .dark .shadow { box-shadow: none; }
    /* Sidebar links: keep the text readable (and lighter than the row) on hover */
    .dark aside nav a:not(.bg-accent-50):hover { background-color: rgb(255 255 255 / 0.08); color: #fff; }

    /* Flatpickr: match the accent colour and dark mode */
    .flatpickr-input.flatpickr-mobile, input.flatpickr-input + input { cursor: pointer; }
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: rgb(var(--a-600)); border-color: rgb(var(--a-600)); }
    .flatpickr-day.today { border-color: rgb(var(--a-500)); }
    .dark .flatpickr-calendar { background: #111a2c; border: 1px solid #223050; box-shadow: none; }
    .dark .flatpickr-months .flatpickr-month, .dark .flatpickr-current-month input.cur-year, .dark span.flatpickr-weekday,
    .dark .flatpickr-day, .dark .flatpickr-time input, .dark .flatpickr-time .flatpickr-am-pm { color: #e5e7eb; fill: #e5e7eb; background: transparent; }
    .dark .flatpickr-day.prevMonthDay, .dark .flatpickr-day.nextMonthDay { color: #64748b; }
    .dark .flatpickr-day:hover { background: #18233b; border-color: #18233b; }
    .dark .flatpickr-months .flatpickr-prev-month svg, .dark .flatpickr-months .flatpickr-next-month svg { fill: #e5e7eb; }
    .dark .flatpickr-calendar.arrowTop:before, .dark .flatpickr-calendar.arrowTop:after { border-bottom-color: #111a2c; }
</style>
