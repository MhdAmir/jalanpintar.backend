<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pendaftaran Siswa',
                'slug' => 'pendaftaran-siswa',
                'description' => 'Formulir untuk pendaftaran siswa baru',
                'icon' => 'school',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Beasiswa',
                'slug' => 'beasiswa',
                'description' => 'Formulir pengajuan beasiswa',
                'icon' => 'award',
                'color' => '#10B981',
                'is_active' => true,
            ],
            [
                'name' => 'Event & Kompetisi',
                'slug' => 'event-kompetisi',
                'description' => 'Pendaftaran event dan kompetisi',
                'icon' => 'trophy',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => 'Survey',
                'slug' => 'survey',
                'description' => 'Formulir survey dan kuesioner',
                'icon' => 'clipboard',
                'color' => '#8B5CF6',
                'is_active' => true,
            ],
            [
                'name' => 'Lowongan Kerja',
                'slug' => 'lowongan-kerja',
                'description' => 'Formulir lamaran pekerjaan',
                'icon' => 'briefcase',
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => 'Kontak & Layanan',
                'slug' => 'kontak-layanan',
                'description' => 'Formulir kontak dan permintaan layanan',
                'icon' => 'mail',
                'color' => '#6366F1',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
