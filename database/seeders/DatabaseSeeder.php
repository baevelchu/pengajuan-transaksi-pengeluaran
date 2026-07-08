<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\CompanyBalance;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['name' => 'staff', 'label' => 'Staff', 'description' => 'Membuat pengajuan transaksi pengeluaran.'],
            ['name' => 'spv', 'label' => 'Supervisor (SPV)', 'description' => 'Approval untuk pengajuan nilai <= Rp 5.000.000.'],
            ['name' => 'manager', 'label' => 'Manager', 'description' => 'Approval untuk pengajuan nilai > Rp 5.000.000.'],
            ['name' => 'direktur', 'label' => 'Direktur', 'description' => 'Approval untuk kategori PO Produk & nilai > Rp 10.000.000.'],
            ['name' => 'finance', 'label' => 'Finance', 'description' => 'Validasi saldo & memproses pembayaran.'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['name' => $r['name']], $r);
        }

        // 2. Akun untuk masing-masing role. Password default: password
        $users = [
            ['name' => 'Staff', 'email' => 'staff@test.com', 'role' => 'staff'],
            ['name' => 'SPV', 'email' => 'spv@test.com', 'role' => 'spv'],
            ['name' => 'Manager', 'email' => 'manager@test.com', 'role' => 'manager'],
            ['name' => 'Direktur', 'email' => 'direktur@test.com', 'role' => 'direktur'],
            ['name' => 'Finance', 'email' => 'finance@test.com', 'role' => 'finance'],
        ];

        foreach ($users as $u) {
            $roleId = Role::where('name', $u['role'])->value('id');

            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $roleId,
                ]
            );
        }

        // 3. Kategori transaksi
        $categoryNames = [
            'PO Produk',
            'Operasional',
            'Marketing',
            'Perjalanan Dinas',
            'ATK & Perlengkapan Kantor',
        ];

        $categories = [];
        foreach ($categoryNames as $name) {
            $categories[$name] = Category::updateOrCreate(['name' => $name]);
        }

        // 4. Budget per kategori
        $budgetAmounts = [
            'PO Produk' => 500000000,
            'Operasional' => 50000000,
            'Marketing' => 30000000,
            'Perjalanan Dinas' => 20000000,
            'ATK & Perlengkapan Kantor' => 10000000,
        ];

        foreach ($budgetAmounts as $name => $total) {
            Budget::updateOrCreate(
                ['category_id' => $categories[$name]->id],
                ['total_budget' => $total, 'used_budget' => 0]
            );
        }

        // 5. Saldo kas perusahaan untuk keperluan Finance
        if (CompanyBalance::count() === 0) {
            CompanyBalance::create(['saldo' => 100000000]);
        }
    }
}
