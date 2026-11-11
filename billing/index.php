<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php'; require_role(['admin', 'staff'], $root);
$page_title = 'Billing'; $active = 'billing'; $rates = $store->rates(); $errors = [];
$tenants = $store->tenants(); $map = []; foreach ($tenants as $t) $map[$t['tenant_id']] = $t;
$d = ['tenant_id' => $_GET['tenant'] ?? $tenants[0]['tenant_id'], 'period' => date('Y-m'), 'kwh' => '', 'm3' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); foreach ($d as $k => $_) $d[$k] = trim($_POST[$k] ?? '');
    if (!isset($map[(int) $d['tenant_id']])) $errors['tenant_id'] = 'Choose a tenant.';
    if (!is_numeric($d['kwh']) || $d['kwh'] < 0) $errors['kwh'] = 'Enter the kWh consumed (0 or more).';
    $free = isset($map[(int) $d['tenant_id']]) && $map[(int) $d['tenant_id']]['water_plan'] === 'free';
    if (!$free && (!is_numeric($d['m3']) || $d['m3'] < 0)) $errors['m3'] = 'Enter the cubic meters consumed (0 or more).';
    if (!preg_match('/^\d{4}-\d{2}$/', $d['period'])) $errors['period'] = 'Choose a billing month.';
    if (!$errors) { $id = $store->addInvoice((int) $d['tenant_id'], $d['period'], (float) $d['kwh'], $free ? 0 : (float) $d['m3']); flash('success', 'Invoice created and added to the tenant balance.'); header('Location: ../invoices/view.php?id=' . $id); exit; }
}
include '../includes/header.php'; ?>
<div class="page-head"><div><h1 class="page-title">Create bill</h1><p class="page-sub">Enter consumption &mdash; the total is computed automatically.</p></div></div>
<div class="grid items-start gap-4 lg:grid-cols-5">
<form id="bill-form" method="POST" class="card card-pad grid gap-x-4 gap-y-5 sm:grid-cols-2 lg:col-span-3" data-once data-tenants='<?= e(json_encode($map)) ?>' data-rates='<?= e(json_encode($rates)) ?>'><?= csrf_field() ?>
  <div class="sm:col-span-2"><label class="field-label" for="b-tenant">Tenant</label><select id="b-tenant" name="tenant_id" class="select <?= isset($errors['tenant_id']) ? 'is-invalid' : '' ?>"><?php foreach ($tenants as $t): ?><option value="<?= $t['tenant_id'] ?>" <?= (int) $d['tenant_id'] === $t['tenant_id'] ? 'selected' : '' ?>><?= e($t['name']) ?> · Rm <?= e($t['room_id']) ?><?= $t['water_plan'] === 'free' ? ' · free water' : '' ?></option><?php endforeach; ?></select><?php if (isset($errors['tenant_id'])): ?><p class="field-error"><?= e($errors['tenant_id']) ?></p><?php endif; ?></div>
  <div><label class="field-label" for="b-kwh">Electricity used (kWh)</label><div class="relative"><input id="b-kwh" type="number" min="0" step="0.01" name="kwh" value="<?= e($d['kwh']) ?>" class="input pr-20 <?= isset($errors['kwh']) ? 'is-invalid' : '' ?>" placeholder="0"><span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400">₱<?= number_format($rates['elec'], 2) ?>/kWh</span></div><?php if (isset($errors['kwh'])): ?><p class="field-error"><?= e($errors['kwh']) ?></p><?php endif; ?></div>
  <div><label class="field-label" for="b-m3">Water used (m³)</label><div class="relative"><input id="b-m3" type="number" min="0" step="0.01" name="m3" value="<?= e($d['m3']) ?>" class="input pr-20 <?= isset($errors['m3']) ? 'is-invalid' : '' ?>" placeholder="0"><span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-medium text-slate-400">₱<?= number_format($rates['water'], 2) ?>/m³</span></div><?php if (isset($errors['m3'])): ?><p class="field-error"><?= e($errors['m3']) ?></p><?php endif; ?></div>
  <div class="sm:col-span-2"><label class="field-label" for="b-period">Billing month</label><input id="b-period" type="month" name="period" value="<?= e($d['period']) ?>" class="input <?= isset($errors['period']) ? 'is-invalid' : '' ?>"><?php if (isset($errors['period'])): ?><p class="field-error"><?= e($errors['period']) ?></p><?php endif; ?></div>
  <div class="form-actions sm:col-span-2"><button class="btn btn-primary btn-lg"><?php icon('bolt', 'w-5 h-5'); ?> Generate invoice</button></div></form>
<div class="card card-pad lg:sticky lg:top-24 lg:col-span-2"><h2 class="card-title"><?php icon('eye', 'w-5 h-5 text-brand-600'); ?> Live preview</h2><div id="bill-preview"></div></div></div>
<?php include '../includes/footer.php'; ?>
