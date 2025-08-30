<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'chat_id' => $this->chat_id,
            'sender' => [
                'id' => $this->sender_id,
                'name' => $this->sender_name,
                'type' => $this->sender_type,
                'avatar' => $this->when($this->sender_id, function () {
                    return $this->sender?->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->sender_name);
                }, 'https://ui-avatars.com/api/?name=' . urlencode($this->sender_name)),
            ],
            'message' => $this->message,
            'message_type' => $this->message_type,
            'is_read' => $this->is_read,
            'sent_at' => $this->sent_at->format('Y-m-d H:i:s'),
            'sent_at_human' => $this->sent_at->diffForHumans(),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'is_from_current_user' => $this->when(
                $request->user(),
                function () use ($request) {
                    return $this->sender_id === $request->user()->id;
                }
            ),
        ];
    }
}
