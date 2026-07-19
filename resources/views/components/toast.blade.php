@if (session()->has('success') || session()->has('error'))
    {{-- Posisi statis di Kanan Bawah untuk semua ukuran layar (Bottom-6 Right-6) --}}
    <div id="toast-container" class="fixed bottom-6 right-6 z-[9999] flex flex-col gap-3 transition-all duration-500 transform translate-y-12 opacity-0 w-[350px] max-w-[85vw]">
        
        @if (session()->has('success'))
            {{-- Desain Blue Glassmorphism Persegi Panjang --}}
            <div class="flex items-start gap-4 p-4 w-full bg-blue-900/60 backdrop-blur-lg border border-blue-400/40 shadow-[0_8px_32px_rgba(30,58,138,0.3)] rounded-xl ring-1 ring-white/10" role="alert">
                <div class="flex-shrink-0 mt-0.5">
                    <div class="w-8 h-8 flex items-center justify-center bg-blue-500/30 rounded-lg border border-blue-300/30 shadow-inner">
                        <i class="fas fa-check text-blue-100 text-sm"></i>
                    </div>
                </div>
                <div class="flex flex-col flex-1 pr-2">
                    <p class="text-[13px] font-black text-white uppercase tracking-widest mb-1 drop-shadow-sm">Sukses</p>
                    <p class="text-xs font-medium text-blue-100 leading-snug">{{ session('success') }}</p>
                </div>
                <button type="button" onclick="closeToast()" class="ml-auto flex-shrink-0 text-blue-300 hover:text-white transition-colors p-1 bg-blue-800/40 rounded-md hover:bg-blue-600/60">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            {{-- Desain Red Glassmorphism Persegi Panjang (Untuk Error) --}}
            <div class="flex items-start gap-4 p-4 w-full bg-rose-900/60 backdrop-blur-lg border border-rose-400/40 shadow-[0_8px_32px_rgba(159,18,57,0.3)] rounded-xl ring-1 ring-white/10" role="alert">
                <div class="flex-shrink-0 mt-0.5">
                    <div class="w-8 h-8 flex items-center justify-center bg-rose-500/30 rounded-lg border border-rose-300/30 shadow-inner">
                        <i class="fas fa-exclamation text-rose-100 text-sm"></i>
                    </div>
                </div>
                <div class="flex flex-col flex-1 pr-2">
                    <p class="text-[13px] font-black text-white uppercase tracking-widest mb-1 drop-shadow-sm">Perhatian</p>
                    <p class="text-xs font-medium text-rose-100 leading-snug">{{ session('error') }}</p>
                </div>
                <button type="button" onclick="closeToast()" class="ml-auto flex-shrink-0 text-rose-300 hover:text-white transition-colors p-1 bg-rose-800/40 rounded-md hover:bg-rose-600/60">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
        @endif

    </div>

    <script>
        function closeToast() {
            const toast = document.getElementById('toast-container');
            if (toast) {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-12', 'opacity-0');
                setTimeout(() => toast.remove(), 400); 
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('toast-container');
            if (toast) {
                setTimeout(() => {
                    toast.classList.remove('translate-y-12', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                }, 100);

                setTimeout(() => {
                    closeToast();
                }, 5000);
            }
        });
    </script>
@endif
