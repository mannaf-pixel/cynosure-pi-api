<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HardwareProductSeeder extends Seeder
{
    public function run(): void
    {
        $hardware = [
            // Cynosure (company_id=1)
            ['company_id'=>1,'code'=>'HW-001','name'=>'Handle','unit'=>'piece','rate'=>150.00],
            ['company_id'=>1,'code'=>'HW-002','name'=>'Lock','unit'=>'piece','rate'=>200.00],
            ['company_id'=>1,'code'=>'HW-003','name'=>'Hinge','unit'=>'piece','rate'=>80.00],
            ['company_id'=>1,'code'=>'HW-004','name'=>'Aluminium Track','unit'=>'meter','rate'=>120.00],
            ['company_id'=>1,'code'=>'HW-005','name'=>'GI Track','unit'=>'meter','rate'=>90.00],
            ['company_id'=>1,'code'=>'HW-006','name'=>'Wool Pile','unit'=>'meter','rate'=>15.00],
            ['company_id'=>1,'code'=>'HW-007','name'=>'Silicon Sealant','unit'=>'piece','rate'=>180.00],
            ['company_id'=>1,'code'=>'HW-008','name'=>'Screw Set','unit'=>'set','rate'=>50.00],

            // Plastrong (company_id=2)
            ['company_id'=>2,'code'=>'HW-001','name'=>'Handle','unit'=>'piece','rate'=>150.00],
            ['company_id'=>2,'code'=>'HW-002','name'=>'Lock','unit'=>'piece','rate'=>200.00],
            ['company_id'=>2,'code'=>'HW-003','name'=>'Hinge','unit'=>'piece','rate'=>80.00],
            ['company_id'=>2,'code'=>'HW-004','name'=>'Aluminium Track','unit'=>'meter','rate'=>120.00],
            ['company_id'=>2,'code'=>'HW-005','name'=>'GI Track','unit'=>'meter','rate'=>90.00],
            ['company_id'=>2,'code'=>'HW-006','name'=>'Wool Pile','unit'=>'meter','rate'=>15.00],
            ['company_id'=>2,'code'=>'HW-007','name'=>'Silicon Sealant','unit'=>'piece','rate'=>180.00],
            ['company_id'=>2,'code'=>'HW-008','name'=>'Screw Set','unit'=>'set','rate'=>50.00],
        ];

        foreach ($hardware as $item) {
            DB::table('hardware_products')->insert([
                ...$item,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Hardware products seeded!\n";
    }
}
