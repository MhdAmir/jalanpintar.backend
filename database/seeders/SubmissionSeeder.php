<?php

namespace Database\Seeders;

use App\Models\AffiliateReward;
use App\Models\Form;
use App\Models\PricingTier;
use App\Models\Submission;
use App\Models\Upsell;
use Illuminate\Database\Seeder;

class SubmissionSeeder extends Seeder
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

        // Get pricing tiers
        $pendaftaranTier = PricingTier::where('form_id', $pendaftaranForm->id)->first();
        $lombaTier = PricingTier::where('form_id', $lombaForm->id)->first();
        $codingBasic = PricingTier::where('form_id', $codingForm->id)->where('order', 1)->first();
        $codingInter = PricingTier::where('form_id', $codingForm->id)->where('order', 2)->first();
        $codingAdv = PricingTier::where('form_id', $codingForm->id)->where('order', 3)->first();

        // Get affiliates
        $ahmadAffiliate = AffiliateReward::where('affiliate_code', 'AHMAD2024')
            ->where('form_id', $pendaftaranForm->id)->first();
        $sitiAffiliate = AffiliateReward::where('affiliate_code', 'SITI10')
            ->where('form_id', $lombaForm->id)->first();
        $budiAffiliate = AffiliateReward::where('affiliate_code', 'BUDI123')
            ->where('form_id', $codingForm->id)->first();

        // Get upsells
        $pendaftaranUpsells = Upsell::where('form_id', $pendaftaranForm->id)->limit(2)->pluck('id')->toArray();
        $codingUpsells = Upsell::where('form_id', $codingForm->id)->limit(2)->pluck('id')->toArray();

        $submissions = [
            // Pendaftaran Siswa - Submissions with payment
            [
                'form_id' => $pendaftaranForm->id,
                'submission_number' => 'PSBA-2024-0001',
                'name' => 'Andi Pratama',
                'email' => 'andi.pratama@email.com',
                'phone' => '081234567890',
                'data' => [
                    'nama_lengkap' => 'Andi Pratama Wijaya',
                    'nik' => '3201012001010001',
                    'jenis_kelamin' => 'L',
                    'tanggal_lahir' => '2001-01-20',
                    'email' => 'andi.pratama@email.com',
                    'telepon' => '081234567890',
                    'alamat' => 'Jl. Merdeka No. 123, RT 01/RW 05',
                    'provinsi' => 'jawa_barat',
                    'nama_ayah' => 'Bambang Wijaya',
                    'nama_ibu' => 'Siti Nurhaliza',
                    'pekerjaan_ortu' => 'swasta',
                    'penghasilan_ortu' => '3-5jt',
                    'telepon_ortu' => '081234567899',
                    'asal_sekolah' => 'SMP Negeri 1 Bandung',
                    'nisn' => '0012345678',
                    'tahun_lulus' => '2023',
                    'nilai_rapor' => '85.5',
                ],
                'pricing_tier_id' => $pendaftaranTier->id,
                'selected_upsells' => $pendaftaranUpsells,
                'affiliate_reward_id' => $ahmadAffiliate->id,
                'tier_amount' => 500000.00,
                'upsells_amount' => 325000.00,
                'affiliate_amount' => 82500.00,
                'total_amount' => 825000.00,
                'payment_status' => 'paid',
                'payment_method' => 'bank_transfer',
                'paid_at' => now()->subDays(5),
                'status' => 'approved',
                'submitted_at' => now()->subDays(5),
            ],
            [
                'form_id' => $pendaftaranForm->id,
                'submission_number' => 'PSBA-2024-0002',
                'name' => 'Sinta Dewi',
                'email' => 'sinta.dewi@email.com',
                'phone' => '082345678901',
                'data' => [
                    'nama_lengkap' => 'Sinta Dewi Lestari',
                    'nik' => '3201012002020002',
                    'jenis_kelamin' => 'P',
                    'tanggal_lahir' => '2002-02-15',
                    'email' => 'sinta.dewi@email.com',
                    'telepon' => '082345678901',
                    'alamat' => 'Jl. Sudirman No. 456, RT 02/RW 03',
                    'provinsi' => 'dki_jakarta',
                ],
                'pricing_tier_id' => $pendaftaranTier->id,
                'tier_amount' => 500000.00,
                'total_amount' => 500000.00,
                'payment_status' => 'pending',
                'status' => 'pending',
                'submitted_at' => now()->subDays(2),
            ],
            [
                'form_id' => $pendaftaranForm->id,
                'submission_number' => 'PSBA-2024-0003',
                'name' => 'Rudi Hartono',
                'email' => 'rudi.hartono@email.com',
                'phone' => '083456789012',
                'data' => [
                    'nama_lengkap' => 'Rudi Hartono',
                    'nik' => '3301012003030003',
                    'jenis_kelamin' => 'L',
                    'tanggal_lahir' => '2001-03-10',
                ],
                'pricing_tier_id' => $pendaftaranTier->id,
                'tier_amount' => 500000.00,
                'total_amount' => 500000.00,
                'payment_status' => 'paid',
                'payment_method' => 'e_wallet',
                'paid_at' => now()->subDays(3),
                'status' => 'pending',
                'submitted_at' => now()->subDays(3),
            ],

            // Beasiswa - No payment required
            [
                'form_id' => $beasiswaForm->id,
                'submission_number' => 'BPA-2024-0001',
                'name' => 'Fitri Handayani',
                'email' => 'fitri.handayani@email.com',
                'phone' => '084567890123',
                'data' => [
                    'nama' => 'Fitri Handayani',
                    'nim' => '202101234',
                    'prodi' => 'Teknik Informatika',
                    'ipk' => '3.85',
                    'prestasi' => 'Juara 1 Olimpiade Matematika Tingkat Provinsi, Juara 2 Lomba Karya Tulis Ilmiah Nasional',
                    'penghasilan' => '<1jt',
                    'tanggungan' => '4',
                    'alasan' => 'Saya berasal dari keluarga kurang mampu dan ingin terus melanjutkan pendidikan...',
                ],
                'status' => 'approved',
                'submitted_at' => now()->subDays(10),
            ],
            [
                'form_id' => $beasiswaForm->id,
                'submission_number' => 'BPA-2024-0002',
                'name' => 'Agus Setiawan',
                'email' => 'agus.setiawan@email.com',
                'phone' => '085678901234',
                'data' => [
                    'nama' => 'Agus Setiawan',
                    'nim' => '202101235',
                    'prodi' => 'Sistem Informasi',
                    'ipk' => '3.75',
                ],
                'status' => 'pending',
                'submitted_at' => now()->subDays(7),
            ],

            // Lomba - With payment and affiliate
            [
                'form_id' => $lombaForm->id,
                'submission_number' => 'LKTI-2024-0001',
                'name' => 'Muhammad Fauzi',
                'email' => 'fauzi@email.com',
                'phone' => '086789012345',
                'data' => [
                    'nama' => 'Muhammad Fauzi',
                    'institusi' => 'Universitas Indonesia',
                    'email' => 'fauzi@email.com',
                    'whatsapp' => '086789012345',
                    'judul' => 'Penerapan AI dalam Pendidikan',
                    'abstrak' => 'Penelitian ini membahas tentang...',
                ],
                'pricing_tier_id' => $lombaTier->id,
                'affiliate_reward_id' => $sitiAffiliate->id,
                'tier_amount' => 150000.00,
                'affiliate_amount' => 30000.00,
                'total_amount' => 150000.00,
                'payment_status' => 'paid',
                'payment_method' => 'bank_transfer',
                'paid_at' => now()->subDays(4),
                'status' => 'approved',
                'submitted_at' => now()->subDays(4),
            ],

            // Coding Class - Multiple tiers
            [
                'form_id' => $codingForm->id,
                'submission_number' => 'CODE-2024-0001',
                'name' => 'Dimas Anggara',
                'email' => 'dimas@email.com',
                'phone' => '087890123456',
                'data' => [
                    'nama' => 'Dimas Anggara',
                    'email' => 'dimas@email.com',
                    'whatsapp' => '087890123456',
                    'usia' => '22',
                    'pengalaman' => 'pemula',
                    'bahasa' => ['python', 'javascript'],
                    'motivasi' => 'Ingin menjadi full-stack developer dan berkontribusi di dunia teknologi',
                    'jadwal' => 'weekday_malam',
                ],
                'pricing_tier_id' => $codingBasic->id,
                'tier_amount' => 250000.00,
                'total_amount' => 250000.00,
                'payment_status' => 'paid',
                'payment_method' => 'credit_card',
                'paid_at' => now()->subDay(),
                'status' => 'approved',
                'submitted_at' => now()->subDay(),
            ],
            [
                'form_id' => $codingForm->id,
                'submission_number' => 'CODE-2024-0002',
                'name' => 'Lina Marlina',
                'email' => 'lina@email.com',
                'phone' => '088901234567',
                'data' => [
                    'nama' => 'Lina Marlina',
                    'email' => 'lina@email.com',
                    'whatsapp' => '088901234567',
                    'usia' => '25',
                    'pengalaman' => 'menengah',
                    'bahasa' => ['php', 'java'],
                ],
                'pricing_tier_id' => $codingInter->id,
                'selected_upsells' => $codingUpsells,
                'affiliate_reward_id' => $budiAffiliate->id,
                'tier_amount' => 650000.00,
                'upsells_amount' => 800000.00,
                'affiliate_amount' => 174000.00,
                'total_amount' => 1450000.00,
                'payment_status' => 'paid',
                'payment_method' => 'bank_transfer',
                'paid_at' => now()->subDays(6),
                'status' => 'approved',
                'submitted_at' => now()->subDays(6),
            ],
            [
                'form_id' => $codingForm->id,
                'submission_number' => 'CODE-2024-0003',
                'name' => 'Teguh Santoso',
                'email' => 'teguh@email.com',
                'phone' => '089012345678',
                'data' => [
                    'nama' => 'Teguh Santoso',
                    'email' => 'teguh@email.com',
                    'whatsapp' => '089012345678',
                    'usia' => '28',
                ],
                'pricing_tier_id' => $codingAdv->id,
                'tier_amount' => 1200000.00,
                'total_amount' => 1200000.00,
                'payment_status' => 'pending',
                'status' => 'pending',
                'submitted_at' => now()->subHours(12),
            ],
        ];

        foreach ($submissions as $submission) {
            Submission::create($submission);
        }
    }
}
