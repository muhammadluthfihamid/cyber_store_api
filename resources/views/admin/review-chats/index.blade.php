@extends('admin.layouts.app')
@section('title', isset($review) ? 'Chat Ulasan - ' . $review->user?->name : 'Chat Ulasan')
@section('page-title', 'Chat Ulasan')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<span>Chat Ulasan</span>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/chat.css') }}?v=1">
@endpush

@section('content')
<div class="slack-chat-workspace">
    <!-- Left Sidebar: Review List -->
    <div class="slack-sidebar">
        <div class="slack-sidebar-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span style="display: flex; align-items: center; gap: 8px;"><iconify-icon icon="flat-color-icons:comments" style="font-size: 20px;"></iconify-icon> Chat Ulasan</span>
            <form method="POST" action="{{ route('admin.cache.flush-review-chats') }}" style="margin: 0;" onsubmit="return confirm('Bersihkan cache ulasan di Redis?')">
                @csrf
                <button type="submit" class="btn btn-sm" style="background-color: #F59E0B; color: white; border: none; padding: 3px 8px; border-radius: 6px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;" title="Bersihkan Cache Redis">
                    <iconify-icon icon="flat-color-icons:synchronize" style="font-size: 14px;"></iconify-icon> Cache
                </button>
            </form>
        </div>
        <div class="slack-search-bar">
            <form method="GET" action="{{ route('admin.review-chats.index') }}" style="display: flex; gap: 5px; width: 100%; align-items: center;">
                <div class="search-input-wrapper" style="flex: 1; min-width: 0; position: relative;">
                    <input type="text" name="search" class="slack-search-input search-input"
                        placeholder="Cari ulasan..." value="{{ request('search') }}" data-suggestion-url="{{ route('admin.review-chats.suggestions') }}" autocomplete="off">
                </div>
                <button type="submit" class="btn btn-sm" style="background-color: #3C3565; color: #ffffff; border: none; height: 32px; padding: 0 10px; border-radius: 8px; font-weight: 600; font-size: 11.5px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; gap: 4px;" title="Cari Ulasan">
                    <iconify-icon icon="lucide:search" style="font-size: 13px;"></iconify-icon>
                    Cari
                </button>
                @if(request('search'))
                <a href="{{ route('admin.review-chats.index') }}" class="btn btn-sm" style="background: var(--bg-card, #ffffff); color: var(--text-primary, #1e293b); border: 1px solid var(--border, #cbd5e1); border-radius: 8px; height: 32px; width: 32px; padding: 0; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center;" title="Reset Search">
                    <iconify-icon icon="lucide:x" style="font-size: 14px; color: var(--text-primary, #1e293b);"></iconify-icon>
                </a>
                @endif
            </form>
        </div>
        <div class="slack-chat-list">
            @forelse($reviews as $r)
            @php
            $isActive = isset($review) && $r->id === $review->id;
            @endphp
            <a href="{{ route('admin.review-chats.show', $r) }}" class="slack-chat-item {{ $isActive ? 'active' : '' }}">
                <div class="slack-chat-name-row">
                    <span class="text-truncate" style="display: flex; align-items: center; gap: 6px;">
                        {{ $r->user?->name ?? 'User' }}
                        @if(!$r->is_read)
                        <span style="background-color: #DF0B2B; color: #ffffff; font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 6px; line-height: 1.2;">BARU</span>
                        @endif
                    </span>
                    <span class="review-stars">
                        @for($i=1; $i<=5; $i++)
                            @if($i <=$r->rating)
                            ★
                            @else
                            ☆
                            @endif
                            @endfor
                    </span>
                </div>
                <div class="product-context text-truncate" style="display: flex; align-items: center; gap: 4px;">
                    <iconify-icon icon="flat-color-icons:box" style="font-size: 14px;"></iconify-icon> {{ $r->product?->name }}
                </div>
                <div class="slack-chat-message">
                    {{ $r->comment ?? '(Tidak ada komentar)' }}
                </div>
            </a>
            @empty
            <div style="text-align:center;color:#94a3b8;padding:40px 10px;font-size:13px;">
                Belum ada ulasan dengan komentar.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Middle: Chat Conversation Area -->
    <div class="slack-chat-area">
        @if(isset($review))
        <div class="slack-chat-header">
            <div>
                <div class="slack-chat-header-title">Ulasan: {{ $review->user?->name }}</div>
                <div class="slack-chat-header-status">
                    <span class="review-stars">
                        @for($i=1; $i<=5; $i++)
                            @if($i <=$review->rating)
                            ★
                            @else
                            ☆
                            @endif
                            @endfor
                    </span>
                    <span>• Rating {{ $review->rating }}/5</span>
                </div>
            </div>
            <div style="position:relative; display:inline-block;">
                <iconify-icon icon="lucide:more-vertical" id="threeDotsBtn" style="color: #64748b; font-size: 18px; cursor: pointer; padding: 4px;"></iconify-icon>

                {{-- Dropdown Menu --}}
                <div id="threeDotsDropdown" style="display:none; position:absolute; right:0; top:30px; background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; width:160px; box-shadow:0 8px 20px rgba(0,0,0,0.08); z-index:1050; padding:4px 0;">
                    <a href="javascript:void(0);" id="toggleDetailsBtn" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#1e293b; font-size:13px; text-decoration:none; transition:background 0.2s;">
                        <iconify-icon icon="flat-color-icons:info" style="font-size: 16px;"></iconify-icon> Toggle Info
                    </a>
                </div>
            </div>
        </div>

        {{-- Bubbles Scrollable Area --}}
        <div class="slack-messages-container" id="chatContainer">
            <!-- Customer Original Review -->
            <div class="slack-msg-row customer">
                <div class="slack-msg-avatar">
                    {{ strtoupper(substr($review->user?->name ?? 'C', 0, 1)) }}
                </div>
                <div class="slack-msg-body">
                    <div class="slack-msg-name-row">
                        <span class="slack-msg-username">{{ $review->user?->name }}</span>
                        <span class="review-stars">
                            @for($i=1; $i<=5; $i++)
                                @if($i <=$review->rating)
                                ★
                                @else
                                ☆
                                @endif
                                @endfor
                        </span>
                    </div>
                    <div class="slack-msg-bubble">
                        <p style="margin: 0; font-size: 14px;">{{ $review->comment ?? '(Tidak ada komentar)' }}</p>

                        @if(!empty($review->photos))
                        <div style="display: flex; gap: 8px; margin-top: 10px;">
                            @foreach($review->photos as $photo)
                            @php
                            $photoUrl = str_starts_with($photo, 'http') ? $photo : asset('storage/' . $photo);
                            @endphp
                            <img src="{{ $photoUrl }}" class="review-photo-preview" onclick="window.open('{{ $photoUrl }}', '_blank')" />
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="slack-msg-time">
                        Ulasan dikirim pada {{ $review->created_at->format('d M Y, H:i') }}
                    </div>
                </div>
            </div>

            <!-- Admin replies thread -->
            @forelse($review->replies as $r)
            <div class="slack-msg-row admin">
                <div class="slack-msg-avatar">
                    A
                </div>
                <div class="slack-msg-body">
                    <div class="slack-msg-name-row">
                        <span class="slack-msg-username">{{ $r->user?->name ?? 'Admin' }}</span>
                        <span class="slack-msg-role-tag">Staff</span>
                    </div>
                    <div class="slack-msg-bubble">
                        {{ $r->reply }}
                    </div>
                    <div class="slack-msg-time">
                        {{ $r->created_at->format('H:i') }}
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;color:#94a3b8;padding:20px 0;font-size:13px;" id="noRepliesPlaceholder">
                Belum ada respon untuk ulasan ini. Ketik pesan di bawah untuk membalas.
            </div>
            @endforelse
        </div>

        {{-- Reply Input Bar --}}
        <div class="slack-input-container">
            <form method="POST" action="{{ route('admin.review-chats.reply', $review) }}" id="replyForm">
                @csrf
                <div class="slack-input-pill">
                    <input type="text" name="message" id="messageInput" class="slack-input-field"
                        placeholder="Tulis balasan ulasan..." required autocomplete="off" />
                    <button type="submit" class="slack-send-btn" id="sendBtn">
                        <iconify-icon icon="lucide:send" style="font-size: 14px;"></iconify-icon>
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="slack-chat-header">
            <div class="slack-chat-header-title">Detail Obrolan</div>
        </div>
        <div class="slack-messages-container" style="justify-content:center; align-items:center; text-align:center; min-height:300px;">
            <div style="margin-bottom:16px; display: flex; justify-content: center;"><iconify-icon icon="flat-color-icons:comments" style="font-size: 56px;"></iconify-icon></div>
            <h3 style="color:#64748b; font-weight:700; margin-bottom:8px;">Selamat Datang di Chat Ulasan</h3>
            <p style="color:#64748b; font-size:13px; max-width:320px; margin:0 auto; line-height:1.6;">
                Pilih salah satu ulasan customer di panel kiri untuk mulai membalas ulasan secara real-time.
            </p>
        </div>
        @endif
    </div>

    <!-- Right Pane: Context & Customer Details -->
    <div class="slack-details-pane">
        @if(isset($review))
        <!-- Section: Product queried -->
        <div class="slack-details-section">
            <div class="slack-details-title">Produk Yang Diulas</div>
            @if($review->product)
            @if($review->product->main_photo)
            @php
            $photoUrl = str_starts_with($review->product->main_photo, 'http') ? $review->product->main_photo : asset('storage/' . $review->product->main_photo);
            @endphp
            <img src="{{ $photoUrl }}" style="width:100%; border-radius:10px; margin-bottom:12px; border:1px solid #e2e8f0; background:#fff; display:block;" />
            @endif
            <div style="font-weight:700; font-size:14.5px; color:#1e293b; line-height:1.4;">{{ $review->product->name }}</div>
            <div style="color:#0F62FE; font-weight:700; font-size:15px; margin-top:6px;">
                Rp. {{ number_format($review->product->price, 0, ',', '.') }}
            </div>
            @else
            <div style="color:#94a3b8; font-size:13px; text-align:center; padding:16px 0;">
                Detail produk tidak tersedia.
            </div>
            @endif
        </div>

        <!-- Section: Customer Info -->
        <div class="slack-details-section">
            <div class="slack-details-title">Info Pembeli</div>
            <div style="display:flex; flex-direction:column; gap:8px; font-size:13px;">
                <div>
                    <span style="color:#94a3b8;">Nama:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px;">{{ $review->user?->name ?? '-' }}</div>
                </div>
                <div>
                    <span style="color:#94a3b8;">Email:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px; word-break:break-all;">{{ $review->user?->email ?? '-' }}</div>
                </div>
                <div>
                    <span style="color:#94a3b8;">Daftar Akun:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px;">{{ $review->user?->created_at ? $review->user->created_at->format('d M Y') : '-' }}</div>
                </div>
            </div>
        </div>
        @else
        <div class="slack-details-section">
            <div class="slack-details-title">Detail Informasi</div>
            <div style="text-align:center; color:#94a3b8; font-size:13px; padding-top:40px;">
                Tidak ada ulasan aktif.
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Auto-scroll ke bawah saat halaman load
    const chatContainer = document.getElementById('chatContainer');
    if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;

    // Submit form dengan Enter (Shift+Enter = new line)
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.focus();
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('replyForm')?.requestSubmit();
            }
        });
    }

    // Intercept form submission and use AJAX to reply
    const replyForm = document.getElementById('replyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const msg = messageInput.value.trim();
            if (!msg) return;

            fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: msg
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        // Append message element
                        const placeholder = document.getElementById('noRepliesPlaceholder');
                        if (placeholder) placeholder.remove();

                        const msgRow = document.createElement('div');
                        msgRow.className = 'slack-msg-row admin';
                        msgRow.innerHTML = `
                    <div class="slack-msg-avatar">A</div>
                    <div class="slack-msg-body">
                        <div class="slack-msg-name-row">
                            <span class="slack-msg-username">${data.reply.admin_name}</span>
                            <span class="slack-msg-role-tag">Staff</span>
                        </div>
                        <div class="slack-msg-bubble">
                            ${data.reply.reply}
                        </div>
                        <div class="slack-msg-time">
                            Baru saja
                        </div>
                    </div>
                `;
                        chatContainer.appendChild(msgRow);
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
                })
                .catch(err => console.error(err));
        });
    }

    // Handling Three Dots Dropdown Toggle
    const threeDotsBtn = document.getElementById('threeDotsBtn');
    const threeDotsDropdown = document.getElementById('threeDotsDropdown');
    const toggleDetailsBtn = document.getElementById('toggleDetailsBtn');
    const detailsPane = document.querySelector('.slack-details-pane');

    if (threeDotsBtn && threeDotsDropdown) {
        threeDotsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            threeDotsDropdown.style.display = threeDotsDropdown.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', (e) => {
            if (threeDotsDropdown && !threeDotsDropdown.contains(e.target) && e.target !== threeDotsBtn) {
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
                localStorage.setItem('hideReviewDetailsPane', 'false');
            } else {
                detailsPane.style.display = 'none';
                localStorage.setItem('hideReviewDetailsPane', 'true');
            }
            if (threeDotsDropdown) threeDotsDropdown.style.display = 'none';
        });

        // Restore state from localStorage
        const shouldHide = localStorage.getItem('hideReviewDetailsPane') === 'true';
        if (shouldHide) {
            detailsPane.style.display = 'none';
        }
    }

    // Auto-refresh bubble setiap 6 detik jika halaman ulasan aktif dan tab terlihat
    if (document.getElementById('chatContainer')) {
        setInterval(function() {
            if (document.hidden) return;
            fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(res) { return res.text(); })
                .then(function(html) {
                    const parser = new window.DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newChat = doc.getElementById('chatContainer');
                    const curChat = document.getElementById('chatContainer');
                    if (newChat && curChat) {
                        const wasAtBottom = curChat.scrollTop + curChat.clientHeight >= curChat.scrollHeight - 20;
                        curChat.innerHTML = newChat.innerHTML;
                        if (wasAtBottom) curChat.scrollTop = curChat.scrollHeight;
                    }
                }).catch(function() {});
        }, 5000);
    }
</script>
@endpush
@endsection