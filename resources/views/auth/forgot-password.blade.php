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
    body { 
      font-family: 'Poppins', sans-serif; 
      /* Background gambar dihapus dari sini */
    }
    .animated-image {
      animation: float 6s ease-in-out infinite;
    }
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }
  </style>
</head>
{{-- --- PERUBAHAN BACKGROUND KEMBALI KE ABU-ABU --- --}}
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

 <div class="flex w-full max-w-4xl rounded-lg shadow-lg bg-white overflow-hidden">
    {{-- --- PERUBAHAN WARNA GRADIEN PANEL --- --}}
    <div class="hidden lg:flex w-1/2 bg-gradient-to-br from-blue-700 to-indigo-900 items-center justify-center p-8">
        <div class="text-center">
            <img 
            src="{{ asset('asset/images/ilustrasi1.png') }}" 
            alt="Ilustrasi login"
            class="w-full max-w-xs mx-auto animated-image"
          />
          <h2 class="text-2xl font-bold text-white mt-12">Kami Siap Membantu Anda</h2>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-8 md:p-12">
      <div class="w-full max-w-md">
        <div class="text-center lg:text-left mb-8">
          <h1 class="text-3xl font-extrabold text-gray-900">Lupa Password?</h1>
          <p class="mt-2 text-gray-600">Hubungi Administrator untuk mereset password Anda.</p>
        </div>

        <div class="text-left bg-gray-50 p-4 rounded-lg border">
            <p class="font-semibold text-gray-800">Kontak Admin:</p>
            <ul class="list-disc list-inside mt-2 space-y-2 text-gray-700">
                <li>
                    <strong>Email :</strong> 
                    <a href="mailto:admin@rakhanusantaramedika.com" class="text-xs text-blue-600 hover:underline">admin@rakhanusantaramedika.com</a>
                </li>
                <li>
                    <strong>WhatsApp :</strong> 
                    <a href="https://wa.me/6281572496312" target="_blank" class="text-blue-600 hover:underline">0815-7249-6312</a>
                </li>
            </ul>
        </div>

        <div class="pt-4">
            {{-- --- PERUBAHAN WARNA TOMBOL --- --}}
            <a href="{{ route('login') }}" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-gradient-to-r from-blue-700 to-indigo-900 hover:from-blue-800 hover:to-indigo-950 transition">
                Kembali ke Halaman Login
            </a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>