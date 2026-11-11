<x-layout-users title="Kartu Stock">
    <div class="mesh-bg flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800">
        <div class="relative z-10 w-full max-w-none mx-0 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 space-y-4 md:space-y-5 flex-1 flex flex-col items-stretch justify-start">

            <x-ui.page-header title="Kartu Stock" subtitle="Mutasi Masuk / Keluar per barang — saldo dihitung dari opname terakhir." icon="fa-id-card-clip" :sticky="false" />

            @if (session('success'))
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-xs font-bold">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-xs font-bold">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-xs font-bold">{{ $errors->first() }}</div>
            @endif

            @include('users.warehouse.stock-card.detail')
        </div>
    </div>

    @include('users.warehouse.stock-card.edit-modal')
    @include('users.warehouse.stock-card.quick-mutasi-modal')
</x-layout-users>