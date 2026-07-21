<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
  <title>Lupa Password - Sistem Workflow</title>

  <meta name="theme-color" content="#0ea5e9">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    body { 
      font-family: 'Plus Jakarta Sans', sans-serif; 
      -webkit-tap-highlight-color: transparent; 
      overscroll-behavior-y: none;
    }
    
    @keyframes slideUp {
        0% { transform: translateY(30px); opacity: 0; }
        100% { transform: translateY(0); opacity: 1; }
    }
    .animate-slide-up {
        animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    /* Hilangkan scrollbar agar card terlihat rapi dan elegan */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
  </style>
</head>

<body class="bg-[#0ea5e9] text-slate-800 antialiased overflow-hidden">

  <!-- Background Ornamen (Tetap) -->
  <div class="fixed inset-0 z-0 pointer-events-none bg-[#0ea5e9]">
      <div class="absolute -top-10 -left-10 w-40 h-40 bg-white/10 rounded-full"></div>
      <div class="absolute top-20 -right-10 w-32 h-32 bg-white/10 rounded-full"></div>
  </div>

  <!-- Layout Utama (Fixed Inset-0 mengunci layar 100% tanpa celah) -->
  <div class="fixed inset-0 z-10 flex flex-col w-full">
      
      <!-- Header Section (Fixed 35%, Diam Tidak Ikut Scroll) -->
      <div class="flex-none w-full flex flex-col items-center justify-center shrink-0" style="height: 35%;">
          <div class="w-[68px] h-[68px] bg-white/20 border border-white/20 rounded-[20px] flex items-center justify-center mb-4 shadow-sm">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          </div>
          <h1 class="text-[28px] font-extrabold text-white tracking-tight">Sistem Workflow</h1>
          <p class="text-sky-100 text-[13px] mt-1 font-semibold tracking-[0.2em] uppercase opacity-90">PT Rakha Nusantara Medika</p>
      </div>

      <!-- Bottom Sheet Wrapper (Kotak Putih yang terkunci diam sampai ke dasar layar) -->
      <div class="flex-1 w-full bg-white rounded-t-[36px] shadow-[0_-10px_30px_rgba(0,0,0,0.1)] flex flex-col relative overflow-hidden animate-slide-up">
          
          <!-- Pull Indicator (Tetap Diam di Atas Card) -->
          <div class="absolute top-3 left-1/2 -translate-x-1/2 w-12 h-[5px] bg-slate-200 rounded-full z-20"></div>

          <!-- AREA INI SAJA YANG BISA DI-SCROLL (Inner Card Content) -->
          <div class="flex-1 w-full overflow-y-auto no-scrollbar px-7 pt-9 pb-6 relative z-10">
              <div class="w-full max-w-md mx-auto flex flex-col min-h-full">
                  
                  <!-- Title -->
                  <div class="mb-7 text-center">
                      <h2 class="text-[28px] font-extrabold text-slate-800 tracking-tight">Lupa Password?</h2>
                      <p class="text-slate-500 text-[14px] mt-1.5 font-medium leading-relaxed">Hubungi Administrator untuk mereset password.</p>
                  </div>

                  <div class="space-y-4">
                      <!-- Email Block -->
                      <a href="mailto:admin@rakhanusantaramedika.com" class="flex items-center p-4 bg-white border border-slate-200 rounded-2xl hover:border-sky-300 shadow-[0_4px_15px_rgba(0,0,0,0.02)] active:scale-[0.98] transition-all group">
                          <div class="flex-shrink-0 flex items-center justify-center w-[48px] h-[48px] rounded-[14px] bg-sky-50 text-sky-500 group-hover:scale-110 transition-transform">
                              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                          </div>
                          <div class="ml-4 overflow-hidden">
                              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Email Admin</p>
                              <p class="text-[15px] font-bold text-slate-700 truncate group-hover:text-sky-600 transition-colors">admin@rakha.com</p>
                          </div>
                      </a>

                      <!-- WA Block -->
                      <a href="https://wa.me/6281572496312" target="_blank" class="flex items-center p-4 bg-white border border-slate-200 rounded-2xl hover:border-green-300 shadow-[0_4px_15px_rgba(0,0,0,0.02)] active:scale-[0.98] transition-all group">
                          <div class="flex-shrink-0 flex items-center justify-center w-[48px] h-[48px] rounded-[14px] bg-green-50 text-green-500 group-hover:scale-110 transition-transform">
                              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12c0 2.17.69 4.18 1.87 5.83l-1.4 4.12 4.25-1.12C8.24 21.6 10.05 22 12 22c5.52 22 10-17.52 10-12S17.52 2 12 2zm4.5 14.5c-.25.75-1.4 1.35-2 1.45-.6.1-1.3-.15-3.5-1.05-2.65-1.1-4.35-3.8-4.5-4-.15-.2-1.05-1.4-1.05-2.65s.65-1.85.9-2.1c.25-.25.55-.3.75-.3s.4 0 .6.05c.2.05.45-.1.7.5.25.6.85 2.1.95 2.3.1.2.15.4-.05.7-.15.25-.35.45-.5.6-.15.15-.3.35-.15.65.15.3.7 1.2 1.5 1.9.95.8 1.8 1.1 2.1 1.25.3.15.5.15.7-.05.2-.2.85-1 .1-1.25.25-.3.55-.25.8.35.3.6.8 1.5.85 1.7.05.2-.05.45-.3.75z" clip-rule="evenodd"></path></svg>
                          </div>
                          <div class="ml-4">
                              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">WhatsApp Admin</p>
                              <p class="text-[15px] font-bold text-slate-700 group-hover:text-green-600 transition-colors">0815-7249-6312</p>
                          </div>
                      </a>
                  </div>

                  <!-- Info Box -->
                  <div class="bg-blue-50/70 border border-blue-100 p-4 mt-3 rounded-[16px] text-[12px] text-sky-800 flex items-start gap-3">
                      <svg class="w-5 h-5 flex-shrink-0 text-sky-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                      <span class="font-medium leading-relaxed">Permintaan reset password akan diproses oleh Administrator pada jam operasional kerja (08:00 - 16:00 WIB).</span>
                  </div>

                  <!-- Spacer agar konten di bawah tertendang ke paling bawah jika layar panjang -->
                  <div class="flex-1"></div>

                  <!-- Back Button -->
                  <div class="pt-6">
                      <a href="{{ route('login') }}" class="w-full bg-[#f8fafc] hover:bg-slate-200 text-slate-700 font-bold py-4 rounded-[14px] active:scale-[0.98] transition-all flex justify-center items-center gap-2">
                          <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                          <span class="text-[15px] tracking-wide">Kembali ke Login</span>
                      </a>
                  </div>

                  <div class="mt-8 text-center mt-auto pt-4 pb-2">
                      <p class="text-[10px] text-slate-400 font-bold tracking-[0.1em] uppercase">© {{ date('Y') }} Workflow Rakha</p>
                  </div>

              </div>
          </div>
      </div>
  </div>

</body>
</html>