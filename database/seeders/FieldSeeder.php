<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Section;
use Illuminate\Database\Seeder;

class FieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sections
        $sections = Section::with('form')->get()->keyBy(function ($section) {
            return $section->form->slug . '|' . $section->order;
        });

        $pendaftaranDataPribadi = $sections->get('pendaftaran-siswa-baru-2024-2025|1');
        $pendaftaranOrangTua = $sections->get('pendaftaran-siswa-baru-2024-2025|2');
        $pendaftaranRiwayat = $sections->get('pendaftaran-siswa-baru-2024-2025|3');
        $pendaftaranDokumen = $sections->get('pendaftaran-siswa-baru-2024-2025|4');

        $beasiswaIdentitas = $sections->get('beasiswa-prestasi-akademik-2024|1');
        $beasiswaPrestasi = $sections->get('beasiswa-prestasi-akademik-2024|2');
        $beasiswaEkonomi = $sections->get('beasiswa-prestasi-akademik-2024|3');

        $lombaData = $sections->get('lomba-karya-tulis-ilmiah-2024|1');
        $lombaKarya = $sections->get('lomba-karya-tulis-ilmiah-2024|2');

        $codingInfo = $sections->get('daftar-kelas-online-coding|1');
        $codingBackground = $sections->get('daftar-kelas-online-coding|2');

        $fields = [
            // Data Pribadi - Pendaftaran Siswa
            [
                'section_id' => $pendaftaranDataPribadi->id,
                'label' => 'Nama Lengkap',
                'name' => 'nama_lengkap',
                'type' => 'text',
                'placeholder' => 'Masukkan nama lengkap sesuai KTP/Akta',
                'help_text' => 'Tulis nama lengkap tanpa gelar',
                'is_required' => true,
                'order' => 1,
                'validation_rules' => ['min:3', 'max:100'],
            ],
            [
                'section_id' => $pendaftaranDataPribadi->id,
                'label' => 'NIK (Nomor Induk Kependudukan)',
                'name' => 'nik',
                'type' => 'number',
                'placeholder' => '16 digit NIK',
                'is_required' => true,
                'order' => 2,
                'validation_rules' => ['digits:16'],
            ],
            [
                'section_id' => $pendaftaranDataPribadi->id,
                'label' => 'Jenis Kelamin',
                'name' => 'jenis_kelamin',
                'type' => 'select',
                'is_required' => true,
                'order' => 3,
                'options' => [
                    ['value' => 'L', 'label' => 'Laki-laki'],
                    ['value' => 'P', 'label' => 'Perempuan'],
                ],
            ],
            [
                'section_id' => $pendaftaranDataPribadi->id,
                'label' => 'Tanggal Lahir',
                'name' => 'tanggal_lahir',
                'type' => 'date',
                'is_required' => true,
                'order' => 4,
            ],
            [
                'section_id' => $pendaftaranDataPribadi->id,
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
                'placeholder' => 'email@example.com',
                'is_required' => true,
                'order' => 5,
                'validation_rules' => ['email'],
            ],
            [
                'section_id' => $pendaftaranDataPribadi->id,
                'label' => 'Nomor Telepon / WhatsApp',
                'name' => 'telepon',
                'type' => 'phone',
                'placeholder' => '08xxxxxxxxxx',
                'is_required' => true,
                'order' => 6,
                'validation_rules' => ['regex:/^08[0-9]{8,11}$/'],
            ],
            [
                'section_id' => $pendaftaranDataPribadi->id,
                'label' => 'Alamat Lengkap',
                'name' => 'alamat',
                'type' => 'textarea',
                'placeholder' => 'Jalan, RT/RW, Kelurahan, Kecamatan',
                'is_required' => true,
                'order' => 7,
                'validation_rules' => ['min:10'],
            ],
            [
                'section_id' => $pendaftaranDataPribadi->id,
                'label' => 'Provinsi',
                'name' => 'provinsi',
                'type' => 'select',
                'is_required' => true,
                'order' => 8,
                'options' => [
                    ['value' => 'jawa_barat', 'label' => 'Jawa Barat'],
                    ['value' => 'jawa_tengah', 'label' => 'Jawa Tengah'],
                    ['value' => 'jawa_timur', 'label' => 'Jawa Timur'],
                    ['value' => 'dki_jakarta', 'label' => 'DKI Jakarta'],
                    ['value' => 'banten', 'label' => 'Banten'],
                ],
            ],

            // Data Orang Tua - Pendaftaran Siswa
            [
                'section_id' => $pendaftaranOrangTua->id,
                'label' => 'Nama Ayah',
                'name' => 'nama_ayah',
                'type' => 'text',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'section_id' => $pendaftaranOrangTua->id,
                'label' => 'Nama Ibu',
                'name' => 'nama_ibu',
                'type' => 'text',
                'is_required' => true,
                'order' => 2,
            ],
            [
                'section_id' => $pendaftaranOrangTua->id,
                'label' => 'Pekerjaan Orang Tua',
                'name' => 'pekerjaan_ortu',
                'type' => 'select',
                'is_required' => true,
                'order' => 3,
                'options' => [
                    ['value' => 'pns', 'label' => 'PNS'],
                    ['value' => 'swasta', 'label' => 'Karyawan Swasta'],
                    ['value' => 'wiraswasta', 'label' => 'Wiraswasta'],
                    ['value' => 'petani', 'label' => 'Petani'],
                    ['value' => 'buruh', 'label' => 'Buruh'],
                    ['value' => 'lainnya', 'label' => 'Lainnya'],
                ],
            ],
            [
                'section_id' => $pendaftaranOrangTua->id,
                'label' => 'Penghasilan Orang Tua per Bulan',
                'name' => 'penghasilan_ortu',
                'type' => 'select',
                'is_required' => true,
                'order' => 4,
                'options' => [
                    ['value' => '<1jt', 'label' => '< Rp 1.000.000'],
                    ['value' => '1-3jt', 'label' => 'Rp 1.000.000 - Rp 3.000.000'],
                    ['value' => '3-5jt', 'label' => 'Rp 3.000.000 - Rp 5.000.000'],
                    ['value' => '5-10jt', 'label' => 'Rp 5.000.000 - Rp 10.000.000'],
                    ['value' => '>10jt', 'label' => '> Rp 10.000.000'],
                ],
            ],
            [
                'section_id' => $pendaftaranOrangTua->id,
                'label' => 'Nomor Telepon Orang Tua',
                'name' => 'telepon_ortu',
                'type' => 'phone',
                'is_required' => true,
                'order' => 5,
            ],

            // Riwayat Pendidikan
            [
                'section_id' => $pendaftaranRiwayat->id,
                'label' => 'Asal Sekolah',
                'name' => 'asal_sekolah',
                'type' => 'text',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'section_id' => $pendaftaranRiwayat->id,
                'label' => 'NISN',
                'name' => 'nisn',
                'type' => 'number',
                'is_required' => true,
                'order' => 2,
                'validation_rules' => ['digits:10'],
            ],
            [
                'section_id' => $pendaftaranRiwayat->id,
                'label' => 'Tahun Lulus',
                'name' => 'tahun_lulus',
                'type' => 'number',
                'is_required' => true,
                'order' => 3,
                'validation_rules' => ['digits:4', 'min:2000', 'max:2024'],
            ],
            [
                'section_id' => $pendaftaranRiwayat->id,
                'label' => 'Nilai Rata-rata Rapor',
                'name' => 'nilai_rapor',
                'type' => 'number',
                'placeholder' => 'Contoh: 85.5',
                'is_required' => true,
                'order' => 4,
                'validation_rules' => ['numeric', 'min:0', 'max:100'],
            ],

            // Dokumen
            [
                'section_id' => $pendaftaranDokumen->id,
                'label' => 'Upload Foto',
                'name' => 'foto',
                'type' => 'file',
                'help_text' => 'Format: JPG/PNG, Maksimal 2MB, background merah',
                'is_required' => true,
                'order' => 1,
                'validation_rules' => ['mimes:jpg,jpeg,png', 'max:2048'],
            ],
            [
                'section_id' => $pendaftaranDokumen->id,
                'label' => 'Upload KTP',
                'name' => 'ktp',
                'type' => 'file',
                'help_text' => 'Format: JPG/PNG/PDF, Maksimal 2MB',
                'is_required' => true,
                'order' => 2,
                'validation_rules' => ['mimes:jpg,jpeg,png,pdf', 'max:2048'],
            ],
            [
                'section_id' => $pendaftaranDokumen->id,
                'label' => 'Upload Ijazah',
                'name' => 'ijazah',
                'type' => 'file',
                'help_text' => 'Format: PDF, Maksimal 5MB',
                'is_required' => true,
                'order' => 3,
                'validation_rules' => ['mimes:pdf', 'max:5120'],
            ],

            // Beasiswa - Identitas
            [
                'section_id' => $beasiswaIdentitas->id,
                'label' => 'Nama Lengkap',
                'name' => 'nama',
                'type' => 'text',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'section_id' => $beasiswaIdentitas->id,
                'label' => 'NIM/NIS',
                'name' => 'nim',
                'type' => 'text',
                'is_required' => true,
                'order' => 2,
            ],
            [
                'section_id' => $beasiswaIdentitas->id,
                'label' => 'Program Studi',
                'name' => 'prodi',
                'type' => 'text',
                'is_required' => true,
                'order' => 3,
            ],

            // Beasiswa - Prestasi
            [
                'section_id' => $beasiswaPrestasi->id,
                'label' => 'IPK/Nilai Rata-rata',
                'name' => 'ipk',
                'type' => 'number',
                'is_required' => true,
                'order' => 1,
                'validation_rules' => ['numeric', 'min:0', 'max:4'],
            ],
            [
                'section_id' => $beasiswaPrestasi->id,
                'label' => 'Prestasi yang Pernah Diraih',
                'name' => 'prestasi',
                'type' => 'textarea',
                'help_text' => 'Tuliskan prestasi akademik atau non-akademik',
                'is_required' => true,
                'order' => 2,
            ],
            [
                'section_id' => $beasiswaPrestasi->id,
                'label' => 'Upload Sertifikat Prestasi',
                'name' => 'sertifikat',
                'type' => 'file',
                'is_required' => false,
                'order' => 3,
                'validation_rules' => ['mimes:pdf,jpg,jpeg,png', 'max:5120'],
            ],

            // Beasiswa - Ekonomi
            [
                'section_id' => $beasiswaEkonomi->id,
                'label' => 'Penghasilan Orang Tua',
                'name' => 'penghasilan',
                'type' => 'select',
                'is_required' => true,
                'order' => 1,
                'options' => [
                    ['value' => '<1jt', 'label' => '< Rp 1.000.000'],
                    ['value' => '1-2jt', 'label' => 'Rp 1.000.000 - Rp 2.000.000'],
                    ['value' => '2-5jt', 'label' => 'Rp 2.000.000 - Rp 5.000.000'],
                    ['value' => '>5jt', 'label' => '> Rp 5.000.000'],
                ],
            ],
            [
                'section_id' => $beasiswaEkonomi->id,
                'label' => 'Jumlah Tanggungan Keluarga',
                'name' => 'tanggungan',
                'type' => 'number',
                'is_required' => true,
                'order' => 2,
                'validation_rules' => ['integer', 'min:1'],
            ],
            [
                'section_id' => $beasiswaEkonomi->id,
                'label' => 'Alasan Membutuhkan Beasiswa',
                'name' => 'alasan',
                'type' => 'textarea',
                'is_required' => true,
                'order' => 3,
                'validation_rules' => ['min:50'],
            ],

            // Lomba - Data Peserta
            [
                'section_id' => $lombaData->id,
                'label' => 'Nama Lengkap',
                'name' => 'nama',
                'type' => 'text',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'section_id' => $lombaData->id,
                'label' => 'Institusi/Universitas',
                'name' => 'institusi',
                'type' => 'text',
                'is_required' => true,
                'order' => 2,
            ],
            [
                'section_id' => $lombaData->id,
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
                'is_required' => true,
                'order' => 3,
            ],
            [
                'section_id' => $lombaData->id,
                'label' => 'Nomor WhatsApp',
                'name' => 'whatsapp',
                'type' => 'phone',
                'is_required' => true,
                'order' => 4,
            ],

            // Lomba - Karya
            [
                'section_id' => $lombaKarya->id,
                'label' => 'Judul Karya Tulis',
                'name' => 'judul',
                'type' => 'text',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'section_id' => $lombaKarya->id,
                'label' => 'Abstrak',
                'name' => 'abstrak',
                'type' => 'textarea',
                'help_text' => 'Maksimal 500 kata',
                'is_required' => true,
                'order' => 2,
                'validation_rules' => ['max:3000'],
            ],
            [
                'section_id' => $lombaKarya->id,
                'label' => 'Upload Karya Tulis (PDF)',
                'name' => 'karya_pdf',
                'type' => 'file',
                'help_text' => 'Format PDF, maksimal 10MB',
                'is_required' => true,
                'order' => 3,
                'validation_rules' => ['mimes:pdf', 'max:10240'],
            ],

            // Coding - Info
            [
                'section_id' => $codingInfo->id,
                'label' => 'Nama Lengkap',
                'name' => 'nama',
                'type' => 'text',
                'is_required' => true,
                'order' => 1,
            ],
            [
                'section_id' => $codingInfo->id,
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
                'is_required' => true,
                'order' => 2,
            ],
            [
                'section_id' => $codingInfo->id,
                'label' => 'Nomor WhatsApp',
                'name' => 'whatsapp',
                'type' => 'phone',
                'is_required' => true,
                'order' => 3,
            ],
            [
                'section_id' => $codingInfo->id,
                'label' => 'Usia',
                'name' => 'usia',
                'type' => 'number',
                'is_required' => true,
                'order' => 4,
                'validation_rules' => ['integer', 'min:10', 'max:100'],
            ],

            // Coding - Background
            [
                'section_id' => $codingBackground->id,
                'label' => 'Pengalaman Programming',
                'name' => 'pengalaman',
                'type' => 'select',
                'is_required' => true,
                'order' => 1,
                'options' => [
                    ['value' => 'pemula', 'label' => 'Pemula (belum pernah coding)'],
                    ['value' => 'menengah', 'label' => 'Menengah (pernah belajar dasar)'],
                    ['value' => 'mahir', 'label' => 'Mahir (sudah membuat project)'],
                ],
            ],
            [
                'section_id' => $codingBackground->id,
                'label' => 'Bahasa Pemrograman yang Ingin Dipelajari',
                'name' => 'bahasa',
                'type' => 'checkbox',
                'is_required' => true,
                'order' => 2,
                'options' => [
                    ['value' => 'python', 'label' => 'Python'],
                    ['value' => 'javascript', 'label' => 'JavaScript'],
                    ['value' => 'java', 'label' => 'Java'],
                    ['value' => 'php', 'label' => 'PHP'],
                    ['value' => 'golang', 'label' => 'Go'],
                ],
            ],
            [
                'section_id' => $codingBackground->id,
                'label' => 'Motivasi Belajar Coding',
                'name' => 'motivasi',
                'type' => 'textarea',
                'help_text' => 'Ceritakan tujuan dan motivasi Anda belajar coding',
                'is_required' => true,
                'order' => 3,
                'validation_rules' => ['min:50'],
            ],
            [
                'section_id' => $codingBackground->id,
                'label' => 'Jadwal Belajar yang Diinginkan',
                'name' => 'jadwal',
                'type' => 'select',
                'is_required' => true,
                'order' => 4,
                'options' => [
                    ['value' => 'weekday_pagi', 'label' => 'Weekday Pagi (08:00 - 12:00)'],
                    ['value' => 'weekday_siang', 'label' => 'Weekday Siang (13:00 - 17:00)'],
                    ['value' => 'weekday_malam', 'label' => 'Weekday Malam (19:00 - 21:00)'],
                    ['value' => 'weekend', 'label' => 'Weekend (Sabtu/Minggu)'],
                ],
            ],
        ];

        foreach ($fields as $field) {
            Field::create($field);
        }
    }
}
