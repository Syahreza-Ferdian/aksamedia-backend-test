<?php

namespace Database\Seeders;

use App\Models\DivisiModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisiDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $data = [
            [
                'name' => 'Mobile Apps'
            ],
            [
                'name' => 'QA'
            ],
            [
                'name' => 'Full Stack'
            ],
            [
                'name' => 'Backend'
            ],
            [
                'name' => 'Frontend'
            ],
            [
                'name' => 'UI/UX Designer'
            ]
        ];

        foreach ($data as $divisi) {
            DivisiModel::create($divisi);
        }
    }
}
