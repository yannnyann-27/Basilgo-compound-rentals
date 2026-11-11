<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php'; require_role(['admin', 'staff'], $root);
$page_title = 'Record Cash Payment'; $active = 'payments'; $staff = $_SESSION['role'] === 'staff'; $errors = []; $today = date('Y-m-d');
// Tenants who still owe money. "due" = balance minus cash already reported and waiting for approval (prevents counting the same cash twice).
$map = [];
foreach ($store->tenants() as $t) {
    $pend = $store->pendingPaymentTotal($t['tenant_id']); $due = round($t['balance'] - $pend, 2);
    if ($due > 0) $map[$t['tenant_id']] = ['name' => $t['name'], 'room' => $t['room_id'], 'balance' => (float) $t['balance'], 'pending' => $pend, 'due' => $due];
}
$d = ['tenant_id' => (string) ($_GET['tenant'] ?? ''), 'amount' => '', 'received_on' => $today, 'note' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); foreach ($d as $k => $_) $d[$k] = trim((string) ($_POST[$k] ?? ''));
    $tid = (int) $d['tenant_id']; $t = $store->tenant($tid); $info = $map[$tid] ?? null;
    if (!$t) $errors['tenant_id'] = 'Choose the tenant who paid.';
    elseif (!$info) $errors['tenant_id'] = 'This tenant has no balance left to pay.';
    if (!is_numeric($d['amount']) || (float) $d['amount'] <= 0) $errors['amount'] = 'Enter the amount of cash received (more than 0).';
    elseif ($info && round((float) $d['amount'], 2) > $info['due']) $errors['amount'] = 'That is more than the tenant still owes (' . money($info['due']) . ').';
    $dt = DateTime::createFromFormat('Y-m-d', $d['received_on']);
    if (!$dt || $dt->format('Y-m-d') !== $d['received_on'] || $d['received_on'] > $today) $errors['received_on'] = 'Choose a valid date that is not in the future.';
    if (strlen($d['note']) > 140) $errors['note'] = 'Please keep the note under 140 characters.';
    if (!$errors) {
        $amt = round((float) $d['amount'], 2);
        if ($staff) { $store->addRequest('payment', $tid, $_SESSION['name'], ['amount' => $amt, 'method' => PAY_METHOD, 'note' => $d['note'], 'received_on' => $d['received_on']]); flash('success', 'Cash payment of ' . money($amt) . ' from ' . $t['name'] . ' sent to the admin for approval. The balance updates once approved.'); }
        else { $store->addPayment($tid, $amt, PAY_METHOD, $d['note'], $_SESSION['name'], $_SESSION['name'], $d['received_on']); flash('success', 'Cash payment recorded. ' . $t['name'] . "'s balance is updated."); }
        header('Location: index.php'); exit;
    }
}
include '../includes/header.php'; ?>
<div class="page-head"><div><h1 class="page-title">Record cash payment</h1><p class="page-sub">Enter the cash a tenant handed to you. Only tenants who still owe money are listed.</p></div><a href="index.php" class="btn btn-secondary"><?php icon('back', 'w-4 h-4'); ?> Back</a></div>

<?php if ($staff): ?><div class="alert alert-info"><?php icon('shield'); ?><span><b>Needs admin approval.</b> The tenant&rsquo;s balance is reduced only after the administrator approves this payment.</span></div>
<?php else: ?><div class="alert alert-success"><?php icon('check'); ?><span>As administrator, your payment entries are applied immediately.</span></div><?php endif; ?>
<?php if ($errors): ?><div class="alert alert-error animate-pop"><?php icon('alert'); ?><span>Please fix the highlighted fields.</span></div><?php endif; ?>

