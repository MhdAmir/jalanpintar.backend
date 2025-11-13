<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Admin Mizu',
            'email' => 'admin@example.com',
            'password' => bcrypt('jawatengah123'),
            'role' => 'admin',
        ]);

        // Create test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        // Run all seeders in proper order
        // The order is important due to foreign key constraints
        $this->call([
            CategorySeeder::class,          // 1. Categories (no dependencies)
            FormSeeder::class,              // 2. Forms (depends on Categories)
            SectionSeeder::class,           // 3. Sections (depends on Forms)
            FieldSeeder::class,             // 4. Fields (depends on Sections)
            PricingTierSeeder::class,       // 5. Pricing Tiers (depends on Forms)
            UpsellSeeder::class,            // 6. Upsells (depends on Forms)
            AffiliateRewardSeeder::class,   // 7. Affiliate Rewards (depends on Forms & creates Users)
            SubmissionSeeder::class,        // 8. Submissions (depends on Forms, Pricing Tiers, Upsells, Affiliates)
            AnnouncementSeeder::class,      // 9. Announcements (depends on Forms & Submissions)
        ]);

        $this->command->info('✅ All seeders completed successfully!');
        $this->command->info('📊 Database has been populated with sample data.');
    }
}
