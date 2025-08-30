<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MessageCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'messages' => $this->collection,
            'meta' => [
                'total_messages' => $this->collection->count(),
                'unread_count' => $this->collection->where('is_read', false)->count(),
                'first_message_date' => $this->collection->first()?->sent_at?->format('Y-m-d'),
                'last_message_date' => $this->collection->last()?->sent_at?->format('Y-m-d'),
            ],
        ];
    }
}
