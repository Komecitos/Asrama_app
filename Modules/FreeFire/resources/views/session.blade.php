@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/freefire.css') }}">
@endpush

@section('topbar')
<a href="{{ route('freefire.calc') }}" class="btn btn-secondary">Kalkulator</a>
<a href="{{ route('freefire.session') }}" class="btn btn-primary">Sesi Spin</a>
<a href="{{ route('freefire.info') }}" class="btn btn-secondary">Informasi</a>

@endsection

@section('content')

<div style="margin-bottom: 1rem;">
    <button onclick="openModal()" class="btn btn-primary">+ Sesi Baru</button>
</div>

{{-- SESI AKTIF --}}
@if($activeSessions->count() > 0)
<h3 class="section-header">Aktif ({{ $activeSessions->count() }})</h3>
@foreach($activeSessions as $session)
<div class="session-card">
    {{-- KOLOM 1: INFO SESI --}}
    <div class="session-col">
        <p class="task-title">{{ $session->item_name }}</p>
        <p class="task-meta" style="margin-bottom: 0.5rem;">
            <span class="badge {{ $session->spin_type === 'token_ring' ? 'badge-info' : ($session->spin_type === 'faded_wheel' ? 'badge-warning' : 'badge-danger') }}">
                {{ $session->spin_type === 'token_ring' ? 'Token Ring' : ($session->spin_type === 'faded_wheel' ? 'Faded Wheel' : 'Token Tower') }}
            </span>
        </p>
        @if($session->event_end)
        <p class="task-meta">📅 Berakhir: {{ \Carbon\Carbon::parse($session->event_end)->translatedFormat('d M Y') }}</p>
        @endif
        @if($session->obtained_items->isNotEmpty())
        <div class="session-obtained-items">
            <span class="session-obtained-items-label">✅ Didapat</span>
            <div class="session-obtained-items-list">
                @foreach($session->obtained_items as $obtainedName)
                {{-- Cari rarity dari slot yang cocok --}}
                @php
                $matchedSlot = $session->slots->first(fn($s) =>
                $s->type === 'item' &&
                strcasecmp(trim($s->item_name ?? ''), trim($obtainedName)) === 0
                );
                $rarity = $matchedSlot?->rarity ?? 'epic';
                @endphp
                <span class="badge badge-{{ $rarity }}">🎁 {{ $obtainedName }}</span>
                @endforeach
            </div>
        </div>
        @endif
        <div class="session-actions">
            <button type="button"
                class="btn btn-secondary btn-sm btn-log-spin"
                data-id="{{ $session->id }}"
                data-spin-type="{{ $session->spin_type }}"
                data-current-spin="{{ $session->current_spin }}"
                data-discount="{{ $session->discount_percentage > 0 ? 'true' : 'false' }}"
                data-items="{{ json_encode($session->slots->where('type', 'item')->values()) }}"
                data-current-token="{{ $session->current_token }}"
                data-ticket-count="{{ $session->ticket_count }}"
                data-item-name="{{ $session->item_name }}">
                + Spin
            </button>
            <button type="button"
                class="btn btn-secondary btn-sm btn-edit-session"
                data-id="{{ $session->id }}"
                data-item-name="{{ $session->item_name }}"
                data-spin-type="{{ $session->spin_type }}"
                data-event-start="{{ $session->event_start ? \Carbon\Carbon::parse($session->event_start)->format('Y-m-d') : '' }}"
                data-event-end="{{ $session->event_end ? \Carbon\Carbon::parse($session->event_end)->format('Y-m-d') : '' }}"
                data-spent-diamond="{{ $session->spent_diamond }}"
                data-current-spin="{{ $session->current_spin }}"
                data-current-token="{{ $session->current_token }}"
                data-starting-token="{{ $session->starting_token }}"
                data-ticket-count="{{ $session->ticket_count }}"
                data-status="{{ $session->status }}"
                data-discount="{{ $session->discount_percentage > 0 ? 'true' : 'false' }}"
                data-luck="{{ $session->luck_percentage ?? 0 }}"
                data-token-needed="{{ $session->token_needed }}"
                data-slots="{{ json_encode($session->slots) }}">
                Edit
            </button>
            <form action="{{ route('freefire.session.complete', $session->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-primary btn-sm">Selesai</button>
            </form>
            <form action="{{ route('freefire.destroy', $session->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form>
        </div>
    </div>

    {{-- KOLOM 2: STATISTIK --}}
    <div class="session-col session-col-stats">
        <p class="task-meta" style="margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">Statistik Sesi</p>
        <div class="session-stat">
            <span class="task-meta">💎 Terpakai</span>
            <span class="task-title" style="font-weight: 700;">{{ $session->spent_diamond }} dm</span>
        </div>
        @if($session->spin_type === 'token_ring')
        <div class="session-stat">
            <span class="task-meta">🪙 Token Saat Ini</span>
            <span class="task-title" style="font-weight: 700;">
                {{ $session->current_token }}{{ $session->token_target ? '/'.$session->token_target : '' }}
                @if($session->starting_token > 0)
                <span style="font-size: 0.7rem; color: var(--text-muted);">(Awal: {{ $session->starting_token }})</span>
                @endif
            </span>
        </div>
        <div class="session-stat">
            <span class="task-meta">🎰 Total Spin</span>
            <span class="task-title">{{ $session->current_spin }}x</span>
        </div>
        @if($session->ticket_count > 0)
        <div class="session-stat">
            <span class="task-meta">🎫 Tiket Digunakan</span>
            <span class="task-title">{{ $session->ticket_count }} tiket</span>
        </div>
        @endif
        @elseif($session->spin_type === 'token_tower')
        <div class="session-stat">
            <span class="task-meta">🏆 Token Tower</span>
            <span class="task-title" style="font-weight: 700;">{{ $session->current_token }}/5</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">🎰 Total Spin</span>
            <span class="task-title">{{ $session->current_spin }}x</span>
        </div>
        @else
        <div class="session-stat">
            <span class="task-meta">🎰 Spin Ke</span>
            <span class="task-title" style="font-weight: 700;">{{ $session->current_spin }}/8</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">💸 Spin Berikutnya</span>
            <span class="task-title">{{ $session->next_spin_cost }} dm</span>
        </div>
        @endif
    </div>

    {{-- KOLOM 3: PERKIRAAN --}}
    <div class="session-col session-col-estimate">
        <p class="task-meta" style="margin-bottom: 0.5rem; font-weight: 600; color: var(--text-primary);">Perkiraan & Analisis</p>
        @if($session->spin_type === 'token_ring')
        @if($session->remaining_token > 0)
        <div class="session-stat">
            <span class="task-meta">🎯 Token Tersisa</span>
            <span class="task-title">{{ $session->remaining_token }} token</span>
        </div>
        @endif
        @if($session->avg_token_per_spin !== null)
        <div class="session-stat">
            <span class="task-meta">📊 Rata-Rata Token/Spin</span>
            <span class="task-title">{{ number_format($session->avg_token_per_spin, 2) }}</span>
        </div>
        @elseif($session->expected_token_per_spin > 0)
        <div class="session-stat">
            <span class="task-meta">📊 Ekspektasi Token/Spin</span>
            <span class="task-title">{{ number_format($session->expected_token_per_spin, 2) }}</span>
        </div>
        @endif
        @if($session->est_spins_left > 0)
        <div class="session-stat">
            <span class="task-meta">🔄 Sisa Spin</span>
            <span class="task-title">~{{ $session->est_spins_left }}x</span>
        </div>
        @endif
        @if($session->est_diamond_left > 0)
        <div class="session-stat">
            <span class="task-meta">💎 Estimasi Sisa Diamond</span>
            <span class="task-title" style="color: var(--accent-primary); font-weight: 700;">~{{ $session->est_diamond_left }} dm</span>
        </div>
        @endif
        @if($session->luck_actual !== null)
        <div class="session-stat">
            <span class="task-meta">🍀 Keberuntungan (Luck)</span>
            <span class="task-title" style="color: {{ $session->luck_actual >= 50 ? '#2ecc71' : '#f39c12' }};">{{ $session->luck_actual }}%</span>
        </div>
        @endif
        @elseif($session->spin_type === 'token_tower')
        <div class="session-stat">
            <span class="task-meta">🎯 Token Tersisa</span>
            <span class="task-title">{{ $session->remaining_token }} token</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">🔄 Sisa Spin (Pity)</span>
            <span class="task-title">~{{ $session->est_spins_left }}x</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">💎 Estimasi Sisa Diamond</span>
            <span class="task-title" style="color: var(--accent-primary); font-weight: 700;">~{{ $session->est_diamond_left }} dm</span>
        </div>
        @else
        <div class="session-stat">
            <span class="task-meta">🎰 Sisa Spin</span>
            <span class="task-title">{{ 8 - $session->current_spin }} spin</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">💎 Estimasi Sisa Diamond</span>
            <span class="task-title" style="color: var(--accent-primary); font-weight: 700;">~{{ $session->remaining_faded_cost }} dm</span>
        </div>
        <div class="session-stat">
            <span class="task-meta">🏆 Jaminan Item Utama</span>
            <span class="task-title">Spin Ke-8 (100%)</span>
        </div>
        @endif
    </div>
