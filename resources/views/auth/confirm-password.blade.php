<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Konfirmasi Password</title>

  <meta name="theme-color" content="#1e40af">
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('favicon-96x96.png') }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
  <link rel="manifest" href="{{ asset('site.webmanifest') }}">

  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Poppins', sans-serif; }
  </style>
</head>

<body class="bg-gray-50 text-gray-800 flex items-center justify-center min-h-screen relative">
    <!-- Latar Belakang Sama Dengan Login -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-700 to-indigo-900 overflow-hidden z-0 hidden lg:block">
        <div class="absolute inset-0 opacity-5" style="background-image: radial-gradient(#4f46e5 0.5px, transparent 0.5px), radial-gradient(#4f46e5 0.5px, #f3f4f6 0.5px); background-size: 20px 20px; background-position: 0 0, 10px 10px;"></div>
        <div class="absolute top-0 left-0 w-64 h-64 bg-white opacity-10 rounded-full -translate-x-10 -translate-y-10 blur-3xl"></div>
    </div>
    
    <div class="lg:hidden absolute top-0 left-0 w-full h-[40vh] bg-gradient-to-b from-blue-800 to-indigo-900 rounded-b-[3rem] z-0 overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-white opacity-5 rounded-full -mr-10 -mt-10 blur-2xl"></div>
    </div>

    <!-- Kotak Utama -->
    <div class="w-full max-w-md bg-white rounded-t-3xl lg:rounded-2xl shadow-xl z-10 px-6 py-10 mt-[30vh] lg:mt-0 relative mx-4">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 text-red-600 rounded-full mb-4 shadow-sm border border-red-200">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Verifikasi Keamanan</h2>
            <p class="text-sm text-gray-500 mt-2">Untuk mencegah tindakan penyusup yang ingin mengubah atau menghapus data admin, harap masukkan <strong class="text-red-500">Kata Sandi Lama / Saat Ini</strong> Anda.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 text-sm rounded-r shadow-sm" role="alert">
                <p class="font-bold flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Konfirmasi Gagal
                </p>
                <p class="mt-1 ml-6">{{ $errors->first() }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
            @csrf

            <div class="group">
                <label for="password" class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1 ml-1">Kata Sandi Saat Ini</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    </div>
                    <input 
                        id="password" 
                        type="password" 
                        name="password" 
                        required 
                        autofocus
                        autocomplete="current-password"
                        placeholder="Ketik password lama Anda di sini..."
                        class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-red-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 outline-none text-gray-800 placeholder-gray-400">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-blue-600 transition-colors focus:outline-none">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"></svg>
                    </button>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-lg shadow-red-500/30 text-sm font-bold text-white bg-gradient-to-r from-red-600 to-red-800 hover:from-red-700 hover:to-red-900 focus:outline-none focus:ring-4 focus:ring-red-300 transition-all duration-300 transform hover:-translate-y-1 active:scale-95">
                    Lanjutkan Akses
                </button>
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">Batal & Kembali ke Beranda</a>
            </div>
        </form>
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