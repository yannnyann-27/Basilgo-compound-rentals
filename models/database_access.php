<?php
// Data layer. Session-backed for the prototype; swap the bodies for PDO queries later and keep the method names.
class Store {
    private array $d;
    private string $file;
    // Shared JSON file so admin, staff and tenants all see the same data (a database replaces this later).
    public function __construct() {
        $this->file = __DIR__ . '/../data/db.json';
        if (is_file($this->file)) $this->d = json_decode((string) file_get_contents($this->file), true) ?: [];
        if (empty($this->d)) { require __DIR__ . '/../includes/sample_data.php'; $this->d = $seed; $this->save(); }
    }
    private function save(): void { @file_put_contents($this->file, json_encode($this->d, JSON_PRETTY_PRINT), LOCK_EX); }
    public function reset(): void { @unlink($this->file); }
    private function id(): int { return ++$this->d['seq']; }
    public function userByName(string $u): ?array { foreach ($this->d['users'] as $x) if ($x['username'] === $u) return $x; return null; }
    public function rates(): array { return $this->d['rates']; }
    public function setRates(float $e, float $w): void { $this->d['rates'] = ['elec' => $e, 'water' => $w]; $this->save(); }
    public function rooms(): array { return $this->d['rooms']; }
    public function tenants(): array { return $this->d['tenants']; }
    public function tenant(int $id): ?array { foreach ($this->d['tenants'] as $t) if ($t['tenant_id'] === $id) return $t; return null; }
    public function tenantByRoom(string $r): ?array { foreach ($this->d['tenants'] as $t) if ($t['room_id'] === $r) return $t; return null; }
    public function roomStatus(array $r): string { return $r['maintenance'] ? 'Maintenance' : ($this->tenantByRoom($r['room_id']) ? 'Occupied' : 'Available'); }
    public function updateTenant(int $id, array $ch): void { foreach ($this->d['tenants'] as &$t) if ($t['tenant_id'] === $id) $t = array_merge($t, $ch); unset($t); $this->save(); }
    public function invoices(?int $tid = null): array { $r = array_filter($this->d['invoices'], fn($i) => !$tid || $i['tenant_id'] === $tid); return array_reverse(array_values($r)); }
    public function invoice(int $id): ?array { foreach ($this->d['invoices'] as $i) if ($i['invoice_id'] === $id) return $i; return null; }
    public function payments(?int $tid = null): array { $r = array_filter($this->d['payments'], fn($p) => !$tid || $p['tenant_id'] === $tid); return array_reverse(array_values($r)); }
    // ---- approval requests (staff -> admin) ----
    public function requests(?string $by = null): array { $r = array_filter($this->d['requests'], fn($x) => $by === null || $x['by'] === $by); return array_reverse(array_values($r)); }
    public function pendingCount(?string $by = null): int { return count(array_filter($this->d['requests'], fn($r) => $r['status'] === 'pending' && ($by === null || $r['by'] === $by))); }
    // Money staff already reported for a tenant that is still waiting for the admin (so the same cash is never deducted twice).
    public function pendingPaymentTotal(int $tid): float { $s = 0.0; foreach ($this->d['requests'] as $r) if ($r['type'] === 'payment' && $r['status'] === 'pending' && $r['tenant_id'] === $tid) $s += $r['amount']; return round($s, 2); }
    public function addRequest(string $type, int $tid, string $by, array $data): void {
        $this->d['requests'][] = ['request_id' => $this->id(), 'type' => $type, 'tenant_id' => $tid, 'by' => $by, 'status' => 'pending', 'created' => date('Y-m-d')] + $data;
        $this->save();
    }
    // Staff may withdraw their own request while it is still pending (e.g. a typo in the amount).
    public function cancelRequest(int $rid, string $by): bool {
        foreach ($this->d['requests'] as $k => $r) {
            if ($r['request_id'] === $rid && $r['status'] === 'pending' && $r['by'] === $by) { $this->d['requests'][$k]['status'] = 'cancelled'; $this->save(); return true; }
        }
        return false;
    }
    // Admin decision. Returns null on success or an error message to show the admin.
    public function decide(int $rid, bool $ok, string $by, string $note = ''): ?string {
        foreach ($this->d['requests'] as $k => $r) {
            if ($r['request_id'] !== $rid) continue;
            if ($r['status'] !== 'pending') return 'That request was already handled.';
            if ($ok && $r['type'] === 'payment') {
                $t = $this->tenant($r['tenant_id']);
                if (!$t || round($r['amount'], 2) > round($t['balance'], 2)) return 'This payment is more than the tenant\'s current balance of ' . money($t['balance'] ?? 0) . '. Reject it and ask staff to enter the correct amount.';
            }
            $this->d['requests'][$k]['status'] = $ok ? 'approved' : 'rejected';
            $this->d['requests'][$k]['decided_by'] = $by;
            $this->d['requests'][$k]['decided_on'] = date('Y-m-d');
            $this->d['requests'][$k]['decision_note'] = $note;
            if ($ok && $r['type'] === 'edit') $this->updateTenant($r['tenant_id'], $r['changes']);
            if ($ok && $r['type'] === 'payment') $this->addPayment($r['tenant_id'], (float) $r['amount'], PAY_METHOD, (string) ($r['note'] ?? ''), $r['by'], $by, $r['received_on'] ?? null);
            $this->save();
            return null;
        }
        return 'Request not found.';
    }
    // Cash received -> deduct from the tenant's outstanding balance and log it.
    public function addPayment(int $tid, float $amt, string $method, string $note, string $by, ?string $approvedBy = null, ?string $paidOn = null): void {
        $after = 0.0;
        foreach ($this->d['tenants'] as &$t) if ($t['tenant_id'] === $tid) { $t['balance'] = round(max(0, $t['balance'] - $amt), 2); $after = $t['balance']; }
        unset($t);
        $this->d['payments'][] = ['payment_id' => $this->id(), 'tenant_id' => $tid, 'amount' => $amt, 'method' => $method, 'note' => $note, 'paid_on' => $paidOn ?: date('Y-m-d'), 'recorded_by' => $by, 'approved_by' => $approvedBy ?? $by, 'balance_after' => $after];
        $this->save();
    }
    public function addInvoice(int $tid, string $period, float $kwh, float $m3): int {
        $t = $this->tenant($tid); $b = calc_bill($t, $kwh, $m3, $this->rates()); $id = $this->id();
        $this->d['invoices'][] = ['invoice_id' => $id, 'tenant_id' => $tid, 'period' => $period, 'rent' => $b['rent'], 'kwh' => $b['kwh'], 'electric' => $b['electric'], 'm3' => $b['m3'], 'water' => $b['water'], 'total' => $b['total'], 'created' => date('Y-m-d')];
        $this->updateTenant($tid, ['balance' => $t['balance'] + $b['total']]);
        return $id;
    }
}
