<?php

namespace Tests\Feature;

use App\Http\Middleware\JwtMiddleware;
use App\Models\Form;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AffiliateRegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const ADMIN_AFFILIATE_ROUTE = '/api/admin/affiliates';

    public function test_regular_user_cannot_access_admin_affiliate_store(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $regularUser = User::factory()->create([
            'role' => 'user',
        ]);

        $form = Form::create([
            'title' => 'Affiliate Restricted',
            'slug' => 'restricted-' . Str::random(5),
            'enable_affiliate' => true,
            'is_active' => true,
        ]);

        $this->actingAs($regularUser);

        $response = $this->postJson(self::ADMIN_AFFILIATE_ROUTE, [
            'form_id' => $form->id,
            'user_id' => $regularUser->id,
            'commission_type' => 'percentage',
            'commission_value' => 10,
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment([
                'success' => false,
            ]);
    }

    public function test_admin_can_create_affiliate_for_target_user(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $partner = User::factory()->create([
            'role' => 'user',
        ]);

        $form = Form::create([
            'title' => 'Admin Created Affiliate',
            'slug' => 'admin-create-' . Str::random(5),
            'enable_affiliate' => true,
            'is_active' => true,
        ]);

        $payload = [
            'form_id' => $form->id,
            'user_id' => $partner->id,
            'commission_type' => 'percentage',
            'commission_value' => 15,
        ];

        $this->actingAs($admin);

        $response = $this->postJson(self::ADMIN_AFFILIATE_ROUTE, $payload);

        $response->assertCreated()
            ->assertJsonFragment([
                'success' => true,
                'message' => 'Affiliate created successfully. Waiting for admin approval.',
            ]);

        $this->assertDatabaseHas('affiliate_rewards', [
            'form_id' => $form->id,
            'user_id' => $partner->id,
            'commission_value' => 15,
        ]);
    }
}
