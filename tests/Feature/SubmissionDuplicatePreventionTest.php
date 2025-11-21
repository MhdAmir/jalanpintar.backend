<?php

namespace Tests\Feature;

use App\Http\Middleware\JwtMiddleware;
use App\Models\Form;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubmissionDuplicatePreventionTest extends TestCase
{
    use RefreshDatabase;

    private const SUBMISSIONS_ENDPOINT = '/api/submissions';

    public function test_prevents_duplicate_submission_for_same_user_and_form(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => now(),
        ]);

        $form = Form::create([
            'title' => 'Duplicate Prevention Form',
            'slug' => 'duplicate-test-' . Str::random(5),
            'is_active' => true,
            'enable_payment' => false,
        ]);

        // First submission should succeed
        $payload = [
            'form_slug' => $form->slug,
            'data' => [
                'name' => 'John Doe',
                'message' => 'First submission',
            ],
        ];

        $firstResponse = $this->actingAs($user)->postJson(self::SUBMISSIONS_ENDPOINT, $payload);
        $firstResponse->assertCreated();

        // Second submission with same user should fail
        $payload['data']['message'] = 'Second submission attempt';

        $secondResponse = $this->actingAs($user)->postJson(self::SUBMISSIONS_ENDPOINT, $payload);
        $secondResponse->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Verify only one submission exists for this user
        $this->assertEquals(1, Submission::where('form_id', $form->id)
            ->where('email', $user->email)->count());
    }

    public function test_allows_same_user_for_different_forms(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $user = User::factory()->create([
            'email' => 'multi.form@example.com',
            'email_verified_at' => now(),
        ]);

        $formA = Form::create([
            'title' => 'Form A',
            'slug' => 'form-a-' . Str::random(5),
            'is_active' => true,
            'enable_payment' => false,
        ]);

        $formB = Form::create([
            'title' => 'Form B',
            'slug' => 'form-b-' . Str::random(5),
            'is_active' => true,
            'enable_payment' => false,
        ]);

        // Submit to Form A
        $responseA = $this->actingAs($user)->postJson(self::SUBMISSIONS_ENDPOINT, [
            'form_slug' => $formA->slug,
            'data' => [
                'name' => 'Jane Doe',
                'message' => 'Form A submission',
            ],
        ]);

        // Submit to Form B with same user
        $responseB = $this->actingAs($user)->postJson(self::SUBMISSIONS_ENDPOINT, [
            'form_slug' => $formB->slug,
            'data' => [
                'name' => 'Jane Doe',
                'message' => 'Form B submission',
            ],
        ]);

        $responseA->assertCreated();
        $responseB->assertCreated();

        // Verify both submissions exist for the same user
        $this->assertEquals(1, Submission::where('form_id', $formA->id)
            ->where('email', $user->email)->count());
        $this->assertEquals(1, Submission::where('form_id', $formB->id)
            ->where('email', $user->email)->count());
    }

    public function test_different_users_can_submit_same_form(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $user1 = User::factory()->create([
            'email' => 'user1@example.com',
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'email' => 'user2@example.com',
            'email_verified_at' => now(),
        ]);

        $form = Form::create([
            'title' => 'Multi User Form',
            'slug' => 'multi-user-' . Str::random(5),
            'is_active' => true,
            'enable_payment' => false,
        ]);

        // Both users should be able to submit the same form
        $this->actingAs($user1)->postJson(self::SUBMISSIONS_ENDPOINT, [
            'form_slug' => $form->slug,
            'data' => [
                'name' => 'User One',
                'message' => 'First user submission',
            ],
        ])->assertCreated();

        $this->actingAs($user2)->postJson(self::SUBMISSIONS_ENDPOINT, [
            'form_slug' => $form->slug,
            'data' => [
                'name' => 'User Two',
                'message' => 'Second user submission',
            ],
        ])->assertCreated();

        $this->assertEquals(2, Submission::where('form_id', $form->id)->count());
        $this->assertEquals(1, Submission::where('form_id', $form->id)
            ->where('email', $user1->email)->count());
        $this->assertEquals(1, Submission::where('form_id', $form->id)
            ->where('email', $user2->email)->count());
    }
}