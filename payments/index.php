<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php';
$page_title = 'Payments'; $active = 'payments'; $tid = $_SESSION['role'] === 'tenant' ? (int) $_SESSION['tenant_id'] : null; $admin = $_SESSION['role'] === 'admin';
$all = $store->payments($tid); $rows = $all; [$rows, $total, $pages, $page] = paginate($rows, 8);
$pend = array_values(array_filter($store->requests(), fn($r) => $r['type'] === 'payment' && $r['status'] === 'pending' && (!$tid || $r['tenant_id'] === $tid)));
$pendSum = array_sum(array_column($pend, 'amount'));
$month = date('Y-m'); $monthSum = array_sum(array_map(fn($p) => strpos($p['paid_on'], $month) === 0 ? $p['amount'] : 0, $all));
$grand = array_sum(array_column($all, 'amount'));
include '../includes/header.php'; ?>
<div class="page-head"><div><h1 class="page-title">Payments</h1><p class="page-sub"><?= $total ?> confirmed cash payment(s)</p></div><?php if (!$tid): ?><a href="create.php" class="btn btn-primary"><?php icon('plus', 'w-4 h-4'); ?> Record cash payment</a><?php endif; ?></div>

<div class="stagger mb-5 grid gap-3 sm:grid-cols-3 sm:gap-4">
  <div class="card card-pad flex items-center gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl tone-brand"><?php icon('cash', 'w-6 h-6'); ?></span><div><p class="text-sm text-slate-500"><?= $tid ? 'Total paid' : 'Total collected' ?></p><p class="text-2xl font-extrabold tabular-nums text-slate-900"><?= money($grand) ?></p></div></div>
  <div class="card card-pad flex items-center gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl tone-info"><?php icon('calendar', 'w-6 h-6'); ?></span><div><p class="text-sm text-slate-500">This month</p><p class="text-2xl font-extrabold tabular-nums text-slate-900"><?= money($monthSum) ?></p></div></div>
  <div class="card card-pad flex items-center gap-4"><span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl tone-warn"><?php icon('clock', 'w-6 h-6'); ?></span><div><p class="text-sm text-slate-500">Awaiting approval</p><p class="text-2xl font-extrabold tabular-nums text-slate-900"><?= money($pendSum) ?></p><p class="text-xs text-slate-400"><?= count($pend) ?> request(s)</p></div></div>
</div>

<?php if ($pend): ?>
<div class="mb-5 overflow-hidden rounded-2xl border border-amber-200 bg-amber-50/60"><div class="flex flex-wrap items-center justify-between gap-2 px-5 py-3"><h2 class="flex items-center gap-2 text-sm font-bold text-amber-800"><?php icon('clock', 'w-5 h-5'); ?> Waiting for admin approval &mdash; balances update once approved</h2>
  <?php if ($admin): ?><a href="../approvals/index.php" class="btn btn-sm border border-amber-300 bg-white text-amber-800 hover:bg-amber-100">Review now</a><?php endif; ?></div>
  <div class="divide-y divide-amber-100 bg-white/70">
  <?php foreach ($pend as $r): $pt = $store->tenant($r['tenant_id']); ?>
    <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 text-sm"><span><?php if (!$tid): ?><b class="text-slate-900"><?= e($pt['name']) ?></b> <span class="text-slate-400">· Rm <?= e($pt['room_id']) ?> ·</span> <?php endif; ?>received <?= fdate($r['received_on'] ?? $r['created']) ?><?php if (!$tid): ?> by <?= e($r['by']) ?><?php endif; ?></span><b class="tabular-nums text-amber-700"><?= money($r['amount']) ?></b></div>
  <?php endforeach; ?></div></div>
<?php endif; ?>

<div class="card table-wrap"><table class="data stack"><thead><tr><th>Date received</th><?php if (!$tid): ?><th>Tenant</th><?php endif; ?><th>Method</th><th>Note</th><th>Recorded by</th><th>Approved by</th><th class="td-num">Amount</th></tr></thead><tbody class="rows-in">
<?php if (!$rows): ?><tr><td colspan="7"><div class="empty"><div class="empty-icon"><?php icon('inbox'); ?></div><b class="text-slate-700">No payments yet</b></div></td></tr><?php endif; ?>
<?php foreach ($rows as $p): $t = $store->tenant($p['tenant_id']); ?><tr><td data-label="Date"><?= fdate($p['paid_on']) ?></td><?php if (!$tid): ?><td data-label="Tenant"><b class="text-slate-900"><?= e($t['name']) ?></b></td><?php endif; ?><td data-label="Method"><?= badge($p['method']) ?></td><td data-label="Note" class="text-slate-500"><?= e($p['note'] ?: '—') ?></td><td data-label="Recorded by"><?= e($p['recorded_by']) ?></td><td data-label="Approved by"><?= e($p['approved_by'] ?? $p['recorded_by']) ?></td><td data-label="Amount" class="td-num"><b class="text-emerald-700">− <?= money($p['amount']) ?></b></td></tr><?php endforeach; ?></tbody></table></div>
<div class="pager mt-4 justify-end"><?php for ($n = 1; $n <= $pages; $n++): ?><a class="page-btn <?= $n === $page ? 'current' : '' ?>" href="<?= qs(['page' => $n]) ?>"><?= $n ?></a><?php endfor; ?></div>
<?php include '../includes/footer.php'; ?>