</div>
@endforeach
@else
<p class="empty-state">Belum ada sesi aktif.</p>
@endif

{{-- SESI SELESAI --}}
@if($completedSessions->count() > 0)
<div style="margin-top: 2rem;">
    <h3 class="section-header">Riwayat</h3>
    @foreach($completedSessions as $session)
    <div class="task-card completed">
        <div class="task-card-content">
            <div class="task-text">
                <p class="task-title">{{ $session->item_name }}</p>
                <p class="task-meta">
                    <span class="badge {{ $session->spin_type === 'token_ring' ? 'badge-info' : 'badge-warning' }}">
                        {{ $session->spin_type === 'token_ring' ? 'Token Ring' : 'Faded Wheel' }}
                    </span>
                    · Total: {{ $session->spent_diamond }} dm
                    · {{ $session->current_spin }} spin
                </p>
            </div>
        </div>
        <div class="task-actions">
            <form action="{{ route('freefire.session.reopen', $session->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-secondary btn-sm">Aktifkan Kembali</button>
            </form>
            <form action="{{ route('freefire.destroy', $session->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- MODAL OVERLAY --}}
<div id="modal-overlay" class="modal-overlay" onclick="closeAllModals()"></div>

{{-- MODAL SESI BARU --}}
<div id="modal-create" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Sesi Spin Baru</h3>
        <button onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>
    <form action="{{ route('freefire.session.store') }}" method="POST" autocomplete="off">
        @csrf
        <div class="form-group">
            <label class="form-label">Nama Item <span class="required">*</span></label>
            <input type="text" name="item_name" class="form-control" placeholder="cth: Bundle Cobra, Skyler...">
        </div>
        <div class="form-group">
            <label class="form-label">Jenis Spin</label>
            <select name="spin_type" class="form-control" onchange="toggleSpinType(this)">
                <option value="token_ring">Token Ring</option>
                <option value="faded_wheel">Faded Wheel</option>
                <option value="token_tower">Token Tower</option>
            </select>
        </div>
        <div class="form-grid-2" style="margin-top: 1rem;">
            <div>
                <label class="form-label">Tanggal Mulai Event</label>
                <input type="date" name="event_start" class="form-control">
            </div>
            <div>
                <label class="form-label">Tanggal Selesai Event</label>
                <input type="date" name="event_end" class="form-control">
            </div>
        </div>
        <div id="faded-options" style="display:none; margin-top: 1rem;">
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="has_discount" id="create-has-discount" value="1" onchange="previewFadedPrice()">
                    Ada diskon?
                </label>
            </div>

            <div class="calc-result" style="margin-top: 0.75rem;">
                <div class="stat-grid">
                    @php $fadedBasePrices = [9, 19, 39, 69, 99, 199, 399, 799]; @endphp
                    @foreach($fadedBasePrices as $i => $price)
                    <div class="stat-item">
                        <span class="stat-number create-faded-price" data-idx="{{ $i }}">{{ $price }}</span>
                        <span class="stat-label">Spin {{ $i+1 }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="calc-total" style="margin-top: 0.5rem;">
                    <span class="task-meta">Total 8 spin:</span>
                    <span id="create-faded-total" class="stat-number" style="color: var(--accent-primary);">1632 dm</span>
                </div>
            </div>
        </div>

        <div id="tower-options" style="display:none; margin-top: 1rem;">

            <p class="task-meta" style="margin-bottom: 0.75rem;">Harga: 1x = 19dm · 5x = 79dm · Target: 5 Token</p>

            <div class="form-group">
                <label class="form-label">Tingkat Keberuntungan (estimasi awal)
                    <span id="tower-create-luck-label" class="badge badge-info">0%</span>
                </label>
                <input type="range" name="tower_luck" id="tower-create-luck" min="0" max="100" value="0" step="10"
                    oninput="document.getElementById('tower-create-luck-label').textContent = this.value + '%'" class="form-range">
            </div>

            <div class="form-group">
                <label class="form-label">Drop Rate Spin Shard (%)</label>
                <input type="number" name="shard_rate" value="80" min="0" max="100" class="form-control">
            </div>
        </div>

        <div id="token-options" style="margin-top: 1rem;">
            <div class="form-grid-2" style="margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label">Token Dibutuhkan (Target Token)</label>
                    <input type="number" name="token_needed" id="create-token-needed" class="form-control" placeholder="cth: 250" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Token Awal (Starting Token)</label>
                    <input type="number" name="starting_token" class="form-control" placeholder="0" value="0" min="0">
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label class="form-label" style="margin: 0;">Komposisi Token (jumlah slot di wheel)</label>
                <button type="button" onclick="addItemSlot()" class="btn btn-secondary btn-sm">+ Item Hadiah</button>
            </div>
            <div class="wheel-token-grid">
                @php
                $tokenOptions = [
                1 => 'x1',
                2 => 'x2',
                3 => 'x3',
                5 => 'x5',
                10 => 'x10',
                20 => 'x20',
                30 => 'x30',
                100 => 'x100',
                'crystal' => 'Crystal Royale',
                ];
                $slotIdx = 0;
                @endphp
                @foreach($tokenOptions as $val => $label)
                <div class="wheel-token-item">
                    <span class="wheel-token-label" style="{{ $val === 'crystal' ? 'font-size: 0.7rem; color: #a855f7;' : '' }}">{{ $label }}</span>
                    <input type="hidden" name="slots[{{ $slotIdx }}][type]" value="token">
                    <input type="hidden" name="slots[{{ $slotIdx }}][token_value]" value="{{ $val }}">
                    <input type="number"
                        name="slots[{{ $slotIdx }}][slot_count]"
                        value="0" min="0"
                        class="form-control wheel-token-input calc-token-input"
                        data-tokenval="{{ $val }}"
                        oninput="updateExpected()">
                </div>
                @php $slotIdx++; @endphp
                @endforeach
            </div>
            <div id="wheel-slots-header" class="wheel-slots-header" style="display: none; gap: 0.5rem; padding: 0 0.5rem; margin-top: 0.75rem; margin-bottom: 0.25rem; font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">
                <span style="flex: 1; min-width: 100px;">Nama Item Hadiah</span>
                <span style="width: 90px; text-align: center;">Rarity</span>
                <span style="width: 110px; text-align: center;">Token Dibutuhkan</span>
                <span style="width: 55px; text-align: center;">Slot</span>
                <span style="width: 32px;"></span>
            </div>
            <div id="wheel-slots-container"></div>
            <p class="task-meta" style="margin-top: 0.4rem; font-size: 0.7rem;">
                Isi "Token Dibutuhkan" = jumlah token yang dibutuhkan untuk menukar item hadiah ini di toko. Isi "Slot" = berapa banyak item ini muncul di wheel.
            </p>
            <div class="calc-result" style="margin-top: 0.75rem;">
                <p class="task-meta" style="margin-bottom: 0.5rem;">
                    Total bobot: <span id="total-bobot">0</span> ·
                    E(token/spin): <span id="expected-token">0.00</span>
                </p>
                <div id="session-droprate-list"></div>
            </div>
            <div class="form-actions">
                <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Batal</button>
                <button type="button" onclick="validateAndSubmitSession()" class="btn btn-primary">Buat Sesi</button>
            </div>
    </form>
</div>

{{-- MODAL LOG SPIN --}}
<div id="modal-log" class="modal modal-sm" aria-hidden="true">
    <div class="modal-header">
        <h3>Catat Spin</h3>
        <button onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>
    <form id="form-log" method="POST" autocomplete="off">
        @csrf

        {{-- TOGGLE MODE KHUSUS TOWER --}}
        <div id="log-tower-mode-toggle" class="form-group" style="display:none;">
            <label class="form-check">
                <input type="checkbox" id="log-tower-diamond-mode" onchange="toggleTowerMode(this)">
                Saya lupa hitung spin, input diamond saja
            </label>
        </div>

        <div id="log-price-mode-wrapper" class="form-group" style="display:none;">
            <label class="form-label">Mode Harga</label>
            <select id="log-price-mode" class="form-control" onchange="autoCalcDiamond()">
                <option value="normal">Normal</option>
                <option value="discount">Diskon</option>
                <option value="ticket" id="log-ticket-option">Tiket (Gratis)</option>
            </select>
        </div>
        <div id="log-normal-mode" class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Jumlah Spin</label>
                <input type="number" id="log-spin-count" name="spin_count"
                    value="1" min="1" class="form-control" oninput="autoCalcDiamond()">
            </div>
            <div class="form-group">
                <label class="form-label">Diamond Dipakai</label>
                <input type="number" id="log-diamond" name="diamond_spent"
                    class="form-control" readonly style="opacity: 0.7;">
            </div>
        </div>

        <div id="log-diamond-mode" class="form-group" style="display:none;">
            <label class="form-label">Total Diamond yang Dihabiskan</label>
            <input type="number" id="log-diamond-input" min="0" class="form-control"
                placeholder="cth: 700" oninput="calcSpinFromDiamond()">
            <p class="task-meta" style="margin-top: 0.3rem;" id="log-diamond-result"></p>
        </div>

        <div id="log-token-section" class="form-group">
            <label class="form-label">Token Didapat</label>
            <input type="number" name="token_gained" value="0" min="0" class="form-control">
        </div>

        <div id="log-tower-progress" class="form-group" style="display:none;">
            <label class="form-check">
                <input type="checkbox" id="log-tower-token-checkbox" onchange="toggleTowerTokenSelect(this)">
                Dapat Token?
            </label>
            <div id="log-tower-token-select-wrapper" style="display:none; margin-top: 0.5rem;">
                <label class="form-label">Token Berapa?</label>
                <select name="tower_token_number" id="log-tower-token-select" class="form-control">
                    <option value="1">Token 1</option>
                    <option value="2">Token 2</option>
                    <option value="3">Token 3</option>
                    <option value="4">Token 4</option>
                    <option value="5">Token 5</option>
                </select>
            </div>
        </div>

        <div id="log-direct-drop-section" class="form-group" style="margin-top: 1rem; padding: 0.75rem; background: rgba(46, 204, 113, 0.08); border: 1px solid rgba(46, 204, 113, 0.3); border-radius: var(--radius-md);">
            <label class="form-check" style="cursor: pointer; font-weight: 600; color: #2ecc71;">
                <input type="checkbox" name="direct_drop" id="log-direct-drop" value="1" onchange="toggleDirectDropInput(this)">
                🎁 Dapat Hadiah / Item Langsung dari Spin?
            </label>
            <div id="log-direct-drop-wrapper" style="display: none; margin-top: 0.5rem;">
                <label class="form-label" style="font-size: 0.8rem;">Nama Item yang Didapat</label>
                <input type="text" name="direct_item_name" id="log-direct-item-name" class="form-control" placeholder="Nama item (cth: Bundle Cobra)">
                <label class="form-check" style="margin-top: 0.5rem; font-size: 0.8rem; color: var(--text-muted); cursor: pointer;">
                    <input type="checkbox" name="auto_complete" value="1" checked>
                    Selesaikan sesi ini (Target Utama Tercapai 🎉)
                </label>
            </div>
        </div>

        <div id="log-item-section" class="form-group" style="display:none; margin-top: 0.75rem;">
            <label class="form-label">Dapat Item Lain dari Slot Wheel?</label>
            <div id="log-item-checkboxes" class="item-checkbox-grid"></div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>

{{-- MODAL EDIT SESI --}}
<div id="modal-edit" class="modal modal-create" aria-hidden="true">
    <div class="modal-header">
        <h3>Edit Sesi Spin</h3>
        <button onclick="closeAllModals()" class="modal-close">&times;</button>
    </div>
    <form id="form-edit-session" method="POST" autocomplete="off">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label class="form-label">Nama Item <span class="required">*</span></label>
            <input type="text" name="item_name" id="edit-item-name" class="form-control" required>
        </div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Jenis Spin</label>
                <select name="spin_type" id="edit-spin-type" class="form-control" onchange="toggleEditSpinType(this)">
                    <option value="token_ring">Token Ring</option>
                    <option value="faded_wheel">Faded Wheel</option>
                    <option value="token_tower">Token Tower</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status Sesi</label>
                <select name="status" id="edit-status" class="form-control">
                    <option value="active">Aktif</option>
                    <option value="completed">Selesai</option>
                </select>
            </div>
        </div>
        <div class="form-grid-2" style="margin-top: 0.5rem;">
            <div>
                <label class="form-label">Tanggal Mulai Event</label>
                <input type="date" name="event_start" id="edit-event-start" class="form-control">
            </div>
            <div>
                <label class="form-label">Tanggal Selesai Event</label>
                <input type="date" name="event_end" id="edit-event-end" class="form-control">
            </div>
        </div>

        <div class="form-grid-3" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; margin-top: 0.75rem;">
            <div class="form-group">
                <label class="form-label">Diamond Terpakai</label>
                <input type="number" name="spent_diamond" id="edit-spent-diamond" min="0" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Total Spin</label>
                <input type="number" name="current_spin" id="edit-current-spin" min="0" class="form-control">
            </div>
            <div class="form-group">
                <label class="form-label">Token Saat Ini</label>
                <input type="number" name="current_token" id="edit-current-token" min="0" class="form-control">
            </div>
        </div>

        {{-- FADED WHEEL EDIT OPTIONS --}}
        <div id="edit-faded-options" style="display:none; margin-top: 1rem;">
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="has_discount" id="edit-has-discount" value="1" onchange="previewEditFadedPrice()">
                    Ada diskon?
                </label>
            </div>

            <div class="calc-result" style="margin-top: 0.75rem;">
                <div class="stat-grid">
                    @foreach([9, 19, 39, 69, 99, 199, 399, 799] as $i => $price)
                    <div class="stat-item">
                        <span class="stat-number edit-faded-price" data-idx="{{ $i }}">{{ $price }}</span>
                        <span class="stat-label">Spin {{ $i+1 }}</span>
                    </div>
                    @endforeach
                </div>
                <div class="calc-total" style="margin-top: 0.5rem;">
                    <span class="task-meta">Total 8 spin:</span>
                    <span id="edit-faded-total" class="stat-number" style="color: var(--accent-primary);">1632 dm</span>
                </div>
            </div>
        </div>

        {{-- TOKEN TOWER EDIT OPTIONS --}}
        <div id="edit-tower-options" style="display:none; margin-top: 1rem;">
            <p class="task-meta" style="margin-bottom: 0.75rem;">Harga: 1x = 19dm · 5x = 79dm · Target: 5 Token</p>

            <div class="form-group">
                <label class="form-label">Tingkat Keberuntungan (estimasi)
                    <span id="edit-tower-luck-label" class="badge badge-info">0%</span>
                </label>
                <input type="range" name="tower_luck" id="edit-tower-luck" min="0" max="100" value="0" step="10"
                    oninput="document.getElementById('edit-tower-luck-label').textContent = this.value + '%'" class="form-range">
            </div>

            <div class="form-group">
                <label class="form-label">Drop Rate Spin Shard (%)</label>
                <input type="number" name="shard_rate" id="edit-shard-rate" value="80" min="0" max="100" class="form-control">
            </div>
        </div>

        {{-- TOKEN RING EDIT OPTIONS --}}
        <div id="edit-token-options" style="margin-top: 1rem;">
            <div class="form-grid-3" style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem;">
                <div class="form-group">
                    <label class="form-label">Token Dibutuhkan</label>
                    <input type="number" name="token_needed" id="edit-token-needed" class="form-control" placeholder="cth: 250" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Token Awal</label>
                    <input type="number" name="starting_token" id="edit-starting-token" class="form-control" placeholder="0" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Tiket Digunakan</label>
                    <input type="number" name="ticket_count" id="edit-ticket-count" class="form-control" placeholder="0" min="0">
                </div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                <label class="form-label" style="margin: 0;">Komposisi Token (jumlah slot di wheel)</label>
                <button type="button" onclick="addEditItemSlot()" class="btn btn-secondary btn-sm">+ Item Hadiah</button>
            </div>
            <div class="wheel-token-grid">
                @php
                $editTokenOptions = [
                1 => 'x1',
                2 => 'x2',
                3 => 'x3',
                5 => 'x5',
                10 => 'x10',
                20 => 'x20',
                30 => 'x30',
                100 => 'x100',
                'crystal' => 'Crystal Royale',
                ];
                $editSlotIdx = 0;
                @endphp
                @foreach($editTokenOptions as $val => $label)
                <div class="wheel-token-item">
                    <span class="wheel-token-label" style="{{ $val === 'crystal' ? 'font-size: 0.7rem; color: #a855f7;' : '' }}">{{ $label }}</span>
                    <input type="hidden" name="slots[{{ $editSlotIdx }}][type]" value="token">
                    <input type="hidden" name="slots[{{ $editSlotIdx }}][token_value]" value="{{ $val }}">
                    <input type="number"
                        name="slots[{{ $editSlotIdx }}][slot_count]"
                        value="0" min="0"
                        class="form-control wheel-token-input edit-calc-token-input"
                        data-tokenval="{{ $val }}"
                        oninput="updateEditExpected()">
                </div>
                @php $editSlotIdx++; @endphp
                @endforeach
            </div>
            <div id="edit-wheel-slots-header" class="wheel-slots-header" style="display: none; gap: 0.5rem; padding: 0 0.5rem; margin-top: 0.75rem; margin-bottom: 0.25rem; font-size: 0.75rem; font-weight: 600; color: var(--text-muted);">
                <span style="flex: 1; min-width: 100px;">Nama Item Hadiah</span>
                <span style="width: 90px; text-align: center;">Rarity</span>
                <span style="width: 110px; text-align: center;">Token Dibutuhkan</span>
                <span style="width: 55px; text-align: center;">Slot</span>
                <span style="width: 32px;"></span>
            </div>
            <div id="edit-wheel-slots-container"></div>
            <p class="task-meta" style="margin-top: 0.4rem; font-size: 0.7rem;">
                Isi "Token Dibutuhkan" = jumlah token yang dibutuhkan untuk menukar item hadiah ini di toko. Isi "Slot" = berapa banyak item ini muncul di wheel.
            </p>
            <div class="calc-result" style="margin-top: 0.75rem;">
                <p class="task-meta" style="margin-bottom: 0.5rem;">
                    Total bobot: <span id="edit-total-bobot">0</span> ·
                    E(token/spin): <span id="edit-expected-token">0.00</span>
                </p>
                <div id="edit-session-droprate-list"></div>
            </div>

            <div class="form-actions" style="margin-top: 1.25rem;">
                <button type="button" onclick="closeAllModals()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
    </form>
</div>

{{-- OVERLAY --}}
<div id="modal-overlay" class="modal-overlay" onclick="closeAllModals()"></div>

@endsection

@push('scripts')
<script>
    var tokenBaseWeight = window.tokenBaseWeight || {
        2: 200,
        3: 150,
        5: 100,
        10: 60,
        20: 30,
        30: 15,
        100: 5,
        crystal: 10
    };
    var tokenNumericVal = window.tokenNumericVal || {
        1: 1,
        3: 3,
        5: 5,
        10: 10,
        20: 20,
        30: 30,
        100: 100,
        crystal: 1
    };
    var fadedBase = window.fadedBase || [9, 19, 39, 69, 99, 199, 399, 799];
    var fadedDiscounted = window.fadedDiscounted || [5, 15, 29, 49, 69, 99, 299, 699];

    var slotIndex = window.slotIndex || 100;
    var currentSpinType = window.currentSpinType || 'token_ring';
    var currentSpinNumber = window.currentSpinNumber || 0;
    var currentHasDiscount = window.currentHasDiscount || false;
    var currentTicketCount = window.currentTicketCount || 0;


    function gcd(a, b) {
        return b === 0 ? a : gcd(b, a % b);
    }

    function lcm(a, b) {
        return (a * b) / gcd(a, b);
    }

    function spinsToHarga(spins, price1 = 9, price5 = 39) {
        const fiveSpins = Math.floor(spins / 5);
        const oneSpins = spins % 5;
        return (fiveSpins * price5) + (oneSpins * price1);
    }

    function fadedPrice(spinIdx, hasDiscount) {
        if (spinIdx < 0 || spinIdx >= fadedBase.length) return 0;
        return hasDiscount ? fadedDiscounted[spinIdx] : fadedBase[spinIdx];
    }

    function openModal() {
        const modal = document.getElementById('modal-create');
        const overlay = document.getElementById('modal-overlay');
        if (modal) {
            modal.classList.add('show');
            modal.style.display = 'block';
        }
        if (overlay) {
            overlay.classList.add('show');
            overlay.style.display = 'block';
        }
        updateExpected();
    }

    function closeAllModals() {
        ['modal-create', 'modal-log', 'modal-edit'].forEach(id => {
            const m = document.getElementById(id);
            if (m) {
                m.classList.remove('show');
                m.style.display = 'none';
            }
        });
        const overlay = document.getElementById('modal-overlay');
        if (overlay) {
            overlay.classList.remove('show');
            overlay.style.display = 'none';
        }
    }

    function toggleSpinType(el) {
        const type = el.value;
        document.getElementById('token-options').style.display = type === 'token_ring' ? 'block' : 'none';
        document.getElementById('faded-options').style.display = type === 'faded_wheel' ? 'block' : 'none';
        document.getElementById('tower-options').style.display = type === 'token_tower' ? 'block' : 'none';
    }

    function autoCalcDiamond() {
        const spinCount = parseInt(document.getElementById('log-spin-count').value) || 1;
        const priceMode = document.getElementById('log-price-mode')?.value || 'normal';
        const isDiscount = priceMode === 'discount';
        const isTicket = priceMode === 'ticket';

        if (currentSpinType === 'faded_wheel') {
            let total = 0;
            for (let i = 0; i < spinCount; i++) {
                total += fadedPrice(currentSpinNumber + i, isDiscount);
            }
            document.getElementById('log-diamond').value = total;
        } else if (currentSpinType === 'token_tower') {
            if (isTicket) {
                document.getElementById('log-diamond').value = 0;
            } else {
                const price1 = isDiscount ? 9 : 19;
                const price5 = isDiscount ? 39 : 79;
                document.getElementById('log-diamond').value = spinsToHarga(spinCount, price1, price5);
            }
        } else {
            if (isTicket) {
                document.getElementById('log-diamond').value = 0;
            } else {
                const price1 = isDiscount ? 5 : 9;
                const price5 = isDiscount ? 19 : 39;
                document.getElementById('log-diamond').value = spinsToHarga(spinCount, price1, price5);
            }
        }
    }

    function towerSpinsToHarga(spins) {
        const fiveSpins = Math.floor(spins / 5);
        const oneSpins = spins % 5;
        return (fiveSpins * 79) + (oneSpins * 19);
    }

    function toggleTowerMode(checkbox) {
        const isDiamondMode = checkbox.checked;
        document.getElementById('log-normal-mode').style.display = isDiamondMode ? 'none' : 'flex';
        document.getElementById('log-diamond-mode').style.display = isDiamondMode ? 'block' : 'none';

        if (isDiamondMode) {
            document.getElementById('log-spin-count').removeAttribute('name');
            document.getElementById('log-diamond').removeAttribute('name');
        } else {
            document.getElementById('log-spin-count').setAttribute('name', 'spin_count');
            document.getElementById('log-diamond').setAttribute('name', 'diamond_spent');
        }
    }

    function toggleTowerTokenSelect(checkbox) {
        document.getElementById('log-tower-token-select-wrapper').style.display = checkbox.checked ? 'block' : 'none';
    }

    function calcSpinFromDiamond() {
        const inputDiamond = parseInt(document.getElementById('log-diamond-input').value) || 0;

        // Cari kombinasi terdekat: maksimalkan paket 5x (79dm), sisanya 1x (19dm)
        const fiveSpins = Math.floor(inputDiamond / 79);
        let remainingDm = inputDiamond - (fiveSpins * 79);
        const oneSpins = Math.floor(remainingDm / 19);
        const usedDiamond = (fiveSpins * 79) + (oneSpins * 19);
        const totalSpin = (fiveSpins * 5) + oneSpins;
        const leftover = inputDiamond - usedDiamond;

        document.getElementById('log-diamond-result').innerHTML =
            `Estimasi: <strong>${totalSpin} spin</strong> (${fiveSpins}x paket 5x + ${oneSpins}x paket 1x) ` +
            `= ${usedDiamond}dm digunakan` +
            (leftover > 0 ? ` <span style="color: var(--text-muted);">(sisa ${leftover}dm diabaikan)</span>` : '');

        // Set hidden input untuk submit
        let hiddenSpin = document.getElementById('hidden-spin-count');
        let hiddenDiamond = document.getElementById('hidden-diamond-spent');

        if (!hiddenSpin) {
            hiddenSpin = document.createElement('input');
            hiddenSpin.type = 'hidden';
            hiddenSpin.id = 'hidden-spin-count';
            hiddenSpin.name = 'spin_count';
            document.getElementById('form-log').appendChild(hiddenSpin);
        }
        if (!hiddenDiamond) {
            hiddenDiamond = document.createElement('input');
            hiddenDiamond.type = 'hidden';
            hiddenDiamond.id = 'hidden-diamond-spent';
            hiddenDiamond.name = 'diamond_spent';
            document.getElementById('form-log').appendChild(hiddenDiamond);
        }

        hiddenSpin.value = totalSpin;
        hiddenDiamond.value = usedDiamond;
    }

    function toggleDirectDropInput(checkbox) {
        document.getElementById('log-direct-drop-wrapper').style.display = checkbox.checked ? 'block' : 'none';
    }

    function openLogModal(id, spinType, currentSpin, discount, itemsJson, currentTokenLevel = 0, ticketCount = 0, mainItemName = '') {
        currentSpinType = spinType;
        currentHasDiscount = !!discount;
        currentSpinNumber = currentSpin;
        currentTicketCount = ticketCount;

        const spinCountInput = document.getElementById('log-spin-count');
        if (spinCountInput) {
            spinCountInput.value = 1;
            spinCountInput.max = spinType === 'faded_wheel' ? 8 : 999;
        }

        const directDropCheck = document.getElementById('log-direct-drop');
        if (directDropCheck) {
            directDropCheck.checked = false;
            toggleDirectDropInput(directDropCheck);
        }
        const directItemInput = document.getElementById('log-direct-item-name');
        if (directItemInput) {
            directItemInput.value = mainItemName || '';
        }

        const elTowerDmCheck = document.getElementById('log-tower-diamond-mode');
        if (elTowerDmCheck) elTowerDmCheck.checked = false;

        const elNormalMode = document.getElementById('log-normal-mode');
        if (elNormalMode) elNormalMode.style.display = 'flex';

        const elDiamondMode = document.getElementById('log-diamond-mode');
        if (elDiamondMode) elDiamondMode.style.display = 'none';

        const elPriceModeWrapper = document.getElementById('log-price-mode-wrapper');
        if (elPriceModeWrapper) {
            elPriceModeWrapper.style.display =
                (spinType === 'token_ring' || spinType === 'faded_wheel' || spinType === 'token_tower') ? 'block' : 'none';
        }

        const elPriceMode = document.getElementById('log-price-mode');
        if (elPriceMode) elPriceMode.value = 'normal';

        autoCalcDiamond();

        const elTokenSection = document.getElementById('log-token-section');
        if (elTokenSection) {
            elTokenSection.style.display = spinType === 'token_ring' ? 'block' : 'none';
        }

        const elTowerToggle = document.getElementById('log-tower-mode-toggle');
        if (elTowerToggle) elTowerToggle.style.display = spinType === 'token_tower' ? 'block' : 'none';

        const elTowerProg = document.getElementById('log-tower-progress');
        if (elTowerProg) elTowerProg.style.display = spinType === 'token_tower' ? 'block' : 'none';

        const itemSection = document.getElementById('log-item-section');
        let items = [];
        try {
            const rawItems = typeof itemsJson === 'string' ? JSON.parse(itemsJson || '[]') : itemsJson;
            items = Array.isArray(rawItems) ? rawItems : Object.values(rawItems || {});
        } catch (e) {
            items = [];
        }

        if (itemSection) {
            if (spinType === 'token_ring' && items.length > 0) {
                itemSection.style.display = 'block';
                const elCheckboxes = document.getElementById('log-item-checkboxes');
                if (elCheckboxes) {
                    elCheckboxes.innerHTML = items.map(item => `
                        <label class="item-checkbox-label">
                            <input type="checkbox" name="got_item_id[]" value="${item.id}">
                            <span class="badge badge-${item.rarity}">${item.item_name}</span>
                        </label>
                    `).join('');
                }
            } else {
                itemSection.style.display = 'none';
            }
        }

        if (spinType === 'token_tower') {
            const elTowerChk = document.getElementById('log-tower-token-checkbox');
            if (elTowerChk) elTowerChk.checked = false;

            const elTowerSelectWrap = document.getElementById('log-tower-token-select-wrapper');
            if (elTowerSelectWrap) elTowerSelectWrap.style.display = 'none';

            const elTowerSelect = document.getElementById('log-tower-token-select');
            if (elTowerSelect) elTowerSelect.value = Math.min(5, currentTokenLevel + 1);
        }

        const formLog = document.getElementById('form-log');
        if (formLog) formLog.action = '/freefires/session/' + id + '/log';

        const modalLog = document.getElementById('modal-log');
        if (modalLog) {
            modalLog.classList.add('show');
            modalLog.style.display = 'block';
        }

        const modalOverlay = document.getElementById('modal-overlay');
        if (modalOverlay) {
            modalOverlay.classList.add('show');
            modalOverlay.style.display = 'block';
        }
    }

    function checkItemHeaderVisibility() {
        const createContainer = document.getElementById('wheel-slots-container');
        const createHeader = document.getElementById('wheel-slots-header');
        if (createContainer && createHeader) {
            createHeader.style.display = createContainer.children.length > 0 ? 'flex' : 'none';
        }

        const editContainer = document.getElementById('edit-wheel-slots-container');
        const editHeader = document.getElementById('edit-wheel-slots-header');
        if (editContainer && editHeader) {
            editHeader.style.display = editContainer.children.length > 0 ? 'flex' : 'none';
        }
    }

    function addItemSlot() {
        const container = document.getElementById('wheel-slots-container');
        const div = document.createElement('div');
        div.className = 'calc-item-row wheel-slot-row';
        div.dataset.type = 'item';
        div.innerHTML = `
            <input type="hidden" name="slots[${slotIndex}][type]" value="item">
            <input type="text" name="slots[${slotIndex}][item_name]" class="form-control calc-item-name"
                placeholder="cth: Skin Katana" style="flex: 1; min-width: 100px;">
            <select name="slots[${slotIndex}][rarity]" class="form-control calc-item-rarity" style="width: 90px;">
                <option value="epic">Epic</option>
                <option value="legendary">Legendary</option>
                <option value="artifact">Artifact</option>
            </select>
            <input type="number" name="slots[${slotIndex}][token_exchange]" class="form-control calc-item-token"
                placeholder="250" min="1" style="width: 110px;" oninput="updateExpected()">
            <input type="number" name="slots[${slotIndex}][slot_count]" class="form-control calc-item-slot"
                placeholder="1" value="1" min="1" style="width: 55px;" oninput="updateExpected()">
            <button type="button" onclick="removeSlot(this)" class="btn btn-danger btn-sm" style="width: 32px;">×</button>
        `;
        container.appendChild(div);
        slotIndex++;
        checkItemHeaderVisibility();
        updateExpected();
    }

    function removeSlot(btn) {
        const isEdit = btn.closest('#modal-edit') || btn.closest('#edit-wheel-slots-container');
        btn.parentElement.remove();
        checkItemHeaderVisibility();
        if (isEdit) {
            updateEditExpected();
        } else {
            updateExpected();
        }
    }


    function updateExpected() {
        const priceMode = document.getElementById('token-price-mode')?.value || 'normal';
        const price1 = priceMode === 'discount' ? 5 : 9;
        const price5 = priceMode === 'discount' ? 19 : 39;

        let tokenSlots = [];
        document.querySelectorAll('#token-options .calc-token-input').forEach(input => {
            const count = parseInt(input.value) || 0;
            const rawVal = input.dataset.tokenval;
            const weightBase = tokenBaseWeight[rawVal] || 10;
            if (count > 0) {
                tokenSlots.push({
                    val: rawVal,
                    count,
                    weight: weightBase * count
                });
            }
        });

        let itemRows = [];
        document.querySelectorAll('#wheel-slots-container .wheel-slot-row').forEach(row => {
            const name = row.querySelector('.calc-item-name')?.value || 'Item';
            const tokenReq = parseInt(row.querySelector('.calc-item-token')?.value) || 0;
            const slot = parseInt(row.querySelector('.calc-item-slot')?.value) || 0;
            if (tokenReq > 0 && slot > 0) {
                itemRows.push({
                    name,
                    tokenReq,
                    slot
                });
            }
        });

        let konstanta = 1;
        if (itemRows.length > 0) {
            konstanta = itemRows[0].tokenReq;
            for (let i = 1; i < itemRows.length; i++) {
                konstanta = lcm(konstanta, itemRows[i].tokenReq);
            }
        }

        itemRows.forEach(item => {
            item.baseWeight = konstanta / item.tokenReq;
            item.totalWeight = item.baseWeight * item.slot;
        });

        let totalBobot = 0;
        tokenSlots.forEach(t => totalBobot += t.weight);
        itemRows.forEach(i => totalBobot += i.totalWeight);

        let expectedToken = 0;
        tokenSlots.forEach(t => {
            const dropRate = totalBobot > 0 ? t.weight / totalBobot : 0;
            const numVal = tokenNumericVal[t.val] || (parseInt(t.val) || 0);
            expectedToken += dropRate * numVal;
        });

        document.getElementById('total-bobot').textContent = totalBobot.toFixed(0);
        document.getElementById('expected-token').textContent = expectedToken.toFixed(2);

        let dropRateHtml = '';
        if (tokenSlots.length > 0) {
            dropRateHtml += '<p class="task-meta" style="font-weight:600; margin-bottom:0.3rem;">Drop Rate Token:</p>';
            tokenSlots.forEach(t => {
                const rate = totalBobot > 0 ? (t.weight / totalBobot * 100) : 0;
                const displayLabel = t.val === 'crystal' ? 'Crystal Royale' : `Token x${t.val}`;
                dropRateHtml += `
                <div class="session-stat">
                    <span class="task-meta">${displayLabel}</span>
                    <span class="task-title">${rate.toFixed(1)}%</span>
                </div>`;
            });
        }

        if (itemRows.length > 0) {
            dropRateHtml += '<p class="task-meta" style="font-weight:600; margin-top:0.75rem; margin-bottom:0.3rem;">Item Hadiah:</p>';
            itemRows.forEach(i => {
                const rate = totalBobot > 0 ? (i.totalWeight / totalBobot * 100) : 0;
                const estSpin = expectedToken > 0 ? Math.ceil(i.tokenReq / expectedToken) : 0;
                const estDiamond = spinsToHarga(estSpin, price1, price5);
                dropRateHtml += `
                <div class="session-stat">
                    <span class="task-meta">${i.name}</span>
                    <span class="task-title" style="color: var(--accent-primary);">${rate.toFixed(1)}% · ~${estDiamond}dm (${estSpin}x)</span>
                </div>`;
            });
        }

        document.getElementById('session-droprate-list').innerHTML = dropRateHtml;
    }

    function validateAndSubmitSession() {
        const form = document.querySelector('#modal-create form');
        const itemName = form.querySelector('[name="item_name"]').value.trim();
        const spinType = form.querySelector('[name="spin_type"]').value;

        let errors = [];

        if (!itemName) {
            errors.push('Nama Item wajib diisi.');
        }

        if (spinType === 'token_ring') {
            let hasComposition = false;
            document.querySelectorAll('#token-options .calc-token-input').forEach(input => {
                if (parseInt(input.value) > 0) hasComposition = true;
            });
            document.querySelectorAll('#wheel-slots-container .wheel-slot-row').forEach(row => {
                const itemName = row.querySelector('.calc-item-name')?.value.trim();
                const slot = parseInt(row.querySelector('.calc-item-slot')?.value) || 0;
                if (itemName || slot > 0) hasComposition = true;
            });

            if (!hasComposition) {
                errors.push('Token Ring wajib punya minimal 1 komposisi token atau item hadiah.');
            }
        }

        if (spinType === 'faded_wheel') {
            // Faded Wheel tidak butuh validasi tambahan selain nama item
            // (harga sudah fix, diskon opsional)
        }

        if (spinType === 'token_tower') {
            const shardRate = form.querySelector('[name="shard_rate"]');
            if (shardRate && (parseInt(shardRate.value) < 0 || parseInt(shardRate.value) > 100)) {
                errors.push('Drop Rate Spin Shard harus antara 0-100%.');
            }
        }

        const eventStart = form.querySelector('[name="event_start"]').value;
        const eventEnd = form.querySelector('[name="event_end"]').value;
        if (eventStart && eventEnd && eventStart > eventEnd) {
            errors.push('Tanggal Mulai Event tidak boleh setelah Tanggal Selesai.');
        }

        if (errors.length > 0) {
            alert(errors.join('\n'));
            return;
        }

        form.submit();
    }

    function previewFadedPrice() {
        const hasDiscount = document.getElementById('create-has-discount').checked;
        let total = 0;

        document.querySelectorAll('.create-faded-price').forEach(el => {
            const idx = parseInt(el.dataset.idx);
            const price = hasDiscount ? fadedDiscounted[idx] : fadedBase[idx];
            el.textContent = price + ' dm';
            total += price;
        });

        document.getElementById('create-faded-total').textContent = total + ' dm';
    }

    function toggleSpinType(el) {
        const type = el.value;
        document.getElementById('token-options').style.display = type === 'token_ring' ? 'block' : 'none';
        document.getElementById('faded-options').style.display = type === 'faded_wheel' ? 'block' : 'none';
        document.getElementById('tower-options').style.display = type === 'token_tower' ? 'block' : 'none';

        if (type === 'faded_wheel') previewFadedPrice();
    }

    var editSlotIndex = window.editSlotIndex || 100;

    function toggleEditSpinType(el) {
        const type = typeof el === 'string' ? el : el.value;
        document.getElementById('edit-token-options').style.display = type === 'token_ring' ? 'block' : 'none';
        document.getElementById('edit-faded-options').style.display = type === 'faded_wheel' ? 'block' : 'none';
        document.getElementById('edit-tower-options').style.display = type === 'token_tower' ? 'block' : 'none';

        if (type === 'faded_wheel') previewEditFadedPrice();
    }

    function previewEditFadedPrice() {
        const hasDiscount = document.getElementById('edit-has-discount').checked;
        let total = 0;

        document.querySelectorAll('.edit-faded-price').forEach(el => {
            const idx = parseInt(el.dataset.idx);
            const price = hasDiscount ? fadedDiscounted[idx] : fadedBase[idx];
            el.textContent = price + ' dm';
            total += price;
        });

        document.getElementById('edit-faded-total').textContent = total + ' dm';
    }

    function addEditItemSlot(name = '', rarity = 'epic', tokenExchange = '', slotCount = 1) {
        const container = document.getElementById('edit-wheel-slots-container');
        const div = document.createElement('div');
        div.className = 'calc-item-row wheel-slot-row';
        div.dataset.type = 'item';
        div.innerHTML = `
            <input type="hidden" name="slots[${editSlotIndex}][type]" value="item">
            <input type="text" name="slots[${editSlotIndex}][item_name]" class="form-control calc-item-name"
                value="${name}" placeholder="cth: Skin Katana" style="flex: 1; min-width: 100px;">
            <select name="slots[${editSlotIndex}][rarity]" class="form-control calc-item-rarity" style="width: 90px;">
                <option value="epic" ${rarity === 'epic' ? 'selected' : ''}>Epic</option>
                <option value="legendary" ${rarity === 'legendary' ? 'selected' : ''}>Legendary</option>
                <option value="artifact" ${rarity === 'artifact' ? 'selected' : ''}>Artifact</option>
            </select>
            <input type="number" name="slots[${editSlotIndex}][token_exchange]" class="form-control calc-item-token"
                value="${tokenExchange}" placeholder="250" min="1" style="width: 110px;" oninput="updateEditExpected()">
            <input type="number" name="slots[${editSlotIndex}][slot_count]" class="form-control calc-item-slot"
                value="${slotCount}" placeholder="1" min="1" style="width: 55px;" oninput="updateEditExpected()">
            <button type="button" onclick="removeSlot(this)" class="btn btn-danger btn-sm" style="width: 32px;">×</button>
        `;
        container.appendChild(div);
        editSlotIndex++;
        checkItemHeaderVisibility();
        updateEditExpected();
    }

    function updateEditExpected() {
        let tokenSlots = [];
        document.querySelectorAll('#edit-token-options .edit-calc-token-input').forEach(input => {
            const count = parseInt(input.value) || 0;
            const rawVal = input.dataset.tokenval;
            const weightBase = tokenBaseWeight[rawVal] || 10;
            if (count > 0) {
                tokenSlots.push({
                    val: rawVal,
                    count,
                    weight: weightBase * count
                });
            }
        });

        let itemRows = [];
        document.querySelectorAll('#edit-wheel-slots-container .wheel-slot-row').forEach(row => {
            const name = row.querySelector('.calc-item-name')?.value || 'Item';
            const tokenReq = parseInt(row.querySelector('.calc-item-token')?.value) || 0;
            const slot = parseInt(row.querySelector('.calc-item-slot')?.value) || 0;
            if (tokenReq > 0 && slot > 0) {
                itemRows.push({
                    name,
                    tokenReq,
                    slot
                });
            }
        });

        let konstanta = 1;
        if (itemRows.length > 0) {
            konstanta = itemRows[0].tokenReq;
            for (let i = 1; i < itemRows.length; i++) {
                konstanta = lcm(konstanta, itemRows[i].tokenReq);
            }
        }

        itemRows.forEach(item => {
            item.baseWeight = konstanta / item.tokenReq;
            item.totalWeight = item.baseWeight * item.slot;
        });

        let totalBobot = 0;
        tokenSlots.forEach(t => totalBobot += t.weight);
        itemRows.forEach(i => totalBobot += i.totalWeight);

        let expectedToken = 0;
        tokenSlots.forEach(t => {
            const dropRate = totalBobot > 0 ? t.weight / totalBobot : 0;
            const numVal = tokenNumericVal[t.val] || (parseInt(t.val) || 0);
            expectedToken += dropRate * numVal;
        });

        document.getElementById('edit-total-bobot').textContent = totalBobot.toFixed(0);
        document.getElementById('edit-expected-token').textContent = expectedToken.toFixed(2);

        let dropRateHtml = '';
        if (tokenSlots.length > 0) {
            dropRateHtml += '<p class="task-meta" style="font-weight:600; margin-bottom:0.3rem;">Drop Rate Token:</p>';
            tokenSlots.forEach(t => {
                const rate = totalBobot > 0 ? (t.weight / totalBobot * 100) : 0;
                const displayLabel = t.val === 'crystal' ? 'Crystal Royale' : `Token x${t.val}`;
                dropRateHtml += `
                <div class="session-stat">
                    <span class="task-meta">${displayLabel}</span>
                    <span class="task-title">${rate.toFixed(1)}%</span>
                </div>`;
            });
        }
        document.getElementById('edit-session-droprate-list').innerHTML = dropRateHtml;
    }

    document.addEventListener('change', function(e) {
        if (e.target.closest('#wheel-slots-container') || e.target.closest('#token-options .wheel-token-grid')) {
            updateExpected();
        }
        if (e.target.closest('#edit-wheel-slots-container') || e.target.closest('#edit-token-options .wheel-token-grid')) {
            updateEditExpected();
        }
    });

    function openEditModal(id, itemName, spinType, eventStart, eventEnd, spentDiamond, currentSpin, currentToken, startingToken, ticketCount, status, hasDiscount, towerLuck, tokenNeeded, slotsJson) {
        try {
            const elItemName = document.getElementById('edit-item-name');
            if (elItemName) elItemName.value = itemName || '';

            const elSpinType = document.getElementById('edit-spin-type');
            if (elSpinType) elSpinType.value = spinType || 'token_ring';

            const elStatus = document.getElementById('edit-status');
            if (elStatus) elStatus.value = status || 'active';

            const elEventStart = document.getElementById('edit-event-start');
            if (elEventStart) elEventStart.value = eventStart || '';

            const elEventEnd = document.getElementById('edit-event-end');
            if (elEventEnd) elEventEnd.value = eventEnd || '';

            const elSpentDiamond = document.getElementById('edit-spent-diamond');
            if (elSpentDiamond) elSpentDiamond.value = spentDiamond || 0;

            const elCurrentSpin = document.getElementById('edit-current-spin');
            if (elCurrentSpin) elCurrentSpin.value = currentSpin || 0;

            const elCurrentToken = document.getElementById('edit-current-token');
            if (elCurrentToken) elCurrentToken.value = currentToken || 0;

            const elStartingToken = document.getElementById('edit-starting-token');
            if (elStartingToken) elStartingToken.value = startingToken || 0;

            const elTicketCount = document.getElementById('edit-ticket-count');
            if (elTicketCount) elTicketCount.value = ticketCount || 0;

            const elTokenNeeded = document.getElementById('edit-token-needed');
            if (elTokenNeeded) elTokenNeeded.value = tokenNeeded || '';

            const elHasDiscount = document.getElementById('edit-has-discount');
            if (elHasDiscount) elHasDiscount.checked = !!hasDiscount;

            const elTowerLuck = document.getElementById('edit-tower-luck');
            if (elTowerLuck) elTowerLuck.value = towerLuck || 0;

            const elTowerLuckLabel = document.getElementById('edit-tower-luck-label');
            if (elTowerLuckLabel) elTowerLuckLabel.textContent = (towerLuck || 0) + '%';

            document.querySelectorAll('#edit-token-options .edit-calc-token-input').forEach(input => input.value = 0);
            const slotsContainer = document.getElementById('edit-wheel-slots-container');
            if (slotsContainer) slotsContainer.innerHTML = '';

            let slots = [];
            if (typeof slotsJson === 'string') {
                try {
                    slots = JSON.parse(slotsJson || '[]');
                } catch (e) {
                    slots = [];
                }
            } else if (Array.isArray(slotsJson)) {
                slots = slotsJson;
            } else if (slotsJson && typeof slotsJson === 'object') {
                slots = Object.values(slotsJson);
            }

            editSlotIndex = 100;

            slots.forEach(slot => {
                if (slot.type === 'token') {
                    const tokenInput = document.querySelector(`#edit-token-options .edit-calc-token-input[data-tokenval="${slot.token_value}"]`);
                    if (tokenInput) tokenInput.value = slot.slot_count || 0;
                } else if (slot.type === 'item') {
                    addEditItemSlot(slot.item_name || '', slot.rarity || 'epic', slot.token_exchange || '', slot.slot_count || 1);
                }
            });

            checkItemHeaderVisibility();
            toggleEditSpinType(spinType || 'token_ring');
            updateEditExpected();
        } catch (err) {
            console.error('Error opening edit modal:', err);
        }

        const formEdit = document.getElementById('form-edit-session');
        if (formEdit) formEdit.action = '/freefires/session/' + id;

        const modalEdit = document.getElementById('modal-edit');
        if (modalEdit) {
            modalEdit.classList.add('show');
            modalEdit.style.display = 'block';
        }

        const modalOverlay = document.getElementById('modal-overlay');
        if (modalOverlay) {
            modalOverlay.classList.add('show');
            modalOverlay.style.display = 'block';
        }
    }

    document.addEventListener('click', function(e) {
        const logBtn = e.target.closest('.btn-log-spin');
        if (logBtn) {
            try {
                const ds = logBtn.dataset;
                openLogModal(
                    ds.id,
                    ds.spinType,
                    parseInt(ds.currentSpin) || 0,
                    ds.discount === 'true',
                    ds.items || '[]',
                    parseInt(ds.currentToken) || 0,
                    parseInt(ds.ticketCount) || 0,
                    ds.itemName || ''
                );
            } catch (err) {
                console.error('Error handling log button click:', err);
            }
            return;
        }

        const editBtn = e.target.closest('.btn-edit-session');
        if (editBtn) {
            try {
                const ds = editBtn.dataset;
                openEditModal(
                    ds.id,
                    ds.itemName,
                    ds.spinType,
                    ds.eventStart,
                    ds.eventEnd,
                    parseInt(ds.spentDiamond) || 0,
                    parseInt(ds.currentSpin) || 0,
                    parseInt(ds.currentToken) || 0,
                    parseInt(ds.startingToken) || 0,
                    parseInt(ds.ticketCount) || 0,
                    ds.status,
                    ds.discount === 'true',
                    parseInt(ds.luck) || 0,
                    ds.tokenNeeded || '',
                    ds.slots || '[]'
                );
            } catch (err) {
                console.error('Error handling edit button click:', err);
            }
            return;
        }
    });
</script>
@endpush