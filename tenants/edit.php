<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php'; require_role(['admin', 'staff'], $root);
$page_title = 'Edit Tenant'; $active = 'tenants'; $staff = $_SESSION['role'] === 'staff';
$t = $store->tenant((int) ($_GET['id'] ?? 0));
if (!$t) { flash('error', 'Tenant not found.'); header('Location: index.php'); exit; }
$fields = [['name', 'Full name', 'text'], ['phone', 'Phone', 'text'], ['email', 'Email', 'email'], ['water_plan', 'Water account', 'select']];
$errors = []; $data = $t;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($fields as [$n, $l, $ty]) { $data[$n] = trim($_POST[$n] ?? ''); if ($data[$n] === '') $errors[$n] = "$l is required."; }
    if (!isset($errors['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Enter a valid email address.';
    if (!in_array($data['water_plan'], ['metered', 'free'], true)) $errors['water_plan'] = 'Choose a water account.';
    if (!$errors) {
        $ch = []; foreach ($fields as [$n]) if ($data[$n] !== $t[$n]) $ch[$n] = $data[$n];
        if (!$ch) flash('success', 'No changes to save.');
        elseif ($staff) { $store->addRequest('edit', $t['tenant_id'], $_SESSION['name'], ['changes' => $ch]); flash('success', 'Edit sent to the admin for approval.'); }
        else { $store->updateTenant($t['tenant_id'], $ch); flash('success', 'Tenant updated.'); }
        header('Location: index.php'); exit;
    }
}
include '../includes/header.php'; ?>
<div class="mx-auto max-w-2xl"><div class="page-head"><div><h1 class="page-title">Edit <?= e($t['name']) ?></h1><p class="page-sub">Room <?= e($t['room_id']) ?></p></div><a href="index.php" class="btn btn-secondary"><?php icon('back', 'w-4 h-4'); ?> Back</a></div>
<?php if ($staff): ?><div class="alert alert-info"><?php icon('shield'); ?><span>Changes you make are saved as a request and only apply after the administrator approves them.</span></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error animate-pop"><?php icon('alert'); ?><span>Please fix the errors below.</span></div><?php endif; ?>
<form method="POST" class="card card-pad grid gap-x-4 gap-y-5 sm:grid-cols-2" data-once><?= csrf_field() ?>
<?php foreach ($fields as [$n, $l, $ty]): ?><div class="<?= $n === 'name' || $n === 'water_plan' ? 'sm:col-span-2' : '' ?>"><label class="field-label" for="f-<?= $n ?>"><?= $l ?></label>
  <?php if ($ty === 'select'): ?><select id="f-<?= $n ?>" name="<?= $n ?>" class="select"><option value="metered" <?= $data[$n] === 'metered' ? 'selected' : '' ?>>Metered (pays water)</option><option value="free" <?= $data[$n] === 'free' ? 'selected' : '' ?>>Free water usage</option></select>
  <?php else: ?><input id="f-<?= $n ?>" type="<?= $ty ?>" name="<?= $n ?>" value="<?= e($data[$n]) ?>" class="input <?= isset($errors[$n]) ? 'is-invalid' : '' ?>"><?php endif; ?>
  <?php if (isset($errors[$n])): ?><p class="field-error"><?php icon('alert', 'w-4 h-4'); ?> <?= e($errors[$n]) ?></p><?php endif; ?></div><?php endforeach; ?>
<div class="form-actions sm:col-span-2"><a href="index.php" class="btn btn-secondary">Cancel</a><button class="btn btn-primary"><?= $staff ? 'Send for approval' : 'Save changes' ?></button></div></form></div>
<?php include '../includes/footer.php'; ?>
