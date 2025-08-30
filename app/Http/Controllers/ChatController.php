<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use App\Services\MessageService;
use App\Http\Requests\StartChatRequest;
use App\Http\Resources\ChatResource;
use Exception;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    protected $chatService;
    protected $messageService;

    public function __construct(ChatService $chatService, MessageService $messageService)
    {
        $this->chatService = $chatService;
        $this->messageService = $messageService;
    }

    public function startChat(StartChatRequest $request)
    {
        try {
            if ($request->user()) {
                // Logged-in user
                $chat = $this->chatService->startChatForUser($request->user()->id);
            } else {
                // Guest user
                $chat = $this->chatService->startChatForGuest(
                    $request->guest_name,
                    $request->guest_email
                );
            }

            return response()->json([
                'success' => true,
                'data' => new ChatResource($chat),
                'message' => 'Chat session started successfully'
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start chat session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getChatMessages($sessionId)
    {
        try {
            $chat = $this->chatService->findChatBySession($sessionId);

            if (!$chat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chat session not found'
                ], 404);
            }

            $messages = $this->messageService->getChatMessages($chat->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'chat' => new ChatResource($chat),
                    'messages' => $messages
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
