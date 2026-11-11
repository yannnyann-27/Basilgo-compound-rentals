<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php'; require_role(['admin'], $root);
$page_title = 'Rooms'; $active = 'rooms'; $rooms = $store->rooms();
$f = $_GET['status'] ?? ''; $statuses = ['Available', 'Occupied', 'Maintenance'];
$counts = array_fill_keys($statuses, 0); foreach ($rooms as $r) $counts[$store->roomStatus($r)]++;
$list = array_values(array_filter($rooms, fn($r) => $f === '' || $store->roomStatus($r) === $f));
$ring = ['Available' => 'border-emerald-200 hover:border-emerald-400', 'Occupied' => 'border-sky-200 hover:border-sky-400', 'Maintenance' => 'border-amber-200 hover:border-amber-400'];
$tones = ['Available' => 'tone-brand', 'Occupied' => 'tone-info', 'Maintenance' => 'tone-warn'];
include '../includes/header.php'; ?>
<div class="page-head"><div><h1 class="page-title">Rooms</h1><p class="page-sub"><?= count($rooms) ?> rooms in the compound</p></div></div>
<div class="mb-5 flex flex-wrap gap-2"><a href="index.php" class="pill <?= $f === '' ? 'on' : '' ?>">All <small><?= count($rooms) ?></small></a>
  <?php foreach ($statuses as $s): ?><a href="?status=<?= $s ?>" class="pill <?= $f === $s ? 'on' : '' ?>"><?= $s ?> <small><?= $counts[$s] ?></small></a><?php endforeach; ?></div>
<div class="stagger grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
<?php foreach ($list as $r): $s = $store->roomStatus($r); $t = $store->tenantByRoom($r['room_id']); ?>
  <div class="card card-pad border-2 transition duration-300 hover:-translate-y-1 hover:shadow-lift <?= $ring[$s] ?>">
    <div class="mb-4 flex items-start justify-between gap-2"><span class="grid h-12 w-12 place-items-center rounded-xl text-lg font-extrabold <?= $tones[$s] ?>"><?= e($r['room_id']) ?></span><?= badge($s) ?></div>
    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Monthly rent</p><p class="text-2xl font-extrabold tabular-nums text-slate-900"><?= money($r['rent']) ?></p>
    <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-3 text-sm"><?php if ($t): ?><span class="avatar !h-8 !w-8 !text-xs"><?= e(initials($t['name'])) ?></span><a href="../tenants/view.php?id=<?= $t['tenant_id'] ?>" class="truncate font-medium text-slate-700 hover:text-brand-700"><?= e($t['name']) ?></a><?php else: ?><span class="text-slate-400">No tenant</span><?php endif; ?></div>
  </div>
<?php endforeach; ?></div>
<?php if (!$list): ?><div class="card empty"><div class="empty-icon"><?php icon('inbox'); ?></div><b class="text-slate-700">No rooms match</b></div><?php endif; ?>
<?php include '../includes/footer.php'; ?>
