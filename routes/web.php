<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AbsenController;
use App\Http\Controllers\PengajuanDanaController;

use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\Admin\AdminAbsensiController;
use App\Http\Controllers\Admin\AdminCutiController;
use App\Http\Controllers\RekapAbsenController;
use App\Http\Controllers\Admin\AdminPengajuanDanaController;
use App\Http\Controllers\Admin\AdminLemburController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\SalesController;

use App\Http\Controllers\Admin\AdminAgendaController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\AktivitasController;
use App\Http\Controllers\Admin\AdminAktivitasController;
use App\Http\Controllers\Admin\AdminCrmController;
use App\Http\Controllers\PengajuanBarangController;
use App\Http\Controllers\Admin\AdminPengajuanBarangController;
use App\Http\Controllers\Admin\AdminHolidayController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\Admin\AdminKpiController;
use App\Http\Controllers\Admin\AdminKpiIndicatorController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\AdminBarangController;

// Awalan
Route::get('/', fn() => redirect()->route('login'));

// Autentikasi (Login, Logout, Lupa Password)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.post');
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');
Route::get('/forgot-password', function () {
    $agent = new \Jenssegers\Agent\Agent();
    $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';
    return view("auth.forgot-password_{$viewSuffix}");
})->middleware('guest')->name('password.request');

Route::middleware('auth')->group(function () {
    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);
});

// Route untuk Fitur Utama Pengguna (yang sudah login)
Route::middleware(['auth', 'redirect.if.admin'])->group(function () {
    
    // Dasboard
    Route::get('/dashboard', function () {
        $agent = new \Jenssegers\Agent\Agent();
        $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';
        return view("users.dashboard.dashboard_{$viewSuffix}", ['title' => 'Dashboard']);
    })->name('dashboard');

    // Absensi
    Route::get('/absen', [AbsenController::class, 'absen'])->name('absen');
    Route::post('/absen', [AbsenController::class, 'store'])->name('absen.store');
    Route::patch('/absen/keluar/{absensi}', [AbsenController::class, 'updateKeluar'])->name('absen.keluar');
    
    // KPI
    Route::get('/kpi', [KpiController::class, 'index'])->name('kpi.index');

    // Lembur
    Route::post('/absen/lembur', [AbsenController::class, 'storeLembur'])->name('absen.lembur.store');
    Route::patch('/absen/lembur/keluar/{lembur}', [AbsenController::class, 'updateLemburKeluar'])->name('absen.lembur.keluar');

    Route::prefix('cuti')->name('cuti.')->group(function () {
        Route::get('/', [CutiController::class, 'index'])->name('index');
        Route::get('/create', [CutiController::class, 'create'])->name('create');
        Route::post('/', [CutiController::class, 'store'])->name('store');
        
        // UBAH INI: Tambahkan kata 'detail' agar tidak bentrok dengan route lain
        Route::get('/detail/{cuti}', [CutiController::class, 'show'])->name('show'); 
        
        Route::put('/{cuti}/status', [CutiController::class, 'updateStatus'])->name('updateStatus');
        Route::post('/{cuti}/cancel', [CutiController::class, 'cancel'])->name('cancel');
        Route::get('/{cuti}/download', [CutiController::class, 'downloadPdf'])->name('download');
    });

    // Route Approval Cuti (Ini yang mentrigger notifikasi 'disetujui'/'ditolak')
    Route::post('/cuti/{cuti}/approve', [AdminCutiController::class, 'approve'])->name('cuti.approve');
    Route::post('/cuti/{cuti}/reject', [AdminCutiController::class, 'reject'])->name('cuti.reject');
    
    // Notifikasi
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/update-fcm-token', [NotifikasiController::class, 'updateFcmToken'])
        ->name('fcm.update');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'editProfile'])->name('profil.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profil.update');
    Route::post('/profile/check-password', [ProfileController::class, 'checkCurrentPassword'])->name('profile.checkPassword');
    Route::get('/profile/download-pdf', [ProfileController::class, 'downloadPdf'])->name('profile.downloadPdf');

    // Pengajuan Dana
    Route::get('/pengajuan-dana/history', [PengajuanDanaController::class, 'history'])->name('pengajuan_dana.history');
    Route::get('/pengajuan-dana', [PengajuanDanaController::class, 'index'])->name('pengajuan_dana.index');
    Route::post('/pengajuan-dana', [PengajuanDanaController::class, 'store'])->name('pengajuan_dana.store');
    Route::get('/pengajuan-dana/{pengajuanDana}', [PengajuanDanaController::class, 'show'])->name('pengajuan_dana.show');
    Route::post('/pengajuan-dana/{pengajuanDana}/approve', [PengajuanDanaController::class, 'approve'])->name('pengajuan_dana.approve');
    Route::post('/pengajuan-dana/{pengajuanDana}/reject', [PengajuanDanaController::class, 'reject'])->name('pengajuan_dana.reject');
    Route::post('/pengajuan-dana/{pengajuanDana}/proses-pembayaran', [PengajuanDanaController::class, 'prosesPembayaran'])
        ->name('pengajuan_dana.proses_pembayaran');

    Route::get('/pengajuan-dana/{pengajuanDana}/download', [PengajuanDanaController::class, 'downloadPDF'])->name('pengajuan_dana.download');
    Route::post('/pengajuan-dana/{pengajuanDana}/cancel', [PengajuanDanaController::class, 'cancel'])->name('pengajuan_dana.cancel');



    // Rekap Absensi 
    Route::get('/rekap-absen', [RekapAbsenController::class, 'index'])->name('rekap_absen.index');

    // Agenda
    Route::get('/agendas', [AgendaController::class, 'index'])->name('agendas.index');
    Route::post('/agendas', [AgendaController::class, 'store'])->name('agendas.store');
    Route::get('/get-users', [AgendaController::class, 'getUsers'])->name('agendas.getUsers');
    Route::put('/agendas/{agenda}', [AgendaController::class, 'update'])->name('agendas.update');
    Route::delete('/agendas/{agenda}', [AgendaController::class, 'destroy'])->name('agendas.destroy');

    
