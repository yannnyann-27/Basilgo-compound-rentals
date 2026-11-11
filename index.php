<?php
require_once __DIR__ . '/includes/auth_check.php';
$root = ''; $page_title = 'Dashboard'; $active = 'dashboard';
$role = $_SESSION['role'];
include 'includes/header.php';

if ($role === 'tenant'):
    $t = $store->tenant((int) $_SESSION['tenant_id']); $inv = $store->invoices($t['tenant_id'])[0] ?? null;
    $pp = array_values(array_filter($store->requests(), fn($r) => $r['type'] === 'payment' && $r['tenant_id'] === $t['tenant_id'] && $r['status'] === 'pending'));
    $myPay = $store->payments($t['tenant_id']); ?>
<div class="page-head"><div><h1 class="page-title">Hello, <?= e(explode(' ', $t['name'])[0]) ?> 👋</h1>
  <p class="page-sub">Room <?= e($t['room_id']) ?> · <?= $t['water_plan'] === 'free' ? 'Water is included in your plan' : 'Metered water' ?></p></div></div>

<div class="mb-4 grid gap-4 lg:grid-cols-3">
  <!-- balance hero -->
  <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-brand-600 via-brand-800 to-brand-950 p-6 text-white shadow-lift sm:p-8 lg:col-span-2">
    <div class="orb -right-16 -top-16 h-64 w-64 bg-lime-300/30"></div>
    <div class="relative">
      <p class="text-sm font-medium text-brand-100/80">Outstanding balance</p>
      <div class="mt-1 text-4xl font-extrabold tabular-nums sm:text-5xl" data-count="<?= $t['balance'] ?>" data-money="1"><?= money($t['balance']) ?></div>
      <p class="mt-3 max-w-md text-sm text-brand-100/80"><?= $t['balance'] > 0 ? 'Pay in cash at the front desk. Your balance goes down once the admin confirms the payment staff record.' : 'You are all settled. Thank you!' ?></p>
      <span class="mt-5 inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold ring-1 ring-white/20"><?php icon('cash', 'w-4 h-4'); ?> Cash payments only</span>
    </div>
  </div>
  <!-- rent + pending -->
  <div class="card card-pad flex flex-col">
    <p class="text-sm text-slate-500">Monthly rent</p><div class="mt-1 text-3xl font-extrabold tabular-nums text-slate-900"><?= money($t['rent']) ?></div>
    <p class="mt-1 text-xs text-slate-400">Moved in <?= fdate($t['move_in']) ?></p>
    <?php foreach ($pp as $p): ?><div class="alert alert-warn mb-0 mt-auto animate-pop"><?php icon('clock'); ?><span>Cash payment of <b><?= money($p['amount']) ?></b> is waiting for admin approval.</span></div><?php endforeach; ?>
  </div>
</div>

<div class="grid gap-4 lg:grid-cols-2">
  <div class="card card-pad"><h2 class="card-title"><?php icon('doc', 'w-5 h-5 text-brand-600'); ?> Latest invoice</h2>
    <?php if ($inv): ?>
    <div class="line"><span>Period</span><b><?= period_label($inv['period']) ?></b></div><div class="line"><span>Rent</span><b><?= money($inv['rent']) ?></b></div>
    <div class="line"><span>Electricity (<?= $inv['kwh'] ?> kWh)</span><b><?= money($inv['electric']) ?></b></div><div class="line"><span>Water</span><b><?= $inv['water'] ? money($inv['water']) : 'Free' ?></b></div>
    <div class="line line-total"><span>Total</span><b><?= money($inv['total']) ?></b></div>
    <a class="btn btn-secondary btn-sm mt-3" href="invoices/view.php?id=<?= $inv['invoice_id'] ?>">Open invoice <?php icon('arrow', 'w-4 h-4'); ?></a>
    <?php else: ?><p class="text-sm text-slate-500">No invoices yet.</p><?php endif; ?></div>
  <div class="card card-pad"><h2 class="card-title"><?php icon('cash', 'w-5 h-5 text-brand-600'); ?> Recent payments</h2>
    <?php foreach (array_slice($myPay, 0, 5) as $p): ?>
    <div class="line"><span><?= fdate($p['paid_on']) ?> <span class="ml-1"><?= badge('Cash') ?></span></span><b class="text-emerald-700">− <?= money($p['amount']) ?></b></div><?php endforeach; ?>
    <?php if (!$myPay): ?><p class="text-sm text-slate-500">No payments yet.</p><?php endif; ?>
    <?php if ($myPay): ?><a class="btn btn-secondary btn-sm mt-3" href="payments/index.php">All payments <?php icon('arrow', 'w-4 h-4'); ?></a><?php endif; ?></div>
