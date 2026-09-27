{{-- Layout induk semua laporan PDF admin.
    Sections:
      judul-dokumen  : <title> (default 'Laporan')
      orientasi      : portrait / landscape (default landscape)
      margin-pdf     : margin @page (default '10mm 8mm')
      kop-judul      : judul kop (wajib)
      kop-periode    : periode (wajib)
      kop-info       : info filter, opsional
      gaya-tambahan  : <style> khusus laporan, opsional
      konten         : isi laporan (wajib)
      legenda-judul  : default 'Keterangan:'
      legenda        : blok legenda, opsional
      nomor-dokumen  : nomor kontrol dokumen kanan bawah, opsional
--}}
<!DOCTYPE html>
<html>
<head>
    <title>@yield('judul-dokumen', 'Laporan')</title>
    <style>
        @page { size: @yield('orientasi', 'landscape'); margin: @yield('margin-pdf', '10mm 8mm'); }
    </style>
    @include('admin.exports.pdf.components.styles')
    @yield('gaya-tambahan')
</head>
<body>
    @include('admin.exports.pdf.components.header', [
        'kopJudul' => $__env->yieldContent('kop-judul'),
        'kopPeriode' => $__env->yieldContent('kop-periode'),
        'kopInfo' => $__env->yieldContent('kop-info'),
    ])

    @yield('konten')

    @hasSection('legenda')
        <div class="legend">
            <strong>@yield('legenda-judul', 'Keterangan:')</strong>
            @yield('legenda')
        </div>
    @endif

    @hasSection('nomor-dokumen')
        <div class="nomor-dokumen"><span>@yield('nomor-dokumen')</span></div>
    @endif
</body>
</html>
