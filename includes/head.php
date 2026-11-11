<?php /* Shared <head> content for every page. Expects $root and $doc_title. */ ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#022c22">
<title><?= e($doc_title ?? APP_NAME) ?></title>
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Crect width='32' height='32' rx='8' fill='%23059669'/%3E%3Cpath d='M7 16l9-8 9 8v8a1 1 0 01-1 1h-5v-6h-6v6H8a1 1 0 01-1-1z' fill='white'/%3E%3C/svg%3E">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
/* Colour binding (one meaning per colour, used everywhere):
   brand/emerald = primary + paid/approved/available   amber = pending/maintenance/attention
   rose = money owed/rejected/danger                   sky = information/occupied/metered   slate = neutral text & surfaces */
tailwind.config = { theme: { extend: {
  fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
  colors: { brand: { 50:'#ecfdf5',100:'#d1fae5',200:'#a7f3d0',300:'#6ee7b7',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857',800:'#065f46',900:'#064e3b',950:'#022c22' } },
  boxShadow: { card: '0 1px 2px rgba(15,23,42,.04), 0 10px 28px -14px rgba(15,23,42,.14)', lift: '0 22px 44px -22px rgba(5,150,105,.5)' },
  animation: { 'fade-up': 'rise .55s cubic-bezier(.22,1,.36,1) both', 'fade-in': 'fade .4s ease-out both', 'pop': 'pop .45s cubic-bezier(.22,1,.36,1) both', 'slide-in': 'slide .5s cubic-bezier(.22,1,.36,1) both', 'float': 'drift 14s ease-in-out infinite alternate' }
} } };
</script>
<style type="text/tailwindcss">
@layer base {
  body { @apply bg-slate-50 font-sans text-slate-700 antialiased; }
  h1, h2, h3 { @apply tracking-tight text-slate-900; }
  ::selection { @apply bg-brand-200; }
}
@layer components {
  /* surfaces */
  .card { @apply rounded-2xl border border-slate-200/80 bg-white shadow-card; }
  .card-pad { @apply p-5 sm:p-6; }
  .card-title { @apply mb-4 flex items-center gap-2 text-base font-bold text-slate-900; }
  .page-head { @apply mb-6 flex flex-wrap items-end justify-between gap-4; }
  .page-title { @apply text-2xl font-extrabold sm:text-3xl; }
  .page-sub { @apply mt-1 text-sm text-slate-500; }

  /* buttons */
  .btn { @apply inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition duration-200 ease-out focus-visible:ring-4 active:scale-[.97] disabled:pointer-events-none disabled:opacity-60; }
  .btn-primary { @apply bg-brand-600 text-white shadow-sm shadow-brand-600/30 hover:-translate-y-px hover:bg-brand-700 hover:shadow-md focus-visible:ring-brand-500/30; }
  .btn-secondary { @apply border border-slate-200 bg-white text-slate-700 hover:border-brand-300 hover:bg-brand-50 hover:text-brand-700 focus-visible:ring-slate-300/40; }
  .btn-danger { @apply border border-rose-200 bg-rose-50 text-rose-700 hover:border-rose-300 hover:bg-rose-100 focus-visible:ring-rose-500/20; }
  .btn-sm { @apply px-3 py-1.5 text-xs; }
  .btn-lg { @apply px-5 py-3 text-base; }
  .act { @apply grid h-9 w-9 place-items-center rounded-lg text-slate-500 transition duration-200 hover:-translate-y-0.5 hover:bg-brand-50 hover:text-brand-700; }
  .act-warn { @apply hover:bg-amber-50 hover:text-amber-700; }

  /* forms */
  .input, .select { @apply block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition duration-200 placeholder:text-slate-400 focus:border-brand-500 focus:ring-4 focus:ring-brand-500/20 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400; }
  .input.is-invalid, .select.is-invalid { @apply border-rose-400 bg-rose-50/40; }
  .input.is-invalid:focus, .select.is-invalid:focus { @apply border-rose-500 ring-rose-500/20; }
  .field-label { @apply mb-1.5 block text-sm font-semibold text-slate-700; }
  .field-hint { @apply mt-1.5 text-xs text-slate-500; }
  .field-error { @apply mt-1.5 flex items-center gap-1.5 text-xs font-medium text-rose-600; }
  .form-actions { @apply mt-2 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end; }

  /* status badges + alerts (semantic colours) */
  .badge { @apply inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset; }
  .badge::before { content: ""; width: .375rem; height: .375rem; border-radius: 9999px; background: currentColor; }
  .badge-success { @apply bg-emerald-50 text-emerald-700 ring-emerald-600/20; }
  .badge-warning { @apply bg-amber-50 text-amber-700 ring-amber-600/20; }
  .badge-danger  { @apply bg-rose-50 text-rose-700 ring-rose-600/20; }
  .badge-info    { @apply bg-sky-50 text-sky-700 ring-sky-600/20; }
  .badge-neutral { @apply bg-slate-100 text-slate-600 ring-slate-500/20; }
  .alert { @apply mb-5 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm; }
  .alert > svg { @apply mt-0.5; }
  .alert-info  { @apply border-sky-200 bg-sky-50 text-sky-800; }
  .alert-warn  { @apply border-amber-200 bg-amber-50 text-amber-800; }
  .alert-error { @apply border-rose-200 bg-rose-50 text-rose-800; }
  .alert-success { @apply border-emerald-200 bg-emerald-50 text-emerald-800; }
  .tone-brand  { @apply bg-brand-100 text-brand-700; }
  .tone-info   { @apply bg-sky-100 text-sky-700; }
  .tone-warn   { @apply bg-amber-100 text-amber-700; }
  .tone-danger { @apply bg-rose-100 text-rose-700; }
  .tone-neutral{ @apply bg-slate-100 text-slate-600; }

  /* tables */
  .table-wrap { @apply overflow-x-auto; }
  table.data { @apply w-full text-sm; }
  .data th { @apply whitespace-nowrap border-b border-slate-200 bg-slate-50/80 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500; }
  .data td { @apply border-b border-slate-100 px-4 py-3.5 align-middle; }
  .data tbody tr { @apply transition-colors duration-150 hover:bg-brand-50/60; }
  .data tbody tr:last-child td { @apply border-b-0; }
  .td-num { @apply text-right tabular-nums; }
  .sort-link { @apply inline-flex items-center gap-1 transition-colors hover:text-brand-700; }
  .sort-link.on { @apply text-brand-700; }
  .row-actions { @apply flex items-center justify-end gap-1; }

  /* lists, filters, paging */
  .line { @apply flex items-center justify-between gap-4 border-b border-dashed border-slate-200 py-2.5 text-sm text-slate-600; }
  .line b { @apply font-semibold text-slate-900 tabular-nums; }
  .line-total { @apply border-0 pt-4 text-base font-semibold text-slate-900; }
  .line-total b { @apply text-xl text-brand-700; }
  .toolbar { @apply mb-4 flex flex-wrap items-center gap-3 p-3 sm:p-4; }
  .pill { @apply inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3.5 py-1.5 text-sm font-medium text-slate-600 transition duration-200 hover:border-brand-300 hover:text-brand-700; }
  .pill.on { @apply border-brand-600 bg-brand-600 text-white hover:text-white; }
  .pill small { @apply rounded-full bg-slate-100 px-1.5 text-[11px] font-bold text-slate-500; }
  .pill.on small { @apply bg-white/25 text-white; }
  .pager { @apply flex flex-wrap items-center gap-1.5; }
  .page-btn { @apply inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-lg border border-slate-200 bg-white px-2.5 text-sm text-slate-700 transition duration-200 hover:border-brand-400 hover:text-brand-700; }
  .page-btn.current { @apply border-brand-600 bg-brand-600 text-white hover:text-white; }
  .avatar { @apply grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-700; }
  .empty { @apply flex w-full flex-col items-center px-4 py-14 text-center text-slate-500; }
  .empty-icon { @apply mb-3 grid h-14 w-14 place-items-center rounded-full bg-slate-100 text-slate-400; }
  .stat { @apply relative block overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-card transition duration-300 ease-out hover:-translate-y-1 hover:border-brand-300 hover:shadow-lift; }
  .detail-item dt { @apply text-xs font-semibold uppercase tracking-wider text-slate-400; }
  .detail-item dd { @apply mt-1 font-semibold text-slate-900; }

  /* sidebar */
  .nav-link { @apply relative flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-brand-100/70 transition duration-200 hover:translate-x-0.5 hover:bg-white/10 hover:text-white; }
  .nav-link.active { @apply bg-white/10 text-white; }
  .nav-link.active::before { content: ""; position: absolute; left: -.75rem; top: 22%; bottom: 22%; width: 4px; border-radius: 0 4px 4px 0; background: #bef264; }
  .nav-title { @apply mb-1.5 mt-5 px-3.5 text-[11px] font-bold uppercase tracking-[.14em] text-brand-200/40; }
  .toast { @apply relative flex w-[22rem] max-w-[calc(100vw-2rem)] items-center gap-3 overflow-hidden rounded-xl border border-slate-200 border-l-4 bg-white px-4 py-3 text-sm font-medium text-slate-800 shadow-xl shadow-slate-900/10 animate-slide-in; }
  .toast-success { @apply border-l-emerald-500; }
  .toast-error { @apply border-l-rose-500; }
}
</style>
<link rel="stylesheet" href="<?= $root ?>assets/css/app.css">
