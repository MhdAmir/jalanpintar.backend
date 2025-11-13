<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Section;
use App\Models\Field;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FormService
{
    public function createFormWithSections(array $data): Form
    {
        return DB::transaction(function () use ($data) {
            // Create the form
            $form = Form::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'slug' => $data['slug'] ?? Str::slug($data['name']),
                'is_active' => $data['is_active'] ?? true,
                'enable_payment' => $data['enable_payment'] ?? false,
                'enable_affiliate' => $data['enable_affiliate'] ?? false,
                'category_id' => $data['category_id'] ?? null,
            ]);

            // Create sections if provided
            if (isset($data['sections'])) {
                foreach ($data['sections'] as $sectionData) {
                    $section = $form->sections()->create([
                        'title' => $sectionData['title'],
                        'description' => $sectionData['description'] ?? null,
                        'order' => $sectionData['order'] ?? 0,
                    ]);

                    // Create fields if provided
                    if (isset($sectionData['fields'])) {
                        foreach ($sectionData['fields'] as $fieldData) {
                            $section->fields()->create([
                                'label' => $fieldData['label'],
                                'name' => $fieldData['name'] ?? \Illuminate\Support\Str::slug($fieldData['label']),
                                'type' => $fieldData['type'],
                                'placeholder' => $fieldData['placeholder'] ?? null,
                                'help_text' => $fieldData['help_text'] ?? null,
                                'is_required' => $fieldData['is_required'] ?? $fieldData['required'] ?? false,
                                'order' => $fieldData['order'] ?? 0,
                                'options' => $fieldData['options'] ?? null,
                                'validation_rules' => $fieldData['validation_rules'] ?? null,
                            ]);
                        }
                    }
                }
            }

            return $form->load(['sections.fields', 'category']);
        });
    }

    public function duplicateForm(string $formId): Form
    {
        $originalForm = Form::with(['sections.fields', 'pricingTiers', 'upsells'])->findOrFail($formId);

        return DB::transaction(function () use ($originalForm) {
            // Create duplicate form
            $newForm = $originalForm->replicate();
            $newForm->name = $originalForm->name . ' (Copy)';
            $newForm->slug = Str::slug($newForm->name) . '-' . Str::random(5);
            $newForm->save();

            // Duplicate sections and fields
            foreach ($originalForm->sections as $section) {
                $newSection = $section->replicate();
                $newSection->form_id = $newForm->id;
                $newSection->save();

                foreach ($section->fields as $field) {
                    $newField = $field->replicate();
                    $newField->section_id = $newSection->id;
                    $newField->save();
                }
            }

            // Duplicate pricing tiers
            foreach ($originalForm->pricingTiers as $tier) {
                $newTier = $tier->replicate();
                $newTier->form_id = $newForm->id;
                $newTier->is_default = false; // Reset default
                $newTier->save();
            }

            // Duplicate upsells
            foreach ($originalForm->upsells as $upsell) {
                $newUpsell = $upsell->replicate();
                $newUpsell->form_id = $newForm->id;
                $newUpsell->save();
            }

            return $newForm->load(['sections.fields', 'pricingTiers', 'upsells']);
        });
    }

    public function getPublicForm(string $slug): ?Form
    {
        return Form::with([
            'sections.fields',
            'pricingTiers' => function ($query) {
                $query->active()->orderBy('order');
            },
            'upsells' => function ($query) {
                $query->active()->orderBy('order');
            }
        ])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }
}
