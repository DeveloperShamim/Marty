<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private function resolveConversation(Request $request): array
    {
        $user = auth()->user();
        $guestToken = $request->cookie('guest_chat_token');

        if (! $guestToken) {
            $guestToken = (string) Str::uuid();
            cookie()->queue('guest_chat_token', $guestToken, 60 * 24 * 365);
        }

        $conversation = null;

        if ($user) {
            // 1. Search by user_id
            $conversation = Conversation::where('user_id', $user->id)->latest()->first();

            // 2. Or search by phone
            if (! $conversation && $user->phone) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone);
                if (strlen($cleanPhone) >= 6) {
                    $conversation = Conversation::where('customer_phone', 'like', '%' . substr($cleanPhone, -8) . '%')->latest()->first();
                }
            }

            // 3. Or search by guest_token
            if (! $conversation) {
                $conversation = Conversation::where('guest_token', $guestToken)->latest()->first();
            }

            if ($conversation) {
                $conversation->update([
                    'user_id'        => $user->id,
                    'customer_name'  => $user->name,
                    'customer_phone' => $user->phone ?? $conversation->customer_phone,
                    'customer_email' => $user->email ?? $conversation->customer_email,
                    'status'         => 'open',
                ]);
            } else {
                $conversation = Conversation::create([
                    'user_id'         => $user->id,
                    'guest_token'     => $guestToken,
                    'customer_name'   => $user->name,
                    'customer_phone'  => $user->phone,
                    'customer_email'  => $user->email,
                    'status'          => 'open',
                    'last_message_at' => now(),
                ]);
            }
        } else {
            // Guest visitor lookup by guest_token
            $conversation = Conversation::where('guest_token', $guestToken)->latest()->first();

            if ($conversation) {
                if ($conversation->status !== 'open') {
                    $conversation->update(['status' => 'open']);
                }
            } else {
                $conversation = Conversation::create([
                    'guest_token'     => $guestToken,
                    'customer_name'   => 'Guest Visitor',
                    'status'          => 'open',
                    'last_message_at' => now(),
                ]);
            }
        }

        return [$conversation, $guestToken];
    }

    public function getConversation(Request $request)
    {
        [$conversation] = $this->resolveConversation($request);

        $conversation->markAsReadForCustomer();

        $messages = $conversation->messages()
            ->latest('id')
            ->take(60)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($msg) {
                return [
                    'id'          => $msg->id,
                    'sender_type' => $msg->sender_type,
                    'type'        => $msg->type ?? 'text',
                    'message'     => $msg->message,
                    'metadata'    => $msg->metadata,
                    'time'        => $msg->created_at->timezone('Asia/Dhaka')->format('g:i a'),
                    'created_at'  => $msg->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success'      => true,
            'conversation' => [
                'id'                     => $conversation->id,
                'customer_name'          => $conversation->customer_name,
                'status'                 => $conversation->status,
                'unread_customer_count'  => 0,
            ],
            'messages'     => $messages,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message'        => 'required|string|max:2000',
            'customer_name'  => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:30',
        ]);

        [$conversation] = $this->resolveConversation($request);

        if ($conversation->status === 'closed') {
            $conversation->update(['status' => 'open']);
        }

        if ($request->filled('customer_name') && $conversation->customer_name === 'Guest Visitor') {
            $conversation->customer_name = trim($request->input('customer_name'));
        }
        if ($request->filled('customer_phone')) {
            $conversation->customer_phone = trim($request->input('customer_phone'));
        }

        $messageText = trim($request->input('message'));

        $msg = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'customer',
            'sender_id'       => auth()->id(),
            'type'            => 'text',
            'message'         => $messageText,
            'is_read'         => false,
        ]);

        $conversation->increment('unread_admin_count');
        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => [
                'id'          => $msg->id,
                'sender_type' => 'customer',
                'type'        => 'text',
                'message'     => $msg->message,
                'metadata'    => null,
                'time'        => $msg->created_at->timezone('Asia/Dhaka')->format('g:i a'),
                'created_at'  => $msg->created_at->toIso8601String(),
            ],
        ]);
    }

    public function pollMessages(Request $request)
    {
        [$conversation] = $this->resolveConversation($request);

        $lastId = (int) $request->query('last_id', 0);

        $newMessages = $conversation->messages()
            ->where('id', '>', $lastId)
            ->get();

        if ($newMessages->isNotEmpty()) {
            $conversation->markAsReadForCustomer();
        }

        $formatted = $newMessages->map(function ($msg) {
            return [
                'id'             => $msg->id,
                'sender_type'    => $msg->sender_type,
                'type'           => $msg->type ?? 'text',
                'message'        => $msg->message,
                'attachment_url' => $msg->attachment_url,
                'metadata'       => $msg->metadata,
                'time'           => $msg->created_at->timezone('Asia/Dhaka')->format('g:i a'),
                'created_at'     => $msg->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success'  => true,
            'messages' => $formatted,
        ]);
    }

    public function sendAttachment(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ]);

        [$conversation] = $this->resolveConversation($request);

        if ($conversation->status === 'closed') {
            $conversation->update(['status' => 'open']);
        }

        $path = $request->file('image')->store('chat_attachments', 'public');
        $url = asset('storage/' . $path);

        $msg = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'customer',
            'sender_id'       => auth()->id(),
            'type'            => 'image',
            'message'         => '📷 Photo Attachment',
            'attachment_url'  => $url,
            'is_read'         => false,
        ]);

        $conversation->increment('unread_admin_count');
        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => [
                'id'             => $msg->id,
                'sender_type'    => 'customer',
                'type'           => 'image',
                'message'        => '📷 Photo Attachment',
                'attachment_url' => $url,
                'time'           => $msg->created_at->format('h:i A'),
            ]
        ]);
    }

    public function sendVoiceNote(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:webm,mp3,wav,ogg,m4a,mp4|max:15360',
        ]);

        [$conversation] = $this->resolveConversation($request);

        if ($conversation->status === 'closed') {
            $conversation->update(['status' => 'open']);
        }

        $path = $request->file('audio')->store('chat_audio', 'public');
        $url = asset('storage/' . $path);

        $msg = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'customer',
            'sender_id'       => auth()->id(),
            'type'            => 'voice',
            'message'         => '🎙️ Voice Note',
            'attachment_url'  => $url,
            'is_read'         => false,
        ]);

        $conversation->increment('unread_admin_count');
        $conversation->update(['last_message_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => [
                'id'             => $msg->id,
                'sender_type'    => 'customer',
                'type'           => 'voice',
                'message'        => '🎙️ Voice Note',
                'attachment_url' => $url,
                'time'           => $msg->created_at->format('h:i A'),
            ]
        ]);
    }
}
