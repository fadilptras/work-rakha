<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\Auth\PasswordResetController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminAbsensiController;
use App\Http\Controllers\Admin\AdminAgendaController;
use App\Http\Controllers\Admin\AdminCutiController;
use App\Http\Controllers\Admin\AdminPengajuanDanaController;
use App\Http\Controllers\Admin\AdminLemburController;
use App\Http\Controllers\Admin\AdminAktivitasController;
use App\Http\Controllers\Admin\AdminCrmController;
use App\Http\Controllers\Admin\AdminPengajuanBarangController;
use App\Http\Controllers\Admin\AdminHolidayController;
use App\Http\Controllers\Admin\AdminKpiController;
use App\Http\Controllers\Admin\AdminKpiIndicatorController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\AdminBarangController;

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\RekapAbsenController;
use App\Http\Controllers\PengajuanDanaController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AktivitasController;
use App\Http\Controllers\PengajuanBarangController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\Sales\SalesDashboardController;
use App\Http\Controllers\Sales\SalesIncentiveController;
use App\Http\Controllers\Sales\SalesDataController;
use App\Http\Controllers\Sales\SalesAnalyticsController;
use App\Http\Controllers\Sales\SalesTargetController;
use App\Http\Controllers\Sales\SalesForecastController;
use App\Http\Controllers\Sales\SalesPricingController;
use App\Http\Controllers\Sales\SalesSphController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\Crm\ClientController;
use App\Http\Controllers\Crm\TransactionController;
use App\Http\Controllers\Crm\ReportController;

Route::get('/', fn() => redirect()->route('login'));

// Auth
Route::controller(AuthenticatedSessionController::class)->middleware('guest')->group(function () {
    Route::get('/login', 'create')->name('login');
    Route::post('/login', 'store')->name('login.post');
});

Route::controller(AuthenticatedSessionController::class)->group(function () {
    Route::post('/logout', 'destroy')->name('logout')->middleware('auth');
});

Route::get('/forgot-password', function () {
    $agent = new \Jenssegers\Agent\Agent();
    $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';
    return view("auth.forgot-password_{$viewSuffix}");
})->middleware('guest')->name('password.request');

Route::controller(ConfirmablePasswordController::class)->middleware('auth')->group(function () {
    Route::get('/confirm-password', 'show')->name('password.confirm');
    Route::post('/confirm-password', 'store');
});

