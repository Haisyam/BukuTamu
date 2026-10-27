<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            'Kepala Dinas / Instansi',
            'Sekretariat & Umum',
            'Bidang Ekonomi Kreatif',
            'Bidang Kebudayaan & Pariwisata',
            'Bidang Promosi & Kemitraan',
            'Pelayanan Publik & Informasi',
        ];

        foreach ($divisions as $name) {
            Division::firstOrCreate(['name' => $name], ['is_active' => true]);
        }
    }
}
