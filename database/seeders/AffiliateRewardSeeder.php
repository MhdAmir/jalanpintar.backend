<?php

namespace Database\Seeders;

use App\Models\AffiliateReward;
use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Seeder;

class AffiliateRewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some affiliate users first
        $affiliate1 = User::create([
            'name' => 'Ahmad Affiliate',
            'email' => 'ahmad.affiliate@example.com',
            'password' => bcrypt('password'),
        ]);

        $affiliate2 = User::create([
            'name' => 'Siti Marketer',
            'email' => 'siti.marketer@example.com',
            'password' => bcrypt('password'),
        ]);

        $affiliate3 = User::create([
            'name' => 'Budi Partner',
            'email' => 'budi.partner@example.com',
            'password' => bcrypt('password'),
        ]);

        // Get forms
        $pendaftaranForm = Form::where('slug', 'pendaftaran-siswa-baru-2024-2025')->first();
        $lombaForm = Form::where('slug', 'lomba-karya-tulis-ilmiah-2024')->first();
        $codingForm = Form::where('slug', 'daftar-kelas-online-coding')->first();

        $affiliateRewards = [
            // Ahmad - Pendaftaran Siswa
            [
                'form_id' => $pendaftaranForm->id,
                'user_id' => $affiliate1->id,
                'affiliate_code' => 'AHMAD2024',
                'commission_type' => 'percentage',
                'commission_value' => 10.00, // 10%
                'total_earned' => 350000.00, // Already earned from 10 referrals
                'total_referrals' => 10,
                'is_active' => true,
            ],
            // Ahmad - Kelas Coding
            [
                'form_id' => $codingForm->id,
                'user_id' => $affiliate1->id,
                'affiliate_code' => 'AHMAD2024',
                'commission_type' => 'percentage',
                'commission_value' => 15.00, // 15%
                'total_earned' => 975000.00, // Already earned from 5 referrals
                'total_referrals' => 5,
                'is_active' => true,
            ],

            // Siti - Pendaftaran Siswa
            [
                'form_id' => $pendaftaranForm->id,
                'user_id' => $affiliate2->id,
                'affiliate_code' => 'SITI10',
                'commission_type' => 'fixed',
                'commission_value' => 25000.00, // Fixed Rp 25k per referral
                'total_earned' => 625000.00, // Already earned from 25 referrals
                'total_referrals' => 25,
                'is_active' => true,
            ],
            // Siti - Lomba
            [
                'form_id' => $lombaForm->id,
                'user_id' => $affiliate2->id,
                'affiliate_code' => 'SITI10',
                'commission_type' => 'percentage',
                'commission_value' => 20.00, // 20%
                'total_earned' => 450000.00, // Already earned from 15 referrals
                'total_referrals' => 15,
                'is_active' => true,
            ],

            // Budi - Kelas Coding
            [
                'form_id' => $codingForm->id,
                'user_id' => $affiliate3->id,
                'affiliate_code' => 'BUDI123',
                'commission_type' => 'percentage',
                'commission_value' => 12.00, // 12%
                'total_earned' => 1440000.00, // Already earned from 8 referrals
                'total_referrals' => 8,
                'is_active' => true,
            ],
            // Budi - Lomba
            [
                'form_id' => $lombaForm->id,
                'user_id' => $affiliate3->id,
                'affiliate_code' => 'BUDI123',
                'commission_type' => 'fixed',
                'commission_value' => 15000.00, // Fixed Rp 15k per referral
                'total_earned' => 180000.00, // Already earned from 12 referrals
                'total_referrals' => 12,
                'is_active' => true,
            ],

            // Additional affiliates without earnings yet (new)
            [
                'form_id' => $pendaftaranForm->id,
                'user_id' => $affiliate1->id,
                'affiliate_code' => 'NEWAFFILIATE',
                'commission_type' => 'percentage',
                'commission_value' => 8.00,
                'total_earned' => 0.00,
                'total_referrals' => 0,
                'is_active' => true,
            ],
            [
                'form_id' => $codingForm->id,
                'user_id' => $affiliate2->id,
                'affiliate_code' => 'CODEMASTER',
                'commission_type' => 'percentage',
                'commission_value' => 18.00,
                'total_earned' => 0.00,
                'total_referrals' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($affiliateRewards as $reward) {
            AffiliateReward::create($reward);
        }
    }
}
