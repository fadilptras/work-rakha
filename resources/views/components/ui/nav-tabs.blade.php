@props([
    'current' => [],
    'homeUrl' => '/',
    'storageKey' => 'app.tabs.v1.guest',
    'activeKey' => 'app.tabs.active.v1.guest',
    'pendingKey' => 'app.tabs.pending.v1.guest',
    'max' => 10,
])

@php
    $homePath = parse_url($homeUrl, PHP_URL_PATH) ?: '/';
    $currentPath = $current['path'] ?? '/';
    $isHome = $currentPath === $homePath;
    $config = [
        'current' => $current,
        'homeUrl' => $homeUrl,
        'homePath' => $homePath,
        'storageKey' => $storageKey,
        'activeKey' => $activeKey,
        'pendingKey' => $pendingKey,
        'max' => (int) $max,
    ];
@endphp

<div id="app-tabbar" class="ui-navtabs" role="navigation" aria-label="Tab halaman">
    <div class="ui-navtabs__scroll" role="tablist" aria-label="Halaman yang terbuka">
        <a href="{{ $homeUrl }}"
            class="ui-navtab ui-navtab--pinned {{ $isHome ? 'is-active' : '' }}"
            data-tab-home="1"
            role="tab"
            aria-selected="{{ $isHome ? 'true' : 'false' }}"
            title="Dashboard">
            <i class="fas fa-th-large ui-navtab__icon" style="color:#2563eb" aria-hidden="true"></i>
            <span class="ui-navtab__label">Dashboard</span>
        </a>
    </div>
    <button type="button" id="app-tabbar-clear" class="ui-navtabs__clear" title="Tutup semua tab" aria-label="Tutup semua tab" hidden>
        <i class="fas fa-xmark" aria-hidden="true"></i>
    </button>
</div>

<script>
    window.__APP_TABS_CONFIG__ = @json($config);
</script>
