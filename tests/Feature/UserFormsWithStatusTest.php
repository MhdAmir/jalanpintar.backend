<?php

namespace Tests\Feature;

use App\Http\Middleware\JwtMiddleware;
use App\Models\Category;
use App\Models\Form;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserFormsWithStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private const USER_FORMS_ENDPOINT = '/api/user/forms';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'testuser@example.com',
            'email_verified_at' => now(),
        ]);
    }

    public function test_authenticated_user_can_get_forms_with_submission_status(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);

        // Create forms
        $submittedForm = Form::create([
            'title' => 'Submitted Form',
            'slug' => 'submitted-form-' . Str::random(5),
            'category_id' => $category->id,
            'is_active' => true,
            'enable_payment' => false,
        ]);

        $unsubmittedForm = Form::create([
            'title' => 'Unsubmitted Form',
            'slug' => 'unsubmitted-form-' . Str::random(5),
            'category_id' => $category->id,
            'is_active' => true,
            'enable_payment' => false,
        ]);

        // Create submission for first form
        Submission::create([
            'form_id' => $submittedForm->id,
            'name' => 'Test User',
            'email' => $this->user->email,
            'data' => ['name' => 'Test User', 'email' => $this->user->email],
            'status' => 'approved',
            'payment_status' => 'paid',
        ]);

        $response = $this->actingAs($this->user)->getJson(self::USER_FORMS_ENDPOINT);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'description',
                        'category',
                        'user_has_submitted',
                        'user_submission_status',
                        'user_submission_id',
                        'user_submitted_at',
                    ]
                ],
                'total',
            ]);

        $data = $response->json('data');

        // Find the forms in response
        $submittedFormData = collect($data)->firstWhere('id', $submittedForm->id);
        $unsubmittedFormData = collect($data)->firstWhere('id', $unsubmittedForm->id);

        // Assert submitted form has correct status
        $this->assertTrue($submittedFormData['user_has_submitted']);
        $this->assertEquals('approved', $submittedFormData['user_submission_status']);
        $this->assertNotNull($submittedFormData['user_submission_id']);

        // Assert unsubmitted form has correct status
        $this->assertFalse($unsubmittedFormData['user_has_submitted']);
        $this->assertNull($unsubmittedFormData['user_submission_status']);
        $this->assertNull($unsubmittedFormData['user_submission_id']);
    }

    public function test_only_active_forms_are_returned(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $activeForm = Form::create([
            'title' => 'Active Form',
            'slug' => 'active-form-' . Str::random(5),
            'is_active' => true,
            'enable_payment' => false,
        ]);

        $inactiveForm = Form::create([
            'title' => 'Inactive Form',
            'slug' => 'inactive-form-' . Str::random(5),
            'is_active' => false,
            'enable_payment' => false,
        ]);

        $response = $this->actingAs($this->user)->getJson(self::USER_FORMS_ENDPOINT);

        $response->assertOk();

        $formIds = collect($response->json('data'))->pluck('id')->toArray();

        $this->assertContains($activeForm->id, $formIds);
        $this->assertNotContains($inactiveForm->id, $formIds);
    }

    public function test_can_filter_forms_by_category(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $category1 = Category::create(['name' => 'Category 1', 'slug' => 'cat-1']);
        $category2 = Category::create(['name' => 'Category 2', 'slug' => 'cat-2']);

        $form1 = Form::create([
            'title' => 'Form 1',
            'slug' => 'form-1-' . Str::random(5),
            'category_id' => $category1->id,
            'is_active' => true,
            'enable_payment' => false,
        ]);

        $form2 = Form::create([
            'title' => 'Form 2',
            'slug' => 'form-2-' . Str::random(5),
            'category_id' => $category2->id,
            'is_active' => true,
            'enable_payment' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(self::USER_FORMS_ENDPOINT . '?category_id=' . $category1->id);

        $response->assertOk();

        $formIds = collect($response->json('data'))->pluck('id')->toArray();

        $this->assertContains($form1->id, $formIds);
        $this->assertNotContains($form2->id, $formIds);
    }

    public function test_can_search_forms_by_title(): void
    {
        $this->withoutMiddleware([JwtMiddleware::class]);

        $matchingForm = Form::create([
            'title' => 'Contact Form',
            'slug' => 'contact-form-' . Str::random(5),
            'is_active' => true,
            'enable_payment' => false,
        ]);

        $nonMatchingForm = Form::create([
            'title' => 'Feedback Survey',
            'slug' => 'feedback-survey-' . Str::random(5),
            'is_active' => true,
            'enable_payment' => false,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(self::USER_FORMS_ENDPOINT . '?search=Contact');

        $response->assertOk();

        $formIds = collect($response->json('data'))->pluck('id')->toArray();

        $this->assertContains($matchingForm->id, $formIds);
        $this->assertNotContains($nonMatchingForm->id, $formIds);
    }

    public function test_unauthenticated_user_cannot_access_endpoint(): void
    {
        $response = $this->getJson(self::USER_FORMS_ENDPOINT);

        $response->assertUnauthorized();
    }
}