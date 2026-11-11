<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php'; require_role(['admin', 'staff'], $root);
$page_title = 'Tenant Details'; $active = 'tenants';
$t = $store->tenant((int) ($_GET['id'] ?? 0));
if (!$t) { flash('error', 'Tenant not found.'); header('Location: index.php'); exit; }
$inv = $store->invoices($t['tenant_id']); $pay = $store->payments($t['tenant_id']); $pend = $store->pendingPaymentTotal($t['tenant_id']);
include '../includes/header.php'; ?>
<div class="mx-auto max-w-4xl">
<div class="page-head"><div class="flex items-center gap-4"><span class="avatar !h-14 !w-14 !text-lg"><?= e(initials($t['name'])) ?></span><div><h1 class="page-title"><?= e($t['name']) ?></h1><p class="page-sub">Room <?= e($t['room_id']) ?> · moved in <?= fdate($t['move_in']) ?></p></div></div>
  <div class="flex flex-wrap gap-2"><a href="index.php" class="btn btn-secondary"><?php icon('back', 'w-4 h-4'); ?> Back</a><a href="edit.php?id=<?= $t['tenant_id'] ?>" class="btn btn-secondary"><?php icon('pencil', 'w-4 h-4'); ?> Edit</a>
    <?php if ($t['balance'] > 0): ?><a href="../payments/create.php?tenant=<?= $t['tenant_id'] ?>" class="btn btn-primary"><?php icon('cash', 'w-4 h-4'); ?> Record cash payment</a><?php endif; ?></div></div>

<div class="mb-4 grid gap-4 sm:grid-cols-3">
  <div class="card card-pad <?= $t['balance'] > 0 ? 'border-rose-200 bg-rose-50/40' : 'border-emerald-200 bg-emerald-50/40' ?>"><p class="text-sm text-slate-500">Outstanding balance</p><p class="mt-1 text-3xl font-extrabold tabular-nums <?= $t['balance'] > 0 ? 'text-rose-600' : 'text-emerald-700' ?>"><?= money($t['balance']) ?></p>
    <?php if ($pend > 0): ?><p class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700"><?php icon('clock', 'w-4 h-4'); ?> <?= money($pend) ?> awaiting approval</p><?php endif; ?></div>
  <div class="card card-pad"><p class="text-sm text-slate-500">Monthly rent</p><p class="mt-1 text-3xl font-extrabold tabular-nums text-slate-900"><?= money($t['rent']) ?></p></div>
  <div class="card card-pad"><p class="text-sm text-slate-500">Water plan</p><p class="mt-2"><?= badge($t['water_plan'] === 'free' ? 'Free water' : 'Metered') ?></p></div>
</div>

<dl class="card card-pad mb-4 grid gap-5 sm:grid-cols-2">
  <div class="detail-item flex items-start gap-3"><span class="grid h-9 w-9 place-items-center rounded-lg tone-neutral"><?php icon('phone', 'w-4 h-4'); ?></span><div><dt>Phone</dt><dd><?= e($t['phone']) ?></dd></div></div>
  <div class="detail-item flex items-start gap-3"><span class="grid h-9 w-9 place-items-center rounded-lg tone-neutral"><?php icon('mail', 'w-4 h-4'); ?></span><div><dt>Email</dt><dd class="break-all"><?= e($t['email']) ?></dd></div></div>
</dl>

<div class="grid gap-4 sm:grid-cols-2"><div class="card card-pad"><h2 class="card-title"><?php icon('doc', 'w-5 h-5 text-brand-600'); ?> Invoices</h2>
  <?php foreach ($inv as $i): ?><div class="line"><a class="font-medium text-brand-700 hover:underline" href="../invoices/view.php?id=<?= $i['invoice_id'] ?>"><?= period_label($i['period']) ?></a><b><?= money($i['total']) ?></b></div><?php endforeach; if (!$inv) echo '<p class="text-sm text-slate-500">None yet.</p>'; ?></div>
<div class="card card-pad"><h2 class="card-title"><?php icon('cash', 'w-5 h-5 text-brand-600'); ?> Cash payments</h2>
  <?php foreach ($pay as $p): ?><div class="line"><span><?= fdate($p['paid_on']) ?></span><b class="text-emerald-700">− <?= money($p['amount']) ?></b></div><?php endforeach; if (!$pay) echo '<p class="text-sm text-slate-500">None yet.</p>'; ?></div></div></div>
<?php include '../includes/footer.php'; ?>