Route::controller(CrmController::class)->group(function () {
    Route::get('/crm', 'index')->name('crm.index');
    Route::get('/crm/matrix', 'matrix')->name('crm.matrix');
    Route::post('/crm/store', 'store')->name('crm.store');
    Route::get('/crm/{client}', 'show')->name('crm.show');
    Route::post('/crm/interaction', 'storeInteraction')->name('crm.interaction.store');
    Route::post('/crm/interaction/support', 'storeSupport')->name('crm.interaction.support');
    Route::delete('/crm/client/{client}', 'destroyClient')->name('crm.client.destroy');
    Route::delete('/crm/interaction/{interaction}', 'destroyInteraction')->name('crm.interaction.destroy');
    Route::get('/crm/matrix/export', 'exportMatrix')->name('crm.matrix.export');
    Route::get('/crm/{client}/export', 'exportClientRecap')->name('crm.client.export');
    Route::get('/crm/client/{client}/edit', [CrmController::class, 'edit'])->name('crm.client.edit');
    Route::put('/crm/client/{client}', [CrmController::class, 'update'])->name('crm.client.update');
    Route::post('/crm/interaction/entertain', 'storeEntertain')->name('crm.interaction.entertain');
    Route::put('/crm/interaction/{interaction}/update', [CrmController::class, 'updateInteraction'])
    ->name('crm.interaction.update');
});

