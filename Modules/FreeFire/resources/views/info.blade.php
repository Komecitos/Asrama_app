@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/modules/freefire.css') }}">
<style>
    .info-wrapper {
        max-width: 900px;
        margin: 0 auto;
    }

    .info-search-header {
        background: var(--bg-card-2, rgba(30, 41, 59, 0.6));
        border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.08));
        border-radius: var(--radius-xl, 16px);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
        backdrop-filter: blur(12px);
    }

    .filter-chips {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.75rem;
    }

    .chip {
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        color: var(--text-muted);
        padding: 0.35rem 0.85rem;
        border-radius: 20px;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }

    .chip:hover,
    .chip.active {
        background: var(--accent-primary, #6366f1);
        color: #fff;
        border-color: var(--accent-primary, #6366f1);
    }

    .highlight-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .highlight-card {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: var(--radius-lg, 12px);
        padding: 1rem;
        position: relative;
        overflow: hidden;
    }

    .highlight-card.badge-gold {
        border-color: rgba(234, 179, 8, 0.4);
        background: linear-gradient(135deg, rgba(234, 179, 8, 0.08), rgba(15, 23, 42, 0.6));
    }

    .highlight-card.badge-indigo {
        border-color: rgba(99, 102, 241, 0.4);
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(15, 23, 42, 0.6));
    }

    .highlight-card.badge-emerald {
        border-color: rgba(16, 185, 129, 0.4);
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(15, 23, 42, 0.6));
    }

    .highlight-tag {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
        text-transform: uppercase;
    }

    .tag-gold {
        background: rgba(234, 179, 8, 0.2);
        color: #fde047;
    }

    .tag-indigo {
        background: rgba(99, 102, 241, 0.2);
        color: #a5b4fc;
    }

    .tag-emerald {
        background: rgba(16, 185, 129, 0.2);
        color: #6ee7b7;
    }

    .guide-card {
        background: var(--bg-card-2, rgba(30, 41, 59, 0.6));
        border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.08));
        border-radius: var(--radius-xl, 16px);
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.5rem;
    }

    .faded-price-steps {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 0.5rem;
    }

    .faded-step {
        background: rgba(15, 23, 42, 0.5);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        padding: 0.4rem 0.75rem;
        text-align: center;
        flex: 1;
        min-width: 75px;
    }

    .faded-step-num {
        font-size: 0.65rem;
        color: var(--text-muted);
        display: block;
    }

    .faded-step-val {
        font-size: 0.85rem;
        font-weight: 700;
        color: #fde047;
    }
</style>
@endpush

@section('topbar')
<a href="{{ route('freefire.calc') }}" class="btn btn-secondary">Kalkulator</a>
<a href="{{ route('freefire.session') }}" class="btn btn-secondary">Sesi Spin</a>
<a href="{{ route('freefire.info') }}" class="btn btn-primary">Informasi</a>
@endsection

@section('content')

<div class="page-header">
    <h2 class="title">Pusat Informasi Free Fire</h2>
</div>

