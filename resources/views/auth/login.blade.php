<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Sign In</title>

  <meta name="theme-color" content="#1e40af">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
  <div class="absolute top-1/4 left-10 w-[30rem] h-[30rem] bg-white rounded-full filter blur-3xl opacity-40 z-0"></div>
  <div class="absolute bottom-1/4 right-10 w-[30rem] h-[30rem] bg-sky-300 rounded-full filter blur-3xl opacity-30 z-0"></div>

  <div class="min-h-screen flex flex-col lg:items-center lg:justify-center relative z-10 w-full">

    <div class="lg:hidden absolute top-0 left-0 w-full h-[40vh] bg-gradient-to-b from-sky-500 to-sky-700 rounded-b-[3rem] z-0 overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white opacity-5 rounded-full -mr-10 -mt-10 blur-2xl"></div>
        <div class="absolute bottom-10 left-0 w-32 h-32 bg-blue-400 opacity-10 rounded-full -ml-5 blur-xl"></div>
        
        <div class="flex flex-col items-center justify-center h-full pb-12 px-6 text-center">
            <h2 class="text-white text-2xl font-bold tracking-wide mb-1">Selamat Datang</h2>
            <p class="text-blue-200 text-sm font-light">Sistem Workflow Rakha</p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row w-full max-w-4xl lg:rounded-2xl lg:shadow-2xl bg-white/80 backdrop-blur-xl border border-white/40 lg:overflow-hidden z-10 
                mt-[30vh] lg:mt-0 px-4 pb-8 lg:p-0 rounded-t-3xl shadow-[0_-10px_40px_-15px_rgba(0,0,0,0.3)] lg:shadow-[0_8px_32px_0_rgba(31,38,135,0.15)]">
      
      <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-sky-500 to-sky-700 items-center justify-center p-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-pattern opacity-5"></div>
        <div class="absolute top-0 left-0 w-40 h-40 bg-white opacity-10 rounded-full -translate-x-10 -translate-y-10 blur-3xl"></div>
        
        <div class="w-full max-w-sm relative z-10"> 
          <img 
            src="{{ asset('asset/images/ilustrasi1.png') }}"
            alt="Ilustrasi login" 
            class="w-64 mx-auto mb-8 animated-float drop-shadow-2xl"
          />
          <h2 class="text-3xl font-bold text-white mb-2 text-left">Selamat Datang Kembali</h2>
          <p class="text-blue-100 text-base font-light text-left">Masuk untuk mengakses Sistem Workflow Rakha.</p>
        </div>
      </div>

      <div class="w-full lg:w-1/2 bg-transparent flex flex-col justify-center p-6 lg:p-12">
        
        <div class="text-center lg:text-left mb-8 mt-4 lg:mt-0">
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900">Sign In</h1>
            <p class="mt-2 text-sm text-gray-500">Silakan masuk untuk melanjutkan</p>
        </div>

        @if ($errors->any())
          <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 text-sm rounded-r shadow-sm animate-pulse" role="alert">
              <p class="font-bold flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Login Gagal
              </p>
              <p class="mt-1 ml-6">{{ $errors->first('email') ?: $errors->first('password') }}</p>
          </div>
        @endif

        <form class="space-y-5" action="{{ route('login.post') }}" method="POST">
          @csrf
          
          <div class="group">
            <label for="email" class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1 ml-1">Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                </div>
                <input 
                  id="email" 
                  name="email" 
                  type="email" 
                  required 
                  placeholder="nama@email.com" 
                  autocomplete="username"
                  class="w-full pl-10 pr-4 py-3 bg-white/60 backdrop-blur-sm border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all duration-200 outline-none text-gray-800 placeholder-gray-400"
                  value="{{ old('email') }}" />
            </div>
          </div>
          
          <div class="group">
            <div class="flex items-center justify-between mb-1 ml-1">
                <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wide">Password</label>
            </div>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
              </div>
              <input 
                id="password" 
                name="password" 
                type="password" 
                required 
                placeholder="Masukkan password"
                autocomplete="current-password"
                class="w-full pl-10 pr-10 py-3 bg-white/60 backdrop-blur-sm border border-gray-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition-all duration-200 outline-none text-gray-800 placeholder-gray-400"
              />
              <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-blue-600 transition-colors focus:outline-none">
                <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"></svg>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between text-sm mt-2">
            <div class="flex items-center">
              <input id="remember" name="remember" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded cursor-pointer">
              <label for="remember" class="ml-2 block text-gray-600 cursor-pointer select-none">Ingat Saya</label>
            </div>
            <div>
              <a href="{{ route('password.request') }}" class="font-medium text-blue-600 hover:text-blue-800 transition-colors">Lupa Password?</a>
            </div>
          </div>

          <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-lg shadow-sky-500/30 text-sm font-bold text-white bg-gradient-to-r from-sky-500 to-sky-600 hover:from-sky-600 hover:to-sky-700 focus:outline-none focus:ring-4 focus:ring-sky-300 transition-all duration-300 transform hover:-translate-y-1 active:scale-95">
              Masuk
            </button>
          </div>
        </form>

        <div class="mt-8 text-center lg:hidden">
            <p class="text-[10px] text-gray-400 font-medium">© {{ date('Y') }} Workflow Rakha. All rights reserved.</p>
        </div>

      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      const eyeOpenPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
      const eyeClosedPath = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.774 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65" />';

      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.innerHTML = eyeClosedPath;
      } else {
        passwordInput.type = 'password';
        eyeIcon.innerHTML = eyeOpenPath;
      }
    }
    document.getElementById('eyeIcon').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
  </script>
</body>
</html>