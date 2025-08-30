<?php

namespace App\Http\Controllers;

use App\Services\ChatService;
use App\Http\Resources\ChatCollection;
use App\Http\Resources\ChatResource;
use Exception;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    protected $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
        $this->middleware(['auth:sanctum', 'role:agent']);
    }

    /**
     * Get waiting chats for assignment
     */
    public function getWaitingChats()
    {
        try {
            $chats = $this->chatService->getWaitingChats();

            return response()->json([
                'success' => true,
                'data' => ChatResource::collection($chats),
                'message' => 'Waiting chats retrieved successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve waiting chats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get agent's active chats
     */
    public function getActiveChats(Request $request)
    {
        try {
            $chats = $this->chatService->getAgentActiveChats($request->user()->id);

            return response()->json([
                'success' => true,
                'data' => ChatResource::collection($chats),
                'message' => 'Active chats retrieved successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve active chats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign chat to current agent
     */
    public function assignChat(Request $request, $chatId)
    {
        try {
            $result = $this->chatService->assignAgentToChat($chatId, $request->user()->id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to assign chat'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Chat assigned successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close chat (only if assigned to current agent)
     */
    public function closeChat(Request $request, $chatId)
    {
        try {
            $chat = $this->chatService->findChatById($chatId);

            if (!$chat || $chat->agent_id !== $request->user()->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to this chat'
                ], 403);
            }

            $this->chatService->closeChat($chatId);

            return response()->json([
                'success' => true,
                'message' => 'Chat closed successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get agent statistics
     */
    public function getStatistics(Request $request)
    {
        try {
            $agentId = $request->user()->id;
            $stats = $this->chatService->getAgentStatistics($agentId);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Statistics retrieved successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
