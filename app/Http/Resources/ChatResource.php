<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatResource extends JsonResource
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
            'session_id' => $this->session_id,
            'status' => $this->status,
            'user' => $this->when($this->user_id, function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'agent' => $this->when($this->agent_id, function () {
                return [
                    'id' => $this->agent->id,
                    'name' => $this->agent->name,
                    'email' => $this->agent->email,
                ];
            }),
            'guest_info' => $this->when(!$this->user_id, function () {
                return [
                    'guest_name' => $this->guest_name,
                    'guest_email' => $this->guest_email,
                ];
            }),
            'started_at' => $this->started_at?->format('Y-m-d H:i:s'),
            'closed_at' => $this->closed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'messages_count' => $this->when(
                $this->relationLoaded('messages'),
                function () {
                    return $this->messages->count();
                }
            ),
            'unread_messages_count' => $this->when(
                $this->relationLoaded('messages'),
                function () {
                    return $this->messages->where('is_read', false)->count();
                }
            ),
            'latest_message' => $this->when(
                $this->relationLoaded('messages') && $this->messages->isNotEmpty(),
                function () {
                    $latestMessage = $this->messages->sortByDesc('sent_at')->first();
                    return [
                        'message' => $latestMessage->message,
                        'sender_name' => $latestMessage->sender_name,
                        'sent_at' => $latestMessage->sent_at->format('Y-m-d H:i:s'),
                    ];
                }
            ),
        ];
    }
}
