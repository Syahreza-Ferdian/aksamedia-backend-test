<?php

namespace Database\Seeders;

use App\Models\AdminModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        AdminModel::create([
            'name' => 'Admin',
            'username' => 'admin',
            'password_hash' => Hash::make('pastibisa'),
            'phone' => '081234567890',
            'email' => 'admin@gmail.com'
        ]);
    }
}
