<?php
session_start();
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../models/database_access.php';
if (!isset($_SESSION['user_id'])) { header('Location: ' . ($root ?? '') . 'auth/login.php'); exit; }
$store = new Store();
// Call at the top of a page: require_role(['admin','staff'])
function require_role(array $roles, string $root = ''): void {
    if (!in_array($_SESSION['role'], $roles, true)) { flash('error', 'You do not have access to that page.'); header('Location: ' . $root . 'index.php'); exit; }
}
