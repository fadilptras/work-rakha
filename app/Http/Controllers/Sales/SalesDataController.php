<?php

namespace App\Http\Controllers\Sales;

use App\Models\Sales;
use App\Models\User;
use App\Imports\SalesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesDataController extends BaseSalesController
{
    public function manage(Request $request)
    {
        if (!$this->hasFullSalesAccess()) abort(403, 'Anda tidak memiliki hak akses ke halaman Kelola Data Sales.');
        $query = Sales::query();

        // pencarian (search)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhere('ps', 'like', "%{$search}%");
            });
        }

        // filter tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('date', $request->input('tanggal'));
        }

        // filter bulan
        if ($request->filled('bulan')) {
            $query->where('month', $request->input('bulan'));
        }

        // filter tahun
        if ($request->filled('tahun')) {
            $query->whereYear('date', $request->input('tahun'));
        }

        // filter customer
        if ($request->filled('nama_customer')) {
            $query->where('customer_name', 'like', '%' . $request->input('nama_customer') . '%');
        }

        // filter produk
        if ($request->filled('nama_produk')) {
            $query->where('product_name', 'like', '%' . $request->input('nama_produk') . '%');
        }

        // filter ps
        if ($request->filled('ps')) {
            $query->where('ps', $request->input('ps'));
        }

        $sort = $request->input('sort', 'terbaru');
        if ($sort === 'terlama') {
            $query->orderBy('date', 'asc')->orderBy('created_at', 'asc');
        } elseif ($sort === 'tertinggi') {
            $query->orderBy('net_price', 'desc')->orderBy('created_at', 'desc');
        } elseif ($sort === 'terendah') {
            $query->orderBy('net_price', 'asc')->orderBy('created_at', 'asc');
        } else {
            // Default: terbaru — sort by tanggal desc, lalu created_at desc (waktu pembuatan)
            $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
        }

        $sales = $query->paginate(30)->withQueryString();

        // data untuk filter
        $bulanAda = Sales::whereNotNull('month')->distinct()->pluck('month')->toArray();
        $bulanAda = array_map(fn($b) => ucfirst(strtolower($b)), $bulanAda);
        $listBulan = array_values(array_intersect($this->urutanBulan, $bulanAda));

        $tahunAda = Sales::whereNotNull('date')
            ->selectRaw('DISTINCT YEAR(date) as tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $listCustomer = Sales::whereNotNull('customer_name')->where('customer_name', '!=', '')->distinct()->orderBy('customer_name', 'asc')->pluck('customer_name');
        $listPs = Sales::whereNotNull('ps')
            ->where('ps', '!=', '')
            ->distinct()
            ->orderBy('ps', 'asc')
            ->pluck('ps');
        $listProduk = Sales::whereNotNull('product_name')
            ->where('product_name', '!=', '')
            ->distinct()
            ->orderBy('product_name', 'asc')
            ->pluck('product_name');
        $listCustomer = Sales::whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->distinct()
            ->orderBy('customer_name', 'asc')
            ->pluck('customer_name');

        $listSatuan = Sales::whereNotNull('unit')
            ->where('unit', '!=', '')
            ->distinct()
            ->orderBy('unit', 'asc')
            ->pluck('unit');

        // kandidat PS: karyawan di divisi Marketing dan Operasional (untuk rekomendasi & validasi PS baru)
        $listUserPs = User::where('divisi', 'Marketing dan Operasional')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->orderBy('name', 'asc')
            ->pluck('name')
            ->map(fn($n) => trim($n))
            ->filter(fn($n) => $n !== '')
            ->values();

        return view('users.sales.manage', [
            'title'     => 'Kelola Data Sales',
            'sales'     => $sales,
            'listBulan' => $listBulan,
            'listTahun' => $tahunAda,
            'listCustomer' => $listCustomer,
            'listProduk' => $listProduk,
            'listPs' => $listPs,
            'listSatuan' => $listSatuan,
            'listUserPs' => $listUserPs,
        ]);
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'tanggal'         => 'required|date',
            'nama_customer'   => 'nullable|string|max:255',
            'ps'              => 'nullable|string|max:255',
            'nama_produk'     => 'required|array|min:1',
            'nama_produk.*'   => 'nullable|string|max:255',
            'qty'             => 'required|array|min:1',
            'qty.*'           => 'nullable|integer|min:1',
            'satuan'          => 'required|array',
            'satuan.*'        => 'nullable|string|max:50',
            'hna'             => 'required|array',
            'hna.*'           => 'nullable|numeric|min:0',
            'diskon'          => 'required|array',
            'diskon.*'        => 'nullable|numeric|min:0',
            'harga_nett'      => 'required|array',
            'harga_nett.*'    => 'nullable|numeric|min:0',
        ]);

        $tanggal = $request->tanggal;
        $customer = $request->nama_customer;
        $ps = $request->ps;
        $bulan = null;

        if ($tanggal) {
            $bulan = ['01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'][date('m', strtotime($tanggal))];
        }

        // Validasi PS baru: jika nama PS belum pernah tercatat di data sales,
        // cocokkan dengan akun karyawan di divisi Marketing dan Operasional.
        if ($ps && trim($ps) !== '' && Sales::where('ps', trim($ps))->exists() === false) {
            $psCocok = $this->psTerdaftar(trim($ps));
            if (!$psCocok) {
                return redirect()->back()
                    ->with('error', 'Akun karyawan tidak terdaftar, cek lagi!')
                    ->with('active_tab', 'input')
                    ->withInput();
            }
        }

        foreach ($request->nama_produk as $index => $produk) {
            if (empty($produk)) continue; // skip baris yang nama produknya kosong

            Sales::create([
                'date' => $tanggal,
                'customer_name' => $customer,
                'ps' => $ps,
                'month' => $bulan,
                'product_name' => $produk,
                'qty' => $request->qty[$index] ?? null,
                'unit' => $request->satuan[$index] ?? null,
                'base_price' => $request->hna[$index] ?? null,
                'discount' => $request->diskon[$index] ?? null,
                'net_price' => $request->harga_nett[$index] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Data sales manual berhasil disimpan!')->with('active_tab', 'input');
    }

    /**
     * Cek apakah nama PS cocok dengan akun karyawan di divisi Marketing dan Operasional.
     * Pencocokan toleran terhadap nama pendek vs nama lengkap (mis. "Eko" vs "Eko Sigit Nugroho").
     */
    protected function psTerdaftar(string $ps): bool
    {
        $psNormalized = $this->normalizeName($ps);
        if ($psNormalized === '') return false;

        // Pengecualian: nama PS yang valid meski bukan akun karyawan.
        $psExceptions = ['office', 'rita'];
        if (in_array($psNormalized, $psExceptions, true)) return true;

        $kandidat = User::where('divisi', 'Marketing dan Operasional')
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->pluck('name');

        foreach ($kandidat as $nama) {
            $namaNormalized = $this->normalizeName((string) $nama);
            if ($namaNormalized === '') continue;

            // Cocok jika nama sama persis, atau satu nama terkandung dalam nama lainnya
            // (mengakomodasi nama panggilan pendek seperti "Eko" vs "Eko Sigit Nugroho").
            if ($psNormalized === $namaNormalized || str_contains($namaNormalized, $psNormalized) || str_contains($psNormalized, $namaNormalized)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalisasi nama: lowercase, hilangkan spasi berlebih dan tanda baca di tepinya.
     */
    protected function normalizeName(string $nama): string
    {
        return trim(preg_replace('/\s+/u', ' ', mb_strtolower($nama)));
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240' // max 10MB
        ]);

        try {
            $import = new SalesImport();
            Excel::import($import, $request->file('file'));

            $months = implode(', ', $import->refreshedMonths);
            $count = number_format($import->importedCount, 0, ',', '.');

            if ($import->importedCount > 0) {
                return redirect()->back()->with('success', "Sukses! $count baris data telah diimpor, me-refresh data untuk periode: $months.")->with('active_tab', 'import');
            } else {
                return redirect()->back()->with('success', 'File berhasil diproses namun tidak ada baris data valid yang diimpor.')->with('active_tab', 'import');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Upload Excel Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return redirect()->back()->with('error', 'Gagal mengimpor data! Pastikan format tanggal dan angka di Excel sudah benar. (Info sistem: ' . $e->getMessage() . ')')->with('active_tab', 'import');
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');

            // Baris 1: Header Kolom Asli (Wajib ada)
            fputcsv($file, [
                'date',
                'customer_name',
                'product_name',
                'qty',
                'unit',
                'base_price',
                'discount',
                'net_price',
                'ps'
            ]);

            // Baris 2: Petunjuk Format (Akan otomatis di-skip oleh sistem import karena tanggal tidak valid)
            fputcsv($file, [
                'FORMAT WAJIB: Bln/Tgl/Tahun',
                'Wajib Diisi',
                'Wajib Diisi',
                'Angka',
                'Teks',
                'Format Bebas (Cth: Rp 529.500)',
                'Format Bebas (Cth: 12.69%)',
                'Format Bebas (Cth: Rp 32.361.500)',
                'Teks (Cth: Arief)'
            ]);

            // Baris 3: Contoh Data Benar (Bisa langsung Anda timpa/hapus)
            fputcsv($file, [
                '8/18/2026',
                'RSUD SAYANG',
                'RAKHA Kasa Katun Premium',
                '70',
                'Polybag',
                'Rp 529.500',
                '12.69%',
                'Rp 32.361.500',
                'Arief'
            ]);

            fclose($file);
        };

        return response()->streamDownload($callback, 'Template_Import_Sales_' . date('Ymd') . '.csv', $headers);
    }

    public function export(Request $request)
    {
        if (!$this->hasFullSalesAccess()) abort(403, 'Anda tidak memiliki hak akses untuk export Data Sales.');
        $query = Sales::query();

        // pencarian (search)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('product_name', 'like', "%{$search}%")
                    ->orWhere('ps', 'like', "%{$search}%");
            });
        }

        // filter tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('date', $request->input('tanggal'));
        }

        // filter bulan
        if ($request->filled('bulan')) {
            $query->where('month', $request->input('bulan'));
        }

        // filter tahun
        if ($request->filled('tahun')) {
            $query->whereYear('date', $request->input('tahun'));
        }

        // filter customer
        if ($request->filled('nama_customer')) {
            $query->where('customer_name', 'like', '%' . $request->input('nama_customer') . '%');
        }

        // filter produk
        if ($request->filled('nama_produk')) {
            $query->where('product_name', 'like', '%' . $request->input('nama_produk') . '%');
        }

        // filter ps
        if ($request->filled('ps')) {
            $query->where('ps', $request->input('ps'));
        }

        $sort = $request->input('sort', 'terlama');
        if ($sort === 'terbaru') {
            $query->orderBy('date', 'desc');
        } elseif ($sort === 'tertinggi') {
            $query->orderBy('net_price', 'desc');
        } elseif ($sort === 'terendah') {
            $query->orderBy('net_price', 'asc');
        } else {
            $query->orderBy('date', 'asc');
        }

        $sales = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
        ];

        $callback = function () use ($sales) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'date',
                'customer_name',
                'product_name',
                'qty',
                'unit',
                'base_price',
                'discount',
                'net_price',
                'ps'
            ]);

            foreach ($sales as $item) {
                $diskon_val = $item->discount;
                if (is_numeric($diskon_val) && $diskon_val > 0 && $diskon_val <= 1) {
                    $diskon_val = $diskon_val * 100;
                }

                fputcsv($file, [
                    $item->date ? date('Y-m-d', strtotime($item->date)) : '',
                    $item->customer_name ?? '',
                    $item->product_name ?? '',
                    $item->qty ?? '',
                    $item->unit ?? '',
                    isset($item->base_price) ? 'Rp ' . number_format($item->base_price, 0, ',', '.') : '',
                    (isset($item->discount) && $item->discount !== '') ? $diskon_val . '%' : '',
                    isset($item->net_price) ? 'Rp ' . number_format($item->net_price, 0, ',', '.') : '',
                    $item->ps ?? ''
                ]);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, 'Export_Data_Sales_' . date('YmdHis') . '.csv', $headers);
    }

    public function update(Request $request, Sales $sale)
    {
        $request->validate([
            'tanggal'       => 'nullable|date',
            'nama_customer' => 'nullable|string|max:255',
            'nama_produk'   => 'nullable|string|max:255',
            'qty'           => 'nullable|integer|min:1',
            'satuan'        => 'nullable|string|max:50',
            'hna'           => 'nullable|numeric|min:0',
            'diskon'        => 'nullable|numeric|min:0',
            'harga_nett'    => 'nullable|numeric|min:0',
            'ps'            => 'nullable|string|max:255',
        ]);

        $data = $request->only(['tanggal', 'date', 'nama_customer', 'customer_name', 'nama_produk', 'product_name', 'qty', 'satuan', 'unit', 'hna', 'base_price', 'diskon', 'discount', 'harga_nett', 'net_price', 'ps']);
        $columnMap = [
            'tanggal' => 'date',
            'nama_customer' => 'customer_name',
            'nama_produk' => 'product_name',
            'satuan' => 'unit',
            'hna' => 'base_price',
            'diskon' => 'discount',
            'harga_nett' => 'net_price',
        ];
        $data = collect($data)->mapWithKeys(fn($value, $key) => [$columnMap[$key] ?? $key => $value])->all();
        $dateInput = $request->filled('tanggal') ? $request->input('tanggal') : $request->input('date');
        if ($dateInput) {
            $data['month'] = ['01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April', '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August', '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'][date('m', strtotime($dateInput))];
        }

        $sale->update($data);

        return redirect()->back()->with('success', 'Data sales berhasil diperbarui!');
    }

    public function destroy(Sales $sale)
    {
        $sale->delete();

        return redirect()->back()->with('success', 'Data sales berhasil dihapus!');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:sales,id',
        ]);

        try {
            $count = count($request->ids);
            Sales::whereIn('id', $request->ids)->delete();
            return redirect()->back()->with('success', "Berhasil menghapus $count data sales terpilih.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Bulk Delete Sales Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data terpilih.');
        }
    }
}
