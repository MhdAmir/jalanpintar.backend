<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\PricingTier;
use Illuminate\Database\Seeder;

class PricingTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pendaftaranForm = Form::where('slug', 'pendaftaran-siswa-baru-2024-2025')->first();
        $lombaForm = Form::where('slug', 'lomba-karya-tulis-ilmiah-2024')->first();
        $codingForm = Form::where('slug', 'daftar-kelas-online-coding')->first();

        $pricingTiers = [
            // Pendaftaran Siswa
            [
                'form_id' => $pendaftaranForm->id,
                'name' => 'Biaya Pendaftaran Regular',
                'description' => 'Biaya pendaftaran standar untuk calon siswa baru',
                'price' => 500000.00,
                'currency' => 'IDR',
                'is_active' => true,
                'order' => 1,
                'features' => [
                    'Formulir pendaftaran',
                    'Tes seleksi',
                    'Wawancara',
                    'Sertifikat peserta',
                ],
            ],
            [
                'form_id' => $pendaftaranForm->id,
                'name' => 'Biaya Pendaftaran Early Bird',
                'description' => 'Diskon untuk pendaftar awal (30 hari pertama)',
                'price' => 350000.00,
                'currency' => 'IDR',
                'is_active' => true,
                'order' => 2,
                'features' => [
                    'Formulir pendaftaran',
                    'Tes seleksi',
                    'Wawancara',
                    'Sertifikat peserta',
                    'Diskon 30%',
                    'Prioritas jadwal tes',
                ],
            ],

            // Lomba Karya Tulis
            [
                'form_id' => $lombaForm->id,
                'name' => 'Biaya Pendaftaran Individu',
                'description' => 'Pendaftaran untuk peserta individu',
                'price' => 150000.00,
                'currency' => 'IDR',
                'is_active' => true,
                'order' => 1,
                'features' => [
                    'Akses materi lomba',
                    'E-certificate peserta',
                    'Konsultasi dengan mentor',
                ],
            ],
            [
                'form_id' => $lombaForm->id,
                'name' => 'Biaya Pendaftaran Tim (3 orang)',
                'description' => 'Pendaftaran untuk tim berisi 3 orang',
                'price' => 400000.00,
                'currency' => 'IDR',
                'is_active' => true,
                'order' => 2,
                'features' => [
                    'Akses materi lomba',
                    'E-certificate untuk semua anggota',
                    'Konsultasi dengan mentor',
                    'Hemat Rp 50.000',
                ],
            ],

            // Kelas Coding
            [
                'form_id' => $codingForm->id,
                'name' => 'Paket Basic (1 Bulan)',
                'description' => 'Akses kelas coding selama 1 bulan',
                'price' => 250000.00,
                'currency' => 'IDR',
                'is_active' => true,
                'order' => 1,
                'features' => [
                    '8 sesi live class',
                    'Materi video on-demand',
                    'Quiz & latihan',
                    'Sertifikat penyelesaian',
                ],
            ],
            [
                'form_id' => $codingForm->id,
                'name' => 'Paket Intermediate (3 Bulan)',
                'description' => 'Akses kelas coding selama 3 bulan',
                'price' => 650000.00,
                'currency' => 'IDR',
                'is_active' => true,
                'order' => 2,
                'features' => [
                    '24 sesi live class',
                    'Materi video on-demand',
                    'Quiz & latihan',
                    'Project akhir',
                    'Mentoring 1-on-1',
                    'Sertifikat penyelesaian',
                    'Hemat Rp 100.000',
                ],
            ],
            [
                'form_id' => $codingForm->id,
                'name' => 'Paket Advanced (6 Bulan)',
                'description' => 'Akses kelas coding selama 6 bulan dengan full support',
                'price' => 1200000.00,
                'currency' => 'IDR',
                'is_active' => true,
                'order' => 3,
                'features' => [
                    '48 sesi live class',
                    'Materi video on-demand',
                    'Quiz & latihan',
                    'Multiple projects',
                    'Unlimited mentoring',
                    'Job placement support',
                    'Sertifikat profesional',
                    'Hemat Rp 300.000',
                ],
            ],
        ];

        foreach ($pricingTiers as $tier) {
            PricingTier::create($tier);
        }
    }
}
