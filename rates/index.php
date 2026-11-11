<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php'; require_role(['admin'], $root);
$page_title = 'Rates'; $active = 'rates'; $r = $store->rates(); $errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['reset'])) { $store->reset(); flash('success', 'Demo data reset.'); header('Location: index.php'); exit; }
    $e = $_POST['elec'] ?? ''; $w = $_POST['water'] ?? '';
    if (!is_numeric($e) || $e <= 0) $errors['elec'] = 'Enter a rate above 0.'; if (!is_numeric($w) || $w <= 0) $errors['water'] = 'Enter a rate above 0.';
    if (!$errors) { $store->setRates((float) $e, (float) $w); flash('success', 'Utility rates saved.'); header('Location: index.php'); exit; }
}
include '../includes/header.php'; ?>
<div class="mx-auto max-w-xl"><div class="page-head"><div><h1 class="page-title">Utility rates</h1><p class="page-sub">Applied to every new bill staff generate.</p></div></div>
<form method="POST" class="card card-pad grid gap-5" data-once><?= csrf_field() ?>
<div class="flex gap-4"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl tone-warn"><?php icon('bolt', 'w-6 h-6'); ?></span><div class="flex-1"><label class="field-label" for="r-elec">Electricity (₱ per kWh)</label><input id="r-elec" type="number" step="0.01" name="elec" class="input <?= isset($errors['elec']) ? 'is-invalid' : '' ?>" value="<?= e($_POST['elec'] ?? $r['elec']) ?>"><?php if (isset($errors['elec'])): ?><p class="field-error"><?= e($errors['elec']) ?></p><?php endif; ?></div></div>
<div class="flex gap-4"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl tone-info"><?php icon('door', 'w-6 h-6'); ?></span><div class="flex-1"><label class="field-label" for="r-water">Water (₱ per m³)</label><input id="r-water" type="number" step="0.01" name="water" class="input <?= isset($errors['water']) ? 'is-invalid' : '' ?>" value="<?= e($_POST['water'] ?? $r['water']) ?>"><p class="field-hint">Estimated Cebu City residential rate &mdash; please verify with MCWD.</p><?php if (isset($errors['water'])): ?><p class="field-error"><?= e($errors['water']) ?></p><?php endif; ?></div></div>
<div class="form-actions"><button class="btn btn-primary">Save rates</button></div></form>
<div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50/50 p-5"><h2 class="font-bold text-rose-800">Danger zone</h2><p class="mt-1 text-sm text-rose-700/80">Resets tenants, invoices, payments and requests back to the demo data.</p>
<form method="POST" class="mt-3"><?= csrf_field() ?><button name="reset" value="1" class="btn btn-danger btn-sm" data-confirm="Reset all demo data? This cannot be undone.">Reset demo data</button></form></div></div>
<?php include '../includes/footer.php'; ?>
