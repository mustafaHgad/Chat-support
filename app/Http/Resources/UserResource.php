<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'is_online' => $this->is_online,
            'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($this->name),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'active_chats_count' => $this->when(
                $this->role === 'agent' && $this->relationLoaded('agentChats'),
                function () {
                    return $this->agentChats->where('status', 'active')->count();
                }
            ),
            'total_chats_handled' => $this->when(
                $this->role === 'agent' && $this->relationLoaded('agentChats'),
                function () {
                    return $this->agentChats->count();
                }
            ),
        ];
    }
}
