<?php
$root = '../'; require_once __DIR__ . '/../includes/auth_check.php'; require_role(['admin', 'staff'], $root);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); $id = (int) ($_POST['id'] ?? 0); $action = (string) ($_POST['action'] ?? ''); $by = $_SESSION['name'];
    $note = substr(trim((string) ($_POST['note'] ?? '')), 0, 140);
    if ($action === 'cancel') {                                   // staff withdraw their own pending request
        if ($store->cancelRequest($id, $by)) flash('success', 'Request cancelled.'); else flash('error', 'That request can no longer be cancelled.');
    } elseif ($_SESSION['role'] !== 'admin') {
        flash('error', 'Only the administrator can approve or reject requests.');
    } elseif (in_array($action, ['approve', 'reject'], true)) {
        $err = $store->decide($id, $action === 'approve', $by, $note);
        if ($err) flash('error', $err); else flash('success', $action === 'approve' ? 'Approved — the change has been applied.' : 'Request rejected.');
    }
}
header('Location: index.php'); exit;
