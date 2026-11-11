<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php'; require_role(['admin', 'staff'], $root);
$admin = $_SESSION['role'] === 'admin'; $page_title = $admin ? 'Approvals' : 'My Requests'; $active = 'approvals';
$all = $store->requests($admin ? null : $_SESSION['name']);
$open = array_values(array_filter($all, fn($r) => $r['status'] === 'pending')); $done = array_values(array_filter($all, fn($r) => $r['status'] !== 'pending'));
$openCash = array_sum(array_map(fn($r) => $r['type'] === 'payment' ? $r['amount'] : 0, $open));
include '../includes/header.php'; ?>
<div class="page-head"><div><h1 class="page-title"><?= $page_title ?></h1><p class="page-sub"><?= $admin ? 'Cash payments and tenant edits entered by staff wait here until you confirm them.' : 'Track the cash payments and edits you sent for approval.' ?></p></div>
  <?php if (!$admin): ?><a href="../payments/create.php" class="btn btn-primary"><?php icon('cash', 'w-4 h-4'); ?> Record cash payment</a><?php endif; ?></div>

<div class="stagger mb-5 grid grid-cols-3 gap-3 sm:gap-4">
  <div class="card card-pad !p-4"><p class="text-xs text-slate-500 sm:text-sm">Pending</p><p class="text-2xl font-extrabold text-amber-600"><?= count($open) ?></p></div>
  <div class="card card-pad !p-4"><p class="text-xs text-slate-500 sm:text-sm">Cash to confirm</p><p class="text-lg font-extrabold tabular-nums text-slate-900 sm:text-2xl"><?= money($openCash) ?></p></div>
  <div class="card card-pad !p-4"><p class="text-xs text-slate-500 sm:text-sm">Decided</p><p class="text-2xl font-extrabold text-slate-900"><?= count($done) ?></p></div>
</div>

