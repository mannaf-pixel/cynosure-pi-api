<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Company 1 — Cynosure (Aapki company)
        $cynosure = Company::create([
            'name'           => 'Assre Plasto World International Pvt. Ltd.',
            'slug'           => 'cynosure',
            'pricing_mode'   => 'per_meter',
            'gstin'          => '19AASCA2816R1ZU',
            'address'        => 'Baliandaga More, NH-12, Kaliachak, Malda, 732201',
            'phone'          => '7479005212',
            'email'          => 'mannaf@cynosurewindow.com',
            'bank_name'      => 'Canara Bank',
            'account_name'   => 'Assre Plasto World International Pvt Ltd',
            'account_number' => '125004130546',
            'ifsc_code'      => 'CNRB0019550',
            'branch'         => 'Malda',
            'logo'           => 'cynosure.png',
            'primary_color'  => '#1E6FD9',
            'is_active'      => true,
        ]);

        // Company 2 — Plastrong (Client)
        $plastrong = Company::create([
            'name'           => 'Plastrong Window',
            'slug'           => 'plastrong',
            'pricing_mode'   => 'per_kg',
            'gstin'          => '',
            'address'        => 'Add Plastrong Address Here',
            'phone'          => '',
            'email'          => '',
            'bank_name'      => '',
            'account_name'   => '',
            'account_number' => '',
            'ifsc_code'      => '',
            'branch'         => '',
            'logo'           => 'plastrong.png',
            'primary_color'  => '#1E6FD9',
            'is_active'      => true,
        ]);

        // Super Admin — aap (no company, sees everything)
        User::where('email', 'admin@cynosurepi.local')->update([
            'is_super_admin' => true,
            'company_id'     => null,
        ]);

        // Cynosure users — company assign karo
        User::where('email', 'md@cynosurepi.local')->update(['company_id' => $cynosure->id]);
        User::where('email', 'ceo@cynosurepi.local')->update(['company_id' => $cynosure->id]);
        User::where('email', 'picreator@cynosurepi.local')->update(['company_id' => $cynosure->id]);

        // Plastrong Admin user
        User::create([
            'name'           => 'Plastrong Admin',
            'email'          => 'admin@plastrong.local',
            'password'       => Hash::make('Plastrong@2026'),
            'role'           => 'admin',
            'company_id'     => $plastrong->id,
            'is_active'      => true,
            'is_super_admin' => false,
        ]);

        $this->command->info('2 companies seeded successfully!');
        $this->command->info('Plastrong Admin: admin@plastrong.local / Plastrong@2026');
    }
}