</div>

<?php else:
    $tenants = $store->tenants(); $rooms = $store->rooms();
    $avail = count(array_filter($rooms, fn($r) => $store->roomStatus($r) === 'Available')); $occ = count($tenants);
    $owing = array_values(array_filter($tenants, fn($t) => $t['balance'] > 0)); usort($owing, fn($a, $b) => $b['balance'] <=> $a['balance']);
    $total = array_sum(array_column($tenants, 'balance')); $pc = (int) round($occ / max(1, count($rooms)) * 100);
    $admin = $role === 'admin'; $pend = $admin ? $store->pendingCount() : $store->pendingCount($_SESSION['name']);
    $queue = array_slice(array_values(array_filter($store->requests($admin ? null : $_SESSION['name']), fn($r) => $r['status'] === 'pending')), 0, 4);
    $max = max(1, $owing ? $owing[0]['balance'] : 1);
    // [label, number, link, icon, tone, is money]
    $cards = [['Available rooms', $avail, $admin ? 'rooms/index.php' : 'tenants/index.php', 'door', 'tone-brand', 0], ['Occupied rooms', $occ, 'tenants/index.php', 'users', 'tone-info', 0],
              ['Outstanding balance', $total, 'tenants/index.php?filter=owing', 'cash', 'tone-danger', 1], [$admin ? 'Pending approvals' : 'My pending requests', $pend, 'approvals/index.php', 'shield', 'tone-warn', 0]];
    ?>
<div class="page-head"><div><h1 class="page-title">Dashboard</h1><p class="page-sub">Welcome back, <?= e($_SESSION['name']) ?>. Here is how the compound is doing.</p></div>
  <div class="flex flex-wrap gap-2">
    <?php if ($admin && $pend): ?><a class="btn btn-secondary" href="approvals/index.php"><?php icon('shield', 'w-4 h-4'); ?> Review <?= $pend ?> pending</a><?php endif; ?>
    <a class="btn btn-primary" href="payments/create.php"><?php icon('cash', 'w-4 h-4'); ?> Record cash payment</a>
    <a class="btn btn-secondary" href="billing/index.php"><?php icon('bolt', 'w-4 h-4'); ?> Create bill</a></div></div>

<div class="stagger mb-4 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
  <?php foreach ($cards as [$l, $n, $href, $ic, $tone, $m]): ?>
  <a href="<?= $href ?>" class="stat group"><div class="mb-4 grid h-11 w-11 place-items-center rounded-xl <?= $tone ?>"><?php icon($ic, 'w-6 h-6'); ?></div>
    <span class="absolute right-4 top-4 text-slate-300 opacity-0 transition duration-300 group-hover:translate-x-0.5 group-hover:opacity-100"><?php icon('arrow'); ?></span>
    <p class="text-2xl font-extrabold leading-tight tabular-nums text-slate-900 sm:text-3xl" data-count="<?= $n ?>" data-money="<?= $m ?>"><?= $m ? money($n) : $n ?></p><p class="mt-1 text-sm text-slate-500"><?= e($l) ?></p></a>
  <?php endforeach; ?>
</div>

