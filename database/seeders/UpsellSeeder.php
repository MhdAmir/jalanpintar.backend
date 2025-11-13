<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\Upsell;
use Illuminate\Database\Seeder;

class UpsellSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pendaftaranForm = Form::where('slug', 'pendaftaran-siswa-baru-2024-2025')->first();
        $lombaForm = Form::where('slug', 'lomba-karya-tulis-ilmiah-2024')->first();
        $codingForm = Form::where('slug', 'daftar-kelas-online-coding')->first();

        $upsells = [
            // Upsells for Pendaftaran Siswa
            [
                'form_id' => $pendaftaranForm->id,
                'name' => 'Buku Panduan Siswa',
                'description' => 'Buku panduan lengkap untuk siswa baru mencakup peraturan sekolah, tips belajar, dan informasi ekstrakurikuler',
                'price' => 75000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell1/400/400',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'form_id' => $pendaftaranForm->id,
                'name' => 'Seragam Olahraga',
                'description' => 'Seragam olahraga lengkap (2 set) berkualitas premium dengan logo sekolah',
                'price' => 250000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell2/400/400',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'form_id' => $pendaftaranForm->id,
                'name' => 'Tas Sekolah Premium',
                'description' => 'Tas sekolah bermerek dengan desain ergonomis dan tahan lama',
                'price' => 350000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell3/400/400',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'form_id' => $pendaftaranForm->id,
                'name' => 'Paket Alat Tulis Lengkap',
                'description' => 'Paket alat tulis lengkap untuk satu tahun ajaran (pensil, pulpen, penghapus, penggaris, dll)',
                'price' => 150000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell4/400/400',
                'is_active' => true,
                'order' => 4,
            ],

            // Upsells for Lomba
            [
                'form_id' => $lombaForm->id,
                'name' => 'Workshop Penulisan Karya Ilmiah',
                'description' => 'Workshop 2 hari dengan expert untuk meningkatkan kualitas karya tulis',
                'price' => 200000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell5/400/400',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'form_id' => $lombaForm->id,
                'name' => 'Review Karya oleh Juri',
                'description' => 'Mendapatkan feedback detail dari juri profesional sebelum submission final',
                'price' => 100000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell6/400/400',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'form_id' => $lombaForm->id,
                'name' => 'E-Book Panduan Karya Tulis Ilmiah',
                'description' => 'E-Book lengkap dengan template dan contoh karya tulis ilmiah terbaik',
                'price' => 50000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell7/400/400',
                'is_active' => true,
                'order' => 3,
            ],

            // Upsells for Coding Class
            [
                'form_id' => $codingForm->id,
                'name' => 'Private Mentoring Session (5 jam)',
                'description' => '5 sesi private mentoring 1-on-1 dengan instructor berpengalaman',
                'price' => 500000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell8/400/400',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'form_id' => $codingForm->id,
                'name' => 'Akses Lifetime ke Materi Premium',
                'description' => 'Akses selamanya ke semua materi video dan update konten terbaru',
                'price' => 300000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell9/400/400',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'form_id' => $codingForm->id,
                'name' => 'Starter Kit Developer',
                'description' => 'Lisensi software development tools (IDE, design tools) senilai Rp 2.000.000',
                'price' => 750000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell10/400/400',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'form_id' => $codingForm->id,
                'name' => 'Portfolio Website Development',
                'description' => 'Pembuatan portfolio website profesional untuk showcase project Anda',
                'price' => 400000.00,
                'currency' => 'IDR',
                'image' => 'https://picsum.photos/seed/upsell11/400/400',
                'is_active' => true,
                'order' => 4,
            ],
        ];

        foreach ($upsells as $upsell) {
            Upsell::create($upsell);
        }
    }
}
