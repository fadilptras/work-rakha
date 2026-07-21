<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover" />
  <title>Masuk - Sistem Workflow</title>

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

    .premium-input {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-out;
    }
    .premium-input:focus {
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
        border-color: #0ea5e9;
        background-color: #ffffff;
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
              <!-- Konten dibungkus agar mengisi minimal 100% tinggi Card, lalu bisa didorong ke bawah -->
              <div class="w-full max-w-md mx-auto flex flex-col min-h-full">
                  
                  <!-- Title -->
                  <div class="mb-7 text-center">
                      <h2 class="text-[28px] font-extrabold text-slate-800 tracking-tight">Masuk</h2>
                      <p class="text-slate-500 text-[14px] mt-1.5 font-medium">Silakan masuk ke akun Anda</p>
                  </div>

                  @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-100 rounded-2xl p-4 flex items-start gap-3">
                        <div class="text-red-500 mt-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-red-700">Gagal Masuk</p>
                            <p class="text-[13px] text-red-600 mt-1 font-medium">{{ $errors->first('email') ?: $errors->first('password') }}</p>
                        </div>
                    </div>
                  @endif

                  <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                      @csrf
                      
                      <!-- Email Input -->
                      <div>
                          <label for="email" class="block text-[11px] font-bold text-slate-600 uppercase tracking-widest mb-2 ml-1">Alamat Email</label>
                          <div class="relative group">
                              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-sky-500 transition-colors">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                              </div>
                              <input 
                                  id="email" 
                                  name="email" 
                                  type="email" 
                                  required 
                                  placeholder="admin@rakha.com" 
                                  autocomplete="username"
                                  class="premium-input w-full pl-12 pr-4 py-[15px] rounded-[14px] text-[15px] font-semibold text-slate-800 placeholder-slate-400 outline-none"
                                  value="{{ old('email') }}" 
                              />
                          </div>
                      </div>

                      <!-- Password Input -->
                      <div class="pt-1">
                          <label for="password" class="block text-[11px] font-bold text-slate-600 uppercase tracking-widest mb-2 ml-1">Password</label>
                          <div class="relative group">
                              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-sky-500 transition-colors">
                                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                              </div>
                              <input 
                                  id="password" 
                                  name="password" 
                                  type="password" 
                                  required 
                                  placeholder="••••••••••" 
                                  autocomplete="current-password"
                                  class="premium-input w-full pl-12 pr-12 py-[15px] rounded-[14px] text-[15px] font-semibold text-slate-800 placeholder-slate-400 outline-none tracking-[0.2em]"
                              />
                              <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center justify-center text-slate-400 active:text-sky-600 transition-colors h-full">
                                  <svg id="eyeIcon" class="w-[22px] h-[22px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                  </svg>
                              </button>
                          </div>
                      </div>

                      <!-- Options -->
                      <div class="flex items-center justify-between pt-3 pb-2 px-1">
                          <label class="flex items-center cursor-pointer group">
                              <div class="relative flex items-center justify-center">
                                  <input id="remember" name="remember" type="checkbox" class="peer w-[18px] h-[18px] appearance-none border-[1.5px] border-slate-300 rounded-[5px] bg-white checked:border-[#0ea5e9] checked:bg-[#0ea5e9] transition-all outline-none">
                                  <svg class="absolute w-3 h-3 text-white pointer-events-none opacity-0 peer-checked:opacity-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                              </div>
                              <span class="ml-2.5 text-[13px] font-semibold text-slate-600">Ingat Saya</span>
                          </label>
                          <a href="{{ route('password.request') }}" class="text-[13px] font-bold text-[#0ea5e9] active:text-sky-700 transition-colors">Lupa Password?</a>
                      </div>

                      <!-- Spacer agar tombol ke bawah jika layar sisa -->
                      <div class="flex-1"></div>

                      <!-- Submit Button -->
                      <div class="pt-5">
                          <button type="submit" class="w-full bg-[#0ea5e9] text-white font-bold py-4 rounded-[14px] shadow-[0_4px_15px_rgba(14,165,233,0.3)] active:scale-[0.98] transition-transform flex justify-center items-center gap-2">
                              <span class="text-[15px] tracking-wide">Masuk Sekarang</span>
                              <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                          </button>
                      </div>
                  </form>

                  <div class="mt-8 text-center mt-auto pt-4 pb-2">
                      <p class="text-[10px] text-slate-400 font-bold tracking-[0.1em] uppercase">© {{ date('Y') }} Workflow Rakha</p>
                  </div>

              </div>
          </div>
      </div>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
      } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
      }
    }
  </script>
</body>
</html>