<div class="info-wrapper">
    {{-- SEARCH & FILTER HEADER --}}
    <div class="info-search-header">
        <div class="form-group" style="margin-bottom: 0;">
            <input type="text" id="info-search" class="form-control"
                placeholder="Cari info... (cth: monthly, evo level 5, faded wheel, diamond)"
                oninput="filterInfo()">
        </div>
        <div class="filter-chips">
            <span class="chip active" onclick="setCategory('all', this)">Semua</span>
            <span class="chip" onclick="setCategory('diamond', this)">💎 Harga Diamond</span>
            <span class="chip" onclick="setCategory('evo', this)">🔫 Token Evo Gun</span>
            <span class="chip" onclick="setCategory('guild', this)">🛡️ Level Guild</span>
            <span class="chip" onclick="setCategory('event', this)">🎰 Panduan Spin</span>
        </div>
    </div>

    {{-- SECTION 1: HARGA DIAMOND --}}
    <div class="info-section" data-category="diamond" data-keywords="harga diamond top-up topup membership weekly monthly paket dm murah hemat">
        <h3 class="section-header" style="margin-bottom: 0.75rem;">💎 Paket Membership & Hemat</h3>

        <div class="highlight-grid">
            <div class="highlight-card badge-gold">
                <span class="highlight-tag tag-gold">⭐ Paling Hemat</span>
                <p class="task-title" style="margin-bottom: 0.2rem;">Monthly Membership</p>
                <h3 style="color: #fde047; margin: 0.2rem 0; font-weight: 700;">2.000 DM</h3>
                <p class="task-meta">Rp 85.000 · <strong style="color:#6ee7b7;">Rp 42,50 / DM</strong></p>
                <p class="task-meta" style="font-size: 0.7rem; margin-top: 0.4rem;">💡 Dicicil harian selama 30 hari. Rasio hemat 4.5x vs Top-Up biasa.</p>
            </div>

            <div class="highlight-card badge-indigo">
                <span class="highlight-tag tag-indigo">🔥 Sangat Murah</span>
                <p class="task-title" style="margin-bottom: 0.2rem;">Weekly Membership</p>
                <h3 style="color: #a5b4fc; margin: 0.2rem 0; font-weight: 700;">450 DM</h3>
                <p class="task-meta">Rp 28.000 · <strong style="color:#6ee7b7;">Rp 62,22 / DM</strong></p>
                <p class="task-meta" style="font-size: 0.7rem; margin-top: 0.4rem;">💡 Dicicil harian 7 hari. Rasio hemat 3x vs Top-Up biasa.</p>
            </div>

            <div class="highlight-card badge-emerald">
                <span class="highlight-tag tag-emerald">👍 Hemat</span>
                <p class="task-title" style="margin-bottom: 0.2rem;">Weekly Lite</p>
                <h3 style="color: #6ee7b7; margin: 0.2rem 0; font-weight: 700;">200 DM</h3>
                <p class="task-meta">Rp 16.000 · <strong style="color:#6ee7b7;">Rp 80,00 / DM</strong></p>
                <p class="task-meta" style="font-size: 0.7rem; margin-top: 0.4rem;">💡 Opsi paling terjangkau untuk langganan mingguan ringan.</p>
            </div>
        </div>

        <div class="guide-card" style="margin-top: 1rem;">
            <h4 class="widget-title" style="margin-bottom: 0.75rem;">Daftar Lengkap Harga Top-Up Murni</h4>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Jenis Paket</th>
                            <th>Jumlah Diamond</th>
                            <th>Estimasi Harga</th>
                            <th>Rasio per 1 DM</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $diamondPrices = [
                        ['Top-Up', '5 DM', 'Rp 1.000', 'Rp 200,00', 'Nominal terkecil'],
                        ['Top-Up', '12 DM', 'Rp 2.000', 'Rp 166,67', ''],
                        ['Top-Up', '50 DM', 'Rp 8.000', 'Rp 160,00', ''],
                        ['Top-Up', '70 DM', 'Rp 10.000', 'Rp 142,86', ''],
                        ['Top-Up', '91 DM', 'Rp 13.000', 'Rp 142,86', 'Pilihan favorit top-up ringan'],
                        ['Top-Up', '140 DM', 'Rp 20.000', 'Rp 142,86', ''],
                        ['Top-Up', '355 DM', 'Rp 50.000', 'Rp 140,84', ''],
                        ['Top-Up', '720 DM', 'Rp 100.000', 'Rp 138,89', ''],
                        ['Top-Up', '1.450 DM', 'Rp 200.000', 'Rp 137,93', 'Top-up murni paling hemat'],
                        ['Weekly Lite', '200 DM', 'Rp 16.000', 'Rp 80,00', 'Membership Harian'],
                        ['Weekly', '450 DM', 'Rp 28.000', 'Rp 62,22', 'Membership Harian'],
                        ['Monthly', '2.000 DM', 'Rp 85.000', 'Rp 42,50', 'Membership Harian (Termurah)'],
                        ];
                        @endphp
                        @foreach($diamondPrices as $row)
                        <tr>
                            <td><span class="badge {{ str_contains($row[0], 'Top-Up') ? 'badge-info' : 'badge-success' }}">{{ $row[0] }}</span></td>
                            <td class="task-title">{{ $row[1] }}</td>
                            <td>{{ $row[2] }}</td>
                            <td class="task-meta" style="color: #6ee7b7;">{{ $row[3] }}</td>
                            <td class="task-meta">{{ $row[4] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- SECTION 2: TOKEN EVO GUN --}}
    <div class="info-section" style="margin-top: 1.5rem;" data-category="evo" data-keywords="token evo gun senjata upgrade level weapon m1014 ak dragon mp40 cobra scar megalodon ump xm8">
        <h3 class="section-header" style="margin-bottom: 0.75rem;">🔫 Upgrade Token Evo Gun</h3>
        <div class="guide-card">
            <p class="task-meta" style="margin-bottom: 1rem;">Rincian kebutuhan token dan estimasi diamond untuk upgrade senjata Evolution dari Level 1 ke Level Max (Level 7).</p>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Level Upgrade</th>
                            <th>Token Dibutuhkan</th>
                            <th>Total Akumulasi</th>
                            <th>Estimasi Toko (10dm/tk)</th>
                            <th>Benefit / Fitur Unik</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $evoData = [
                        ['Level 1 -> 2', 30, 30, '300 DM', 'Announcement Kill Broadcast'],
                        ['Level 2 -> 3', 30, 60, '600 DM', 'Bentuk Visual Baru (New Look)'],
                        ['Level 3 -> 4', 80, 140, '1.400 DM', 'Hit Effect / Effect Tembakan'],
                        ['Level 4 -> 5', 120, 260, '2.600 DM', 'Kill Effect & Fire Effect'],
                        ['Level 5 -> 6', 400, 660, '6.600 DM', 'Tampilan Visual Maksimal & Ability Unlock'],
                        ['Level 6 -> 7', 400, 1.060, '10.600 DM', 'Exclusive Emote & Custom Ability Max'],
                        ['Level 7 (Max)', 600, 1.660, '16.600 DM', 'Special Attributes & Max Evolution Status'],
                        ];
                        @endphp
                        @foreach($evoData as $row)
                        <tr>
                            <td class="task-title">{{ $row[0] }}</td>
                            <td><span class="badge badge-info">{{ $row[1] }} Token</span></td>
                            <td class="task-meta">{{ $row[2] }} Token</td>
                            <td style="color: #fde047; font-weight: 600;">{{ $row[3] }}</td>
                            <td class="task-meta">{{ $row[4] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="task-meta" style="margin-top: 0.75rem; font-size: 0.75rem;">
                💡 <strong>Tips Hemat:</strong> Jangan beli token 1-per-1 di shop toko seharga 10 DM/token. Manfaatkan <em>Evo Gun Token Box (Crate)</em> saat ada diskon atau beli dari event <em>Token Ring / Mystery Shop</em> untuk menghemat hingga 50-70% Diamond!
            </p>
        </div>
    </div>

    {{-- SECTION 4: LEVEL GUILD --}}
    <div class="info-section" style="margin-top: 1.5rem;" data-category="guild" data-keywords="level guild 1 2 3 4 5 6 kapasitas anggota guild store token mabar name change card banner logo tag">
        <h3 class="section-header" style="margin-bottom: 0.75rem;">🛡️ Rincian Level Guild (Level 1 - 6)</h3>
        <div class="guide-card">
            <p class="task-meta" style="margin-bottom: 1rem;">Tabel kapasitas anggota, tingkatan Guild Store, serta berbagai fitur & keuntungan eksklusif berdasarkan Level Guild.</p>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Level Guild</th>
                            <th>Kapasitas Anggota</th>
                            <th>Tingkat Store</th>
                            <th>Keuntungan & Fitur Utama</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $guildLevels = [
                        [
                        'level' => 'Level 1 (Dasar)',
                        'capacity' => '20 Orang',
                        'store' => 'Tier 1',
                        'benefits' => 'Membuka fitur dasar & akses awal Misi Guild. Akses Guild Store Tier 1 (Gold, Loadout standar, Universal Fragment). Anggota bisa mulai mengumpulkan Guild Token dari mabar.'
                        ],
                        [
                        'level' => 'Level 2',
                        'capacity' => '25 Orang',
                        'store' => 'Tier 2',
                        'benefits' => 'Batas maksimal harian Guild Token yang bisa didapatkan anggota meningkat. Akses Guild Store Tier 2 (Weapon Loot Crate biasa & Voucher diskon).'
                        ],
                        [
                        'level' => 'Level 3',
                        'capacity' => '30 Orang',
                        'store' => 'Tier 3',
                        'benefits' => 'Membuka Guild Store Tier 3 (Custom Room Card / Tiket Luck Royale: Gold Royale & Weapon Royale). Pilihan ikon/logo Guild yang bisa digunakan Ketua Guild lebih variatif.'
                        ],
                        [
                        'level' => 'Level 4',
                        'capacity' => '35 Orang',
                        'store' => 'Tier 4',
                        'benefits' => 'Membuka Guild Store Tier 4 (Item kosmetik eksklusif seperti Parasut, Surfboard, & Baju tema khusus Guild mulai tersedia).'
                        ],
                        [
                        'level' => 'Level 5',
                        'capacity' => '40 Orang',
                        'store' => 'Tier 5',
                        'benefits' => 'Membuka Guild Store Tier 5 (Akses Name Change Card / Kartu Ganti Nama dengan harga Guild Token murah + sedikit Diamond).'
                        ],
                        [
                        'level' => 'Level 6 (Max)',
                        'capacity' => '45 - 50 Orang*',
                        'store' => 'Tier Max',
                        'benefits' => 'Membuka seluruh item tier tertinggi Guild Store (Avatar, Banner eksklusif Guild, Pin, & Skin premium). Guild Tag / Banner spesial di profil & loading screen (jika di leaderboard teratas). Bonus maksimal Activity Points & Guild Token dari mabar. (*Kapasitas dasar 45 orang, bisa ditambah hingga 50 orang dengan Diamond).'
                        ]
                        ];
                        @endphp
                        @foreach($guildLevels as $g)
                        <tr>
                            <td class="task-title">{{ $g['level'] }}</td>
                            <td><span class="badge badge-info">{{ $g['capacity'] }}</span></td>
                            <td><span class="badge badge-warning">{{ $g['store'] }}</span></td>
                            <td class="task-meta" style="font-size: 0.8rem; line-height: 1.4;">{{ $g['benefits'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="task-meta" style="margin-top: 0.75rem; font-size: 0.75rem;">
                💡 <strong>Tips Guild:</strong> Lakukan mabar bersama teman satu Guild secara rutin untuk mempercepat kenaikan Activity Points dan memaksimalkan perolehan Guild Token harian!
            </p>
        </div>
    </div>

    {{-- SECTION 3: PANDUAN SPIN --}}
    <div class="info-section" style="margin-top: 1.5rem;" data-category="event" data-keywords="panduan spin token ring faded wheel token tower aturan rate luck diskon">
        <h3 class="section-header" style="margin-bottom: 0.75rem;">🎰 Ketentuan & Aturan Event Spin</h3>

        {{-- TOKEN RING --}}
        <div class="guide-card" style="margin-bottom: 1rem;">
            <h4 class="widget-title" style="margin-bottom: 0.5rem;">🎡 Token Ring</h4>
            <p class="task-meta" style="margin-bottom: 0.75rem;">Event wheel yang memberikan Token (x1, x2, x3, x5, x10, x20, x30, x100) atau item langsung. Token yang terkumpul dapat ditukarkan di Exchange Store.</p>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Mode Spin</th>
                            <th>Harga Standar</th>
                            <th>Harga Diskon (50%)</th>
                            <th>Ekspektasi Token / Spin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="task-title">1x Spin</td>
                            <td>9 DM</td>
                            <td style="color: #6ee7b7;">5 DM</td>
                            <td class="task-meta">~1,5 - 2,5 Token per spin</td>
                        </tr>
                        <tr>
                            <td class="task-title">5x Spin (Paket Hemat)</td>
                            <td>39 DM</td>
                            <td style="color: #6ee7b7;">19 DM</td>
                            <td class="task-meta">Bonus jaminan hemat 6 DM</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- FADED WHEEL --}}
        <div class="guide-card" style="margin-bottom: 1rem;">
            <h4 class="widget-title" style="margin-bottom: 0.5rem;">🃏 Faded Wheel</h4>
            <p class="task-meta" style="margin-bottom: 0.75rem;">Event 8 item tanpa item ganda. Anda wajib membuang 2 item tidak diinginkan sebelum mulai spin. Harga spin meningkat tiap putaran.</p>

            <p class="task-meta" style="font-size: 0.75rem; font-weight: 600; color: var(--text-primary); margin-bottom: 0.3rem;">Urutan Harga Spin Standar (8x Putaran):</p>
            <div class="faded-price-steps">
                <div class="faded-step"><span class="faded-step-num">Spin 1</span><span class="faded-step-val">9 dm</span></div>
                <div class="faded-step"><span class="faded-step-num">Spin 2</span><span class="faded-step-val">19 dm</span></div>
                <div class="faded-step"><span class="faded-step-num">Spin 3</span><span class="faded-step-val">39 dm</span></div>
                <div class="faded-step"><span class="faded-step-num">Spin 4</span><span class="faded-step-val">69 dm</span></div>
                <div class="faded-step"><span class="faded-step-num">Spin 5</span><span class="faded-step-val">99 dm</span></div>
                <div class="faded-step"><span class="faded-step-num">Spin 6</span><span class="faded-step-val">199 dm</span></div>
                <div class="faded-step"><span class="faded-step-num">Spin 7</span><span class="faded-step-val">399 dm</span></div>
                <div class="faded-step"><span class="faded-step-num">Spin 8</span><span class="faded-step-val">799 dm</span></div>
            </div>
            <p class="task-meta" style="margin-top: 0.6rem; font-size: 0.75rem;">
                🔥 <strong>Total Maksimal:</strong> 1.632 DM (atau 1.234 DM jika Spin ke-1 diskon menjadi 5 DM). Grand prize dipastikan dapat maksimal pada Spin ke-8!
            </p>
        </div>

        {{-- TOKEN TOWER --}}
        <div class="guide-card">
            <h4 class="widget-title" style="margin-bottom: 0.5rem;">🗼 Token Tower</h4>
            <p class="task-meta" style="margin-bottom: 0.5rem;">Spin bertingkat dengan target mengumpulkan 5 Token Tower (batu tower) untuk membuka hadiah utama bundle / legend skin.</p>
            <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.8rem; color: var(--text-muted);">
                <li style="margin-bottom: 0.3rem;">Setiap spin menghasilkan Shard Ticket atau Token Utama.</li>
                <li style="margin-bottom: 0.3rem;">Item sisa dari wheel dapat dilebur (3 item biasa = 1x Free Spin Tambahan).</li>
                <li>Gunakan slider <em>Tingkat Keberuntungan (Luck %)</em> di Kalkulator untuk menghitung estimasi diskon diamond.</li>
            </ul>
        </div>
    </div>

    <p id="info-no-result" class="empty-state" style="display:none; margin-top: 2rem;">Tidak ditemukan informasi yang cocok dengan pencarian Anda.</p>
</div>

@endsection

@push('scripts')
<script>
    var currentCategory = window.currentCategory || 'all';

    function setCategory(cat, el) {
        currentCategory = cat;
        document.querySelectorAll('.filter-chips .chip').forEach(c => c.classList.remove('active'));
        if (el) el.classList.add('active');
        filterInfo();
    }

    function filterInfo() {
        const query = document.getElementById('info-search').value.toLowerCase().trim();
        const sections = document.querySelectorAll('.info-section');
        let anyVisible = false;

        sections.forEach(section => {
            const catMatch = currentCategory === 'all' || section.dataset.category === currentCategory;
            const keywords = (section.dataset.keywords || '').toLowerCase();
            const text = section.textContent.toLowerCase();
            const searchMatch = !query || keywords.includes(query) || text.includes(query);

            const isVisible = catMatch && searchMatch;
            section.style.display = isVisible ? 'block' : 'none';
            if (isVisible) anyVisible = true;
        });

        const noRes = document.getElementById('info-no-result');
        if (noRes) noRes.style.display = anyVisible ? 'none' : 'block';
    }
</script>
@endpush