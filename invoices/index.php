<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php';
$page_title = 'Invoices'; $active = 'invoices'; $tid = $_SESSION['role'] === 'tenant' ? (int) $_SESSION['tenant_id'] : null;
$search = trim($_GET['search'] ?? '');
$rows = array_values(array_filter($store->invoices($tid), function ($i) use ($store, $search) { $t = $store->tenant($i['tenant_id']); return $search === '' || stripos($t['name'] . ' ' . $i['invoice_id'] . ' ' . period_label($i['period']), $search) !== false; }));
[$rows, $total, $pages, $page] = paginate($rows, 8);
include '../includes/header.php'; ?>
<div class="page-head"><div><h1 class="page-title"><?= $tid ? 'My invoices' : 'Invoices' ?></h1><p class="page-sub"><?= $total ?> invoice(s)</p></div><?php if (!$tid): ?><a href="../billing/index.php" class="btn btn-primary"><?php icon('plus', 'w-4 h-4'); ?> Create bill</a><?php endif; ?></div>
<form class="card toolbar"><div class="relative min-w-[12rem] flex-1"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"><?php icon('search', 'w-5 h-5'); ?></span><input name="search" value="<?= e($search) ?>" class="input pl-11" placeholder="Search invoice, tenant or month…"></div><button class="btn btn-primary">Search</button><a class="btn btn-secondary" href="index.php">Reset</a></form>
<div class="card table-wrap"><table class="data stack"><thead><tr><th>Invoice</th><?php if (!$tid): ?><th>Tenant</th><?php endif; ?><th>Period</th><th>Electric</th><th>Water</th><th class="td-num">Total</th><th></th></tr></thead><tbody class="rows-in">
<?php if (!$rows): ?><tr><td colspan="7"><div class="empty"><div class="empty-icon"><?php icon('inbox'); ?></div><b class="text-slate-700">No invoices found</b></div></td></tr><?php endif; ?>
<?php foreach ($rows as $i): $t = $store->tenant($i['tenant_id']); ?><tr><td data-label="Invoice"><a href="view.php?id=<?= $i['invoice_id'] ?>" class="font-bold text-slate-900 hover:text-brand-700">#<?= $i['invoice_id'] ?></a></td><?php if (!$tid): ?><td data-label="Tenant"><?= e($t['name']) ?></td><?php endif; ?><td data-label="Period"><?= period_label($i['period']) ?></td><td data-label="Electric" class="tabular-nums"><?= money($i['electric']) ?></td><td data-label="Water"><?= $i['water'] ? '<span class="tabular-nums">' . money($i['water']) . '</span>' : badge('Free water') ?></td><td data-label="Total" class="td-num"><b class="text-slate-900"><?= money($i['total']) ?></b></td>
<td><div class="row-actions"><a class="act" href="view.php?id=<?= $i['invoice_id'] ?>" title="Open"><?php icon('eye'); ?></a></div></td></tr><?php endforeach; ?></tbody></table></div>
<div class="pager mt-4 justify-end"><?php for ($n = 1; $n <= $pages; $n++): ?><a class="page-btn <?= $n === $page ? 'current' : '' ?>" href="<?= qs(['page' => $n]) ?>"><?= $n ?></a><?php endfor; ?></div>
<?php include '../includes/footer.php'; ?>
