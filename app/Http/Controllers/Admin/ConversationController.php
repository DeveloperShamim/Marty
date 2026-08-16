<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'open');
        $search = trim((string) $request->query('q', ''));

        $query = Conversation::with(['lastMessage', 'user']);

        if ($status === 'open') {
            $query->where('status', 'open');
        } elseif ($status === 'closed') {
            $query->where('status', 'closed');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $conversations = $query->orderByRaw('COALESCE(last_message_at, created_at) DESC')->paginate(50);
        $unreadCount = Conversation::where('status', 'open')->sum('unread_admin_count');

        $activeId = $request->query('chat');
        $hasExplicitChat = $request->has('chat');
        $activeConversation = null;
        $customerOrders = collect();

        if ($activeId) {
            $activeConversation = Conversation::with('messages')->find($activeId);
        } elseif ($conversations->isNotEmpty()) {
            $activeConversation = Conversation::with('messages')->find($conversations->first()->id);
        }

        if ($activeConversation) {
            if ($hasExplicitChat) {
                $activeConversation->markAsReadForAdmin();
            }
            $customerOrders = $activeConversation->getCustomerOrders();
        }

        return view('admin.conversations.index', [
            'conversations'      => $conversations,
            'activeConversation' => $activeConversation,
            'customerOrders'     => $customerOrders,
            'status'             => $status,
            'search'             => $search,
            'unreadCount'        => $unreadCount,
            'hasExplicitChat'    => $hasExplicitChat,
        ]);
    }

    public function show(Conversation $conversation)
    {
        $conversation->markAsReadForAdmin();

        $messages = $conversation->messages()->get()->map(function ($msg) use ($conversation) {
            return [
                'id'             => $msg->id,
                'sender_type'    => $msg->sender_type,
                'sender_name'    => $msg->sender_type === 'admin' ? 'Support Agent' : $conversation->customer_name,
                'type'           => $msg->type ?? 'text',
                'message'        => $msg->message,
                'attachment_url' => $msg->attachment_url,
                'metadata'       => $msg->metadata,
                'time'           => $msg->created_at->format('h:i A'),
                'date'           => $msg->created_at->format('M d, Y'),
                'created_at'     => $msg->created_at->toIso8601String(),
            ];
        });

        return response()->json([
            'success'      => true,
            'conversation' => [
                'id'              => $conversation->id,
                'customer_name'   => $conversation->customer_name,
                'customer_phone'  => $conversation->customer_phone,
                'customer_email'  => $conversation->customer_email,
                'status'          => $conversation->status,
                'last_message_at' => $conversation->last_message_at?->diffForHumans(),
            ],
            'messages'     => $messages,
            'orders'       => $conversation->getCustomerOrders(),
        ]);
    }

    public function reply(Request $request, Conversation $conversation)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $messageText = trim($request->input('message'));

        $msg = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'admin',
            'sender_id'       => auth()->id(),
            'type'            => 'text',
            'message'         => $messageText,
            'is_read'         => false,
        ]);

        $conversation->increment('unread_customer_count');
        $conversation->update([
            'last_message_at' => now(),
            'status'          => 'open',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id'          => $msg->id,
                    'sender_type' => 'admin',
                    'sender_name' => auth()->user()?->name ?? 'Support Agent',
                    'type'        => 'text',
                    'message'     => $msg->message,
                    'metadata'    => null,
                    'time'        => $msg->created_at->format('h:i A'),
                    'created_at'  => $msg->created_at->toIso8601String(),
                ],
            ]);
        }

        return back()->with('status', 'Reply sent successfully.');
    }

    public function sendProduct(Request $request, Conversation $conversation)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($request->input('product_id'));

        $metadata = [
            'product_id'      => $product->id,
            'name'            => $product->name,
            'price'           => (float) $product->regular_price,
            'formatted_price' => money($product->regular_price),
            'image_url'       => $product->imageUrl(),
            'url'             => route('product.show', $product->slug ?: $product->id),
        ];

        $msg = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'admin',
            'sender_id'       => auth()->id(),
            'type'            => 'product',
            'message'         => "📦 Product Recommendation: {$product->name}",
            'metadata'        => $metadata,
            'is_read'         => false,
        ]);

        $conversation->increment('unread_customer_count');
        $conversation->update(['last_message_at' => now(), 'status' => 'open']);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('status', 'Product Card sent to chat.');
    }

    public function sendOrder(Request $request, Conversation $conversation)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $order = Order::with('items')->findOrFail($request->input('order_id'));
        $orderTotal = (float) ($order->total > 0 ? $order->total : $order->grand_total);

        $itemsSummary = $order->items->map(function ($item) {
            $name = $item->product_name ?: ($item->product?->name ?? 'Product');
            return $name . ($item->variant_label ? " ({$item->variant_label})" : '') . " ×{$item->quantity}";
        })->join(', ');

        $metadata = [
            'order_id'         => $order->id,
            'order_number'     => $order->order_number ?? '#' . $order->id,
            'total'            => $orderTotal,
            'formatted_total'  => money($orderTotal),
            'payment_status'   => ucfirst($order->payment_status ?? 'pending'),
            'delivery_status'  => ucfirst($order->status ?? 'pending'),
            'items_count'      => $order->items->sum('quantity'),
            'items_summary'    => $itemsSummary,
            'tracking_url'     => route('track', ['order_number' => $order->order_number ?? $order->id, 'token' => $order->secureTrackingToken()]),
        ];

        $msg = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'admin',
            'sender_id'       => auth()->id(),
            'type'            => 'order',
            'message'         => "🧾 Order Details: Order {$metadata['order_number']} ({$metadata['formatted_total']})",
            'metadata'        => $metadata,
            'is_read'         => false,
        ]);

        $conversation->increment('unread_customer_count');
        $conversation->update(['last_message_at' => now(), 'status' => 'open']);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('status', 'Order Card sent to chat.');
    }

    public function updateOrderStatus(Request $request, Conversation $conversation, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $newStatus = $request->input('status');
        $wasNotCancelled = $order->status !== 'cancelled';

        if ($newStatus === 'cancelled' && $wasNotCancelled && method_exists($order, 'restoreStock')) {
            $order->restoreStock();
            $order->releaseCoupon();
        }

        $order->update(['status' => $newStatus]);

        return back()->with('status', "Order {$order->order_number} status updated to " . ucfirst($newStatus) . '.');
    }

    public function sendCoupon(Request $request, Conversation $conversation)
    {
        $request->validate([
            'coupon_id' => 'required|exists:coupons,id',
        ]);

        $coupon = Coupon::findOrFail($request->input('coupon_id'));

        $discountText = $coupon->type === 'percent' 
            ? "{$coupon->value}% OFF" 
            : money($coupon->value) . " OFF";

        $metadata = [
            'coupon_id'     => $coupon->id,
            'code'          => strtoupper($coupon->code),
            'discount_text' => $discountText,
            'min_spend'     => $coupon->min_spend ? money($coupon->min_spend) : 'No Minimum',
            'expires_at'    => $coupon->expires_at ? $coupon->expires_at->format('M d, Y') : 'No Expiry',
        ];

        $msg = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'admin',
            'sender_id'       => auth()->id(),
            'type'            => 'coupon',
            'message'         => "🎟️ Discount Coupon Offered: Code {$coupon->code} ({$discountText})",
            'metadata'        => $metadata,
            'is_read'         => false,
        ]);

        $conversation->increment('unread_customer_count');
        $conversation->update(['last_message_at' => now(), 'status' => 'open']);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('status', 'Coupon Card sent to chat.');
    }

    public function uploadAttachment(Request $request, Conversation $conversation)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:10240',
        ]);

        $path = $request->file('image')->store('chat_attachments', 'public');
        $url = asset('storage/' . $path);

        $msg = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'admin',
            'sender_id'       => auth()->id(),
            'type'            => 'image',
            'message'         => '📷 Photo Attachment',
            'attachment_url'  => $url,
            'is_read'         => false,
        ]);

        $conversation->increment('unread_customer_count');
        $conversation->update(['last_message_at' => now(), 'status' => 'open']);

        return response()->json([
            'success' => true,
            'message' => [
                'id'             => $msg->id,
                'sender_type'    => 'admin',
                'type'           => 'image',
                'message'        => '📷 Photo Attachment',
                'attachment_url' => $url,
                'time'           => $msg->created_at->format('h:i A'),
            ]
        ]);
    }

    public function uploadVoiceNote(Request $request, Conversation $conversation)
    {
        $request->validate([
            'audio' => 'required|file|mimes:webm,mp3,wav,ogg,m4a,mp4|max:15360',
        ]);

        $path = $request->file('audio')->store('chat_audio', 'public');
        $url = asset('storage/' . $path);

        $msg = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_type'     => 'admin',
            'sender_id'       => auth()->id(),
            'type'            => 'voice',
            'message'         => '🎙️ Voice Note',
            'attachment_url'  => $url,
            'is_read'         => false,
        ]);

        $conversation->increment('unread_customer_count');
        $conversation->update(['last_message_at' => now(), 'status' => 'open']);

        return response()->json([
            'success' => true,
            'message' => [
                'id'             => $msg->id,
                'sender_type'    => 'admin',
                'type'           => 'voice',
                'message'        => '🎙️ Voice Note',
                'attachment_url' => $url,
                'time'           => $msg->created_at->format('h:i A'),
            ]
        ]);
    }

    public function getCoupons()
    {
        $coupons = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->get()
            ->map(function ($c) {
                return [
                    'id'            => $c->id,
                    'code'          => strtoupper($c->code),
                    'discount_text' => $c->type === 'percent' ? "{$c->value}% OFF" : money($c->value) . " OFF",
                ];
            });

        return response()->json(['coupons' => $coupons]);
    }

    public function searchProducts(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $products = Product::where('name', 'like', "%{$q}%")
            ->orWhere('sku', 'like', "%{$q}%")
            ->orderBy('id', 'desc')
            ->take(15)
            ->get()
            ->map(function ($p) {
                return [
                    'id'              => $p->id,
                    'name'            => $p->name,
                    'sku'             => $p->sku,
                    'formatted_price' => money($p->regular_price),
                    'image_url'       => $p->imageUrl(),
                ];
            });

        return response()->json(['products' => $products]);
    }

    public function toggleStatus(Conversation $conversation)
    {
        $newStatus = ($conversation->status === 'open') ? 'closed' : 'open';
        $conversation->update(['status' => $newStatus]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'status'  => $newStatus,
            ]);
        }

        return redirect()
            ->route('admin.conversations.index', ['status' => $newStatus, 'chat' => $conversation->id])
            ->with('status', 'Conversation marked as ' . $newStatus . '.');
    }

    public function pruneStorage(Request $request)
    {
        $days = (int) $request->input('days', 90);
        if ($days < 1) $days = 90;

        \Illuminate\Support\Facades\Artisan::call('chat:prune', ['--days' => $days]);
        $output = \Illuminate\Support\Facades\Artisan::output();

        return back()->with('status', 'Storage cleanup complete! ' . trim(strip_tags($output)));
    }
}
