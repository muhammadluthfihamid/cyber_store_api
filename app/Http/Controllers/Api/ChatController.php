<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * GET /api/v1/chats
     * Daftar semua chat milik customer yang login.
     */
    public function index(Request $request): JsonResponse
    {
        $chats = Chat::with(['lastMessage'])
            ->where('customer_id', $request->user()->id)
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Chat $chat) {
                $unread = $chat->messages()
                    ->where('sender_type', 'admin')
                    ->where('is_read', false)
                    ->count();

                $lastMsg = $chat->lastMessage?->message;
                if ($lastMsg && (str_starts_with($lastMsg, '[IMAGE]:') || str_contains($lastMsg, 'data:image'))) {
                    $lastMsg = '📷 [Gambar]';
                }

                return [
                    'id'           => $chat->id,
                    'subject'      => $chat->subject,
                    'product_id'   => $chat->product_id,
                    'product_name' => $chat->product_name,
                    'status'       => $chat->status,
                    'unread_count' => $unread,
                    'last_message' => $lastMsg,
                    'last_message_at' => $chat->last_message_at?->toIso8601String(),
                    'created_at'   => $chat->created_at->toIso8601String(),
                ];
            });

        return response()->json(['chats' => $chats]);
    }

    /**
     * POST /api/v1/chats
     * Mulai percakapan baru (atau ambil existing open chat untuk produk yang sama).
     * Body: { product_id?, subject?, message }
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'message'    => 'required|string|max:10000000',
            'product_id' => 'nullable|exists:products,id',
            'subject'    => 'nullable|string|max:255',
        ]);

        $user      = $request->user();
        $productId = $request->input('product_id');
        $product   = $productId ? Product::find($productId) : null;

        // Cek apakah ada chat open untuk produk yang sama (hindari duplikat)
        $chat = Chat::where('customer_id', $user->id)
            ->where('product_id', $productId)
            ->where('status', 'open')
            ->first();

        $isNewChat = false;
        if (!$chat) {
            $chat = Chat::create([
                'customer_id'  => $user->id,
                'product_id'   => $productId,
                'product_name' => $product?->name,
                'subject'      => $request->input('subject', $product ? "Tanya stok: {$product->name}" : 'Pertanyaan'),
                'status'       => 'open',
                'last_message_at' => now(),
            ]);
            $isNewChat = true;
        }

        // Cek apakah chat ini baru atau belum pernah menerima balasan admin/bot sebelumnya
        $needsBotReply = $isNewChat || $chat->messages()->where('sender_type', 'admin')->doesntExist();

        // Simpan pesan pertama
        $message = ChatMessage::create([
            'chat_id'     => $chat->id,
            'sender_type' => 'customer',
            'sender_id'   => $user->id,
            'message'     => $request->input('message'),
            'is_read'     => false,
        ]);

        $chat->update(['last_message_at' => now()]);

        \App\Http\Controllers\Admin\ChatController::flushRedisCache();

        // Auto-reply chatbot HANYA ketika pesan pertama kali
        if ($needsBotReply) {
            $this->autoReplyBot($chat, $request->input('message'));
        }

        return response()->json([
            'chat'    => ['id' => $chat->id, 'subject' => $chat->subject, 'status' => $chat->status],
            'message' => $this->formatMessage($message),
        ], 201);
    }

    /**
     * GET /api/v1/chats/{chat}/messages
     * Ambil semua pesan dalam chat (polling).
     */
    public function messages(Request $request, Chat $chat): JsonResponse
    {
        // Pastikan hanya customer pemilik chat yang bisa akses
        if ($chat->customer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Tandai pesan admin sebagai sudah dibaca
        $chat->messages()
            ->where('sender_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        \App\Http\Controllers\Admin\ChatController::flushRedisCache();

        $query = $chat->messages()->orderBy('created_at');
        if ($request->filled('after_id')) {
            $query->where('id', '>', (int)$request->query('after_id'));
        }

        $messages = $query->get()->map(fn($m) => $this->formatMessage($m));

        return response()->json([
            'chat_id' => $chat->id,
            'status'  => $chat->status,
            'messages' => $messages,
        ]);
    }

    /**
     * POST /api/v1/chats/{chat}/messages
     * Kirim pesan lanjutan dari customer.
     */
    public function sendMessage(Request $request, Chat $chat): JsonResponse
    {
        if ($chat->customer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($chat->status === 'closed') {
            return response()->json(['message' => 'Chat sudah ditutup.'], 422);
        }

        $request->validate(['message' => 'required|string|max:10000000']);

        // Cek apakah belum pernah ada balasan admin/bot sama sekali di percakapan ini
        $needsBotReply = $chat->messages()->where('sender_type', 'admin')->doesntExist();

        $message = ChatMessage::create([
            'chat_id'     => $chat->id,
            'sender_type' => 'customer',
            'sender_id'   => $request->user()->id,
            'message'     => $request->input('message'),
            'is_read'     => false,
        ]);

        $chat->update(['last_message_at' => now()]);

        \App\Http\Controllers\Admin\ChatController::flushRedisCache();

        // Auto-reply chatbot HANYA jika chat ini belum pernah dibalas sama sekali
        if ($needsBotReply) {
            $this->autoReplyBot($chat, $request->input('message'));
        }

        return response()->json(['message' => $this->formatMessage($message)], 201);
    }

    private function autoReplyBot(Chat $chat, string $userMessage): void
    {
        // Guard: Pastikan bot tidak membalas jika sudah pernah ada pesan dari admin atau bot di chat ini
        if ($chat->messages()->where('sender_type', 'admin')->exists()) {
            return;
        }

        $textLower = strtolower($userMessage);
        $user = $chat->customer;
        $userName = $user?->name ?? 'Kak';
        $product = $chat->product_id ? Product::find($chat->product_id) : null;

        $replyMessage = null;

        // 1. Jika menanyakan stok / produk spesifik
        if ($product && (
            str_contains($textLower, 'stok') || 
            str_contains($textLower, 'ready') || 
            str_contains($textLower, 'ada') || 
            str_contains($textLower, 'stock') || 
            str_contains($textLower, 'size') || 
            str_contains($textLower, 'ukuran') || 
            str_contains($textLower, 'warna') ||
            str_contains(strtolower($chat->subject ?? ''), 'tanya stok')
        )) {
            if ($product->stock > 0) {
                $sizes = is_array($product->sizes) ? implode(', ', $product->sizes) : $product->sizes;
                $replyMessage = "Halo {$userName}! 👋 [BOT AUTO-REPLY]\n\n" .
                    "Produk *{$product->name}* saat ini *READY STOK* (Tersedia {$product->stock} pcs).\n" .
                    ($sizes ? "Pilihan Ukuran: {$sizes}\n" : "") .
                    "Harga: Rp " . number_format($product->price, 0, ',', '.') . "\n\n" .
                    "Silakan langsung melakukan checkout sebelum kehabisan! 🛍️ Tim Admin CS kami juga akan merespons percakapan ini jika Anda memiliki pertanyaan khusus.";
            } else {
                $replyMessage = "Halo {$userName}! 👋 [BOT AUTO-REPLY]\n\n" .
                    "Mohon maaf, produk *{$product->name}* saat ini sedang *HABIS / OUT OF STOCK*.\n\n" .
                    "Admin CS kami telah menerima notifikasi ini dan akan mengecek jadwal restok produk untuk Anda. Mohon tunggu sebentar ya!";
            }
        } 
        // 2. Pertanyaan umum / Salam
        elseif (
            str_contains($textLower, 'halo') || 
            str_contains($textLower, 'hai') || 
            str_contains($textLower, 'pagi') || 
            str_contains($textLower, 'siang') || 
            str_contains($textLower, 'malam') || 
            str_contains($textLower, 'tanya') || 
            str_contains($textLower, 'bantu') || 
            str_contains($textLower, 'ongkir') || 
            str_contains($textLower, 'resi') || 
            str_contains($textLower, 'bayar')
        ) {
            $replyMessage = "Halo {$userName}! 👋 [BOT AUTO-REPLY]\n\n" .
                "Terima kasih telah menghubungi Customer Service BSI Cyber Store.\n" .
                "Pesan Anda sudah kami terima dan Admin CS akan segera membalas percakapan Anda. Silakan sertakan rincian pesanan atau nomor resi jika ada!";
        }
        // 3. Fallback jika pesan pertama tidak cocok kata kunci di atas
        else {
            $replyMessage = "Halo {$userName}! 👋 [BOT AUTO-REPLY]\n\n" .
                "Pesan Anda telah masuk ke sistem Customer Service kami. Admin CS BSI Cyber Store akan segera melayani percakapan Anda.";
        }

        if ($replyMessage) {
            ChatMessage::create([
                'chat_id'     => $chat->id,
                'sender_type' => 'admin',
                'sender_id'   => null,
                'message'     => $replyMessage,
                'is_read'     => false,
            ]);
            $chat->update(['last_message_at' => now()]);
            \App\Http\Controllers\Admin\ChatController::flushRedisCache();
        }
    }

    private function formatMessage(ChatMessage $message): array
    {
        return [
            'id'          => $message->id,
            'sender_type' => $message->sender_type,
            'message'     => $message->message,
            'is_read'     => $message->is_read,
            'created_at'  => $message->created_at->toIso8601String(),
        ];
    }
}
