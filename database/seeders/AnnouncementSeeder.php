<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Form;
use App\Models\Submission;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
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

        // Get some submissions
        $pendaftaranSubmission1 = Submission::where('form_id', $pendaftaranForm->id)
            ->where('submission_number', 'PSBA-2024-0001')->first();
        $pendaftaranSubmission2 = Submission::where('form_id', $pendaftaranForm->id)
            ->where('submission_number', 'PSBA-2024-0003')->first();

        $beasiswaSubmission1 = Submission::where('form_id', $beasiswaForm->id)
            ->where('submission_number', 'BPA-2024-0001')->first();

        $lombaSubmission1 = Submission::where('form_id', $lombaForm->id)
            ->where('submission_number', 'LKTI-2024-0001')->first();

        $codingSubmission1 = Submission::where('form_id', $codingForm->id)
            ->where('submission_number', 'CODE-2024-0001')->first();
        $codingSubmission2 = Submission::where('form_id', $codingForm->id)
            ->where('submission_number', 'CODE-2024-0002')->first();

        $announcements = [
            // Pendaftaran Siswa - Diterima
            [
                'form_id' => $pendaftaranForm->id,
                'submission_id' => $pendaftaranSubmission1->id,
                'identifier' => 'PSBA-2024-0001',
                'name' => 'Andi Pratama Wijaya',
                'status' => 'accepted',
                'result_data' => [
                    'keterangan' => 'DITERIMA',
                    'kelas' => 'X IPA 1',
                    'jalur' => 'Reguler',
                    'catatan' => 'Selamat! Anda diterima sebagai siswa baru.',
                    'langkah_selanjutnya' => [
                        'Daftar ulang di sekolah (5-10 Januari 2025)',
                        'Membayar biaya pendidikan',
                        'Mengikuti Masa Orientasi Siswa',
                    ],
                ],
                'notes' => 'Siswa berprestasi dengan nilai rapor sangat baik',
                'announced_at' => now()->subDays(2),
            ],
            [
                'form_id' => $pendaftaranForm->id,
                'submission_id' => $pendaftaranSubmission2->id,
                'identifier' => 'PSBA-2024-0003',
                'name' => 'Rudi Hartono',
                'status' => 'accepted',
                'result_data' => [
                    'keterangan' => 'DITERIMA',
                    'kelas' => 'X IPA 2',
                    'jalur' => 'Reguler',
                ],
                'announced_at' => now()->subDays(2),
            ],

            // Beasiswa - Lolos Seleksi
            [
                'form_id' => $beasiswaForm->id,
                'submission_id' => $beasiswaSubmission1->id,
                'identifier' => 'BPA-2024-0001',
                'name' => 'Fitri Handayani',
                'status' => 'accepted',
                'result_data' => [
                    'keterangan' => 'LOLOS SELEKSI',
                    'jenis_beasiswa' => 'Beasiswa Prestasi Penuh',
                    'nilai_beasiswa' => 'Rp 20.000.000 per tahun',
                    'periode' => '2 tahun (4 semester)',
                    'syarat' => [
                        'IPK minimal 3.5 per semester',
                        'Aktif dalam kegiatan kampus',
                        'Membuat laporan penggunaan beasiswa setiap semester',
                    ],
                ],
                'notes' => 'Prestasi akademik sangat menonjol',
                'announced_at' => now()->subDays(3),
            ],

            // Lomba - Juara
            [
                'form_id' => $lombaForm->id,
                'submission_id' => $lombaSubmission1->id,
                'identifier' => 'LKTI-2024-0001',
                'name' => 'Muhammad Fauzi',
                'status' => 'accepted',
                'result_data' => [
                    'keterangan' => 'JUARA 2',
                    'hadiah' => [
                        'uang' => 'Rp 5.000.000',
                        'piala' => 'Piala Juara 2',
                        'sertifikat' => 'Sertifikat Nasional',
                    ],
                    'nilai_karya' => [
                        'originalitas' => 85,
                        'metodologi' => 88,
                        'presentasi' => 90,
                        'total' => 87.67,
                    ],
                    'catatan_juri' => 'Karya yang sangat baik dengan metodologi penelitian yang solid',
                ],
                'notes' => 'Karya terbaik kedua dari 150 peserta',
                'announced_at' => now()->subDay(),
            ],

            // Coding - Class Placement
            [
                'form_id' => $codingForm->id,
                'submission_id' => $codingSubmission1->id,
                'identifier' => 'CODE-2024-0001',
                'name' => 'Dimas Anggara',
                'status' => 'accepted',
                'result_data' => [
                    'keterangan' => 'KELAS DIMULAI',
                    'batch' => 'Batch 15 - Beginner',
                    'tanggal_mulai' => '15 Januari 2025',
                    'jadwal' => 'Senin, Rabu, Jumat (19:00 - 21:00 WIB)',
                    'instruktur' => 'Budi Santoso, S.Kom., M.T.',
                    'link_kelas' => 'https://zoom.us/j/123456789',
                    'grup_whatsapp' => 'https://chat.whatsapp.com/xxxxx',
                ],
                'announced_at' => now()->subHours(6),
            ],
            [
                'form_id' => $codingForm->id,
                'submission_id' => $codingSubmission2->id,
                'identifier' => 'CODE-2024-0002',
                'name' => 'Lina Marlina',
                'status' => 'accepted',
                'result_data' => [
                    'keterangan' => 'KELAS DIMULAI',
                    'batch' => 'Batch 12 - Intermediate',
                    'tanggal_mulai' => '10 Januari 2025',
                    'jadwal' => 'Selasa, Kamis, Sabtu (19:00 - 21:00 WIB)',
                    'instruktur' => 'Ahmad Fauzi, S.T., M.Kom.',
                    'link_kelas' => 'https://zoom.us/j/987654321',
                    'grup_whatsapp' => 'https://chat.whatsapp.com/yyyyy',
                ],
                'announced_at' => now()->subHours(6),
            ],

            // Additional announcements for rejected/pending status
            [
                'form_id' => $pendaftaranForm->id,
                'submission_id' => null,
                'identifier' => 'PSBA-2024-0099',
                'name' => 'Test Pending Student',
                'status' => 'pending',
                'result_data' => [
                    'keterangan' => 'DALAM PROSES VERIFIKASI',
                    'catatan' => 'Mohon menunggu hasil seleksi',
                ],
                'announced_at' => null,
            ],
            [
                'form_id' => $beasiswaForm->id,
                'submission_id' => null,
                'identifier' => 'BPA-2024-0099',
                'name' => 'Test Rejected Applicant',
                'status' => 'rejected',
                'result_data' => [
                    'keterangan' => 'TIDAK LOLOS SELEKSI',
                    'alasan' => 'Kuota telah terpenuhi. Tetap semangat untuk kesempatan berikutnya!',
                ],
                'notes' => 'Tidak memenuhi kriteria minimum',
                'announced_at' => now()->subDays(4),
            ],
        ];

        foreach ($announcements as $announcement) {
            Announcement::create($announcement);
        }
    }
}