<div class="grid items-start gap-4 lg:grid-cols-5">
<form id="pay-form" method="POST" class="card card-pad grid gap-5 lg:col-span-3" data-once data-tenants='<?= e(json_encode($map)) ?>'><?= csrf_field() ?>
  <div><label class="field-label" for="p-tenant">1 · Who paid?</label>
    <select id="p-tenant" name="tenant_id" class="select <?= isset($errors['tenant_id']) ? 'is-invalid' : '' ?>"><option value="">— Select tenant —</option>
      <?php foreach ($map as $id => $m): ?><option value="<?= $id ?>" <?= (string) $id === $d['tenant_id'] ? 'selected' : '' ?>><?= e($m['name']) ?> · Rm <?= e($m['room']) ?> — owes <?= money($m['due']) ?></option><?php endforeach; ?></select>
    <?php if (isset($errors['tenant_id'])): ?><p class="field-error"><?php icon('alert', 'w-4 h-4'); ?> <?= e($errors['tenant_id']) ?></p><?php endif; ?></div>

  <div><div class="mb-1.5 flex items-center justify-between gap-2"><label class="field-label !mb-0" for="p-amount">2 · Amount of cash received</label><button type="button" class="text-xs font-semibold text-brand-700 hover:underline" data-pay-full>Pay full balance</button></div>
    <div class="relative"><span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-slate-400">₱</span><input id="p-amount" type="number" inputmode="decimal" step="0.01" min="0.01" name="amount" value="<?= e($d['amount']) ?>" placeholder="0.00" class="input pl-9 text-lg font-bold tabular-nums <?= isset($errors['amount']) ? 'is-invalid' : '' ?>"></div>
    <?php if (isset($errors['amount'])): ?><p class="field-error"><?php icon('alert', 'w-4 h-4'); ?> <?= e($errors['amount']) ?></p><?php endif; ?></div>

  <div class="grid gap-4 sm:grid-cols-2">
    <div><label class="field-label" for="p-date">3 · Date received</label><input id="p-date" type="date" name="received_on" value="<?= e($d['received_on']) ?>" max="<?= $today ?>" class="input <?= isset($errors['received_on']) ? 'is-invalid' : '' ?>"><?php if (isset($errors['received_on'])): ?><p class="field-error"><?= e($errors['received_on']) ?></p><?php endif; ?></div>
    <div><span class="field-label">Payment method</span><div class="flex h-[2.65rem] items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-sm font-semibold text-slate-700"><?php icon('cash', 'w-5 h-5 text-brand-600'); ?> Cash <span class="ml-auto text-xs font-medium text-slate-400">only</span></div></div>
  </div>
  <div><label class="field-label" for="p-note">Note <span class="font-normal text-slate-400">(optional)</span></label><input id="p-note" name="note" maxlength="140" value="<?= e($d['note']) ?>" class="input <?= isset($errors['note']) ? 'is-invalid' : '' ?>" placeholder="e.g. November rent, partial">
    <?php if (isset($errors['note'])): ?><p class="field-error"><?= e($errors['note']) ?></p><?php endif; ?></div>
  <div class="form-actions"><a href="index.php" class="btn btn-secondary">Cancel</a><button class="btn btn-primary btn-lg"><?php icon($staff ? 'shield' : 'check', 'w-5 h-5'); ?> <?= $staff ? 'Submit for approval' : 'Record payment' ?></button></div>
</form>

<div class="grid gap-4 lg:col-span-2 lg:sticky lg:top-24">
  <div class="card card-pad"><h2 class="card-title"><?php icon('eye', 'w-5 h-5 text-brand-600'); ?> Balance preview</h2><div id="pay-preview"><p class="text-sm text-slate-500">Choose a tenant to see their balance.</p></div></div>
  <div class="card card-pad"><h2 class="card-title"><?php icon('info', 'w-5 h-5 text-sky-600'); ?> How it works</h2>
    <ol class="grid gap-3 text-sm text-slate-600">
      <?php foreach (['Tenant hands you the cash.', 'You enter the amount received here.', $staff ? 'The admin approves it, then the amount is deducted from the tenant\'s balance.' : 'The amount is deducted from the tenant\'s balance right away.'] as $n => $step): ?>
      <li class="flex gap-3"><span class="grid h-6 w-6 shrink-0 place-items-center rounded-full tone-brand text-xs font-bold"><?= $n + 1 ?></span><span><?= e($step) ?></span></li><?php endforeach; ?></ol></div>
</div></div>
<?php include '../includes/footer.php'; ?>
