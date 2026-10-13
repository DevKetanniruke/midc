{{-- Bootstrap 5 pagination look (the app itself is Tailwind, so only the pagination styles are reproduced). Wrap $paginator->links('pagination::bootstrap-5') in .bs-pagination. --}}
<style>
    .bs-pagination nav { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: .5rem; }
    .bs-pagination .d-flex { display: flex; }
    .bs-pagination .justify-content-between { justify-content: space-between; }
    .bs-pagination .flex-fill { flex: 1 1 auto; }
    .bs-pagination .d-none { display: none; }
    .bs-pagination .small { font-size: .875em; }
    .bs-pagination .text-muted { color: #6c757d; }
    .bs-pagination p { margin: 0; }
    @media (min-width: 640px) {
        .bs-pagination .d-sm-none { display: none; }
        .bs-pagination .d-sm-flex { display: flex; }
        .bs-pagination .flex-sm-fill { flex: 1 1 auto; }
        .bs-pagination .align-items-sm-center { align-items: center; }
        .bs-pagination .justify-content-sm-between { justify-content: space-between; }
    }
    .bs-pagination .pagination { display: flex; padding-left: 0; margin: 0; list-style: none; }
    .bs-pagination .page-link { position: relative; display: block; padding: .375rem .75rem; margin-left: -1px; color: #0d6efd; background: #fff; border: 1px solid #dee2e6; text-decoration: none; font-size: 1rem; line-height: 1.5; transition: color .15s, background-color .15s, border-color .15s; }
    .bs-pagination .page-link:hover { color: #0a58ca; background: #e9ecef; border-color: #dee2e6; }
    .bs-pagination .page-item:first-child .page-link { margin-left: 0; border-radius: .375rem 0 0 .375rem; }
    .bs-pagination .page-item:last-child .page-link { border-radius: 0 .375rem .375rem 0; }
    .bs-pagination .page-item.active .page-link { z-index: 3; color: #fff; background: #0d6efd; border-color: #0d6efd; }
    .bs-pagination .page-item.disabled .page-link { color: #6c757d; pointer-events: none; background: #fff; border-color: #dee2e6; }
    .bs-pagination svg { width: 1.25rem; height: 1.25rem; }
    .dark .bs-pagination .page-link { background: #111a2c; border-color: #223050; color: #93c5fd; }
    .dark .bs-pagination .page-link:hover { background: #18233b; }
    .dark .bs-pagination .page-item.active .page-link { background: #0d6efd; border-color: #0d6efd; color: #fff; }
    .dark .bs-pagination .page-item.disabled .page-link { color: #64748b; }
    .dark .bs-pagination .text-muted { color: #94a3b8; }
</style>
