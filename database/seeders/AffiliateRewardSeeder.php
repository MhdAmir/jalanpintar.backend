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
            'role' => 'user',
        ]);

        $affiliate2 = User::create([
            'name' => 'Siti Marketer',
            'email' => 'siti.marketer@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $affiliate3 = User::create([
            'name' => 'Budi Partner',
            'email' => 'budi.partner@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        // Get forms
        $pendaftaranForm = Form::where('slug', 'pendaftaran-siswa-baru-2024-2025')->first();
        $lombaForm = Form::where('slug', 'lomba-karya-tulis-ilmiah-2024')->first();
        $codingForm = Form::where('slug', 'daftar-kelas-online-coding')->first();

        $affiliateRewards = [
            // Ahmad - Pendaftaran Siswa (APPROVED)
            [
                'form_id' => $pendaftaranForm->id,
                'user_id' => $affiliate1->id,
                'affiliate_code' => 'AHMAD2024',
                'commission_type' => 'percentage',
                'commission_value' => 10.00, // 10%
                'total_earned' => 350000.00, // Already earned from 10 referrals
                'total_referrals' => 10,
                'is_active' => true,
                'status' => 'approved',
                'approved_at' => now()->subDays(30),
                'approved_by' => User::where('email', 'admin@example.com')->first()->id,
            ],
            // Ahmad - Kelas Coding (APPROVED)
            [
                'form_id' => $codingForm->id,
                'user_id' => $affiliate1->id,
                'affiliate_code' => 'AHMAD2024',
                'commission_type' => 'percentage',
                'commission_value' => 15.00, // 15%
                'total_earned' => 975000.00, // Already earned from 5 referrals
                'total_referrals' => 5,
                'is_active' => true,
                'status' => 'approved',
                'approved_at' => now()->subDays(30),
                'approved_by' => User::where('email', 'admin@example.com')->first()->id,
            ],

            // Siti - Pendaftaran Siswa (APPROVED)
            [
                'form_id' => $pendaftaranForm->id,
                'user_id' => $affiliate2->id,
                'affiliate_code' => 'SITI10',
                'commission_type' => 'fixed',
                'commission_value' => 25000.00, // Fixed Rp 25k per referral
                'total_earned' => 625000.00, // Already earned from 25 referrals
                'total_referrals' => 25,
                'is_active' => true,
                'status' => 'approved',
                'approved_at' => now()->subDays(25),
                'approved_by' => User::where('email', 'admin@example.com')->first()->id,
            ],
            // Siti - Lomba (PENDING)
            [
                'form_id' => $lombaForm->id,
                'user_id' => $affiliate2->id,
                'affiliate_code' => 'SITI10',
                'commission_type' => 'percentage',
                'commission_value' => 20.00, // 20%
                'total_earned' => 0.00,
                'total_referrals' => 0,
                'is_active' => true,
                'status' => 'pending',
            ],

            // Budi - Kelas Coding (APPROVED)
            [
                'form_id' => $codingForm->id,
                'user_id' => $affiliate3->id,
                'affiliate_code' => 'BUDI123',
                'commission_type' => 'percentage',
                'commission_value' => 12.00, // 12%
                'total_earned' => 1440000.00, // Already earned from 8 referrals
                'total_referrals' => 8,
                'is_active' => true,
                'status' => 'approved',
                'approved_at' => now()->subDays(20),
                'approved_by' => User::where('email', 'admin@example.com')->first()->id,
            ],
            // Budi - Lomba (REJECTED)
            [
                'form_id' => $lombaForm->id,
                'user_id' => $affiliate3->id,
                'affiliate_code' => 'BUDI123',
                'commission_type' => 'fixed',
                'commission_value' => 15000.00, // Fixed Rp 15k per referral
                'total_earned' => 0.00,
                'total_referrals' => 0,
                'is_active' => false,
                'status' => 'rejected',
                'rejection_reason' => 'Commission rate too high for competition form',
                'approved_by' => User::where('email', 'admin@example.com')->first()->id,
            ],

            // Additional affiliates without earnings yet (PENDING)
            [
                'form_id' => $pendaftaranForm->id,
                'user_id' => $affiliate1->id,
                'affiliate_code' => 'NEWAFFILIATE',
                'commission_type' => 'percentage',
                'commission_value' => 8.00,
                'total_earned' => 0.00,
                'total_referrals' => 0,
                'is_active' => true,
                'status' => 'pending',
            ],
            // New pending with high commission
            [
                'form_id' => $codingForm->id,
                'user_id' => $affiliate2->id,
                'affiliate_code' => 'CODEMASTER',
                'commission_type' => 'percentage',
                'commission_value' => 25.00, // High commission - needs review
                'total_earned' => 0.00,
                'total_referrals' => 0,
                'is_active' => true,
                'status' => 'pending',
            ],
        ];

        foreach ($affiliateRewards as $reward) {
            AffiliateReward::create($reward);
        }
    }
}
