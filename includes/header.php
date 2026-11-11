<?php
// expects: $root, $page_title, $active (and a logged-in session)
$role = $_SESSION['role'];
$pending = $role === 'admin' ? $store->pendingCount() : $store->pendingCount($_SESSION['name']);
// [key, label, href, icon] grouped for the sidebar
$navs = [
  'admin'  => ['Overview' => [['dashboard','Dashboard','index.php','home']],
               'Manage'   => [['tenants','Tenants','tenants/index.php','users'],['rooms','Rooms','rooms/index.php','door']],
               'Money'    => [['billing','Create bill','billing/index.php','bolt'],['invoices','Invoices','invoices/index.php','doc'],['payments','Payments','payments/index.php','cash'],['approvals','Approvals','approvals/index.php','shield']],
               'Settings' => [['rates','Utility rates','rates/index.php','sliders']]],
  'staff'  => ['Overview' => [['dashboard','Dashboard','index.php','home']],
               'Manage'   => [['tenants','Tenants','tenants/index.php','users']],
               'Money'    => [['billing','Create bill','billing/index.php','bolt'],['invoices','Invoices','invoices/index.php','doc'],['payments','Payments','payments/index.php','cash'],['approvals','My requests','approvals/index.php','shield']]],
  'tenant' => ['My account' => [['dashboard','My balance','index.php','home'],['invoices','Invoices','invoices/index.php','doc'],['payments','Payments','payments/index.php','cash']]],
];
$doc_title = ($page_title ?? '') . ' | ' . APP_NAME;
$roleTone = ['admin' => 'bg-brand-400/20 text-brand-200', 'staff' => 'bg-sky-400/20 text-sky-200', 'tenant' => 'bg-amber-400/20 text-amber-200'][$role] ?? '';
?><!DOCTYPE html>
<html lang="en"><head>
<?php include __DIR__ . '/head.php'; ?>
</head><body class="min-h-screen">
<div id="sidebar-overlay" class="fixed inset-0 z-40 bg-slate-900/50 opacity-0 pointer-events-none backdrop-blur-sm transition-opacity duration-300 lg:hidden"></div>

<aside id="sidebar" class="sidebar fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col text-brand-100 shadow-2xl transition-transform duration-300 ease-out lg:translate-x-0 lg:shadow-none" aria-label="Main navigation">
  <a href="<?= $root ?>index.php" class="flex items-center gap-3 px-5 pb-4 pt-6">
    <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-brand-400 to-lime-300 text-brand-950 shadow-lg shadow-brand-500/30"><?php icon('home', 'w-6 h-6'); ?></span>
    <span class="min-w-0"><span class="block truncate text-base font-extrabold leading-tight text-white"><?= APP_SHORT ?></span><span class="block truncate text-xs text-brand-200/70"><?= APP_TAGLINE ?></span></span>
  </a>
  <nav class="nav-scroll flex-1 overflow-y-auto px-3 pb-4">
    <?php foreach ($navs[$role] as $group => $items): ?>
      <p class="nav-title"><?= e($group) ?></p>
      <?php foreach ($items as [$k, $label, $href, $ico]): ?>
        <a href="<?= $root . $href ?>" class="nav-link <?= ($active ?? '') === $k ? 'active' : '' ?>" <?= ($active ?? '') === $k ? 'aria-current="page"' : '' ?>>
          <?php icon($ico); ?><span><?= e($label) ?></span>
          <?php if ($k === 'approvals' && $pending): ?><span class="pulse-ring ml-auto grid h-5 min-w-[1.25rem] place-items-center rounded-full bg-amber-400 px-1.5 text-[11px] font-bold text-amber-950"><?= $pending ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    <?php endforeach; ?>
  </nav>
  <div class="p-3">
    <div class="flex items-center gap-3 rounded-2xl bg-white/[.07] p-3 ring-1 ring-white/10">
      <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-brand-400 text-sm font-extrabold text-brand-950"><?= e(initials($_SESSION['name'])) ?></span>
      <div class="min-w-0 flex-1"><div class="truncate text-sm font-semibold text-white"><?= e($_SESSION['name']) ?></div><span class="mt-0.5 inline-block rounded-full px-2 py-px text-[11px] font-semibold <?= $roleTone ?>"><?= e(ucfirst($role)) ?></span></div>
      <a href="<?= $root ?>auth/logout.php" class="rounded-lg p-2 text-brand-100/60 transition hover:bg-white/10 hover:text-white" title="Sign out" aria-label="Sign out"><?php icon('logout'); ?></a>
    </div>
  </div>
</aside>

<div class="flex min-h-screen flex-col lg:pl-72">
  <header class="topbar sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-slate-200/70 bg-slate-50/85 px-4 backdrop-blur-md sm:px-6 lg:px-8">
    <button type="button" id="menu-btn" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-brand-300 hover:text-brand-700 lg:hidden" aria-label="Open menu"><?php icon('menu'); ?></button>
    <div class="min-w-0 text-sm"><span class="hidden text-slate-400 sm:inline"><?= APP_SHORT ?> <span class="mx-1">/</span></span><strong class="truncate font-bold text-slate-900"><?= e($page_title ?? '') ?></strong></div>
    <div class="ml-auto flex items-center gap-2">
      <span class="hidden items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-500 md:inline-flex"><?php icon('calendar', 'w-4 h-4'); ?><span data-today><?= date('l, F j') ?></span></span>
      <?php if ($role !== 'tenant'): ?>
      <a href="<?= $root ?>approvals/index.php" class="relative grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:border-brand-300 hover:text-brand-700" title="<?= $role === 'admin' ? 'Pending approvals' : 'My pending requests' ?>" aria-label="Pending requests"><?php icon('bell'); ?>
        <?php if ($pending): ?><span class="absolute -right-1 -top-1 grid h-5 min-w-[1.25rem] place-items-center rounded-full bg-amber-400 px-1 text-[11px] font-bold text-amber-950 ring-2 ring-slate-50"><?= $pending ?></span><?php endif; ?></a>
      <?php endif; ?>
    </div>
  </header>

<?php if (!empty($_SESSION['flash'])): $ok = $_SESSION['flash']['type'] === 'success'; ?>
  <div class="fixed right-4 top-20 z-[80]" role="status" aria-live="polite">
    <div class="toast <?= $ok ? 'toast-success text-emerald-700' : 'toast-error text-rose-700' ?>"><?php icon($ok ? 'check' : 'alert'); ?><span class="flex-1 text-slate-800"><?= e($_SESSION['flash']['msg']) ?></span><button type="button" class="toast-close text-slate-400 transition hover:text-slate-700" aria-label="Dismiss"><?php icon('x', 'w-4 h-4'); ?></button><span class="toast-bar"></span></div>
  </div>
<?php unset($_SESSION['flash']); endif; ?>

<main class="mx-auto w-full max-w-7xl flex-1 animate-fade-up px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