<?php if (!$open): ?><div class="card empty"><div class="empty-icon tone-brand"><?php icon('check'); ?></div><b class="text-slate-700">All caught up</b><p class="mt-1 text-sm">Nothing is waiting for approval.</p></div><?php endif; ?>
<div class="stagger grid gap-4">
<?php foreach ($open as $r): $t = $store->tenant($r['tenant_id']); $pay = $r['type'] === 'payment'; $after = $pay ? max(0, $t['balance'] - $r['amount']) : 0; $bad = $pay && round($r['amount'], 2) > round($t['balance'], 2); ?>
  <div class="card overflow-hidden transition duration-300 hover:shadow-lift"><div class="h-1 <?= $pay ? 'bg-gradient-to-r from-brand-500 to-lime-300' : 'bg-amber-400' ?>"></div>
  <div class="flex flex-col gap-5 p-5 lg:flex-row lg:items-center lg:justify-between">
    <div class="min-w-0 flex-1">
      <div class="mb-3 flex items-center gap-3"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl <?= $pay ? 'tone-brand' : 'tone-warn' ?>"><?php icon($pay ? 'cash' : 'pencil', 'w-6 h-6'); ?></span>
        <div class="min-w-0"><div class="flex flex-wrap items-center gap-x-2"><b class="truncate text-slate-900"><?= e($t['name']) ?></b><span class="text-sm text-slate-400">· Rm <?= e($t['room_id']) ?></span></div>
          <p class="text-xs text-slate-500"><?= $pay ? 'Cash payment' : 'Information edit' ?> · entered by <b><?= e($r['by']) ?></b> · <?= fdate($r['created']) ?></p></div></div>
      <?php if ($pay): ?>
      <div class="grid gap-2 rounded-xl bg-slate-50 p-3 text-sm sm:grid-cols-3">
        <div><p class="text-xs text-slate-500">Cash received</p><p class="text-lg font-extrabold tabular-nums text-emerald-700"><?= money($r['amount']) ?></p><p class="text-xs text-slate-400">on <?= fdate($r['received_on'] ?? $r['created']) ?></p></div>
        <div class="sm:col-span-2"><p class="text-xs text-slate-500">Balance if approved</p><p class="font-semibold tabular-nums text-slate-900"><span class="text-slate-400 line-through"><?= money($t['balance']) ?></span> <span class="mx-1 text-slate-400">→</span> <span class="text-lg font-extrabold <?= $after > 0 ? 'text-rose-600' : 'text-emerald-700' ?>"><?= money($after) ?></span></p>
          <?php if ($r['note'] ?? ''): ?><p class="mt-1 text-xs text-slate-500">Note: <?= e($r['note']) ?></p><?php endif; ?></div></div>
      <?php if ($bad): ?><div class="alert alert-error mb-0 mt-3"><?php icon('alert'); ?><span>This is more than the tenant&rsquo;s current balance, so it can&rsquo;t be approved. Reject it and ask staff to re-enter the amount.</span></div><?php endif; ?>
      <?php else: ?>
      <div class="grid gap-1.5 rounded-xl bg-slate-50 p-3 text-sm"><?php foreach ($r['changes'] as $k => $v): ?><p><span class="text-slate-500"><?= e(ucwords(str_replace('_', ' ', $k))) ?>:</span> <s class="text-slate-400"><?= e($t[$k]) ?></s> <span class="text-slate-400">→</span> <b class="text-slate-900"><?= e($v) ?></b></p><?php endforeach; ?></div>
      <?php endif; ?>
    </div>

    <?php if ($admin): ?>
    <form method="POST" action="decide.php" class="grid shrink-0 gap-2 lg:w-64" data-once><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['request_id'] ?>">
      <input name="note" maxlength="140" class="input !py-2 text-xs" placeholder="Reason (optional, shown to staff)" aria-label="Decision note">
      <div class="grid grid-cols-2 gap-2"><button name="action" value="approve" class="btn btn-primary" <?= $bad ? 'disabled' : '' ?>><?php icon('check', 'w-4 h-4'); ?> Approve</button><button name="action" value="reject" class="btn btn-danger" data-confirm="Reject this request?"><?php icon('x', 'w-4 h-4'); ?> Reject</button></div></form>
    <?php else: ?>
    <form method="POST" action="decide.php" class="flex shrink-0 items-center gap-3" data-once><?= csrf_field() ?><input type="hidden" name="id" value="<?= $r['request_id'] ?>"><?= badge('pending') ?><span class="hidden text-xs text-slate-400 sm:inline">Awaiting admin</span>
      <button name="action" value="cancel" class="btn btn-secondary btn-sm" data-confirm="Cancel this request?">Cancel</button></form>
    <?php endif; ?>
  </div></div>
<?php endforeach; ?></div>

<h2 class="mb-3 mt-10 text-lg font-bold text-slate-900">History</h2>
<div class="card table-wrap"><table class="data stack"><thead><tr><th>Request</th><th>Tenant</th><th>Entered by</th><th>Decision</th><th>Decided by</th><th>Reason</th></tr></thead><tbody>
<?php foreach ($done as $r): $t = $store->tenant($r['tenant_id']); ?><tr>
  <td data-label="Request"><b class="text-slate-900"><?= $r['type'] === 'payment' ? 'Cash ' . money($r['amount']) : 'Info edit' ?></b></td><td data-label="Tenant"><?= e($t['name']) ?></td><td data-label="Entered by"><?= e($r['by']) ?></td>
  <td data-label="Decision"><?= badge($r['status']) ?></td><td data-label="Decided by" class="text-slate-500"><?= isset($r['decided_by']) ? e($r['decided_by']) . ' · ' . fdate($r['decided_on']) : '—' ?></td><td data-label="Reason" class="text-slate-500"><?= e(($r['decision_note'] ?? '') ?: '—') ?></td></tr><?php endforeach; ?>
<?php if (!$done): ?><tr><td colspan="6"><div class="empty !py-8">Nothing yet</div></td></tr><?php endif; ?></tbody></table></div>
<?php include '../includes/footer.php'; ?>
