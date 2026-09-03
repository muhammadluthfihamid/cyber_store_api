@use('Illuminate\Support\Str')
@extends('admin.layouts.app')
@section('title', isset($chat) ? 'Chat dengan ' . $chat->customer?->name : 'Chat Customer')
@section('page-title', 'Support Chat')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<span>Support Chat</span>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/chat.css') }}?v=1">
<style>
    @keyframes fadeInMsg {
        from {
            opacity: 0;
            transform: translateY(6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .slack-msg-row {
        animation: fadeInMsg 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .slack-chat-item {
        transition: background-color 0.2s ease, border-left 0.2s ease;
    }
</style>
@endpush

@section('content')
<div class="slack-chat-workspace">
    <!-- Left Sidebar: Chat List -->
    <div class="slack-sidebar">
        <div class="slack-sidebar-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span style="display: flex; align-items: center; gap: 8px;">
                <iconify-icon icon="flat-color-icons:comments" style="font-size: 22px;"></iconify-icon> Customer Support
            </span>
            <form method="POST" action="{{ route('admin.cache.flush-chats') }}" style="margin: 0;" onsubmit="return confirm('Bersihkan cache chat di Redis?')">
                @csrf
                <button type="submit" class="btn btn-sm" style="background-color: #F59E0B; color: white; border: none; padding: 3px 8px; border-radius: 6px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;" title="Bersihkan Cache Redis">
                    <iconify-icon icon="flat-color-icons:synchronize" style="font-size: 14px;"></iconify-icon> Cache
                </button>
            </form>
        </div>
        <div class="slack-search-bar">
            <form method="GET" action="{{ route('admin.chats.index') }}" style="display: flex; gap: 5px; width: 100%; align-items: center;">
                <div class="search-input-wrapper" style="flex: 1; min-width: 0; position: relative;">
                    <input type="text" name="search" class="slack-search-input search-input"
                        placeholder="Cari customer..." value="{{ request('search') }}" data-suggestion-url="{{ route('admin.chats.suggestions') }}" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-sm" style="background-color: #3C3565; color: #ffffff; border: none; height: 32px; padding: 0 10px; border-radius: 8px; font-weight: 600; font-size: 11.5px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; gap: 4px;" title="Cari Customer">
                    <iconify-icon icon="lucide:search" style="font-size: 13px;"></iconify-icon>
                    Cari
                </button>
                @if(request('search'))
                <a href="{{ route('admin.chats.index') }}" class="btn btn-sm" style="background: var(--bg-card, #ffffff); color: var(--text-primary, #1e293b); border: 1px solid var(--border, #cbd5e1); border-radius: 8px; height: 32px; width: 32px; padding: 0; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center;" title="Reset Search">
                    <iconify-icon icon="lucide:x" style="font-size: 14px; color: var(--text-primary, #1e293b);"></iconify-icon>
                </a>
                @endif
            </form>
        </div>
        <div class="slack-chat-list">
            @forelse($chats as $c)
            @php
            $initials = strtoupper(substr($c->customer?->name ?? 'C', 0, 1));
            $unread = $c->unread_count;
            $isActive = isset($chat) && $c->id === $chat->id;
            @endphp
            <a href="{{ route('admin.chats.show', $c) }}" class="slack-chat-item {{ $isActive ? 'active' : '' }}">
                <div class="slack-chat-avatar">{{ $initials }}</div>
                <div class="slack-chat-details">
                    <div class="slack-chat-name-row">
                        <span class="text-truncate">{{ $c->customer?->name ?? 'Customer' }}</span>
                        @if($unread > 0)
                        <span class="slack-chat-badge">{{ $unread }}</span>
                        @endif
                    </div>
                    <div class="slack-chat-message">
                        @if($c->product_name)
                        <span style="color:#4f6ef7; display: inline-flex; align-items: center; gap: 3px;">[<iconify-icon icon="flat-color-icons:box" style="font-size: 13px;"></iconify-icon> {{ $c->product_name }}]</span>
                        @endif
                        @if(\Illuminate\Support\Str::startsWith($c->lastMessage?->message ?? '', '[IMAGE]:') || \Illuminate\Support\Str::contains($c->lastMessage?->message ?? '', 'data:image'))
                        <iconify-icon icon="flat-color-icons:picture" style="font-size: 14px; vertical-align: middle;"></iconify-icon> [Gambar]
                        @else
                        {{ $c->lastMessage?->message ?? 'Memulai percakapan...' }}
                        @endif
                    </div>
                </div>
            </a>
            @empty
            <div style="text-align:center;color:#94a3b8;padding:40px 10px;font-size:13px;">
                Belum ada chat masuk.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Middle: Chat Conversation Area -->
    <div class="slack-chat-area">
        @if(isset($chat))
        <div class="slack-chat-header">
            <div>
                <div class="slack-chat-header-title">{{ $chat->customer?->name ?? 'Customer' }}</div>
                @if($chat->status === 'open')
                <div class="slack-chat-header-status">
                    <span style="font-size: 8px;">●</span> Online
                </div>
                @else
                <div class="slack-chat-header-status closed">
                    <span style="font-size: 8px;">●</span> Closed
                </div>
                @endif
            </div>
            <div style="position:relative; display:inline-block;">
                <button type="button" id="threeDotsBtn" class="btn btn-sm" style="background:transparent; border:none; color: #64748b; font-size: 18px; cursor: pointer; padding: 6px; border-radius: 50%; display: flex; align-items: center; justify-content: center; outline: none;" title="Opsi Chat">
                    <iconify-icon icon="lucide:more-vertical"></iconify-icon>
                </button>

                {{-- Dropdown Menu --}}
                <div id="threeDotsDropdown" style="display:none; position:absolute; right:0; top:30px; background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; width:160px; box-shadow:0 8px 20px rgba(0,0,0,0.08); z-index:1050; padding:4px 0;">
                    <a href="javascript:void(0);" id="toggleDetailsBtn" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#1e293b; font-size:13px; text-decoration:none; transition:background 0.2s;">
                        <iconify-icon icon="flat-color-icons:info" style="font-size: 16px;"></iconify-icon> Toggle Info
                    </a>
                    @if($chat->status === 'open')
                    <a href="javascript:void(0);" onclick="if(confirm('Tutup percakapan ini?')) document.getElementById('dropdownCloseForm').submit();" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#ef4444; font-size:13px; text-decoration:none; transition:background 0.2s;">
                        <iconify-icon icon="flat-color-icons:cancel" style="font-size: 16px;"></iconify-icon> Tutup Chat
                    </a>
                    <form id="dropdownCloseForm" action="{{ route('admin.chats.close', $chat) }}" method="POST" style="display:none;">
                        @csrf
                    </form>
                    @else
                    <a href="javascript:void(0);" onclick="document.getElementById('dropdownReopenForm').submit();" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#0F62FE; font-size:13px; text-decoration:none; transition:background 0.2s;">
                        <iconify-icon icon="flat-color-icons:undo" style="font-size: 16px;"></iconify-icon> Buka Kembali
                    </a>
                    <form id="dropdownReopenForm" action="{{ route('admin.chats.reopen', $chat) }}" method="POST" style="display:none;">
                        @csrf
                    </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bubbles Scrollable Area --}}
        <div class="slack-messages-container" id="chatContainer">
            @forelse($chat->messages as $msg)
            @php
            $isSelf = $msg->sender_type === 'admin';
            $avatarText = $isSelf ? 'A' : strtoupper(substr($chat->customer?->name ?? 'C', 0, 1));

            $msgText = $msg->message;
            $bubbleStyle = '';
            if (str_starts_with($msgText, '[IMAGE]:')) {
            $base64 = substr($msgText, 8);
            $msgContent = '<img src="' . $base64 . '" style="max-width:260px; border-radius:10px; display:block; cursor:pointer;" onclick="openImageModal(this.src)" title="Klik untuk lihat gambar penuh" />';
            } elseif (str_starts_with($msgText, '[STICKER]:')) {
            $stickerUrl = substr($msgText, 10);
            $msgContent = '<img src="' . e($stickerUrl) . '" style="width:100px; height:100px; display:block;" />';
            $bubbleStyle = 'background:transparent; box-shadow:none; padding:0;';
            } elseif (str_starts_with($msgText, '[VOICE]:')) {
            $duration = substr($msgText, 8);
            $padDuration = str_pad($duration, 2, '0', STR_PAD_LEFT);
            $msgContent = '
            <div style="display:flex; align-items:center; gap:10px; padding:4px 0; min-width:180px;">
                <button type="button" style="background:rgba(255,255,255,0.15); border:none; border-radius:50%; width:32px; height:32px; color:white; display:flex; align-items:center; justify-content:center; cursor:pointer;" onclick="playMockAudio(this, ' . e($duration) . ')">
                    <iconify-icon icon="lucide:play" style="font-size:16px;"></iconify-icon>
                </button>
                <div style="flex:1;">
                    <div style="height:4px; background:rgba(255,255,255,0.2); border-radius:2px; overflow:hidden;">
                        <div class="progress-bar-fill" style="width:0%; height:100%; background:#fff; transition:width 0.1s linear;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:10px; color:rgba(255,255,255,0.7); margin-top:4px;">
                        <span class="audio-time">0:00</span>
                        <span>0:' . $padDuration . '</span>
                    </div>
                </div>
            </div>';
            } else {
            $msgContent = e($msgText);
            }
            @endphp

            <div class="slack-msg-row {{ $msg->sender_type }}" data-msg-id="{{ $msg->id }}">
                <div class="slack-msg-avatar">{{ $avatarText }}</div>
                <div class="slack-msg-body">
                    <div class="slack-msg-bubble {{ !empty($bubbleStyle) ? 'no-bubble-style' : '' }}">
                        {!! $msgContent !!}
                    </div>
                    <div class="slack-msg-time" style="display: flex; align-items: center; gap: 4px;">
                        {{ $msg->created_at->format('H:i') }}
                        @if($isSelf)
                        @if($msg->is_read)
                        <iconify-icon icon="lucide:check-check" style="color: #3b82f6; font-size: 14px;" title="Dibaca"></iconify-icon>
                        @else
                        <iconify-icon icon="lucide:check-check" style="color: #64748b; font-size: 14px;" title="Terkirim"></iconify-icon>
                        @endif
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-chat-notice" style="text-align:center;color:#94a3b8;padding:40px 0;font-size:14px;">
                Belum ada pesan.
            </div>
            @endforelse
        </div>

        {{-- Reply Input Bar --}}
        <div class="slack-input-container">
            @if($chat->status === 'open')
            <form method="POST" action="{{ route('admin.chats.reply', $chat) }}" id="replyForm">
                @csrf
                <div class="slack-input-pill">
                    <!-- Input File Tersembunyi untuk Gambar -->
                    <input type="file" id="imageFileInput" accept="image/*" style="display:none;" />

                    <div class="slack-input-icon" id="paperclipBtn" title="Kirim Gambar">
                        <iconify-icon icon="lucide:paperclip" style="font-size: 18px;"></iconify-icon>
                    </div>
                    <div class="slack-input-icon">
                        <iconify-icon icon="lucide:mic" style="font-size: 18px;"></iconify-icon>
                    </div>
                    <input type="text" name="message" id="messageInput" class="slack-input-field"
                        placeholder="Write a message..." required autocomplete="off" />

                    <!-- Emoji Popover Container -->
                    <div style="position:relative; display:flex; align-items:center;">
                        <div class="slack-input-icon" id="stickerBtn" title="Pilih Emoji">
                            <iconify-icon icon="lucide:smile" style="font-size: 18px;"></iconify-icon>
                        </div>

                        {{-- Emoji Picker Popover --}}
                        <div id="stickerPopover" style="display:none; position:absolute; bottom:40px; right:0; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:12px; width:280px; box-shadow:0 10px 25px rgba(0,0,0,0.08); z-index:1000;">
                            <div style="font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">Pilih Emoji</div>
                            <div style="display:grid; grid-template-columns: repeat(7, 1fr); gap:6px; max-height:160px; overflow-y:auto; font-size:20px; text-align:center;">
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😀</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😂</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😊</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😍</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🤣</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🥰</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😘</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😎</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😭</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🥺</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😡</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😮</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🤔</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😴</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👍</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👎</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🙏</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👏</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🙌</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👋</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👌</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">✌️</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">❤️</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🔥</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">✨</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🎉</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">💯</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">📦</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">💬</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">❓</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">✅</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">❌</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👕</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🧢</span>
                                <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🥤</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="slack-send-btn" id="sendBtn">
                        <i class="bi bi-send-fill" style="font-size: 14px;"></i>
                    </button>
                </div>
            </form>
            @else
            <div style="text-align:center; padding:12px; background:#fee2e2; border:1px solid #fecaca; border-radius:12px; color:#ef4444; font-size:13px; font-weight:500;">
                ⚠️ Percakapan ini sudah ditutup. Buka kembali untuk membalas.
            </div>
            @endif
        </div>
        @else
        <div class="slack-chat-header">
            <div class="slack-chat-header-title">Detail Obrolan</div>
        </div>
        <div class="slack-messages-container" style="justify-content:center; align-items:center; text-align:center; min-height:300px;">
            <div style="margin-bottom:16px;"><iconify-icon icon="flat-color-icons:comments" style="font-size: 56px;"></iconify-icon></div>
            <h3 style="color:#1e293b; font-weight:700; margin-bottom:8px;">Selamat Datang di Customer Support</h3>
            <p style="color:#64748b; font-size:13px; max-width:320px; margin:0 auto; line-height:1.6;">
                Pilih salah satu customer di panel kiri untuk mulai membaca dan membalas pesan obrolan secara interaktif.
            </p>
        </div>
        @endif
    </div>

    <!-- Right Pane: Context & Customer Details -->
    <div class="slack-details-pane">
        @if(isset($chat))
        <!-- Section: Product queried -->
        <div class="slack-details-section">
            <div class="slack-details-title">Produk Yang Ditanyakan</div>
            @if($chat->product)
            @if($chat->product->main_photo)
            @php
            $photoUrl = '/storage/' . implode('/', array_map('rawurlencode', explode('/', $chat->product->main_photo)));
            @endphp
            <img src="{{ $photoUrl }}" style="width:100%; border-radius:10px; margin-bottom:12px; border:1px solid #e2e8f0; background:#fff; display:block;"
                onerror="this.style.display='none'; document.getElementById('product-image-placeholder').style.display='flex';" />
            <div id="product-image-placeholder" style="width:100%; height:160px; background:#f1f5f9; border-radius:10px; margin-bottom:12px; display:none; align-items:center; justify-content:center; border:1px solid #e2e8f0;">
                <iconify-icon icon="flat-color-icons:picture" style="font-size: 48px;"></iconify-icon>
            </div>
            @else
            <div id="product-image-placeholder" style="width:100%; height:160px; background:#f1f5f9; border-radius:10px; margin-bottom:12px; display:flex; align-items:center; justify-content:center; border:1px solid #e2e8f0;">
                <iconify-icon icon="flat-color-icons:picture" style="font-size: 48px;"></iconify-icon>
            </div>
            @endif
            <div style="font-weight:700; font-size:14.5px; color:#1e293b; line-height:1.4;">{{ $chat->product->name }}</div>
            <div style="color:#0F62FE; font-weight:700; font-size:15px; margin-top:6px;">
                Rp. {{ number_format($chat->product->price, 0, ',', '.') }}
            </div>
            <div style="display:inline-block; background:#e2e8f0; color:#475569; font-size:11px; padding:2px 8px; border-radius:4px; margin-top:10px; font-weight:500;">
                Tanya Stok
            </div>
            @elseif($chat->product_name)
            <div style="font-weight:600; font-size:13.5px; color:#1e293b; line-height:1.4; display:flex; align-items:center; gap:6px;">
                <iconify-icon icon="flat-color-icons:box" style="font-size: 16px;"></iconify-icon> {{ $chat->product_name }}
            </div>
            <div style="display:inline-block; background:#e2e8f0; color:#475569; font-size:11px; padding:2px 8px; border-radius:4px; margin-top:8px;">
                Konteks Manual
            </div>
            @else
            <div style="color:#94a3b8; font-size:13px; text-align:center; padding:16px 0;">
                Tidak ada produk spesifik.
            </div>
            @endif
        </div>

        <!-- Section: Customer Info -->
        <div class="slack-details-section">
            <div class="slack-details-title">Customer Info</div>
            <div style="display:flex; flex-direction:column; gap:8px; font-size:13px;">
                <div>
                    <span style="color:#94a3b8;">Nama:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px;">{{ $chat->customer?->name ?? '-' }}</div>
                </div>
                <div>
                    <span style="color:#94a3b8;">Email:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px; word-break:break-all;">{{ $chat->customer?->email ?? '-' }}</div>
                </div>
                <div>
                    <span style="color:#94a3b8;">Daftar Akun:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px;">{{ $chat->customer?->created_at ? $chat->customer->created_at->format('d M Y') : '-' }}</div>
                </div>
            </div>
        </div>

        <!-- Section: Actions -->
        <div class="slack-details-section">
            <div class="slack-details-title">Status Chat</div>
            @if($chat->status === 'open')
            <form action="{{ route('admin.chats.close', $chat) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger" style="width:100%; font-size:13px; font-weight:700; border-radius:8px; padding:10px; display:inline-flex; align-items:center; justify-content:center; gap:6px;"
                    onclick="return confirm('Tutup percakapan ini?')">
                    <iconify-icon icon="flat-color-icons:cancel" style="font-size: 16px;"></iconify-icon> Tutup Chat
                </button>
            </form>
            @else
            <form action="{{ route('admin.chats.reopen', $chat) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary" style="width:100%; font-size:13px; font-weight:700; border-radius:8px; padding:10px; display:inline-flex; align-items:center; justify-content:center; gap:6px;">
                    <iconify-icon icon="flat-color-icons:undo" style="font-size: 16px;"></iconify-icon> Buka Kembali
                </button>
            </form>
            @endif
        </div>
        @else
        <div class="slack-details-section">
            <div class="slack-details-title">Detail Informasi</div>
            <div style="text-align:center; color:#94a3b8; font-size:13px; padding-top:40px;">
                Tidak ada percakapan aktif.
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script id="chat-config-data" type="application/json">
{!! json_encode([
    'activeChatId' => isset($chat) ? $chat->id : null,
    'activeChatUrl' => isset($chat) ? route('admin.chats.show', $chat) : '',
    'replyUrl' => isset($chat) ? route('admin.chats.reply', $chat) : '',
    'customerName' => isset($chat) ? ($chat->customer?->name ?? 'Customer') : 'Customer',
    'customerInitial' => isset($chat) ? strtoupper(substr($chat->customer?->name ?? 'C', 0, 1)) : 'C',
    'chatsIndexUrl' => route('admin.chats.index')
]) !!}
</script>

<script>
    // ── Global Context & Initial State ──────────────────────────
    const chatConfig = JSON.parse(document.getElementById('chat-config-data')?.textContent || '{}');
    const activeChatId = chatConfig.activeChatId || null;
    const activeChatUrl = chatConfig.activeChatUrl || '';
    const replyUrl = chatConfig.replyUrl || '';
    const customerName = chatConfig.customerName || 'Customer';
    const customerInitial = chatConfig.customerInitial || 'C';
    const chatsIndexUrl = chatConfig.chatsIndexUrl || '';

    const chatContainer = document.getElementById('chatContainer');
    const messageInput = document.getElementById('messageInput');
    const replyForm = document.getElementById('replyForm');
    const sendBtn = document.getElementById('sendBtn');

    const renderedMsgIds = new Set();
    let lastMsgId = 0;

    // Collect initial message IDs from server rendered DOM
    if (chatContainer) {
        document.querySelectorAll('.slack-msg-row[data-msg-id]').forEach(el => {
            const id = parseInt(el.getAttribute('data-msg-id'));
            if (id) {
                renderedMsgIds.add(id);
                if (id > lastMsgId) lastMsgId = id;
            }
        });
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // ── Audio Chime for New Incoming Messages ────────────────────
    let audioCtx = null;
    function playIncomingSound() {
        try {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
            if (!audioCtx) return;

            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = 'sine';

            const now = audioCtx.currentTime;
            osc.frequency.setValueAtTime(659.25, now); // E5
            osc.frequency.setValueAtTime(880, now + 0.08); // A5
            gain.gain.setValueAtTime(0.12, now);
            gain.gain.exponentialRampToValueAtTime(0.01, now + 0.35);

            osc.start(now);
            osc.stop(now + 0.35);
        } catch (e) {}
    }

    // ── Safe HTML & Message Renderer Helpers ──────────────────────
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderMessageContent(msgText) {
        if (!msgText) return { html: '', noBubble: false };
        if (msgText.startsWith('[IMAGE]:')) {
            const base64 = msgText.substring(8);
            return {
                html: `<img src="${base64}" style="max-width:260px; border-radius:10px; display:block; cursor:pointer;" onclick="openImageModal(this.src)" title="Klik untuk lihat gambar penuh" />`,
                noBubble: false
            };
        } else if (msgText.startsWith('[STICKER]:')) {
            const stickerUrl = msgText.substring(10);
            return {
                html: `<img src="${escapeHtml(stickerUrl)}" style="width:100px; height:100px; display:block;" />`,
                noBubble: true
            };
        } else if (msgText.startsWith('[VOICE]:')) {
            const duration = msgText.substring(8);
            const padDuration = String(duration).padStart(2, '0');
            return {
                html: `
                <div style="display:flex; align-items:center; gap:10px; padding:4px 0; min-width:180px;">
                    <button type="button" style="background:rgba(255,255,255,0.15); border:none; border-radius:50%; width:32px; height:32px; color:white; display:flex; align-items:center; justify-content:center; cursor:pointer;" onclick="playMockAudio(this, ${escapeHtml(duration)})">
                        <iconify-icon icon="lucide:play" style="font-size:16px;"></iconify-icon>
                    </button>
                    <div style="flex:1;">
                        <div style="height:4px; background:rgba(255,255,255,0.2); border-radius:2px; overflow:hidden;">
                            <div class="progress-bar-fill" style="width:0%; height:100%; background:#fff; transition:width 0.1s linear;"></div>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-size:10px; color:rgba(255,255,255,0.7); margin-top:4px;">
                            <span class="audio-time">0:00</span>
                            <span>0:${padDuration}</span>
                        </div>
                    </div>
                </div>`,
                noBubble: false
            };
        } else {
            const safeText = escapeHtml(msgText).replace(/\n/g, '<br>');
            return {
                html: safeText,
                noBubble: false
            };
        }
    }

    function createMessageRow(msg) {
        const isSelf = msg.sender_type === 'admin';
        const avatarText = isSelf ? 'A' : customerInitial;
        const { html: contentHtml, noBubble } = renderMessageContent(msg.message);

        const checkIcon = isSelf ? (
            msg.is_read 
                ? '<iconify-icon icon="lucide:check-check" style="color: #3b82f6; font-size: 14px;" title="Dibaca"></iconify-icon>'
                : '<iconify-icon icon="lucide:check-check" style="color: #64748b; font-size: 14px;" title="Terkirim"></iconify-icon>'
        ) : '';

        const row = document.createElement('div');
        row.className = `slack-msg-row ${msg.sender_type}`;
        row.setAttribute('data-msg-id', msg.id);
        row.innerHTML = `
            <div class="slack-msg-avatar">${avatarText}</div>
            <div class="slack-msg-body">
                <div class="slack-msg-bubble ${noBubble ? 'no-bubble-style' : ''}">
                    ${contentHtml}
                </div>
                <div class="slack-msg-time" style="display: flex; align-items: center; gap: 4px;">
                    ${msg.formatted_time || ''}
                    ${checkIcon}
                </div>
            </div>
        `;
        return row;
    }

    // ── Update Active Chat Preview in Left Sidebar ───────────────
    function updateActiveChatSidebarPreview(lastMsgText) {
        if (!activeChatId) return;
        const activeItem = document.querySelector(`.slack-chat-item[href*="${activeChatId}"], .slack-chat-item.active`);
        if (activeItem) {
            const msgEl = activeItem.querySelector('.slack-chat-message');
            if (msgEl) {
                let preview = lastMsgText;
                if (preview.startsWith('[IMAGE]:') || preview.includes('data:image')) {
                    preview = '<iconify-icon icon="flat-color-icons:picture" style="font-size: 14px; vertical-align: middle;"></iconify-icon> [Gambar]';
                } else if (preview.startsWith('[STICKER]:')) {
                    preview = '😄 [Stiker]';
                } else if (preview.startsWith('[VOICE]:')) {
                    preview = '🎤 [Pesan Suara]';
                } else {
                    preview = escapeHtml(preview);
                }
                const prodSpan = msgEl.querySelector('span');
                if (prodSpan) {
                    msgEl.innerHTML = prodSpan.outerHTML + ' ' + preview;
                } else {
                    msgEl.innerHTML = preview;
                }
            }
            // Move item to top of sidebar list
            const list = document.querySelector('.slack-chat-list');
            if (list && list.firstElementChild !== activeItem) {
                list.insertBefore(activeItem, list.firstElementChild);
            }
        }
    }

    // ── AJAX Message Sender (No-Refresh) ─────────────────────────
    let isSending = false;

    async function sendAdminMessage(messageContent) {
        if (!replyUrl || isSending || !messageContent || messageContent.trim() === '') return;

        isSending = true;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
            || document.querySelector('input[name="_token"]')?.value;

        if (sendBtn) {
            sendBtn.disabled = true;
            sendBtn.style.opacity = '0.6';
        }

        const formData = new FormData();
        formData.append('message', messageContent);
        if (csrfToken) formData.append('_token', csrfToken);

        try {
            const res = await fetch(replyUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken || ''
                },
                body: formData
            });

            const data = await res.json();
            if (res.ok && data.success && data.message) {
                if (messageInput) {
                    messageInput.value = '';
                    messageInput.focus();
                }

                // Remove empty notice placeholder if any
                const emptyNotice = chatContainer?.querySelector('.empty-chat-notice');
                if (emptyNotice) emptyNotice.remove();

                if (!renderedMsgIds.has(data.message.id)) {
                    renderedMsgIds.add(data.message.id);
                    if (data.message.id > lastMsgId) lastMsgId = data.message.id;
                    const row = createMessageRow(data.message);
                    chatContainer?.appendChild(row);
                    if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;
                }

                updateActiveChatSidebarPreview(data.message.message);
            } else {
                alert(data.error || 'Gagal mengirim pesan.');
            }
        } catch (err) {
            console.error('Error sending message:', err);
            alert('Gagal mengirim pesan. Silakan periksa koneksi Anda.');
        } finally {
            isSending = false;
            if (sendBtn) {
                sendBtn.disabled = false;
                sendBtn.style.opacity = '1';
            }
        }
    }

    // Intercept form submit
    if (replyForm) {
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (messageInput && messageInput.value.trim() !== '') {
                sendAdminMessage(messageInput.value.trim());
            }
        });
    }

    // Enter key sends without page refresh
    if (messageInput) {
        messageInput.focus();
        messageInput.addEventListener('keydown', function(e) {
            if ((e.key === 'Enter' || e.keyCode === 13) && !e.shiftKey) {
                e.preventDefault();
                if (messageInput.value.trim() !== '') {
                    sendAdminMessage(messageInput.value.trim());
                }
            }
        });
    }

    // ── Image Upload via AJAX (No-Refresh) ────────────────────────
    const paperclipBtn = document.getElementById('paperclipBtn');
    const imageFileInput = document.getElementById('imageFileInput');

    if (paperclipBtn && imageFileInput) {
        paperclipBtn.addEventListener('click', () => {
            imageFileInput.click();
        });

        imageFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) {
                    alert('Ukuran gambar terlalu besar (maksimal 10MB).');
                    imageFileInput.value = '';
                    return;
                }
                const reader = new window.FileReader();
                reader.onload = function(e) {
                    const base64Msg = '[IMAGE]:' + e.target.result;
                    sendAdminMessage(base64Msg);
                    imageFileInput.value = '';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ── Emoji Popover Interaction ────────────────────────────────
    const stickerBtn = document.getElementById('stickerBtn');
    const stickerPopover = document.getElementById('stickerPopover');

    if (stickerBtn && stickerPopover) {
        stickerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            stickerPopover.style.display = stickerPopover.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', (e) => {
            if (stickerPopover && !stickerPopover.contains(e.target) && e.target !== stickerBtn) {
                stickerPopover.style.display = 'none';
            }
        });
    }

    document.querySelectorAll('.emoji-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.stopPropagation();
            const emoji = this.textContent;
            if (emoji && messageInput) {
                const startPos = messageInput.selectionStart || 0;
                const endPos = messageInput.selectionEnd || 0;
                const text = messageInput.value;
                messageInput.value = text.substring(0, startPos) + emoji + text.substring(endPos);
                const newCursorPos = startPos + emoji.length;
                messageInput.setSelectionRange(newCursorPos, newCursorPos);
                messageInput.focus();
            }
        });
    });

    // ── Three Dots & Info Pane Toggle ────────────────────────────
    const threeDotsBtn = document.getElementById('threeDotsBtn');
    const threeDotsDropdown = document.getElementById('threeDotsDropdown');
    const toggleDetailsBtn = document.getElementById('toggleDetailsBtn');
    const detailsPane = document.querySelector('.slack-details-pane');

    if (threeDotsBtn && threeDotsDropdown) {
        threeDotsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const isHidden = window.getComputedStyle(threeDotsDropdown).display === 'none';
            threeDotsDropdown.style.display = isHidden ? 'block' : 'none';
        });

        document.addEventListener('click', (e) => {
            if (threeDotsDropdown && !threeDotsDropdown.contains(e.target) && !threeDotsBtn.contains(e.target)) {
                threeDotsDropdown.style.display = 'none';
            }
        });
    }

    if (toggleDetailsBtn && detailsPane) {
        toggleDetailsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (detailsPane.style.display === 'none') {
                detailsPane.style.display = 'flex';
                localStorage.setItem('hideDetailsPane', 'false');
            } else {
                detailsPane.style.display = 'none';
                localStorage.setItem('hideDetailsPane', 'true');
            }
            if (threeDotsDropdown) threeDotsDropdown.style.display = 'none';
        });

        if (localStorage.getItem('hideDetailsPane') === 'true') {
            detailsPane.style.display = 'none';
        }
    }

    // ── Real-Time Active Conversation Delta Polling ──────────────
    let activeChatPollTimer = null;

    async function pollActiveChat() {
        if (!activeChatUrl) return;

        try {
            const res = await fetch(`${activeChatUrl}?after_id=${lastMsgId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) return;
            const data = await res.json();

            if (data && data.messages && data.messages.length > 0) {
                const wasAtBottom = chatContainer ? (chatContainer.scrollTop + chatContainer.clientHeight >= chatContainer.scrollHeight - 80) : true;
                let hasNewCustomerMsg = false;

                const emptyNotice = chatContainer?.querySelector('.empty-chat-notice');
                if (emptyNotice) emptyNotice.remove();

                data.messages.forEach(msg => {
                    if (!renderedMsgIds.has(msg.id)) {
                        renderedMsgIds.add(msg.id);
                        if (msg.id > lastMsgId) lastMsgId = msg.id;

                        const row = createMessageRow(msg);
                        chatContainer?.appendChild(row);

                        if (msg.sender_type === 'customer') {
                            hasNewCustomerMsg = true;
                            updateActiveChatSidebarPreview(msg.message);
                        }
                    }
                });

                if (hasNewCustomerMsg) {
                    playIncomingSound();
                }

                if (chatContainer && (wasAtBottom || hasNewCustomerMsg)) {
                    chatContainer.scrollTo({
                        top: chatContainer.scrollHeight,
                        behavior: 'smooth'
                    });
                }
            }
        } catch (e) {
            // Background poll failure ignored
        } finally {
            scheduleNextActiveChatPoll();
        }
    }

    function scheduleNextActiveChatPoll() {
        if (!activeChatUrl) return;
        clearTimeout(activeChatPollTimer);
        const delay = document.hidden ? 12000 : 2500;
        activeChatPollTimer = setTimeout(pollActiveChat, delay);
    }

    // ── Real-Time Sidebar Chat List Polling ──────────────────────
    let sidebarPollTimer = null;

    async function pollSidebarChats() {
        const searchInput = document.querySelector('.slack-search-input');
        // Do not overwrite sidebar if user is typing a search
        if (searchInput && searchInput.value.trim() !== '') {
            scheduleNextSidebarPoll();
            return;
        }

        try {
            const res = await fetch(chatsIndexUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!res.ok) return;
            const data = await res.json();

            if (data && Array.isArray(data.chats)) {
                renderSidebarChatList(data.chats);
            }
        } catch (e) {
            // Background poll failure ignored
        } finally {
            scheduleNextSidebarPoll();
        }
    }

    function renderSidebarChatList(chats) {
        const listContainer = document.querySelector('.slack-chat-list');
        if (!listContainer) return;

        if (chats.length === 0) {
            listContainer.innerHTML = `<div style="text-align:center;color:#94a3b8;padding:40px 10px;font-size:13px;">Belum ada chat masuk.</div>`;
            return;
        }

        // Build signature to check if DOM re-render is actually necessary
        const signature = JSON.stringify(chats.map(c => [c.id, c.unread_count, c.last_message]));
        if (listContainer.dataset.lastSignature === signature) {
            return;
        }
        listContainer.dataset.lastSignature = signature;

        let html = '';
        chats.forEach(c => {
            const isActive = activeChatId && c.id === activeChatId;
            const initials = c.initials || (c.customer_name ? c.customer_name.substring(0, 1).toUpperCase() : 'C');
            const unreadBadge = c.unread_count > 0 ? `<span class="slack-chat-badge">${c.unread_count}</span>` : '';

            let lastMsg = c.last_message || 'Memulai percakapan...';
            if (lastMsg.startsWith('[IMAGE]:') || lastMsg.includes('data:image')) {
                lastMsg = '<iconify-icon icon="flat-color-icons:picture" style="font-size: 14px; vertical-align: middle;"></iconify-icon> [Gambar]';
            } else if (lastMsg.startsWith('[STICKER]:')) {
                lastMsg = '😄 [Stiker]';
            } else if (lastMsg.startsWith('[VOICE]:')) {
                lastMsg = '🎤 [Pesan Suara]';
            } else {
                lastMsg = escapeHtml(lastMsg);
            }

            const productBadge = c.product_name 
                ? `<span style="color:#4f6ef7; display: inline-flex; align-items: center; gap: 3px;">[<iconify-icon icon="flat-color-icons:box" style="font-size: 13px;"></iconify-icon> ${escapeHtml(c.product_name)}]</span> `
                : '';

            html += `
                <a href="${c.url}" class="slack-chat-item ${isActive ? 'active' : ''}">
                    <div class="slack-chat-avatar">${initials}</div>
                    <div class="slack-chat-details">
                        <div class="slack-chat-name-row">
                            <span class="text-truncate">${escapeHtml(c.customer_name)}</span>
                            ${unreadBadge}
                        </div>
                        <div class="slack-chat-message">
                            ${productBadge}${lastMsg}
                        </div>
                    </div>
                </a>
            `;
        });

        listContainer.innerHTML = html;
    }

    function scheduleNextSidebarPoll() {
        clearTimeout(sidebarPollTimer);
        const delay = document.hidden ? 15000 : 4000;
        sidebarPollTimer = setTimeout(pollSidebarChats, delay);
    }

    // ── Start Polling & Visibility Listener ───────────────────────
    if (activeChatUrl) {
        scheduleNextActiveChatPoll();
    }
    scheduleNextSidebarPoll();

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            pollActiveChat();
            pollSidebarChats();
        }
    });

    // ── Modal Image Preview ──────────────────────────────────────
    function openImageModal(src) {
        const modal = document.getElementById('imagePreviewModal');
        const img = document.getElementById('imagePreviewModalImg');
        if (modal && img) {
            img.src = src;
            modal.style.display = 'flex';
        }
    }

    function closeImageModal() {
        const modal = document.getElementById('imagePreviewModal');
        if (modal) modal.style.display = 'none';
    }
</script>

<!-- Modal Image Preview -->
<div id="imagePreviewModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; backdrop-filter:blur(6px);" onclick="closeImageModal()">
    <img id="imagePreviewModalImg" src="" style="max-width:90vw; max-height:90vh; border-radius:12px; box-shadow:0 20px 40px rgba(0,0,0,0.5); object-fit:contain;">
    <button type="button" style="position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.2); border:none; color:white; font-size:24px; border-radius:50%; width:44px; height:44px; cursor:pointer; display:flex; align-items:center; justify-content:center;" onclick="closeImageModal()">&times;</button>
</div>
@endpush
@endsection