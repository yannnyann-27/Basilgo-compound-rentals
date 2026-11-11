<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../models/database_access.php';
if (isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }
$store = new Store(); $error = ''; $root = '../'; $doc_title = APP_NAME;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? ''); $p = (string) ($_POST['password'] ?? '');
    $user = $u !== '' ? $store->userByName($u) : null;
    if ($u === '' || $p === '') $error = 'Please enter your username and password.';
    elseif ($user && password_verify($p, $user['hash'])) {
        session_regenerate_id(true);
        $_SESSION += ['user_id' => $user['user_id'], 'username' => $user['username'], 'name' => $user['name'], 'role' => $user['role'], 'tenant_id' => $user['tenant_id']];
        header('Location: ../index.php'); exit;
    } else $error = 'Invalid username or password.';
}
?><!DOCTYPE html>
<html lang="en"><head>
<?php include __DIR__ . '/../includes/head.php'; ?>
</head><body class="min-h-screen">
<div class="grid min-h-screen lg:grid-cols-[1.1fr_1fr]">

  <!-- brand panel -->
  <aside class="auth-art relative hidden overflow-hidden p-12 text-white lg:flex lg:flex-col lg:justify-between">
    <div class="orb -right-24 -top-24 h-96 w-96 bg-brand-400/40"></div><div class="orb -bottom-20 -left-16 h-80 w-80 bg-lime-300/20" style="animation-delay:-6s"></div>
    <div class="relative flex items-center gap-3"><span class="grid h-12 w-12 place-items-center rounded-2xl bg-gradient-to-br from-brand-400 to-lime-300 text-brand-950 shadow-lg shadow-brand-500/30"><?php icon('home', 'w-6 h-6'); ?></span>
      <div><div class="text-lg font-extrabold leading-tight"><?= APP_SHORT ?></div><div class="text-sm text-brand-200/80"><?= APP_TAGLINE ?></div></div></div>
    <div class="stagger relative max-w-lg">
      <h2 class="text-4xl font-extrabold leading-[1.1] text-white xl:text-5xl">Rent, billing and cash payments &mdash; beautifully simple.</h2>
      <p class="mt-4 text-lg text-brand-100/75">Everything for your houses and rooms in one calm workspace.</p>
      <ul class="mt-8 grid gap-3 text-brand-100">
        <?php foreach (['Electric and water bills computed for you', 'Staff record cash received, admin approves it', 'Tenants always see their latest balance'] as $pt): ?>
        <li class="flex items-center gap-3"><span class="grid h-6 w-6 place-items-center rounded-full bg-brand-400/25 text-brand-300"><?php icon('check', 'w-4 h-4'); ?></span><?= e($pt) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <p class="relative text-xs text-brand-200/50">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?></p>
  </aside>

  <!-- form -->
  <main class="grid place-items-center px-5 py-10 sm:px-8">
    <div class="stagger w-full max-w-sm">
      <div class="mb-8 flex items-center gap-3 lg:hidden"><span class="grid h-11 w-11 place-items-center rounded-xl bg-gradient-to-br from-brand-400 to-lime-300 text-brand-950 shadow-lg shadow-brand-500/30"><?php icon('home', 'w-6 h-6'); ?></span>
        <div><div class="font-extrabold leading-tight text-slate-900"><?= APP_SHORT ?></div><div class="text-xs text-slate-500"><?= APP_TAGLINE ?></div></div></div>
      <div class="mb-6"><h1 class="text-3xl font-extrabold">Welcome back</h1><p class="mt-1 text-slate-500">Sign in to your account to continue.</p></div>
      <?php if ($error): ?><div class="alert alert-error animate-pop"><?php icon('alert'); ?><span><?= e($error) ?></span></div><?php endif; ?>
      <form method="POST" class="grid gap-4" data-once>
        <div><label class="field-label" for="username">Username</label><input id="username" name="username" class="input <?= $error ? 'is-invalid' : '' ?>" autocomplete="username" value="<?= e($_POST['username'] ?? '') ?>" autofocus></div>
        <div><label class="field-label" for="password">Password</label>
          <div class="relative"><input id="password" type="password" name="password" class="input pr-11 <?= $error ? 'is-invalid' : '' ?>" autocomplete="current-password">
            <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg p-1.5 text-slate-400 transition hover:text-brand-700" data-toggle-pass="#password" aria-label="Show or hide password"><?php icon('eye'); ?></button></div></div>
        <button class="btn btn-primary btn-lg w-full" type="submit">Sign in</button>
      </form>
      <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-4">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Demo accounts &middot; tap to sign in</p>
        <div class="grid grid-cols-2 gap-2">
          <button type="button" class="btn btn-secondary btn-sm" data-fill="admin:admin123">Administrator</button><button type="button" class="btn btn-secondary btn-sm" data-fill="staff:staff123">Staff</button>
          <button type="button" class="btn btn-secondary btn-sm" data-fill="juan:tenant123">Tenant &middot; metered</button><button type="button" class="btn btn-secondary btn-sm" data-fill="ana:tenant123">Tenant &middot; free water</button>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="../assets/js/app.js"></script>
</body></html>