Route::middleware(['auth', 'redirect.if.admin'])->group(function () {
    Route::get('/dashboard', function () {
        $agent = new \Jenssegers\Agent\Agent();
        $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';
        return view("users.dashboard.dashboard_{$viewSuffix}", ['title' => 'Dashboard']);
    })->name('dashboard');

    // Absensi & Lembur
    Route::controller(AbsenController::class)->group(function () {
        Route::get('/absen', 'absen')->name('absen');
        Route::post('/absen', 'store')->name('absen.store');
        Route::patch('/absen/keluar/{absensi}', 'updateKeluar')->name('absen.keluar');
        Route::post('/absen/lembur', 'storeLembur')->name('absen.lembur.store');
        Route::patch('/absen/lembur/keluar/{lembur}', 'updateLemburKeluar')->name('absen.lembur.keluar');
    });

    // KPI
    Route::controller(KpiController::class)->group(function () {
        Route::get('/kpi', 'index')->name('kpi.index');
    });

    // Cuti
    Route::controller(CutiController::class)->prefix('cuti')->name('cuti.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/detail/{cuti}', 'show')->name('show');
        Route::put('/{cuti}/status', 'updateStatus')->name('updateStatus');
        Route::post('/{cuti}/cancel', 'cancel')->name('cancel');
        Route::get('/{cuti}/download', 'downloadPdf')->name('download');
    });

    Route::controller(AdminCutiController::class)->group(function () {
        Route::post('/cuti/{cuti}/approve', 'approve')->name('cuti.approve');
        Route::post('/cuti/{cuti}/reject', 'reject')->name('cuti.reject');
    });

    // Notifikasi
    Route::controller(NotifikasiController::class)->group(function () {
        Route::get('/notifikasi', 'index')->name('notifikasi.index');
        Route::post('/update-fcm-token', 'updateFcmToken')->name('fcm.update');
        Route::get('/kirim-ulang-tahun', 'kirimUlangTahun')->name('notifikasi.ulangtahun');
    });

    // Profile
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'editProfile')->name('profil.index');
        Route::put('/profile', 'update')->name('profil.update');
        Route::post('/profile/check-password', 'checkCurrentPassword')->name('profile.checkPassword');
        Route::get('/profile/download-pdf', 'downloadPdf')->name('profile.downloadPdf');
    });

    // Pengajuan Dana
    Route::controller(PengajuanDanaController::class)->group(function () {
        Route::get('/pengajuan-dana/history', 'history')->name('pengajuan_dana.history');
        Route::get('/pengajuan-dana', 'index')->name('pengajuan_dana.index');
        Route::post('/pengajuan-dana', 'store')->name('pengajuan_dana.store');
        Route::get('/pengajuan-dana/monitoring', 'monitoringAll')->name('pengajuan_dana.monitoring_all');
        Route::get('/pengajuan-dana/{pengajuanDana}', 'show')->name('pengajuan_dana.show');
        Route::post('/pengajuan-dana/{pengajuanDana}/approve', 'approve')->name('pengajuan_dana.approve');
        Route::post('/pengajuan-dana/{pengajuanDana}/reject', 'reject')->name('pengajuan_dana.reject');
        Route::post('/pengajuan-dana/{pengajuanDana}/proses-pembayaran', 'prosesPembayaran')->name('pengajuan_dana.proses_pembayaran');
        Route::get('/pengajuan-dana/{pengajuanDana}/download', 'downloadPDF')->name('pengajuan_dana.download');
        Route::post('/pengajuan-dana/{pengajuanDana}/cancel', 'cancel')->name('pengajuan_dana.cancel');
    });

    // Rekap Absensi
    Route::controller(RekapAbsenController::class)->group(function () {
        Route::get('/rekap-absen', 'index')->name('rekap_absen.index');
    });

    // Agenda
    Route::controller(AgendaController::class)->group(function () {
        Route::get('/agendas', 'index')->name('agendas.index');
        Route::post('/agendas', 'store')->name('agendas.store');
        Route::get('/get-users', 'getUsers')->name('agendas.getUsers');
        Route::put('/agendas/{agenda}', 'update')->name('agendas.update');
        Route::delete('/agendas/{agenda}', 'destroy')->name('agendas.destroy');
    });

    // CRM
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::controller(ReportController::class)->group(function () {
            Route::get('/matrix', 'matrix')->name('matrix');
            Route::get('/matrix/export', 'exportMatrix')->name('matrix.export');
            Route::get('/{client}/export', 'exportClientRecap')->name('client.export');
        });

        Route::controller(ClientController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::get('/{client}', 'show')->name('show');
            Route::get('/client/{client}/edit', 'edit')->name('client.edit');
            Route::put('/client/{client}', 'update')->name('client.update');
            Route::delete('/client/{client}', 'destroyClient')->name('client.destroy');
        });

        Route::controller(TransactionController::class)->group(function () {
            Route::post('/interaction', 'storeInteraction')->name('interaction.store');
            Route::post('/interaction/support', 'storeSupport')->name('interaction.support');
            Route::post('/interaction/entertain', 'storeEntertain')->name('interaction.entertain');
            Route::put('/interaction/{interaction}/update', 'updateInteraction')->name('interaction.update');
            Route::delete('/interaction/{interaction}', 'destroyInteraction')->name('interaction.destroy');
            Route::get('/client/{client}/fetch-sales', 'fetchSalesData')->name('client.fetch_sales');
        });
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::controller(SalesDashboardController::class)->group(function () {
            Route::get('/', 'index')->name('index');
        });

        Route::controller(SalesAnalyticsController::class)->group(function () {
            Route::get('/analytics', 'analytics')->name('analytics');
            Route::get('/monthly', 'monthly')->name('monthly');
            Route::get('/monitoring/data', 'monitoringData')->name('monitoring.data');
            Route::get('/monthly-detail', 'monthlyDetailData')->name('monthly.detail');
            Route::get('/visualisasi/data', 'visualisasiData')->name('visualisasi.data');
        });

        Route::controller(SalesIncentiveController::class)->group(function () {
            Route::get('/incentive', 'incentive')->name('incentive');
            Route::post('/incentive/settings', 'saveIncentiveSettings')->name('incentive.settings.save');
            Route::post('/incentive/settings/delete', 'deleteIncentiveSettings')->name('incentive.settings.delete');
        });

        Route::controller(SalesForecastController::class)->group(function () {
            Route::get('/forecast', 'forecast')->name('forecast');
            Route::get('/forecast/export/excel', 'exportExcel')->name('forecast.export.excel');
            Route::get('/forecast/export/pdf', 'exportPdf')->name('forecast.export.pdf');
            Route::post('/forecast/store-order', 'storeSuggestedOrder')->name('forecast.store-order');
            Route::post('/forecast/settings', 'saveForecastSettings')->name('forecast.settings.save');
        });

        Route::controller(StockController::class)->group(function () {
            Route::get('/stock', 'index')->name('stock');
            Route::get('/stock/history', 'historyIndex')->name('stock.history_index');
            Route::post('/stock/update-bulk', 'updateBulk')->name('stock.update_bulk');
            Route::post('/stock/add-barang', 'addBarang')->name('stock.add_barang');
            Route::post('/stock/parse-import', 'parseImport')->name('stock.parse_import');
            Route::post('/stock/save-import', 'saveImport')->name('stock.save_import');
            Route::get('/stock/export', 'exportExcel')->name('stock.export');
            Route::get('/stock/template', 'downloadTemplate')->name('stock.template');
            Route::post('/stock/undo/{log}', 'undo')->name('stock.undo');
            Route::get('/stock/log/{log}/details', 'logDetails')->name('stock.log_details');
            Route::get('/gudang/dashboard', 'dashboard')->name('gudang.dashboard');

            Route::get('/stock/barang', 'barangIndex')->name('stock.barang.index');
            Route::post('/stock/barang', 'storeBarang')->name('stock.barang.store');
            Route::post('/stock/barang/bulk-update', 'bulkUpdateBarang')->name('stock.barang.bulk_update');
            Route::get('/stock/barang/export', 'exportBarang')->name('stock.barang.export');
            Route::put('/stock/barang/{barang}', 'updateBarang')->name('stock.barang.update');
            Route::delete('/stock/barang/{barang}', 'destroyBarang')->name('stock.barang.destroy');
            Route::get('/stock/barang/packagings', 'barangPackagings')->name('stock.barang.packagings.index');
            Route::post('/stock/barang/packagings', 'storeBarangPackaging')->name('stock.barang.packagings.store');
            Route::put('/stock/barang/packagings/{packaging}', 'updateBarangPackaging')->name('stock.barang.packagings.update');
            Route::delete('/stock/barang/packagings/{packaging}', 'destroyBarangPackaging')->name('stock.barang.packagings.destroy');
        });

        Route::controller(SalesDataController::class)->group(function () {
            Route::get('/manage', 'manage')->name('manage');
            Route::post('/manual', 'storeManual')->name('store_manual');
            Route::post('/import', 'importExcel')->name('import_excel');
            Route::get('/template', 'downloadTemplate')->name('download_template');
            Route::get('/export', 'export')->name('export');
            Route::delete('/bulk-destroy', 'bulkDestroy')->name('bulk_destroy');
            Route::put('/{sale}', 'update')->name('update');
            Route::delete('/{sale}', 'destroy')->name('destroy');
        });

        Route::controller(SalesTargetController::class)->group(function () {
            Route::post('/target', 'storeTarget')->name('target.store');
        });

        Route::controller(SalesPricingController::class)->group(function () {
            Route::get('/pricing', 'pricing')->name('pricing');
            Route::get('/pricing/barang/list', 'listBarang')->name('pricing.barang.list');
            Route::post('/pricing/barang', 'storeBarang')->name('pricing.barang.store');
            Route::put('/pricing/barang/{barang}', 'updateBarang')->name('pricing.barang.update');
            Route::delete('/pricing/barang/{barang}', 'destroyBarang')->name('pricing.barang.destroy');
            Route::get('/pricing/export/pdf', 'exportPdf')->name('pricing.export.pdf');
            Route::get('/pricing/export/excel', 'exportExcel')->name('pricing.export.excel');
        });

        Route::controller(SalesSphController::class)->group(function () {
            Route::get('/sph', 'index')->name('sph.index');
            Route::get('/sph/{sph}', 'show')->name('sph.show');
            Route::post('/sph', 'store')->name('sph.store');
            Route::put('/sph/{sph}', 'update')->name('sph.update');
            Route::delete('/sph/{sph}', 'destroy')->name('sph.destroy');
            Route::get('/sph/{sph}/export/pdf', 'exportPdf')->name('sph.export.pdf');
            Route::get('/sph/{sph}/export/excel', 'exportExcel')->name('sph.export.excel');
        });
    });

    // Aktivitas
    Route::resource('aktivitas', AktivitasController::class)->only(['index', 'store'])->middleware('auth');

    Route::controller(AktivitasController::class)->group(function () {
        Route::get('/aktivitas/json', 'getAktivitasJson')->name('aktivitas.getJson')->middleware('auth');
    });

    // Pengajuan Barang (User)
    Route::controller(PengajuanBarangController::class)->prefix('pengajuan-barang')->name('pengajuan_barang.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/history', 'history')->name('history');
        Route::get('/monitoring-all', 'monitoringAll')->name('monitoring_all');
        Route::get('/{pengajuanBarang}', 'show')->name('show');
        Route::get('/{pengajuanBarang}/download', 'download')->name('download');
        Route::patch('/{pengajuanBarang}/status', 'updateStatus')->name('updateStatus');
        Route::post('/{pengajuanBarang}/cancel', 'cancel')->name('cancel');
        Route::post('/{pengajuanBarang}/update-monitoring', 'updateMonitoring')->name('updateMonitoring');
        Route::post('/{pengajuanBarang}/konfirmasi-proses', 'konfirmasiProses')->name('konfirmasiProses');
        Route::post('/{pengajuanBarang}/migrasi-termin-lama', 'migrasiTerminLama')->name('migrasiTerminLama');
    });

    Route::controller(AdminPengajuanBarangController::class)->prefix('pengajuan-barang')->name('pengajuan_barang.')->group(function () {
        Route::get('/export-excel', 'exportRekapExcel')->name('export_excel');
    });
});

