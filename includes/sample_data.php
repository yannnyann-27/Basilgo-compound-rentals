<?php
// PROTOTYPE ONLY: seed data that stands in for database tables. Replace via models/database_access.php.
$seed = [
  'seq' => 100,
  'rates' => ['elec' => ELEC_RATE, 'water' => WATER_RATE],
  'users' => [
    ['user_id'=>1,'username'=>'admin','name'=>'Admin Lorenzo','role'=>'admin','tenant_id'=>null,'hash'=>password_hash('admin123', PASSWORD_DEFAULT)],
    ['user_id'=>2,'username'=>'staff','name'=>'Staff Rica','role'=>'staff','tenant_id'=>null,'hash'=>password_hash('staff123', PASSWORD_DEFAULT)],
    ['user_id'=>3,'username'=>'juan','name'=>'Juan dela Cruz','role'=>'tenant','tenant_id'=>2,'hash'=>password_hash('tenant123', PASSWORD_DEFAULT)],
    ['user_id'=>4,'username'=>'ana','name'=>'Ana Villanueva','role'=>'tenant','tenant_id'=>3,'hash'=>password_hash('tenant123', PASSWORD_DEFAULT)],
  ],
  'rooms' => array_map(fn($id, $i) => ['room_id'=>$id,'rent'=>6500 + ($i % 4) * 1000,'maintenance'=>$id === '104'],
      ['101','102','103','104','105','106','201','202','203','204','205','206'], range(0, 11)),
  'tenants' => [
    ['tenant_id'=>1,'name'=>'Maria Santos','room_id'=>'101','phone'=>'0917 555 0101','email'=>'maria@mail.ph','rent'=>6500,'balance'=>0,'water_plan'=>'metered','move_in'=>'2024-02-01'],
    ['tenant_id'=>2,'name'=>'Juan dela Cruz','room_id'=>'102','phone'=>'0918 555 0102','email'=>'juan@mail.ph','rent'=>7500,'balance'=>8420,'water_plan'=>'metered','move_in'=>'2024-03-15'],
    ['tenant_id'=>3,'name'=>'Ana Villanueva','room_id'=>'103','phone'=>'0920 555 0103','email'=>'ana@mail.ph','rent'=>8500,'balance'=>3150.50,'water_plan'=>'free','move_in'=>'2024-05-01'],
    ['tenant_id'=>4,'name'=>'Pedro Reyes','room_id'=>'105','phone'=>'0921 555 0104','email'=>'pedro@mail.ph','rent'=>6500,'balance'=>15200,'water_plan'=>'metered','move_in'=>'2023-11-20'],
    ['tenant_id'=>5,'name'=>'Liza Gonzales','room_id'=>'106','phone'=>'0922 555 0105','email'=>'liza@mail.ph','rent'=>7500,'balance'=>0,'water_plan'=>'free','move_in'=>'2024-07-10'],
    ['tenant_id'=>6,'name'=>'Mark Tan','room_id'=>'201','phone'=>'0923 555 0106','email'=>'mark@mail.ph','rent'=>6500,'balance'=>1960,'water_plan'=>'metered','move_in'=>'2025-01-05'],
  ],
  'invoices' => [
    ['invoice_id'=>1001,'tenant_id'=>2,'period'=>'2026-10','rent'=>7500,'kwh'=>84,'electric'=>1344,'m3'=>12,'water'=>420,'total'=>9264,'created'=>'2026-10-30'],
    ['invoice_id'=>1002,'tenant_id'=>3,'period'=>'2026-10','rent'=>8500,'kwh'=>110,'electric'=>1760,'m3'=>0,'water'=>0,'total'=>10260,'created'=>'2026-10-30'],
  ],
  'payments' => [['payment_id'=>1,'tenant_id'=>2,'amount'=>5000,'method'=>'Cash','note'=>'Oct partial','paid_on'=>'2026-10-28','recorded_by'=>'Staff Rica','approved_by'=>'Admin Lorenzo','balance_after'=>8420]],
  'requests' => [
    ['request_id'=>1,'type'=>'edit','tenant_id'=>6,'by'=>'Staff Rica','changes'=>['phone'=>'0923 555 9999'],'status'=>'pending','created'=>'2026-11-10'],
    ['request_id'=>2,'type'=>'payment','tenant_id'=>4,'by'=>'Staff Rica','amount'=>5000,'method'=>'Cash','note'=>'November rent (partial)','received_on'=>'2026-11-10','status'=>'pending','created'=>'2026-11-10'],
  ],
];
