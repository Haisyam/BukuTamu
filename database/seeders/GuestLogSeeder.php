<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Division;
use App\Models\GuestLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class GuestLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = Division::all();
        if ($divisions->isEmpty()) {
            return;
        }

        // Ensure storage directory exists and copy a dummy avatar if not present
        if (!Storage::disk('public')->exists('guests')) {
            Storage::disk('public')->makeDirectory('guests');
        }

        $samplePhotoPath = 'guests/sample_guest.png';
        if (!Storage::disk('public')->exists($samplePhotoPath) && file_exists(public_path('images/ekraf.png'))) {
            Storage::disk('public')->put($samplePhotoPath, file_get_contents(public_path('images/ekraf.png')));
        }

        $samples = [
            [
                'name' => 'Bambang Pamungkas, M.Si',
                'phone' => '081234567890',
                'institution' => 'Dinas Pendidikan Jawa Barat',
                'person_to_meet' => 'Kepala Dinas',
                'purpose' => 'Koordinasi program pelatihan wirausaha muda kreatif tingkat daerah',
                'days_ago' => 0,
                'time' => '08:45:00',
                'division_index' => 0,
            ],
            [
                'name' => 'Siti Nurhaliza, S.E.',
                'phone' => '085712345678',
                'institution' => 'Bank BJB Cabang Majalengka',
                'person_to_meet' => 'Bendahara Sekretariat',
                'purpose' => 'Sosialisasi program permodalan dan fasilitas QRIS UMKM binaan',
                'days_ago' => 0,
                'time' => '09:30:00',
                'division_index' => 1,
            ],
            [
                'name' => 'Rahmat Hidayat',
                'phone' => '082198765432',
                'institution' => 'Komunitas Kreatif Wangkelang',
                'person_to_meet' => 'Kabid Ekonomi Kreatif',
                'purpose' => 'Audiensi penyelenggaraan pameran produk kriya dan digital festival 2026',
                'days_ago' => 0,
                'time' => '10:15:00',
                'division_index' => 2,
            ],
            [
                'name' => 'Dewi Lestari',
                'phone' => '081399887766',
                'institution' => 'Universitas Majalengka',
                'person_to_meet' => 'Staf Bidang Kebudayaan',
                'purpose' => 'Penyerahan surat izin penelitian kesenian tradisional dan kebudayaan daerah',
                'days_ago' => 1,
                'time' => '11:00:00',
                'division_index' => 3,
            ],
            [
                'name' => 'Ahmad Fauzi',
                'phone' => '087811223344',
                'institution' => 'PT Media Nusantara Citra',
                'person_to_meet' => 'Humas & Promosi',
                'purpose' => 'Konfirmasi liputan media expo produk unggulan lokal',
                'days_ago' => 2,
                'time' => '13:30:00',
                'division_index' => 4,
            ],
            [
                'name' => 'Hj. Endang Suryani',
                'phone' => '081900112233',
                'institution' => 'Kecamatan Cigasong',
                'person_to_meet' => 'Bagian Pelayanan',
                'purpose' => 'Konsultasi pelayanan data registrasi pelaku usaha ekonomi kreatif',
                'days_ago' => 3,
                'time' => '14:20:00',
                'division_index' => 5,
            ],
            [
                'name' => 'Ir. Hendra Gunawan',
                'phone' => '082233445566',
                'institution' => 'Bappeda Kabupaten Majalengka',
                'person_to_meet' => 'Sekretaris Dinas',
                'purpose' => 'Rapat sinkronisasi penyusunan rencana kerja tahunan',
                'days_ago' => 4,
                'time' => '09:10:00',
                'division_index' => 1,
            ],
        ];

        foreach ($samples as $sample) {
            $division = $divisions[$sample['division_index'] % $divisions->count()];
            GuestLog::create([
                'division_id' => $division->id,
                'name' => $sample['name'],
                'phone' => $sample['phone'],
                'institution' => $sample['institution'],
                'person_to_meet' => $sample['person_to_meet'],
                'purpose' => $sample['purpose'],
                'photo_path' => $samplePhotoPath,
                'visit_date' => Carbon::today()->subDays($sample['days_ago'])->toDateString(),
                'visit_time' => $sample['time'],
            ]);
        }
    }
}
