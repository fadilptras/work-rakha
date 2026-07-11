<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Prosedur Reset Password</title>

    <meta name="theme-color" content="#2563eb"> {{-- Warna biru sesuai tema --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
  <style>
    body { 
      font-family: 'Poppins', sans-serif; 
      background: linear-gradient(180deg, #f0f9ff 0%, #e0f2fe 40%, #bae6fd 100%); /* Light sky theme */
      background-attachment: fixed;
    }
    
    .bg-rings {
      background-image: repeating-radial-gradient(
        circle at center,
        transparent,
        transparent 150px,
        rgba(255, 255, 255, 0.6) 151px,
        transparent 152px
      );
    }
    .animated-float { animation: floating 6s ease-in-out infinite; }
    @keyframes floating {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }

    .bg-pattern {
      background-image: radial-gradient(#4f46e5 0.5px, transparent 0.5px), radial-gradient(#4f46e5 0.5px, #f3f4f6 0.5px);
      background-size: 20px 20px;
      background-position: 0 0, 10px 10px;
    }
  </style>
</head>

<body class="text-gray-800 relative min-h-screen">

  <!-- Sky theme background accents -->
  <div class="absolute inset-0 bg-rings z-0"></div>
  <div class="absolute top-1/4 left-10 w-[30rem] h-[30rem] bg-white rounded-full filter blur-[100px] opacity-70 z-0"></div>
  <div class="absolute bottom-1/4 right-10 w-[30rem] h-[30rem] bg-sky-300 rounded-full filter blur-[100px] opacity-50 z-0"></div>

  <div class="min-h-screen flex flex-col lg:items-center lg:justify-center relative z-10 w-full">

    <div class="lg:hidden absolute top-0 left-0 w-full h-[40vh] bg-gradient-to-b from-sky-500 to-sky-700 rounded-b-[3rem] z-0 overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white opacity-5 rounded-full -mr-10 -mt-10 blur-2xl"></div>
        <div class="absolute bottom-10 left-0 w-32 h-32 bg-blue-400 opacity-10 rounded-full -ml-5 blur-xl"></div>
        
        <div class="flex flex-col items-center justify-center h-full pb-12 px-6 text-center">
            <h2 class="text-white text-2xl font-bold tracking-wide mb-1">Lupa Password?</h2>
            <p class="text-blue-200 text-sm font-light">Sistem Workflow Rakha</p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row w-full max-w-4xl lg:rounded-2xl lg:shadow-2xl bg-white/80 backdrop-blur-xl border border-white/40 lg:overflow-hidden z-10 
                mt-[30vh] lg:mt-0 px-4 pb-8 lg:p-0 rounded-t-3xl shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.3)] lg:shadow-[0_8px_32px_0_rgba(31,38,135,0.15)]">

    {{-- --- PERUBAHAN WARNA GRADIEN PANEL --- --}}
    <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-sky-500 to-sky-700 items-center justify-center p-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-pattern opacity-5"></div>
        <div class="absolute top-0 left-0 w-40 h-40 bg-white opacity-10 rounded-full -translate-x-10 -translate-y-10 blur-3xl"></div>
        <div class="w-full max-w-sm relative z-10"> 
          <img 
            src="{{ asset('asset/images/ilustrasi1.png') }}"
            alt="Ilustrasi bantuan" 
            class="w-64 mx-auto mb-8 animated-float drop-shadow-2xl"
          />
          <h2 class="text-3xl font-bold text-white mb-2 text-left">Butuh Bantuan?</h2>
          <p class="text-blue-100 text-base font-light text-left">Tim Administrator kami siap membantu mereset password akun Anda agar dapat kembali beraktivitas.</p>
        </div>
    </div>

    <div class="w-full lg:w-1/2 bg-transparent flex flex-col justify-center p-6 lg:p-12">
      <div class="w-full max-w-md mx-auto">
        
        <div class="text-center lg:text-left mb-6 mt-0">
          <h1 class="text-3xl font-extrabold text-gray-900">Reset Password</h1>
          <p class="mt-2 text-sm text-gray-500 leading-relaxed">Hubungi Administrator untuk mereset password Anda.</p>
        </div>

        <div class="space-y-3 mb-4">
            <!-- Email Block -->
            <a href="mailto:admin@rakhanusantaramedika.com" class="group flex items-center p-3 bg-white/60 backdrop-blur-sm border border-gray-200 rounded-xl hover:bg-white transition-all shadow-sm hover:shadow-md">
                <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-lg bg-sky-50 text-sky-600 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                <div class="ml-4 overflow-hidden">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Email Admin</p>
                    <p class="text-sm font-semibold text-gray-700 group-hover:text-sky-600 truncate">admin@rakhanusantaramedika.com</p>
                    </div>
                </a>

            <!-- WA Block -->
            <a href="https://wa.me/6281572496312" target="_blank" class="group flex items-center p-3 bg-white/60 backdrop-blur-sm border border-gray-200 rounded-xl hover:bg-white transition-all shadow-sm hover:shadow-md">
                <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded-lg bg-green-50 text-green-600 group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12c0 2.17.69 4.18 1.87 5.83l-1.4 4.12 4.25-1.12C8.24 21.6 10.05 22 12 22c5.52 22 10-17.52 10-12S17.52 2 12 2zm4.5 14.5c-.25.75-1.4 1.35-2 1.45-.6.1-1.3-.15-3.5-1.05-2.65-1.1-4.35-3.8-4.5-4-.15-.2-1.05-1.4-1.05-2.65s.65-1.85.9-2.1c.25-.25.55-.3.75-.3s.4 0 .6.05c.2.05.45-.1.7.5.25.6.85 2.1.95 2.3.1.2.15.4-.05.7-.15.25-.35.45-.5.6-.15.15-.3.35-.15.65.15.3.7 1.2 1.5 1.9.95.8 1.8 1.1 2.1 1.25.3.15.5.15.7-.05.2-.2.85-1 .1-1.25.25-.3.55-.25.8.35.3.6.8 1.5.85 1.7.05.2-.05.45-.3.75z" clip-rule="evenodd"></path></svg>
                    </div>
                <div class="ml-4">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">WhatsApp Admin</p>
                    <p class="text-sm font-semibold text-gray-700 group-hover:text-green-600">0815-7249-6312</p>
                    </div>
                </a>
        </div>

        <div class="bg-blue-50 border-l-4 border-sky-400 p-3 mb-8 rounded-r-lg text-xs text-sky-800 shadow-sm">
            <p class="flex items-center">
                <svg class="w-4 h-4 mr-2 flex-shrink-0 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Diproses pada jam operasional (08:00 - 16:00 WIB).</span>
            </p>
        </div>

        <div class="mt-4">
            {{-- --- PERUBAHAN WARNA TOMBOL --- --}}
            <a href="{{ route('login') }}" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-lg shadow-sky-500/30 text-sm font-bold text-white bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 focus:outline-none focus:ring-4 focus:ring-sky-300 transition-all duration-300 transform hover:-translate-y-1 active:scale-95">
                Kembali ke Halaman Login
            </a>
        </div>
      </div>
    </div>
  </div>

  <div class="mt-8 text-center lg:hidden z-10">
      <p class="text-[10px] text-gray-400 font-medium">© {{ date('Y') }} Workflow Rakha. All rights reserved.</p>
  </div>
  
  </div>
</body>
</html>