Route::controller(SalesController::class)->prefix('sales')->name('sales.')->group(function () {
    Route::get('/', 'index')->name('index'); 
    Route::get('/analytics', 'analytics')->name('analytics');
    Route::get('/monthly', 'monthly')->name('monthly');
    Route::get('/manage', 'manage')->name('manage');
    Route::post('/manual', 'storeManual')->name('store_manual');
    Route::post('/import', 'importExcel')->name('import_excel');
    Route::get('/template', 'downloadTemplate')->name('download_template');
    Route::get('/export', 'export')->name('export');
    Route::delete('/bulk-destroy', 'bulkDestroy')->name('bulk_destroy');
    Route::put('/{sale}', 'update')->name('update');
    Route::delete('/{sale}', 'destroy')->name('destroy');
    Route::post('/target', 'storeTarget')->name('target.store');
    Route::get('/monitoring/data', 'monitoringData')->name('monitoring.data');
    Route::get('/monthly-detail', 'monthlyDetailData')->name('monthly.detail');
    Route::get('/visualisasi/data', 'visualisasiData')->name('visualisasi.data');
});

    Route::resource('aktivitas', AktivitasController::class)
        ->only(['index', 'store']) // Kita hanya butuh method index() dan store()
        ->middleware('auth');

    Route::get('/aktivitas/json', [AktivitasController::class, 'getAktivitasJson'])
        ->name('aktivitas.getJson')
        ->middleware('auth');

    Route::get('/kirim-ulang-tahun', [NotifikasiController::class, 'kirimUlangTahun'])
        ->name('notifikasi.ulangtahun');

    Route::controller(PengajuanBarangController::class)
        ->prefix('pengajuan-barang')
        ->name('pengajuan_barang.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            
            Route::get('/history', 'history')->name('history');
            
            // Halaman Monitoring (Khusus Manajemen)
            Route::get('/monitoring-all', 'monitoringAll')->name('monitoring_all');
            
            // Export Excel Rekap PO
            Route::get('/export-excel', [App\Http\Controllers\Admin\AdminPengajuanBarangController::class, 'exportRekapExcel'])->name('export_excel');

            // Halaman Detail
            Route::get('/{pengajuanBarang}', 'show')->name('show');
            
            // [PERBAIKAN] Ganti route approve/reject lama dengan satu route updateStatus
            // Pastikan form di View (users.detail-pengajuan-barang) action-nya mengarah ke route ini
            Route::patch('/{pengajuanBarang}/status', 'updateStatus')->name('updateStatus');

            // Download & Cancel
            Route::get('/{pengajuanBarang}/download', 'download')->name('download');
            Route::post('/{pengajuanBarang}/cancel', 'cancel')->name('cancel');
            
            // Tambahan untuk Admin (Approver 4) di sisi user
            Route::post('/{pengajuanBarang}/update-monitoring', 'updateMonitoring')->name('updateMonitoring');
            Route::post('/{pengajuanBarang}/konfirmasi-proses', 'konfirmasiProses')->name('konfirmasiProses');
            Route::post('/{pengajuanBarang}/migrasi-termin-lama', 'migrasiTerminLama')->name('migrasiTerminLama');
        });
});

