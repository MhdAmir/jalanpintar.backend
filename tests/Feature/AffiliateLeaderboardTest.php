<?php

namespace Tests\Feature;

use App\Http\Middleware\JwtMiddleware;
use App\Models\AffiliateReward;
use App\Models\Form;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AffiliateLeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_affiliate_leaderboard_and_rank(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $userA = User::factory()->create(['role' => 'user']);
        $userB = User::factory()->create(['role' => 'user']);
        $userC = User::factory()->create(['role' => 'user']);

        $formAlpha = Form::create([
            'title' => 'Form Alpha',
            'slug' => 'form-alpha-' . Str::random(5),
            'enable_affiliate' => true,
            'is_active' => true,
        ]);

        $formBeta = Form::create([
            'title' => 'Form Beta',
            'slug' => 'form-beta-' . Str::random(5),
            'enable_affiliate' => true,
            'is_active' => true,
        ]);

        AffiliateReward::create([
            'form_id' => $formAlpha->id,
            'user_id' => $userA->id,
            'affiliate_code' => 'ALPHA1',
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'total_earned' => 300,
            'total_referrals' => 30,
            'is_active' => true,
            'status' => 'approved',
        ]);

        AffiliateReward::create([
            'form_id' => $formBeta->id,
            'user_id' => $userA->id,
            'affiliate_code' => 'BETA1',
            'commission_type' => 'percentage',
            'commission_value' => 12,
            'total_earned' => 150,
            'total_referrals' => 15,
            'is_active' => true,
            'status' => 'approved',
        ]);

        AffiliateReward::create([
            'form_id' => $formAlpha->id,
            'user_id' => $userB->id,
            'affiliate_code' => 'ALPHA2',
            'commission_type' => 'percentage',
            'commission_value' => 8,
            'total_earned' => 200,
            'total_referrals' => 20,
            'is_active' => true,
            'status' => 'approved',
        ]);

        AffiliateReward::create([
            'form_id' => $formAlpha->id,
            'user_id' => $userC->id,
            'affiliate_code' => 'ALPHA3',
            'commission_type' => 'percentage',
            'commission_value' => 5,
            'total_earned' => 100,
            'total_referrals' => 10,
            'is_active' => true,
            'status' => 'approved',
        ]);

        $this->actingAs($userA);

        $response = $this->getJson('/api/affiliates/leaderboard');

        $response->assertOk()
            ->assertJsonPath('data.leaderboard.0.user.id', $userA->id)
            ->assertJsonPath('data.my_rank', 1)
            ->assertJsonPath('data.my_summary.total_earned', '450.00')
            ->assertJsonPath('meta.total_participants', 3);

        $filteredResponse = $this->getJson('/api/affiliates/leaderboard?form_id=' . $formBeta->id);

        $filteredResponse->assertOk()
            ->assertJsonPath('data.leaderboard.0.user.id', $userA->id)
            ->assertJsonPath('meta.total_participants', 1)
            ->assertJsonCount(1, 'data.leaderboard');
    }
}
