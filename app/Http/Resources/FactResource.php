<?php

namespace App\Http\Resources;

use App\Models\Fact;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Fact
 */
class FactResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date' => $this->fact_date->toDateString(),
            'title' => $this->title,
            'fact' => $this->content,
            'tags' => $this->tags,
            'source' => [
                'title' => $this->source_title,
                'url' => $this->source_url,
            ],
        ];
    }
}
