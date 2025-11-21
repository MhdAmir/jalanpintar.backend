<?php

namespace Tests\Feature;

use App\Http\Middleware\JwtMiddleware;
use App\Models\AffiliateReward;
use App\Models\Form;
use App\Models\PricingTier;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AffiliateReferralSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $affiliateOwner;
    private User $customer;
    private Form $form;
    private PricingTier $pricingTier;
    private AffiliateReward $affiliate;

    protected function setUp(): void
    {
        parent::setUp();

        // Create affiliate owner (person who has the referral code)
        $this->affiliateOwner = User::factory()->create([
            'name' => 'Affiliate Owner',
            'email' => 'affiliate@example.com',
            'email_verified_at' => now(),
        ]);

        // Create customer (person who will use referral code)
        $this->customer = User::factory()->create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'email_verified_at' => now(),
        ]);

        // Create form
        $this->form = Form::create([
            'title' => 'Test Event Registration',
            'slug' => 'test-event-' . Str::random(5),
            'is_active' => true,
            'enable_payment' => true,
            'enable_affiliate' => true,
        ]);

        // Create pricing tier
        $this->pricingTier = PricingTier::create([
            'form_id' => $this->form->id,
            'name' => 'Standard Tier',
            'price' => 100000, // Rp 100,000
            'description' => 'Standard registration',
            'order' => 1,
        ]);

        // Create affiliate reward manually (simulate affiliate owner already submitted form)
        $this->affiliate = AffiliateReward::create([
            'user_id' => $this->affiliateOwner->id,
            'form_id' => $this->form->id,
            'affiliate_code' => 'TESTEVENT_AFFILIATE_ABC123',
            'commission_type' => 'percentage',
            'commission_value' => 10, // 10% commission
            'status' => 'approved',
            'is_active' => true,
            'total_earned' => 0,
            'total_referrals' => 0,
            'approved_at' => now(),
        ]);
    }

    public function test_customer_can_verify_valid_affiliate_code(): void
    {
        $response = $this->postJson('/api/public/affiliates/verify', [
            'affiliate_code' => $this->affiliate->affiliate_code,
            'form_id' => $this->form->id,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'affiliate_code' => $this->affiliate->affiliate_code,
                    'affiliate_name' => $this->affiliateOwner->name,
                    'commission_type' => 'percentage',
                    'commission_value' => 10,
                    'form' => [
                        'id' => $this->form->id,
                        'title' => $this->form->title,
                    ]
                ]
            ]);
    }

    public function test_customer_cannot_verify_invalid_affiliate_code(): void
    {
        $response = $this->postJson('/api/public/affiliates/verify', [
            'affiliate_code' => 'INVALID_CODE_123',
            'form_id' => $this->form->id,
        ]);

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Invalid affiliate code',
            ]);
    }

    public function test_customer_can_submit_form_with_valid_affiliate_code(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $response = $this->actingAs($this->customer)->postJson('/api/submissions', [
            'form_slug' => $this->form->slug,
            'data' => [
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'message' => 'Test submission with affiliate code',
                'affiliate_code' => $this->affiliate->affiliate_code,
            ],
            'pricing_tier_id' => $this->pricingTier->id,
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'submission' => [
                        'id',
                        'email',
                        'total_amount',
                        'affiliate_amount',
                    ]
                ]
            ]);

        // Verify submission was created with affiliate information
        $submission = Submission::where('email', $this->customer->email)
            ->where('form_id', $this->form->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertEquals($this->affiliate->id, $submission->affiliate_reward_id);
        $this->assertEquals(10000, $submission->affiliate_amount); // 10% of 100,000 = 10,000
        $this->assertEquals($this->customer->email, $submission->email);
    }

    public function test_affiliate_statistics_update_after_referral(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        // Initial stats
        $initialAffiliate = AffiliateReward::find($this->affiliate->id);
        $this->assertEquals(0, $initialAffiliate->total_earned);
        $this->assertEquals(0, $initialAffiliate->total_referrals);

        // Customer submits form with affiliate code
        $this->actingAs($this->customer)->postJson('/api/submissions', [
            'form_slug' => $this->form->slug,
            'data' => [
                'name' => $this->customer->name,
                'message' => 'Test submission with affiliate code',
                'affiliate_code' => $this->affiliate->affiliate_code,
            ],
            'pricing_tier_id' => $this->pricingTier->id,
        ]);

        // Check updated stats
        $updatedAffiliate = AffiliateReward::find($this->affiliate->id);
        $this->assertEquals(10000, $updatedAffiliate->total_earned); // 10% commission
        $this->assertEquals(1, $updatedAffiliate->total_referrals);
    }

    public function test_multiple_customers_can_use_same_affiliate_code(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        // Create second customer
        $customer2 = User::factory()->create([
            'name' => 'Second Customer',
            'email' => 'customer2@example.com',
            'email_verified_at' => now(),
        ]);

        // First customer submits
        $this->actingAs($this->customer)->postJson('/api/submissions', [
            'form_slug' => $this->form->slug,
            'data' => [
                'name' => $this->customer->name,
                'affiliate_code' => $this->affiliate->affiliate_code,
            ],
            'pricing_tier_id' => $this->pricingTier->id,
        ]);

        // Second customer submits with same affiliate code
        $this->actingAs($customer2)->postJson('/api/submissions', [
            'form_slug' => $this->form->slug,
            'data' => [
                'name' => $customer2->name,
                'affiliate_code' => $this->affiliate->affiliate_code,
            ],
            'pricing_tier_id' => $this->pricingTier->id,
        ]);

        // Check affiliate stats
        $updatedAffiliate = AffiliateReward::find($this->affiliate->id);
        $this->assertEquals(20000, $updatedAffiliate->total_earned); // 2 × 10,000 commission
        $this->assertEquals(2, $updatedAffiliate->total_referrals);

        // Verify both submissions exist
        $this->assertEquals(1, Submission::where('email', $this->customer->email)->count());
        $this->assertEquals(1, Submission::where('email', $customer2->email)->count());
    }

    public function test_affiliate_owner_can_view_referral_statistics(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        // Create some referrals
        $this->actingAs($this->customer)->postJson('/api/submissions', [
            'form_slug' => $this->form->slug,
            'data' => [
                'name' => $this->customer->name,
                'affiliate_code' => $this->affiliate->affiliate_code,
            ],
            'pricing_tier_id' => $this->pricingTier->id,
        ]);

        // Affiliate owner checks statistics
        $response = $this->actingAs($this->affiliateOwner)
            ->getJson('/api/affiliates/my/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_earned',
                    'total_referrals',
                    'total_affiliates',
                    'affiliates' => [
                        '*' => [
                            'id',
                            'affiliate_code',
                            'form' => [
                                'id',
                                'title',
                            ],
                            'total_earned',
                            'total_referrals',
                            'is_active'
                        ]
                    ]
                ]
            ]);

        $responseData = $response->json('data');
        $this->assertEquals('10000.00', $responseData['total_earned']);
        $this->assertEquals(1, $responseData['total_referrals']);
        $this->assertCount(1, $responseData['affiliates']);

        $affiliate = $responseData['affiliates'][0];
        $this->assertEquals($this->affiliate->affiliate_code, $affiliate['affiliate_code']);
        $this->assertEquals('10000.00', $affiliate['total_earned']);
        $this->assertEquals(1, $affiliate['total_referrals']);
    }

    public function test_affiliate_appears_in_leaderboard(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        // Create referral to generate earnings
        $this->actingAs($this->customer)->postJson('/api/submissions', [
            'form_slug' => $this->form->slug,
            'data' => [
                'name' => $this->customer->name,
                'affiliate_code' => $this->affiliate->affiliate_code,
            ],
            'pricing_tier_id' => $this->pricingTier->id,
        ]);

        // Check leaderboard
        $response = $this->actingAs($this->affiliateOwner)
            ->getJson('/api/affiliates/leaderboard');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'leaderboard' => [
                        '*' => [
                            'rank',
                            'user' => ['id', 'name', 'email'],
                            'total_earned',
                            'total_referrals',
                            'affiliate_count'
                        ]
                    ],
                    'my_rank',
                    'my_summary' => [
                        'total_earned',
                        'total_referrals',
                        'affiliate_count'
                    ]
                ]
            ]);

        $responseData = $response->json('data');

        // Check affiliate owner appears in leaderboard
        $this->assertNotEmpty($responseData['leaderboard']);
        $this->assertEquals(1, $responseData['my_rank']);
        $this->assertEquals('10000.00', $responseData['my_summary']['total_earned']);
        $this->assertEquals(1, $responseData['my_summary']['total_referrals']);
    }

    public function test_submission_without_affiliate_code_still_works(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $response = $this->actingAs($this->customer)->postJson('/api/submissions', [
            'form_slug' => $this->form->slug,
            'data' => [
                'name' => $this->customer->name,
                'message' => 'Submission without affiliate code',
            ],
            'pricing_tier_id' => $this->pricingTier->id,
            // No affiliate_code provided
        ]);

        $response->assertCreated();

        // Verify submission has no affiliate information
        $submission = Submission::where('email', $this->customer->email)->first();
        $this->assertNull($submission->affiliate_reward_id);
        $this->assertEquals(0, $submission->affiliate_amount);
    }

    public function test_invalid_affiliate_code_is_ignored(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $response = $this->actingAs($this->customer)->postJson('/api/submissions', [
            'form_slug' => $this->form->slug,
            'data' => [
                'name' => $this->customer->name,
                'message' => 'Submission with invalid affiliate code',
            ],
            'pricing_tier_id' => $this->pricingTier->id,
            'affiliate_code' => 'INVALID_CODE_123',
        ]);

        $response->assertCreated();

        // Verify submission has no affiliate information (invalid code ignored)
        $submission = Submission::where('email', $this->customer->email)->first();
        $this->assertNull($submission->affiliate_reward_id);
        $this->assertEquals(0, $submission->affiliate_amount);
    }

    public function test_inactive_affiliate_code_is_ignored(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        // Deactivate affiliate
        $this->affiliate->update(['is_active' => false]);

        $response = $this->actingAs($this->customer)->postJson('/api/submissions', [
            'form_slug' => $this->form->slug,
            'data' => [
                'name' => $this->customer->name,
                'message' => 'Submission with inactive affiliate code',
                'affiliate_code' => $this->affiliate->affiliate_code,
            ],
            'pricing_tier_id' => $this->pricingTier->id,
        ]);

        $response->assertCreated();

        // Verify submission has no affiliate information (inactive code ignored)
        $submission = Submission::where('email', $this->customer->email)->first();
        $this->assertNull($submission->affiliate_reward_id);
        $this->assertEquals(0, $submission->affiliate_amount);
    }

    public function test_commission_calculation_with_different_rates(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        // Update affiliate to have different commission rate
        $this->affiliate->update(['commission_value' => 15]); // 15%

        $response = $this->actingAs($this->customer)->postJson('/api/submissions', [
            'form_slug' => $this->form->slug,
            'data' => [
                'name' => $this->customer->name,
                'affiliate_code' => $this->affiliate->affiliate_code,
            ],
            'pricing_tier_id' => $this->pricingTier->id,
        ]);

        $response->assertCreated();

        // Verify commission calculation (15% of 100,000 = 15,000)
        $submission = Submission::where('email', $this->customer->email)->first();
        $this->assertEquals(15000, $submission->affiliate_amount);

        // Verify affiliate stats
        $updatedAffiliate = AffiliateReward::find($this->affiliate->id);
        $this->assertEquals(15000, $updatedAffiliate->total_earned);
    }
}