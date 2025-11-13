<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pendaftaranForm = Form::where('slug', 'pendaftaran-siswa-baru-2024-2025')->first();
        $beasiswaForm = Form::where('slug', 'beasiswa-prestasi-akademik-2024')->first();
        $lombaForm = Form::where('slug', 'lomba-karya-tulis-ilmiah-2024')->first();
        $codingForm = Form::where('slug', 'daftar-kelas-online-coding')->first();

        $sections = [
            // Sections for Pendaftaran Siswa
            [
                'form_id' => $pendaftaranForm->id,
                'title' => 'Data Pribadi',
                'description' => 'Isi data pribadi calon siswa dengan lengkap',
                'order' => 1,
            ],
            [
                'form_id' => $pendaftaranForm->id,
                'title' => 'Data Orang Tua / Wali',
                'description' => 'Isi data orang tua atau wali yang bertanggung jawab',
                'order' => 2,
            ],
            [
                'form_id' => $pendaftaranForm->id,
                'title' => 'Riwayat Pendidikan',
                'description' => 'Isi riwayat pendidikan terakhir',
                'order' => 3,
            ],
            [
                'form_id' => $pendaftaranForm->id,
                'title' => 'Dokumen Pendukung',
                'description' => 'Upload dokumen yang diperlukan',
                'order' => 4,
            ],

            // Sections for Beasiswa
            [
                'form_id' => $beasiswaForm->id,
                'title' => 'Identitas Pemohon',
                'description' => 'Data identitas pemohon beasiswa',
                'order' => 1,
            ],
            [
                'form_id' => $beasiswaForm->id,
                'title' => 'Prestasi Akademik',
                'description' => 'Rincian prestasi akademik yang dimiliki',
                'order' => 2,
            ],
            [
                'form_id' => $beasiswaForm->id,
                'title' => 'Kondisi Ekonomi',
                'description' => 'Informasi kondisi ekonomi keluarga',
                'order' => 3,
            ],

            // Sections for Lomba
            [
                'form_id' => $lombaForm->id,
                'title' => 'Data Peserta',
                'description' => 'Informasi peserta lomba',
                'order' => 1,
            ],
            [
                'form_id' => $lombaForm->id,
                'title' => 'Karya Ilmiah',
                'description' => 'Upload dan detail karya tulis ilmiah',
                'order' => 2,
            ],

            // Sections for Coding Class
            [
                'form_id' => $codingForm->id,
                'title' => 'Informasi Pendaftar',
                'description' => 'Data diri pendaftar kelas coding',
                'order' => 1,
            ],
            [
                'form_id' => $codingForm->id,
                'title' => 'Latar Belakang & Minat',
                'description' => 'Pengalaman dan minat di bidang programming',
                'order' => 2,
            ],
        ];

        foreach ($sections as $section) {
            Section::create($section);
        }
    }
}
