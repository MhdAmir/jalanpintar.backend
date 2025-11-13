<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Get sections with auto-injected affiliate field
        $sections = $this->whenLoaded('sections', function () {
            return $this->sectionsWithAffiliate;
        });

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'cover_image' => $this->cover_image,
            'enable_payment' => $this->enable_payment,
            'enable_affiliate' => $this->enable_affiliate,
            'is_active' => $this->is_active,
            'published_at' => $this->published_at?->toISOString(),
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'max_submissions' => $this->max_submissions,
            'settings' => $this->settings,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'sections' => SectionResource::collection($sections),
            'pricing_tiers' => PricingTierResource::collection($this->whenLoaded('pricingTiers')),
            'upsells' => UpsellResource::collection($this->whenLoaded('upsells')),
            'submissions_count' => $this->whenCounted('submissions'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
