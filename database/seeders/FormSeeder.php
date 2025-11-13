<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Form;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pendaftaranCategory = Category::where('slug', 'pendaftaran-siswa')->first();
        $beasiswaCategory = Category::where('slug', 'beasiswa')->first();
        $eventCategory = Category::where('slug', 'event-kompetisi')->first();

        $forms = [
            [
                'category_id' => $pendaftaranCategory->id,
                'title' => 'Pendaftaran Siswa Baru 2024/2025',
                'slug' => 'pendaftaran-siswa-baru-2024-2025',
                'description' => 'Formulir pendaftaran siswa baru tahun ajaran 2024/2025. Silakan isi dengan lengkap dan benar.',
                'cover_image' => 'https://picsum.photos/seed/form1/800/400',
                'enable_payment' => true,
                'enable_affiliate' => true,
                'is_active' => true,
                'published_at' => now(),
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'max_submissions' => 1000,
                'settings' => [
                    'show_progress' => true,
                    'allow_draft' => true,
                    'require_login' => false,
                    'send_confirmation_email' => true,
                    'custom_success_message' => 'Terima kasih telah mendaftar!',
                ],
            ],
            [
                'category_id' => $beasiswaCategory->id,
                'title' => 'Beasiswa Prestasi Akademik 2024',
                'slug' => 'beasiswa-prestasi-akademik-2024',
                'description' => 'Program beasiswa untuk siswa berprestasi di bidang akademik.',
                'cover_image' => 'https://picsum.photos/seed/form2/800/400',
                'enable_payment' => false,
                'enable_affiliate' => false,
                'is_active' => true,
                'published_at' => now(),
                'start_date' => now(),
                'end_date' => now()->addMonths(2),
                'max_submissions' => 500,
                'settings' => [
                    'show_progress' => true,
                    'allow_draft' => true,
                    'require_login' => true,
                    'send_confirmation_email' => true,
                ],
            ],
            [
                'category_id' => $eventCategory->id,
                'title' => 'Lomba Karya Tulis Ilmiah 2024',
                'slug' => 'lomba-karya-tulis-ilmiah-2024',
                'description' => 'Pendaftaran lomba karya tulis ilmiah tingkat nasional.',
                'cover_image' => 'https://picsum.photos/seed/form3/800/400',
                'enable_payment' => true,
                'enable_affiliate' => true,
                'is_active' => true,
                'published_at' => now(),
                'start_date' => now(),
                'end_date' => now()->addMonth(),
                'max_submissions' => 300,
                'settings' => [
                    'show_progress' => true,
                    'allow_draft' => false,
                    'require_login' => true,
                    'send_confirmation_email' => true,
                ],
            ],
            [
                'category_id' => $pendaftaranCategory->id,
                'title' => 'Daftar Kelas Online Coding',
                'slug' => 'daftar-kelas-online-coding',
                'description' => 'Pendaftaran kelas online belajar coding untuk pemula.',
                'cover_image' => 'https://picsum.photos/seed/form4/800/400',
                'enable_payment' => true,
                'enable_affiliate' => true,
                'is_active' => true,
                'published_at' => now(),
                'start_date' => now(),
                'end_date' => now()->addMonths(6),
                'max_submissions' => null,
                'settings' => [
                    'show_progress' => true,
                    'allow_draft' => true,
                    'require_login' => false,
                    'send_confirmation_email' => true,
                ],
            ],
        ];

        foreach ($forms as $formData) {
            Form::create($formData);
        }
    }
}