Route::middleware(['auth', 'admin', 'admin.idle'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn() => redirect()->route('admin.employees.index'));

    Route::controller(PasswordResetController::class)->group(function () {
        Route::patch('/users/{user}/reset-password', 'resetToDefault')->name('users.resetPassword');
    });

    Route::prefix('employees')->name('employees.')->group(function () {
        Route::controller(AdminUserController::class)->group(function () {
            Route::get('/', 'indexByRole')->defaults('role', 'user')->name('index');
            Route::get('/{user}/edit', 'edit')->name('edit');
            Route::put('/{user}', 'update')->name('update');
            Route::delete('/{user}', 'destroy')->name('destroy');
            Route::post('/{user}/set-as-head', 'setAsDivisionHead')->name('setAsHead');
            Route::get('/{user}/download-pdf', 'downloadProfilePdf')->name('downloadProfilePdf');
            Route::get('/{user}/ajax-detail', 'ajaxDetail')->name('ajaxDetail');
        });

        Route::controller(RegisteredUserController::class)->group(function () {
            Route::post('/', 'store')->name('store');
        });
    });

    Route::prefix('admins')->name('admins.')->group(function () {
        Route::controller(AdminUserController::class)->group(function () {
            Route::get('/', 'indexByRole')->defaults('role', 'admin')->name('index');
        });

        Route::controller(RegisteredUserController::class)->group(function () {
            Route::post('/', 'store')->name('store')->middleware('password.confirm');
        });

        Route::controller(AdminUserController::class)->group(function () {
            Route::post('/update', 'updateAdmin')->name('update')->middleware('password.confirm');
            Route::delete('/{user}', 'destroy')->name('destroy')->middleware('password.confirm');
        });
    });

    Route::controller(AdminAbsensiController::class)->prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/pdf/harian', 'downloadPdfHarian')->name('downloadPdfHarian');
        Route::get('/excel/harian', 'downloadExcelHarian')->name('downloadExcelHarian');
        Route::get('/rekap', 'rekap')->name('rekap');
        Route::get('/rekap/pdf', 'downloadPdf')->name('rekap.downloadPdf');
        Route::get('/rekap/excel', 'downloadExcel')->name('rekap.downloadExcel');
    });

    Route::controller(AdminLemburController::class)->prefix('lembur')->name('lembur.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/pdf', 'downloadPdf')->name('downloadPdf');
    });

    Route::controller(AdminCutiController::class)->prefix('cuti')->name('cuti.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/rekap-pdf', 'downloadRekapPDF')->name('downloadRekapPdf');
        Route::get('/set-approvers', 'setApprovers')->name('set_approvers');
        Route::post('/set-approvers', 'saveApprovers')->name('set_approvers.save');
        Route::get('/pengaturan-pdf', 'downloadPengaturanPDF')->name('downloadPengaturanPDF');
        Route::get('/pengaturan', 'pengaturanCuti')->name('pengaturan');
        Route::post('/pengaturan', 'updatePengaturanCuti')->name('updatePengaturan');
        Route::get('/{cuti}', 'show')->name('show');
        Route::delete('/{cuti}', 'destroy')->name('destroy');
        Route::get('/{cuti}/download', 'download')->name('download');
        Route::post('/{cuti}/force-approve', 'forceApprove')->name('forceApprove');
    });

    Route::controller(AdminPengajuanDanaController::class)->prefix('pengajuan-dana')->name('pengajuan_dana.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/rekap-pdf', 'downloadRekapPDF')->name('downloadRekapPdf');
        Route::get('/{pengajuanDana}', 'show')->name('show');
        Route::delete('/{pengajuanDana}', 'destroy')->name('destroy');
        Route::get('/{pengajuanDana}/download', 'downloadPDF')->name('downloadPdf');
        Route::get('/pengaturan/approvers', 'showSetApprovers')->name('set_approvers.index');
        Route::post('/pengaturan/approvers', 'saveSetApprovers')->name('set_approvers.save');
        Route::post('/{pengajuanDana}/mark-as-paid', 'markAsPaid')->name('markAsPaid');
    });

    Route::controller(AdminAgendaController::class)->prefix('agenda')->name('agenda.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/get-all-users', 'getAllUsers')->name('getAllUsers');
        Route::get('/events', 'getAdminAgendas')->name('getEvents');
        Route::put('/{agenda}', 'update')->name('update');
        Route::delete('/{agenda}', 'destroy')->name('destroy');
    });

    Route::controller(AdminAktivitasController::class)->prefix('aktivitas')->name('aktivitas.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/download-pdf', 'downloadPdf')->name('downloadPdf');
        Route::get('/download-excel', 'downloadExcel')->name('downloadExcel');
    });

    Route::prefix('kpi')->name('kpi.')->group(function () {
        Route::controller(AdminKpiController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{id}/evaluate', 'evaluate')->name('evaluate');
            Route::post('/{id}/evaluate', 'storeEvaluate')->name('storeEvaluate');
            Route::post('/{id}/approve', 'approve')->name('approve');
        });

        Route::controller(AdminKpiIndicatorController::class)->prefix('indicators')->name('indicators.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('/{indicator}', 'update')->name('update');
            Route::delete('/{indicator}', 'destroy')->name('destroy');
        });
    });

    Route::controller(AdminCrmController::class)->prefix('crm')->name('crm.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/matrix/export', 'exportMatrix')->name('matrix.export');
        Route::get('/{client}', 'show')->name('show');
        Route::get('/client/{client}/edit', 'edit')->name('client.edit');
        Route::put('/client/{client}', 'update')->name('client.update');
        Route::delete('/client/{client}', 'destroyClient')->name('client.destroy');
        Route::post('/interaction', 'storeInteraction')->name('interaction.store');
        Route::post('/interaction/support', 'storeSupport')->name('interaction.support');
        Route::post('/interaction/entertain', 'storeEntertain')->name('interaction.entertain');
        Route::delete('/interaction/{interaction}', 'destroyInteraction')->name('interaction.destroy');
        Route::put('/interaction/{interaction}/update', 'updateInteraction')->name('interaction.update');
        Route::get('/{client}/export', 'exportClientRecap')->name('client.export');
    });

    Route::controller(AdminPengajuanBarangController::class)->prefix('pengajuan-barang')->name('pengajuan_barang.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/rekap-pdf', 'downloadRekapPDF')->name('downloadRekapPdf');
        Route::get('/export-excel', 'exportRekapExcel')->name('export_excel');
        Route::get('/set-approvers', 'setApprovers')->name('set_approvers');
        Route::post('/set-approvers', 'saveApprovers')->name('set_approvers.save');
        Route::get('/{pengajuanBarang}', 'show')->name('show');
        Route::delete('/{pengajuanBarang}', 'destroy')->name('destroy');
        Route::get('/{pengajuanBarang}/download', 'downloadPDF')->name('downloadPdf');
        Route::put('/{pengajuanBarang}/status', 'updateStatus')->name('updateStatus');
        Route::post('/{pengajuanBarang}/update-monitoring', 'updateMonitoring')->name('updateMonitoring');
        Route::post('/{pengajuanBarang}/konfirmasi-proses', 'konfirmasiProses')->name('konfirmasiProses');
        Route::post('/{pengajuanBarang}/migrasi-termin-lama', 'migrasiTerminLama')->name('migrasiTerminLama');
    });

    Route::resource('holidays', AdminHolidayController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('barangs', AdminBarangController::class);
});
