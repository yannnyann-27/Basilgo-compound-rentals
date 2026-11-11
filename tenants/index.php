<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php'; require_role(['admin', 'staff'], $root);
$page_title = 'Tenants'; $active = 'tenants'; $staff = $_SESSION['role'] === 'staff';
$cols = [['name', 'Tenant'], ['room_id', 'Room'], ['phone', 'Contact'], ['water_plan', 'Water'], ['balance', 'Balance']];
$search = trim($_GET['search'] ?? ''); $filter = $_GET['filter'] ?? '';
$sort = in_array($_GET['sort'] ?? '', array_column($cols, 0), true) ? $_GET['sort'] : 'name'; $dir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
$all = $store->tenants();
$counts = ['' => count($all), 'owing' => count(array_filter($all, fn($t) => $t['balance'] > 0)), 'free' => count(array_filter($all, fn($t) => $t['water_plan'] === 'free')), 'metered' => count(array_filter($all, fn($t) => $t['water_plan'] === 'metered'))];
$rows = array_values(array_filter($all, function ($t) use ($search, $filter) {
    if ($filter === 'owing' && $t['balance'] <= 0) return false; if ($filter === 'free' && $t['water_plan'] !== 'free') return false; if ($filter === 'metered' && $t['water_plan'] !== 'metered') return false;
    return $search === '' || stripos($t['name'] . ' ' . $t['room_id'] . ' ' . $t['email'] . ' ' . $t['phone'], $search) !== false; }));
usort($rows, fn($a, $b) => ($dir === 'desc' ? -1 : 1) * ($a[$sort] <=> $b[$sort]));
[$rows, $total, $pages, $page] = paginate($rows, 6);
include '../includes/header.php'; ?>
<div class="page-head"><div><h1 class="page-title">Tenants</h1><p class="page-sub"><?= $total ?> record(s)<?= $staff ? ' · your edits are sent to the admin for approval' : '' ?></p></div>
  <a href="../payments/create.php" class="btn btn-primary"><?php icon('cash', 'w-4 h-4'); ?> Record cash payment</a></div>

<div class="mb-4 flex flex-wrap gap-2">
  <?php foreach (['' => 'All', 'owing' => 'With balance', 'free' => 'Free water', 'metered' => 'Metered'] as $k => $v): ?>
  <a href="<?= qs(['filter' => $k, 'page' => 1]) ?>" class="pill <?= $filter === $k ? 'on' : '' ?>"><?= $v ?> <small><?= $counts[$k] ?></small></a><?php endforeach; ?>
</div>

<form method="GET" class="card toolbar"><input type="hidden" name="filter" value="<?= e($filter) ?>">
  <div class="relative min-w-[12rem] flex-1"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><?php icon('search', 'w-5 h-5'); ?></span>
    <input name="search" value="<?= e($search) ?>" placeholder="Search name, room, phone…" class="input pl-11"><kbd class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded-md border border-slate-200 px-1.5 text-xs text-slate-400 sm:block">/</kbd></div>
  <button class="btn btn-primary">Search</button><a href="index.php" class="btn btn-secondary">Reset</a></form>

<div class="card table-wrap"><table class="data stack"><thead><tr><?php foreach ($cols as [$k, $l]): ?><th><a class="sort-link <?= $sort === $k ? 'on' : '' ?>" href="<?= qs(['sort' => $k, 'dir' => ($sort === $k && $dir === 'asc') ? 'desc' : 'asc', 'page' => 1]) ?>"><?= $l ?> <?php icon($sort === $k ? ($dir === 'asc' ? 'up' : 'down') : 'updown', 'w-3.5 h-3.5'); ?></a></th><?php endforeach; ?><th class="text-right">Actions</th></tr></thead><tbody class="rows-in">
<?php if (!$rows): ?><tr><td colspan="6"><div class="empty"><div class="empty-icon"><?php icon('inbox'); ?></div><b class="text-slate-700">No tenants found</b><p class="mt-1 text-sm">Try a different search or reset the filters.</p></div></td></tr><?php endif; ?>
<?php foreach ($rows as $t): ?><tr>
  <td data-label="Tenant"><div class="flex items-center gap-3 text-left"><span class="avatar"><?= e(initials($t['name'])) ?></span><div><a href="view.php?id=<?= $t['tenant_id'] ?>" class="font-semibold text-slate-900 hover:text-brand-700"><?= e($t['name']) ?></a><div class="text-xs text-slate-400"><?= e($t['email']) ?></div></div></div></td>
  <td data-label="Room"><span class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700"><?= e($t['room_id']) ?></span></td>
  <td data-label="Contact" class="text-slate-600"><?= e($t['phone']) ?></td>
  <td data-label="Water"><?= badge($t['water_plan'] === 'free' ? 'Free water' : 'Metered') ?></td>
  <td data-label="Balance"><?= $t['balance'] > 0 ? '<b class="tabular-nums text-rose-600">' . money($t['balance']) . '</b>' : badge('Settled') ?></td>
  <td><div class="row-actions"><?php if ($t['balance'] > 0): ?><a class="act" title="Record cash payment" href="../payments/create.php?tenant=<?= $t['tenant_id'] ?>"><?php icon('cash'); ?></a><?php endif; ?><a class="act" title="View" href="view.php?id=<?= $t['tenant_id'] ?>"><?php icon('eye'); ?></a><a class="act act-warn" title="Edit" href="edit.php?id=<?= $t['tenant_id'] ?>"><?php icon('pencil'); ?></a></div></td></tr><?php endforeach; ?></tbody></table></div>

<div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm"><span class="text-slate-500">Showing <?= count($rows) ?> of <?= $total ?></span><div class="pager">
  <?php if ($page > 1): ?><a class="page-btn" href="<?= qs(['page' => $page - 1]) ?>" aria-label="Previous"><?php icon('left', 'w-4 h-4'); ?></a><?php endif; for ($i = 1; $i <= $pages; $i++): ?><a class="page-btn <?= $i === $page ? 'current' : '' ?>" href="<?= qs(['page' => $i]) ?>"><?= $i ?></a><?php endfor; ?>
  <?php if ($page < $pages): ?><a class="page-btn" href="<?= qs(['page' => $page + 1]) ?>" aria-label="Next"><?php icon('right', 'w-4 h-4'); ?></a><?php endif; ?></div></div>
<?php include '../includes/footer.php'; ?>
