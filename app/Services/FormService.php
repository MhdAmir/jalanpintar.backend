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
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'slug' => $data['slug'] ?? Str::slug($data['title']),
                'category_id' => $data['category_id'] ?? null,
                'cover_image' => $data['cover_image'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'enable_payment' => $data['enable_payment'] ?? false,
                'enable_affiliate' => $data['enable_affiliate'] ?? false,
                'max_submissions' => $data['max_submissions'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'settings' => $data['settings'] ?? null,
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
                                'name' => $fieldData['name'] ?? Str::slug($fieldData['label']),
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

            // Create pricing tiers if provided
            if (isset($data['pricing_tiers'])) {
                foreach ($data['pricing_tiers'] as $tierData) {
                    $form->pricingTiers()->create([
                        'name' => $tierData['name'],
                        'description' => $tierData['description'] ?? null,
                        'price' => $tierData['price'],
                        'currency' => $tierData['currency'] ?? 'IDR',
                        'is_default' => $tierData['is_default'] ?? false,
                        'is_active' => $tierData['is_active'] ?? true,
                        'order' => $tierData['order'] ?? 0,
                    ]);
                }
            }

            // Create upsells if provided
            if (isset($data['upsells'])) {
                foreach ($data['upsells'] as $upsellData) {
                    $form->upsells()->create([
                        'name' => $upsellData['name'],
                        'description' => $upsellData['description'] ?? null,
                        'price' => $upsellData['price'],
                        'is_active' => $upsellData['is_active'] ?? true,
                        'order' => $upsellData['order'] ?? 0,
                    ]);
                }
            }

            return $form->load(['sections.fields', 'pricingTiers', 'upsells', 'category']);
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

    public function updateFormWithSections(Form $form, array $data): Form
    {
        return DB::transaction(function () use ($form, $data) {
            // Update form basic info
            $form->update([
                'title' => $data['title'] ?? $form->title,
                'description' => $data['description'] ?? $form->description,
                'category_id' => $data['category_id'] ?? $form->category_id,
                'cover_image' => $data['cover_image'] ?? $form->cover_image,
                'is_active' => $data['is_active'] ?? $form->is_active,
                'enable_payment' => $data['enable_payment'] ?? $form->enable_payment,
                'enable_affiliate' => $data['enable_affiliate'] ?? $form->enable_affiliate,
                'max_submissions' => $data['max_submissions'] ?? $form->max_submissions,
                'start_date' => $data['start_date'] ?? $form->start_date,
                'end_date' => $data['end_date'] ?? $form->end_date,
                'settings' => $data['settings'] ?? $form->settings,
            ]);

            // Handle sections if provided
            if (isset($data['sections'])) {
                // Get existing section IDs
                $existingSectionIds = $form->sections()->pluck('id')->toArray();
                $providedSectionIds = [];

                foreach ($data['sections'] as $sectionData) {
                    if (isset($sectionData['id']) && in_array($sectionData['id'], $existingSectionIds)) {
                        // Update existing section
                        $section = Section::findOrFail($sectionData['id']);
                        $section->update([
                            'title' => $sectionData['title'],
                            'description' => $sectionData['description'] ?? null,
                            'order' => $sectionData['order'] ?? $section->order,
                        ]);
                        $providedSectionIds[] = $sectionData['id'];
                    } else {
                        // Create new section
                        $section = $form->sections()->create([
                            'title' => $sectionData['title'],
                            'description' => $sectionData['description'] ?? null,
                            'order' => $sectionData['order'] ?? 0,
                        ]);
                        $providedSectionIds[] = $section->id;
                    }

                    // Handle fields
                    if (isset($sectionData['fields'])) {
                        $existingFieldIds = $section->fields()->pluck('id')->toArray();
                        $providedFieldIds = [];

                        foreach ($sectionData['fields'] as $fieldData) {
                            if (isset($fieldData['id']) && in_array($fieldData['id'], $existingFieldIds)) {
                                // Update existing field
                                $field = Field::findOrFail($fieldData['id']);
                                $field->update([
                                    'label' => $fieldData['label'],
                                    'name' => $fieldData['name'] ?? Str::slug($fieldData['label']),
                                    'type' => $fieldData['type'],
                                    'placeholder' => $fieldData['placeholder'] ?? null,
                                    'help_text' => $fieldData['help_text'] ?? null,
                                    'is_required' => $fieldData['is_required'] ?? $fieldData['required'] ?? false,
                                    'order' => $fieldData['order'] ?? $field->order,
                                    'options' => $fieldData['options'] ?? null,
                                    'validation_rules' => $fieldData['validation_rules'] ?? null,
                                ]);
                                $providedFieldIds[] = $fieldData['id'];
                            } else {
                                // Create new field
                                $field = $section->fields()->create([
                                    'label' => $fieldData['label'],
                                    'name' => $fieldData['name'] ?? Str::slug($fieldData['label']),
                                    'type' => $fieldData['type'],
                                    'placeholder' => $fieldData['placeholder'] ?? null,
                                    'help_text' => $fieldData['help_text'] ?? null,
                                    'is_required' => $fieldData['is_required'] ?? $fieldData['required'] ?? false,
                                    'order' => $fieldData['order'] ?? 0,
                                    'options' => $fieldData['options'] ?? null,
                                    'validation_rules' => $fieldData['validation_rules'] ?? null,
                                ]);
                                $providedFieldIds[] = $field->id;
                            }
                        }

                        // Delete fields that were not provided (removed from form)
                        $fieldsToDelete = array_diff($existingFieldIds, $providedFieldIds);
                        if (!empty($fieldsToDelete)) {
                            Field::whereIn('id', $fieldsToDelete)->delete();
                        }
                    }
                }

                // Delete sections that were not provided (removed from form)
                $sectionsToDelete = array_diff($existingSectionIds, $providedSectionIds);
                if (!empty($sectionsToDelete)) {
                    Section::whereIn('id', $sectionsToDelete)->delete();
                }
            }

            // Handle pricing tiers if provided
            if (isset($data['pricing_tiers'])) {
                $existingTierIds = $form->pricingTiers()->pluck('id')->toArray();
                $providedTierIds = [];

                foreach ($data['pricing_tiers'] as $tierData) {
                    if (isset($tierData['id']) && in_array($tierData['id'], $existingTierIds)) {
                        // Update existing tier
                        $tier = $form->pricingTiers()->findOrFail($tierData['id']);
                        $tier->update([
                            'name' => $tierData['name'],
                            'description' => $tierData['description'] ?? null,
                            'price' => $tierData['price'],
                            'currency' => $tierData['currency'] ?? 'IDR',
                            'is_default' => $tierData['is_default'] ?? false,
                            'is_active' => $tierData['is_active'] ?? true,
                            'order' => $tierData['order'] ?? $tier->order,
                        ]);
                        $providedTierIds[] = $tierData['id'];
                    } else {
                        // Create new tier
                        $tier = $form->pricingTiers()->create([
                            'name' => $tierData['name'],
                            'description' => $tierData['description'] ?? null,
                            'price' => $tierData['price'],
                            'currency' => $tierData['currency'] ?? 'IDR',
                            'is_default' => $tierData['is_default'] ?? false,
                            'is_active' => $tierData['is_active'] ?? true,
                            'order' => $tierData['order'] ?? 0,
                        ]);
                        $providedTierIds[] = $tier->id;
                    }
                }

                // Delete tiers that were not provided
                $tiersToDelete = array_diff($existingTierIds, $providedTierIds);
                if (!empty($tiersToDelete)) {
                    $form->pricingTiers()->whereIn('id', $tiersToDelete)->delete();
                }
            }

            // Handle upsells if provided
            if (isset($data['upsells'])) {
                $existingUpsellIds = $form->upsells()->pluck('id')->toArray();
                $providedUpsellIds = [];

                foreach ($data['upsells'] as $upsellData) {
                    if (isset($upsellData['id']) && in_array($upsellData['id'], $existingUpsellIds)) {
                        // Update existing upsell
                        $upsell = $form->upsells()->findOrFail($upsellData['id']);
                        $upsell->update([
                            'name' => $upsellData['name'],
                            'description' => $upsellData['description'] ?? null,
                            'price' => $upsellData['price'],
                            'is_active' => $upsellData['is_active'] ?? true,
                            'order' => $upsellData['order'] ?? $upsell->order,
                        ]);
                        $providedUpsellIds[] = $upsellData['id'];
                    } else {
                        // Create new upsell
                        $upsell = $form->upsells()->create([
                            'name' => $upsellData['name'],
                            'description' => $upsellData['description'] ?? null,
                            'price' => $upsellData['price'],
                            'is_active' => $upsellData['is_active'] ?? true,
                            'order' => $upsellData['order'] ?? 0,
                        ]);
                        $providedUpsellIds[] = $upsell->id;
                    }
                }

                // Delete upsells that were not provided
                $upsellsToDelete = array_diff($existingUpsellIds, $providedUpsellIds);
                if (!empty($upsellsToDelete)) {
                    $form->upsells()->whereIn('id', $upsellsToDelete)->delete();
                }
            }

            return $form->fresh(['sections.fields', 'pricingTiers', 'upsells', 'category']);
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

    public function getAdminForm(string $slug): ?Form
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
            ->first();
    }
}
