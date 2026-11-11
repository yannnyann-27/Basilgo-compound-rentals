<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php';
$page_title = 'Invoice'; $active = 'invoices'; $i = $store->invoice((int) ($_GET['id'] ?? 0));
if (!$i || ($_SESSION['role'] === 'tenant' && $i['tenant_id'] !== (int) $_SESSION['tenant_id'])) { flash('error', 'Invoice not found.'); header('Location: index.php'); exit; }
$t = $store->tenant($i['tenant_id']); $r = $store->rates();
include '../includes/header.php'; ?>
<div class="mx-auto max-w-2xl"><div class="page-head no-print"><h1 class="page-title">Invoice #<?= $i['invoice_id'] ?></h1><div class="flex gap-2"><a href="index.php" class="btn btn-secondary"><?php icon('back', 'w-4 h-4'); ?> Back</a><button type="button" onclick="print()" class="btn btn-primary"><?php icon('printer', 'w-4 h-4'); ?> Print</button></div></div>
<div class="card overflow-hidden"><div class="h-2 bg-gradient-to-r from-brand-500 to-lime-300"></div><div class="card-pad">
<div class="mb-6 flex flex-wrap justify-between gap-4"><div><b class="text-lg text-slate-900"><?= APP_SHORT ?></b><p class="text-sm text-slate-500"><?= APP_TAGLINE ?> · Billing statement</p></div><div class="sm:text-right"><b class="text-slate-900"><?= period_label($i['period']) ?></b><p class="text-sm text-slate-500">Issued <?= fdate($i['created']) ?></p></div></div>
<div class="mb-5 rounded-xl bg-slate-50 p-4"><p class="font-bold text-slate-900"><?= e($t['name']) ?></p><p class="text-sm text-slate-500">Room <?= e($t['room_id']) ?> · <?= e($t['email']) ?></p></div>
<div class="line"><span>Monthly rent</span><b><?= money($i['rent']) ?></b></div><div class="line"><span>Electricity · <?= $i['kwh'] ?> kWh × <?= money($r['elec']) ?></span><b><?= money($i['electric']) ?></b></div>
<div class="line"><span>Water · <?= $t['water_plan'] === 'free' ? 'Free water account' : $i['m3'] . ' m³' ?></span><b><?= $i['water'] ? money($i['water']) : '₱0.00' ?></b></div><div class="line line-total"><span>Total</span><b><?= money($i['total']) ?></b></div>
<div class="mt-5 flex flex-wrap items-center justify-between gap-2 rounded-xl border px-4 py-3 text-sm <?= $t['balance'] > 0 ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' ?>"><span>Current outstanding balance</span><b class="text-base tabular-nums"><?= money($t['balance']) ?></b></div>
<p class="mt-4 text-xs text-slate-400">Payments are accepted in cash only. Please pay at the front desk; your balance is updated once the administrator confirms the payment.</p></div></div></div>
<?php include '../includes/footer.php'; ?>