<div class="mb-4 grid gap-4 lg:grid-cols-3">
  <div class="card card-pad"><h2 class="card-title">Occupancy</h2>
    <div class="gauge relative mx-auto h-44 w-44"><svg viewBox="0 0 170 170" class="h-full w-full"><defs><linearGradient id="rg"><stop offset="0" stop-color="#059669"/><stop offset="1" stop-color="#a3e635"/></linearGradient></defs>
      <circle cx="85" cy="85" r="73" stroke="#e2e8f0"/><circle class="ring-arc" cx="85" cy="85" r="73" data-dash="<?= round(459 * $pc / 100) ?>" style="--dash:<?= round(459 * $pc / 100) ?>"/></svg>
      <b class="absolute inset-0 grid place-items-center text-3xl font-extrabold text-slate-900"><?= $pc ?>%</b></div>
    <p class="mt-4 text-center text-sm text-slate-500"><?= $occ ?> of <?= count($rooms) ?> rooms occupied</p></div>

  <div class="card card-pad lg:col-span-2"><div class="mb-4 flex items-center justify-between gap-3"><h2 class="card-title !mb-0">Tenants with outstanding balance</h2><?php if ($owing): ?><a href="tenants/index.php?filter=owing" class="text-sm font-semibold text-brand-700 hover:underline">View all</a><?php endif; ?></div>
    <div class="grid gap-4">
    <?php foreach (array_slice($owing, 0, 5) as $t): ?>
      <div><div class="mb-1.5 flex items-center justify-between gap-3 text-sm"><span class="min-w-0 truncate"><b class="text-slate-900"><?= e($t['name']) ?></b> <span class="text-slate-400">· Rm <?= e($t['room_id']) ?></span></span>
        <span class="flex shrink-0 items-center gap-2"><b class="tabular-nums text-rose-600"><?= money($t['balance']) ?></b><a href="payments/create.php?tenant=<?= $t['tenant_id'] ?>" class="act !h-7 !w-7" title="Record cash payment"><?php icon('cash', 'w-4 h-4'); ?></a></span></div>
        <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="fill h-full rounded-full bg-gradient-to-r from-rose-400 to-amber-400" data-w="<?= round($t['balance'] / $max * 100) ?>" style="--w:<?= round($t['balance'] / $max * 100) ?>%"></div></div></div>
    <?php endforeach; ?></div>
    <?php if (!$owing): ?><div class="empty !py-8"><div class="empty-icon tone-brand"><?php icon('check'); ?></div><b class="text-slate-700">Everyone is settled 🎉</b></div><?php endif; ?></div>
</div>

<div class="grid gap-4 lg:grid-cols-3">
  <div class="card card-pad lg:col-span-2"><div class="mb-4 flex flex-wrap items-center justify-between gap-2"><h2 class="card-title !mb-0">Room map</h2>
    <div class="flex flex-wrap gap-2 text-xs"><?= badge('Available') ?><?= badge('Occupied') ?><?= badge('Maintenance') ?></div></div>
    <div class="grid grid-cols-3 gap-2.5 sm:grid-cols-4 md:grid-cols-6">
    <?php $tile = ['Available' => 'border-emerald-300 bg-emerald-50 text-emerald-800', 'Occupied' => 'border-sky-200 bg-sky-50 text-sky-800', 'Maintenance' => 'border-amber-300 bg-amber-50 text-amber-800'];
    foreach ($rooms as $i => $r): $s = $store->roomStatus($r); ?>
      <div class="pop-in rounded-xl border p-3 text-center text-lg font-bold transition duration-300 hover:-translate-y-1 hover:scale-105 hover:shadow-md <?= $tile[$s] ?>" style="--i:<?= $i ?>"><?= e($r['room_id']) ?><small class="block text-[11px] font-medium opacity-75"><?= $s ?></small></div>
    <?php endforeach; ?></div></div>

  <div class="card card-pad"><h2 class="card-title"><?php icon('shield', 'w-5 h-5 text-amber-600'); ?> <?= $admin ? 'Waiting for your approval' : 'My pending requests' ?></h2>
    <?php foreach ($queue as $r): $qt = $store->tenant($r['tenant_id']); ?>
      <a href="approvals/index.php" class="mb-2 flex items-center gap-3 rounded-xl border border-slate-100 p-3 transition duration-200 hover:border-amber-200 hover:bg-amber-50/50">
        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg <?= $r['type'] === 'payment' ? 'tone-brand' : 'tone-warn' ?>"><?php icon($r['type'] === 'payment' ? 'cash' : 'pencil', 'w-4 h-4'); ?></span>
        <span class="min-w-0 flex-1 text-sm"><b class="block truncate text-slate-900"><?= e($qt['name']) ?></b><span class="text-slate-500"><?= $r['type'] === 'payment' ? 'Cash ' . money($r['amount']) : 'Info edit' ?></span></span><?= badge('pending') ?></a>
    <?php endforeach; ?>
    <?php if (!$queue): ?><div class="empty !py-8"><div class="empty-icon tone-brand"><?php icon('check'); ?></div><b class="text-slate-700">All caught up</b></div><?php endif; ?>
  </div>
</div>
<?php endif; include 'includes/footer.php'; ?>
