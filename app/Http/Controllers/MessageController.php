<?php

namespace App\Http\Controllers;

use App\Services\MessageService;
use App\Http\Requests\SendMessageRequest;
use Exception;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function sendMessage(SendMessageRequest $request)
    {
        try {
            $messageData = [
                'chat_id' => $request->chat_id,
                'sender_id' => $request->user('sanctum') ? $request->user('sanctum')->id : null,
                'sender_type' => $request->user('sanctum')->role ? $request->user('sanctum')->role : $request->sender_type,
                'sender_name' => $request->user('sanctum')->name ? $request->user('sanctum')->name : $request->sender_name,
                'message' => $request->message,
                'message_type' => $request->message_type ?? 'text'
            ];

            $message = $this->messageService->sendMessage($messageData);

            return response()->json([
                'success' => true,
                'data' => $message,
                'message' => 'Message sent successfully'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markAsRead($messageId)
    {
        try {
            $updated = $this->messageService->markMessageAsRead($messageId);
            return response()->json([
                'success' => true,
                'data' => $updated,
                'message' => 'Message sent successfully'
            ], 204);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
