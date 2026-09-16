{{-- Blok error validasi form KPI. --}}
@if ($errors->any())
    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg shadow-sm mb-6">
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle mt-1 mr-3"></i>
            <div>
                <p class="font-bold">Mohon periksa kembali form Anda:</p>
                <ul class="list-disc pl-5 mt-1 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
