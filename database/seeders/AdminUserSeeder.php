<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'      => 'Abday Mannaf',
            'email'     => 'admin@cynosurepi.local',
            'password'  => Hash::make('Cyno@Admin2026'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'MD User',
            'email'     => 'md@cynosurepi.local',
            'password'  => Hash::make('Cyno@MD2026'),
            'role'      => 'md',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'CEO User',
            'email'     => 'ceo@cynosurepi.local',
            'password'  => Hash::make('Cyno@CEO2026'),
            'role'      => 'ceo',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'PI Creator',
            'email'     => 'picreator@cynosurepi.local',
            'password'  => Hash::make('Cyno@PI2026'),
            'role'      => 'pi_creator',
            'is_active' => true,
        ]);
    }
}