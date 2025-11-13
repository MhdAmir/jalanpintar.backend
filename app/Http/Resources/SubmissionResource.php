<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'form_id' => $this->form_id,
            'submission_number' => $this->submission_number,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'data' => $this->data,
            'pricing_tier_id' => $this->pricing_tier_id,
            'selected_upsells' => $this->selected_upsells,
            'tier_amount' => $this->tier_amount ? number_format((float) $this->tier_amount, 2, '.', '') : '0.00',
            'upsells_amount' => $this->upsells_amount ? number_format((float) $this->upsells_amount, 2, '.', '') : '0.00',
            'affiliate_amount' => $this->affiliate_amount ? number_format((float) $this->affiliate_amount, 2, '.', '') : '0.00',
            'total_amount' => $this->total_amount ? number_format((float) $this->total_amount, 2, '.', '') : '0.00',
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'paid_at' => $this->paid_at?->toISOString(),
            'affiliate_reward_id' => $this->affiliate_reward_id,
            'status' => $this->status,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'form' => new FormResource($this->whenLoaded('form')),
            'pricing_tier' => new PricingTierResource($this->whenLoaded('pricingTier')),
            'affiliate_reward' => new AffiliateRewardResource($this->whenLoaded('affiliateReward')),
            'announcement' => new AnnouncementResource($this->whenLoaded('announcement')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
