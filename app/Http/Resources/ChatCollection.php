<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ChatCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'chats' => $this->collection,
            'meta' => [
                'total_chats' => $this->collection->count(),
                'active_chats' => $this->collection->where('status', 'active')->count(),
                'waiting_chats' => $this->collection->where('status', 'waiting')->count(),
                'closed_chats' => $this->collection->where('status', 'closed')->count(),
            ],
            'pagination' => [
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage(),
            ],
        ];
    }
}