Route::middleware(['auth', 'admin', 'admin.idle'])->prefix('admin')->name('admin.')->group(function () {
    
    Route::get('/', fn() => redirect()->route('admin.employees.index'));

    Route::patch('/users/{user}/reset-password', [PasswordResetController::class, 'resetToDefault'])->name('users.resetPassword'); 
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/', [AdminUserController::class, 'indexByRole'])->defaults('role', 'user')->name('index');
        Route::post('/', [RegisteredUserController::class, 'store'])->name('store');

        Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');

        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/set-as-head', [AdminUserController::class, 'setAsDivisionHead'])->name('setAsHead');
        Route::get('/{user}/download-pdf', [AdminUserController::class, 'downloadProfilePdf'])->name('downloadProfilePdf');
        Route::get('/{user}/ajax-detail', [AdminUserController::class, 'ajaxDetail'])->name('ajaxDetail');
    });

    Route::prefix('admins')->name('admins.')->group(function () {
        Route::get('/', [AdminUserController::class, 'indexByRole'])->defaults('role', 'admin')->name('index');
        
        Route::post('/', [RegisteredUserController::class, 'store'])
            ->name('store')
            ->middleware('password.confirm'); // Proteksi Tambah Admin
            
        Route::post('/update', [AdminUserController::class, 'updateAdmin'])
            ->name('update')
            ->middleware('password.confirm'); // Proteksi Edit Admin
            
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])
            ->name('destroy')
            ->middleware('password.confirm'); // Proteksi Hapus Admin
    });
    
    // Kelola Absen
    Route::prefix('absensi')->name('absensi.')->group(function () {
        // Aktivitas Harian & download PDF/Excel
        Route::get('/', [AdminAbsensiController::class, 'index'])->name('index');
        Route::get('/pdf/harian', [AdminAbsensiController::class, 'downloadPdfHarian'])->name('downloadPdfHarian');
        Route::get('/excel/harian', [AdminAbsensiController::class, 'downloadExcelHarian'])->name('downloadExcelHarian');
        
        // Rekap Absensi Bulanan & download PDF
        Route::get('/rekap', [AdminAbsensiController::class, 'rekap'])->name('rekap');
        Route::get('/rekap/pdf', [AdminAbsensiController::class, 'downloadPdf'])->name('rekap.downloadPdf');
        Route::get('/rekap/excel', [AdminAbsensiController::class, 'downloadExcel'])->name('rekap.downloadExcel');
    });


    // Rekap Lembur 
    Route::prefix('lembur')->name('lembur.')->group(function () {
        Route::get('/', [AdminLemburController::class, 'index'])->name('index');
        Route::get('/pdf', [AdminLemburController::class, 'downloadPdf'])->name('downloadPdf');
    });
        
    // Cuti
    Route::prefix('cuti')->name('cuti.')->group(function () {
        Route::get('/', [AdminCutiController::class, 'index'])->name('index');
        Route::get('/rekap-pdf', [AdminCutiController::class, 'downloadRekapPDF'])->name('downloadRekapPdf');
        Route::get('/set-approvers', [AdminCutiController::class, 'setApprovers'])->name('set_approvers');
        Route::post('/set-approvers', [AdminCutiController::class, 'saveApprovers'])->name('set_approvers.save');
        Route::get('/pengaturan-pdf', [AdminCutiController::class, 'downloadPengaturanPDF'])->name('downloadPengaturanPDF');
        Route::get('/pengaturan', [AdminCutiController::class, 'pengaturanCuti'])->name('pengaturan');
        Route::post('/pengaturan', [AdminCutiController::class, 'updatePengaturanCuti'])->name('updatePengaturan');
        Route::get('/{cuti}', [AdminCutiController::class, 'show'])->name('show');
        Route::delete('/{cuti}', [AdminCutiController::class, 'destroy'])->name('destroy');
        Route::get('/{cuti}/download', [AdminCutiController::class, 'download'])->name('download');
        Route::post('/{cuti}/force-approve', [AdminCutiController::class, 'forceApprove'])->name('forceApprove');
    });

    // Pengajuan Dana
    Route::prefix('pengajuan-dana')->name('pengajuan_dana.')->group(function() {
        Route::get('/', [AdminPengajuanDanaController::class, 'index'])->name('index');
        Route::get('/rekap-pdf', [AdminPengajuanDanaController::class, 'downloadRekapPDF'])->name('downloadRekapPdf');
        Route::get('/{pengajuanDana}', [AdminPengajuanDanaController::class, 'show'])->name('show');
        Route::delete('/{pengajuanDana}', [AdminPengajuanDanaController::class, 'destroy'])->name('destroy');
        Route::get('/{pengajuanDana}/download', [AdminPengajuanDanaController::class, 'downloadPDF'])->name('downloadPdf');
        Route::get('/pengaturan/approvers', [AdminPengajuanDanaController::class, 'showSetApprovers'])->name('set_approvers.index');
        Route::post('/pengaturan/approvers', [AdminPengajuanDanaController::class, 'saveSetApprovers'])->name('set_approvers.save');
        Route::post('/{pengajuanDana}/mark-as-paid', [AdminPengajuanDanaController::class, 'markAsPaid'])
             ->name('markAsPaid');
    });



    // Agenda
    Route::prefix('agenda')->name('agenda.')->group(function () {
        Route::get('/', [AdminAgendaController::class, 'index'])->name('index');
        Route::post('/', [AdminAgendaController::class, 'store'])->name('store');
        Route::get('/get-all-users', [AdminAgendaController::class, 'getAllUsers'])->name('getAllUsers');
        Route::get('/events', [AdminAgendaController::class, 'getAdminAgendas'])->name('getEvents');
        Route::put('/{agenda}', [AdminAgendaController::class, 'update'])->name('update');
        Route::delete('/{agenda}', [AdminAgendaController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('aktivitas')->name('aktivitas.')->group(function () {
        Route::get('/', [AdminAktivitasController::class, 'index'])->name('index');
        
        // Tambahkan ini:
        Route::get('/download-pdf', [AdminAktivitasController::class, 'downloadPdf'])->name('downloadPdf');
        Route::get('/download-excel', [AdminAktivitasController::class, 'downloadExcel'])->name('downloadExcel');
    });

    Route::prefix('kpi')->name('kpi.')->group(function () {
        Route::get('/', [AdminKpiController::class, 'index'])->name('index');
        Route::get('/{id}/evaluate', [AdminKpiController::class, 'evaluate'])->name('evaluate');
        Route::post('/{id}/evaluate', [AdminKpiController::class, 'storeEvaluate'])->name('storeEvaluate');
        Route::post('/{id}/approve', [AdminKpiController::class, 'approve'])->name('approve');
        
        Route::prefix('indicators')->name('indicators.')->group(function () {
            Route::get('/', [AdminKpiIndicatorController::class, 'index'])->name('index');
            Route::post('/', [AdminKpiIndicatorController::class, 'store'])->name('store');
            Route::put('/{indicator}', [AdminKpiIndicatorController::class, 'update'])->name('update');
            Route::delete('/{indicator}', [AdminKpiIndicatorController::class, 'destroy'])->name('destroy');
        });
    });

    Route::controller(AdminCrmController::class)->prefix('crm')->name('crm.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        
        // Export Matrix
        Route::get('/matrix/export', 'exportMatrix')->name('matrix.export');

        // Client Management
        Route::get('/{client}', 'show')->name('show');
        Route::get('/client/{client}/edit', 'edit')->name('client.edit');
        Route::put('/client/{client}', 'update')->name('client.update');
        Route::delete('/client/{client}', 'destroyClient')->name('client.destroy');
        
        // Interaction Store
        Route::post('/interaction', 'storeInteraction')->name('interaction.store');
        Route::post('/interaction/support', 'storeSupport')->name('interaction.support');
        Route::post('/interaction/entertain', 'storeEntertain')->name('interaction.entertain');
        
        // Interaction Delete & Update
        Route::delete('/interaction/{interaction}', 'destroyInteraction')->name('interaction.destroy');
        
        // [PERBAIKAN ROUTE UPDATE]
        // Menghapus '/crm' di awal karena sudah ada prefix
        // Menggunakan string method 'updateInteraction' bukan array
        Route::put('/interaction/{interaction}/update', 'updateInteraction')->name('interaction.update');
        
        Route::get('/{client}/export', 'exportClientRecap')->name('client.export');
    });
    
    Route::prefix('pengajuan-barang')->name('pengajuan_barang.')->group(function() {
        Route::get('/', [App\Http\Controllers\Admin\AdminPengajuanBarangController::class, 'index'])->name('index');
        Route::get('/rekap-pdf', [App\Http\Controllers\Admin\AdminPengajuanBarangController::class, 'downloadRekapPDF'])->name('downloadRekapPdf');
        Route::get('/export-excel', [App\Http\Controllers\Admin\AdminPengajuanBarangController::class, 'exportRekapExcel'])->name('export_excel');
        Route::get('/set-approvers', [AdminPengajuanBarangController::class, 'setApprovers'])->name('set_approvers'); // Sesuaikan method
        Route::post('/set-approvers', [AdminPengajuanBarangController::class, 'saveApprovers'])->name('set_approvers.save');
        Route::get('/{pengajuanBarang}', [App\Http\Controllers\Admin\AdminPengajuanBarangController::class, 'show'])->name('show');
        Route::delete('/{pengajuanBarang}', [AdminPengajuanBarangController::class, 'destroy'])->name('destroy');
        Route::get('/{pengajuanBarang}/download', [App\Http\Controllers\Admin\AdminPengajuanBarangController::class, 'downloadPDF'])->name('downloadPdf');
        Route::put('/{pengajuanBarang}/status', [App\Http\Controllers\Admin\AdminPengajuanBarangController::class, 'updateStatus'])->name('updateStatus');
        Route::post('/{pengajuanBarang}/update-monitoring', [App\Http\Controllers\Admin\AdminPengajuanBarangController::class, 'updateMonitoring'])->name('updateMonitoring');
        Route::post('/{pengajuanBarang}/konfirmasi-proses', [App\Http\Controllers\Admin\AdminPengajuanBarangController::class, 'konfirmasiProses'])->name('konfirmasiProses');
        Route::post('/{pengajuanBarang}/migrasi-termin-lama', [App\Http\Controllers\Admin\AdminPengajuanBarangController::class, 'migrasiTerminLama'])->name('migrasiTerminLama');
    });
    
    Route::resource('holidays', AdminHolidayController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('barangs', AdminBarangController::class);

    // [SECURITY] Route sinkronisasi Fonnte — hanya admin yang bisa akses
    Route::get('/fonnte-sync-group', function () {
        $token = env('FONNTE_TOKEN');

        if (!$token) {
            return response()->json(['error' => 'FONNTE_TOKEN tidak dikonfigurasi di .env'], 500);
        }

        $sync = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => $token,
        ])->post('https://api.fonnte.com/fetch-group');

        $list = \Illuminate\Support\Facades\Http::withHeaders([
            'Authorization' => $token,
        ])->post('https://api.fonnte.com/get-whatsapp-group');

        return response()->json([
            '1_status_sinkronisasi' => $sync->json(),
            '2_daftar_grup_kamu'    => $list->json()
        ]);
    })->name('admin.fonnte.sync');
    
});