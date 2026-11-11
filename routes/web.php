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
use App\Http\Controllers\Admin\SupplierController as AdminSupplierController;
use App\Http\Controllers\Admin\AdminBarangController;
use App\Http\Controllers\Admin\AdminCustomerController;

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\RekapAbsenController;
use App\Http\Controllers\PengajuanDanaController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AktivitasController;
use App\Http\Controllers\Warehouse\WsSpbController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\Sales\SalesDashboardController;
use App\Http\Controllers\Sales\SalesIncentiveController;
use App\Http\Controllers\Sales\SalesDataController;
use App\Http\Controllers\Sales\SalesAnalyticsController;
use App\Http\Controllers\Sales\SalesClosingController;
use App\Http\Controllers\Sales\SalesTargetController;
use App\Http\Controllers\Sales\SalesForecastController;
use App\Http\Controllers\Sales\SalesPricingController;
use App\Http\Controllers\Sales\SalesSphController;
use App\Http\Controllers\Sales\MonitoringStockController;
use App\Http\Controllers\Warehouse\WsProductController;
use App\Http\Controllers\Warehouse\WsAnalyticsController;
use App\Http\Controllers\Warehouse\WsStockCardController;
use App\Http\Controllers\Warehouse\WsStockOpnameController;
use App\Http\Controllers\Warehouse\WsMutationController;
use App\Http\Controllers\Warehouse\WsSjController;
use App\Http\Controllers\Crm\ClientController;
use App\Http\Controllers\Crm\TransactionController;
use App\Http\Controllers\Crm\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PengajuanCenterController;
use App\Http\Controllers\HrdController;
use App\Http\Controllers\AdminApproverController;

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
    Route::controller(KpiController::class)->prefix('kpi')->name('kpi.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/evaluate/{id}', 'evaluate')->name('evaluate');
        Route::post('/evaluate/{id}', 'storeEvaluate')->name('storeEvaluate');
        Route::post('/approve/{id}', 'approve')->name('approve');
        Route::get('/pdf/{id}', 'exportPdf')->name('exportPdf');
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
        Route::get('/pengajuan-dana/{pengajuanDana}/edit', 'edit')->name('pengajuan_dana.edit');
        Route::put('/pengajuan-dana/{pengajuanDana}', 'update')->name('pengajuan_dana.update');
        Route::put('/pengajuan-dana/{pengajuanDana}/adjust', 'adjustByApprover')->name('pengajuan_dana.adjust');
        Route::post('/pengajuan-dana/{pengajuanDana}/approve', 'approve')->name('pengajuan_dana.approve');
        Route::post('/pengajuan-dana/{pengajuanDana}/hold', 'hold')->name('pengajuan_dana.hold');
        Route::post('/pengajuan-dana/{pengajuanDana}/reject', 'reject')->name('pengajuan_dana.reject');
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

        Route::controller(SalesClosingController::class)->group(function () {
            Route::get('/monthly/closings', 'index')->name('monthly.closings.index');
            Route::post('/monthly/closings', 'store')->name('monthly.closings.store');
            Route::put('/monthly/closings', 'update')->name('monthly.closings.update');
            Route::delete('/monthly/closings', 'destroy')->name('monthly.closings.destroy');
        });

        Route::controller(SalesIncentiveController::class)->group(function () {
            Route::get('/incentive', 'incentive')->name('incentive');
            Route::post('/incentive/settings', 'saveIncentiveSettings')->name('incentive.settings.save');
            Route::post('/incentive/settings/delete', 'deleteIncentiveSettings')->name('incentive.settings.delete');
        });

        Route::controller(SalesForecastController::class)->group(function () {
            Route::get('/forecast', 'forecast')->name('forecast');
            Route::get('/forecast/data', 'forecastData')->name('forecast.data');
            Route::get('/forecast/export/excel', 'exportExcel')->name('forecast.export.excel');
            Route::get('/forecast/export/pdf', 'exportPdf')->name('forecast.export.pdf');
            Route::post('/forecast/store-order', 'storeSuggestedOrder')->name('forecast.store-order');
            Route::post('/forecast/settings', 'saveForecastSettings')->name('forecast.settings.save');
        });

        Route::controller(MonitoringStockController::class)->prefix('stock')->name('monitoring.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/update-bulk', 'updateBulk')->name('update_bulk');
            Route::post('/parse-import', 'parseImport')->name('parse_import');
            Route::post('/save-import', 'saveImport')->name('save_import');
            Route::post('/add-barang', 'addBarang')->name('add_barang');
            Route::get('/export', 'exportExcel')->name('export');
            Route::get('/template', 'downloadTemplate')->name('template');
            Route::post('/undo/{log}', 'undo')->name('undo');
            Route::get('/log/{log}/details', 'logDetails')->name('log_details');
        });

        // NOTE: route spesifik /target harus didaftarkan SEBELUM wildcard /{sale}
        // di bawah, supaya DELETE /sales/target tidak ditangkap SalesDataController@destroy.
        Route::controller(SalesTargetController::class)->group(function () {
            Route::post('/target', 'storeTarget')->name('target.store');
            Route::delete('/target', 'destroyByPs')->name('target.destroy');
        });

        Route::controller(SalesDataController::class)->group(function () {
            Route::get('/manage', 'manage')->name('manage');
            Route::get('/table-data', 'tableData')->name('table_data');
            Route::get('/price-history', 'priceHistory')->name('price_history');
            Route::post('/manual', 'storeManual')->name('store_manual');
            Route::post('/import', 'importExcel')->name('import_excel');
            Route::get('/template', 'downloadTemplate')->name('download_template');
            Route::get('/export', 'export')->name('export');
            Route::delete('/bulk-destroy', 'bulkDestroy')->name('bulk_destroy');
            Route::put('/{sale}', 'update')->name('update');
            Route::delete('/{sale}', 'destroy')->name('destroy');
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
            Route::get('/sph/ps-list', 'psList')->name('sph.ps.list');
            Route::get('/sph/{sph}', 'show')->name('sph.show');
            Route::post('/sph', 'store')->name('sph.store');
            Route::put('/sph/{sph}', 'update')->name('sph.update');
            Route::delete('/sph/{sph}', 'destroy')->name('sph.destroy');
            Route::get('/sph/{sph}/export/pdf', 'exportPdf')->name('sph.export.pdf');
            Route::get('/sph/{sph}/export/excel', 'exportExcel')->name('sph.export.excel');
        });
    });

    Route::prefix('warehouse')->name('warehouse.')->group(function () {
        Route::get('/analytics', [WsAnalyticsController::class, 'index'])->name('analytics');

        Route::controller(WsStockCardController::class)->group(function () {
            Route::get('/stock/card', 'cardIndex')->name('stockcard.index');
            Route::get('/stock/card/{product}/export-pdf', 'cardExportPdf')->name('stockcard.export.pdf');
            Route::get('/stock/card/{product}/print', 'cardPrint')->name('stockcard.print');
            Route::post('/stock/card/{product}/moves', 'cardMoveStore')->name('stockcard.moves.store');
            Route::put('/stock/card/moves/{move}', 'cardMoveUpdate')->name('stockcard.moves.update');
            Route::delete('/stock/card/moves/{move}', 'cardMoveDestroy')->name('stockcard.moves.destroy');
            Route::get('/stock/card/{product}', 'cardShow')->name('stockcard.show');
        });

        Route::controller(WsMutationController::class)->group(function () {
            Route::get('/stock/pencatatan', 'pencatatanRedirect')->name('mutation.index');
            Route::get('/stock/pencatatan/batches', 'productBatches')->name('mutation.batches');
            Route::post('/stock/pencatatan', 'pencatatanStore')->name('mutation.store');
            Route::put('/stock/pencatatan/doc/{doc}', 'pencatatanDocUpdate')->name('mutation.doc.update');
            Route::delete('/stock/pencatatan/bulk', 'pencatatanBulkDestroy')->name('mutation.bulk_destroy');
            Route::put('/stock/pencatatan/{move}', 'pencatatanUpdate')->name('mutation.update');
            Route::delete('/stock/pencatatan/{move}', 'pencatatanDestroy')->name('mutation.destroy');
            Route::get('/stock/warehouse', 'warehouse')->name('index');
            Route::post('/stock/warehouse/set-stock', 'setOpeningStock')->name('product.set-stock');
            Route::get('/stock/warehouse/dokumen/{doc}', 'documentDetail')->name('document');
            Route::get('/stock/warehouse/export-mutasi', 'exportMutasiPdf')->name('export-mutasi');
            Route::get('/stock/warehouse/export-sj', 'exportSjPdf')->name('export-sj');
        });

        Route::controller(WsSjController::class)->group(function () {
            Route::get('/stock/surat-jalan', 'index')->name('sj.index');
            Route::get('/stock/surat-jalan/export/pdf', 'exportPdf')->name('sj.export.pdf');
            Route::get('/stock/surat-jalan/{doc}/edit', 'edit')->name('mutation.doc.edit');
            Route::get('/stock/surat-jalan/{doc}/cetak', 'print')->name('sj.print');
        });

        // Data Product: CRUD master produk.
        Route::controller(WsProductController::class)->group(function () {
            Route::get('/stock/barang', 'barangIndex')->name('product.index');
            Route::get('/stock/barang/export/pdf', 'exportPdf')->name('product.export.pdf');
            Route::get('/stock/barang/export/excel', 'exportExcel')->name('product.export.excel');
            Route::post('/stock/barang', 'storeBarang')->name('product.store');
            Route::delete('/stock/barang/bulk', 'bulkDestroyBarang')->name('product.bulk_destroy');
            Route::put('/stock/barang/{product}', 'updateBarang')->name('product.update');
            Route::delete('/stock/barang/{product}', 'destroyBarang')->name('product.destroy');
        });

        Route::controller(WsStockOpnameController::class)->group(function () {
            Route::get('/opname', 'opnameIndex')->name('stockopname.index');
            Route::get('/opname/catat', 'catatIndex')->name('stockopname.catat');
            Route::get('/opname/draf', 'draftList')->name('stockopname.drafts');
            Route::get('/opname/cetak-bulan', 'opnamePrintBulan')->name('stockopname.print-bulan');
            Route::get('/opname/rekap/{bulan}/{format}', 'opnameExportRekap')
                ->name('stockopname.export-rekap')
                ->where('bulan', '\d{4}-(0[1-9]|1[0-2])')
                ->whereIn('format', ['excel', 'pdf']);
            Route::post('/opname/selesai', 'opnameFinish')->name('stockopname.finish');
            Route::get('/opname/{opname}/cetak', 'opnamePrint')->name('stockopname.print')->whereNumber('opname');
            Route::post('/inventory/opname', 'opnameStore')->name('stockopname.store');
            Route::post('/inventory/scrap', 'scrapStore')->name('stockopname.scrap');
        });

        // Alias lawas: halaman monitoring stok pernah diakses via warehouse.*.
        // Diarahkan ke controller yang sama agar route('warehouse.monitoring.*')
        // tidak RouteNotFoundException.
        Route::controller(MonitoringStockController::class)->prefix('monitoring')->name('monitoring.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/update-bulk', 'updateBulk')->name('update_bulk');
            Route::post('/parse-import', 'parseImport')->name('parse_import');
            Route::post('/save-import', 'saveImport')->name('save_import');
            Route::post('/add-barang', 'addBarang')->name('add_barang');
            Route::get('/export', 'exportExcel')->name('export');
            Route::get('/template', 'downloadTemplate')->name('template');
            Route::post('/undo/{log}', 'undo')->name('undo');
            Route::get('/log/{log}/details', 'logDetails')->name('log_details');
        });
    });

    Route::get('/test-gap', fn() => view('test-gap'))->name('test.gap');

    // Aktivitas
    Route::resource('aktivitas', AktivitasController::class)->only(['index', 'store'])->middleware('auth');

    Route::controller(AktivitasController::class)->group(function () {
        Route::get('/aktivitas/json', 'getAktivitasJson')->name('aktivitas.getJson')->middleware('auth');
    });

    // Pengajuan Barang (User)
    Route::controller(WsSpbController::class)->prefix('pengajuan-barang')->name('pengajuan_barang.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/history', 'history')->name('history');
        Route::get('/cek-riwayat', 'checkRiwayat')->name('checkRiwayat');
        Route::get('/cek-forecast', 'checkForecast')->name('checkForecast');
        Route::get('/status-batch', 'statusBatch')->name('statusBatch');
        Route::post('/status-batch-names', 'statusBatchNames')->name('statusBatchNames');
        Route::get('/monitoring-all', 'monitoringAll')->name('monitoring_all');
        Route::get('/export-all-excel', 'exportAllExcel')->name('export_all_excel');
        Route::get('/{pengajuanBarang}', 'show')->name('show')->whereNumber('pengajuanBarang');
        Route::get('/{pengajuanBarang}/download', 'download')->name('download')->whereNumber('pengajuanBarang');
        Route::get('/{pengajuanBarang}/export-excel', 'exportSingleExcel')->name('export_single_excel')->whereNumber('pengajuanBarang');
        Route::patch('/{pengajuanBarang}/status', 'updateStatus')->name('updateStatus')->whereNumber('pengajuanBarang');
        Route::post('/{pengajuanBarang}/cancel', 'cancel')->name('cancel')->whereNumber('pengajuanBarang');
        Route::post('/{pengajuanBarang}/update-monitoring', 'updateMonitoring')->name('updateMonitoring')->whereNumber('pengajuanBarang');
        Route::post('/{pengajuanBarang}/konfirmasi-proses', 'konfirmasiProses')->name('konfirmasiProses')->whereNumber('pengajuanBarang');
        Route::post('/{pengajuanBarang}/migrasi-termin-lama', 'migrasiTerminLama')->name('migrasiTerminLama')->whereNumber('pengajuanBarang');
    });

    Route::controller(AdminPengajuanBarangController::class)->prefix('pengajuan-barang')->name('pengajuan_barang.')->group(function () {
        Route::get('/export-excel', 'exportRekapExcel')->name('export_excel');
    });

    // Kelola Supplier & Customer & Pengajuan (Sisi User)
    Route::resource('suppliers', SupplierController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('customers', CustomerController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/pengajuan-center', [PengajuanCenterController::class, 'index'])->name('pengajuan.center');

    // Admin Center - Pengaturan Approvers (Sisi User)
    Route::get('/admin-center/approvers', [AdminApproverController::class, 'index'])->name('admin.approvers.index');
    Route::post('/admin-center/approvers', [AdminApproverController::class, 'update'])->name('admin.approvers.update');

    // Admin Center - Kelola Agenda & Hari Libur (Sisi User / GA)
    Route::controller(AdminAgendaController::class)->prefix('admin-center/agenda')->name('admin.agenda.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/get-all-users', 'getAllUsers')->name('getAllUsers');
        Route::get('/events', 'getAdminAgendas')->name('getEvents');
        Route::put('/{agenda}', 'update')->name('update');
        Route::delete('/{agenda}', 'destroy')->name('destroy');
        Route::post('/{agenda}/resend', 'resend')->name('resend');
    });

    Route::resource('admin-center/holidays', AdminHolidayController::class)->names([
        'index' => 'admin.holidays.index',
        'store' => 'admin.holidays.store',
        'update' => 'admin.holidays.update',
        'destroy' => 'admin.holidays.destroy',
    ]);

    // HRD & People Operations (Sisi User)
    Route::prefix('hrd')->name('hrd.')->group(function () {
        Route::get('/karyawan', [HrdController::class, 'karyawan'])->name('karyawan.index');
        Route::post('/karyawan', [HrdController::class, 'storeKaryawan'])->name('karyawan.store');
        Route::get('/karyawan/{user}/edit', [HrdController::class, 'editKaryawan'])->name('karyawan.edit');
        Route::put('/karyawan/{user}', [HrdController::class, 'updateKaryawan'])->name('karyawan.update');
        Route::delete('/karyawan/{user}', [HrdController::class, 'destroyKaryawan'])->name('karyawan.destroy');
        Route::post('/karyawan/{user}/set-as-head', [HrdController::class, 'setAsHeadKaryawan'])->name('karyawan.setAsHead');
        Route::get('/karyawan/{user}/download-pdf', [HrdController::class, 'downloadProfilePdf'])->name('karyawan.downloadPdf');
        Route::get('/absensi', [HrdController::class, 'absensi'])->name('absensi.index');
        Route::get('/absensi/rekap', [HrdController::class, 'absensiRekap'])->name('absensi.rekap');
        Route::get('/absensi/pdf/harian', [HrdController::class, 'downloadPdfHarian'])->name('absensi.downloadPdfHarian');
        Route::get('/absensi/excel/harian', [HrdController::class, 'downloadExcelHarian'])->name('absensi.downloadExcelHarian');
        Route::get('/absensi/rekap/pdf', [HrdController::class, 'downloadRekapPdf'])->name('absensi.rekap.downloadPdf');
        Route::get('/absensi/rekap/excel', [HrdController::class, 'downloadRekapExcel'])->name('absensi.rekap.downloadExcel');
        Route::get('/cuti', [HrdController::class, 'cuti'])->name('cuti.index');
        Route::get('/lembur', [HrdController::class, 'lembur'])->name('lembur.index');
        Route::get('/lembur/rekap', [HrdController::class, 'lemburRekap'])->name('lembur.rekap');
        Route::get('/lembur/rekap/pdf', [HrdController::class, 'downloadRekapLemburPdf'])->name('lembur.rekap.downloadPdf');
        Route::get('/lembur/rekap/excel', [HrdController::class, 'downloadRekapLemburExcel'])->name('lembur.rekap.downloadExcel');
        Route::get('/aktivitas', [HrdController::class, 'aktivitas'])->name('aktivitas.index');
        Route::prefix('kpi')->name('kpi.')->group(function () {
            Route::get('/', [HrdController::class, 'kpi'])->name('index');
            Route::get('/indicators', [HrdController::class, 'kpiIndicators'])->name('indicators.index');
            Route::post('/indicators/seed', [HrdController::class, 'kpiSeedDefaultIndicators'])->name('indicators.seed');
            Route::post('/indicators', [HrdController::class, 'kpiStoreIndicator'])->name('indicators.store');
            Route::put('/indicators/{indicator}', [HrdController::class, 'kpiUpdateIndicator'])->name('indicators.update');
            Route::delete('/indicators/{indicator}', [HrdController::class, 'kpiDestroyIndicator'])->name('indicators.destroy');
            Route::get('/evaluators', [HrdController::class, 'kpiEvaluators'])->name('evaluators.index');
            Route::post('/evaluators', [HrdController::class, 'kpiSaveEvaluators'])->name('evaluators.save');
        });
        Route::get('/agenda', [HrdController::class, 'agenda'])->name('agenda.index');
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
        Route::get('/rekap', 'rekap')->name('rekap');
        Route::get('/rekap/pdf', 'downloadRekapPdf')->name('rekap.downloadPdf');
        Route::get('/rekap/excel', 'downloadRekapExcel')->name('rekap.downloadExcel');
    });

    Route::controller(AdminCutiController::class)->prefix('cuti')->name('cuti.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/bersama', 'rekapBersama')->name('bersama');
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
        Route::put('/{cuti}/status', 'updateStatus')->name('updateStatus');
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
        Route::post('/{agenda}/resend', 'resend')->name('resend');
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
    Route::resource('suppliers', AdminSupplierController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('customers', AdminCustomerController::class)->only(['index', 'store', 'update', 'destroy']);
    
    Route::redirect('/products', '/admin/barangs', 301);
    Route::redirect('/products/{any}', '/admin/barangs', 301)->where('any', '.*');
    
    Route::controller(AdminBarangController::class)->prefix('barangs')->name('barangs.')->group(function () {
        Route::get('/packagings', 'packagings')->name('packagings.index');
        Route::post('/packagings', 'storePackaging')->name('packagings.store');
        Route::put('/packagings/{packaging}', 'updatePackaging')->name('packagings.update');
        Route::delete('/packagings/{packaging}', 'destroyPackaging')->name('packagings.destroy');
    });
    
    Route::resource('barangs', AdminBarangController::class);
});
