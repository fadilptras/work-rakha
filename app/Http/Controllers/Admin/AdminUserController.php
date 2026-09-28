<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class AdminUserController extends Controller
{
    /**
     * Menampilkan daftar user berdasarkan role ('admin' or 'user').
     */
    public function indexByRole(string $role): View
    {
        if (!in_array($role, ['admin', 'user'])) {
            abort(404);
        }

        if ($role === 'admin') {
            $users = User::query()->where('role', 'admin')->orderBy('name')->get();
            
            return view('admin.admin', [
                'users' => $users,
                'title' => 'Kelola Admin',
                'defaultRole' => 'admin'
            ]);
        }

        $usersByDivision = User::query()
                        ->where('role', 'user')
                        ->where('email', '!=', 'test@gmail.com')
                        ->with(['riwayatPendidikan', 'riwayatPekerjaan'])
                        ->orderBy('divisi')
                        ->orderByDesc('is_kepala_divisi')
                        ->orderBy('name')
                        ->get()
                        ->groupBy('divisi');

        return view('admin.karyawan', [
            'usersByDivision' => $usersByDivision, 
            'title' => 'Kelola Karyawan',
            'defaultRole' => 'user'
        ]);
    }

    /**
     * Menampilkan halaman edit terpisah untuk karyawan tertentu.
     */
    public function edit(User $user): View
    {
        if ($user->role !== 'user') {
            abort(404);
        }

        return view('admin.edit_karyawan', [
            'user' => $user,
            'title' => 'Edit Data Karyawan'
        ]);
    }

    /**
     * Memperbarui data biodata, divisi, jabatan, jatah cuti, beserta foto hasil crop.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'divisi' => ['required', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'tanggal_bergabung' => ['nullable', 'date'],
            'jatah_cuti' => ['nullable', 'integer', 'min:0'],
            'sisa_cuti' => ['nullable', 'integer', 'min:0'],
            'status_karyawan' => ['nullable', 'string', 'max:100'],
            
            // Validasi Data Tambahan Lengkap
            'nip' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:20'],
            'nomor_telepon' => ['nullable', 'string', 'max:20'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'in:Laki-laki,Perempuan'],
            'agama' => ['nullable', 'string', 'max:50'],
            'golongan_darah' => ['nullable', 'string', 'max:10'],
            'status_pernikahan' => ['nullable', 'string', 'max:50'],
            'alamat_ktp' => ['nullable', 'string'],
            'alamat_domisili' => ['nullable', 'string'],
            
            // Finansial & Bank
            'nama_bank' => ['nullable', 'string', 'max:50'],
            'nomor_rekening' => ['nullable', 'string', 'max:50'],
            'pemilik_rekening' => ['nullable', 'string', 'max:100'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'bpjs_kesehatan' => ['nullable', 'string', 'max:50'],
            'bpjs_ketenagakerjaan' => ['nullable', 'string', 'max:50'],
            
            // Kontak Darurat
            'kontak_darurat_nama' => ['nullable', 'string', 'max:100'],
            'kontak_darurat_nomor' => ['nullable', 'string', 'max:20'],
            'kontak_darurat_hubungan' => ['nullable', 'string', 'max:50'],

            // Penampung file base64 dari Cropper.js
            'cropped_image' => ['nullable', 'string'],
        ]);

        // Memproses Upload Gambar jika ada string base64 baru dari cropper
        if ($request->filled('cropped_image')) {
            try {
                $image_parts = explode(";base64,", $request->cropped_image);
                $image_base64 = base64_decode($image_parts[1]);
                
                $fileName = 'profile_pictures/' . uniqid() . '.png';
                
                // Hapus foto lama di storage jika ada sebelumnya
                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                Storage::disk('public')->put($fileName, $image_base64);
                $validated['profile_picture'] = $fileName;
            } catch (\Exception $e) {
                return back()->with('error', 'Gagal memproses crop foto profil. Silakan coba lagi.');
            }
        }

        // Singkirkan input cropped_image agar tidak error mass-assignment ke DB
        unset($validated['cropped_image']);

        $user->update($validated);

        Cache::forget('karyawan_list_dropdown');
        Cache::forget('admin_list_dropdown');
        Cache::forget('approvers_list_dropdown');

        return redirect()->route('admin.employees.index')->with('success', "Seluruh data profil a.n. {$user->name} berhasil diperbarui.");
    }

    /**
     * Memperbarui data admin (termasuk password jika diisi).
     */
    public function updateAdmin(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($request->user_id);
        
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'profile_picture' => ['nullable', 'image', 'max:2048']
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $validated['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $user->update($validated);

        Cache::forget('karyawan_list_dropdown');
        Cache::forget('admin_list_dropdown');
        Cache::forget('approvers_list_dropdown');

        return back()->with('success', "Data admin a.n. {$user->name} berhasil diperbarui.");
    }

    /**
     * Menghapus akun pengguna dari sistem secara permanen.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $nama = $user->name;

        try {
            DB::transaction(function () use ($user) {
                // Bersihkan referensi ke user ini di kolom-kolom FK yang masih
                // memakai RESTRICT (skema lama) agar DELETE tidak kena error 1451.
                // Kolom dengan nullOnDelete/set null/cascade aman tanpa ini,
                // tapi pengecekan Schema::hasColumn membuatnya aman di semua versi skema.
                $this->nullifyUserReferences($user->id);

                // Hapus file foto dari storage sebelum delete records
                $fresh = User::find($user->id);
                if ($fresh && $fresh->profile_picture && Storage::disk('public')->exists($fresh->profile_picture)) {
                    Storage::disk('public')->delete($fresh->profile_picture);
                }

                User::where('id', $user->id)->delete();
            });
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Gagal hapus akun karyawan', [
                'user_id' => $user->id,
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'db_message' => $e->getMessage(),
            ]);

            return back()->with('error', "Gagal menghapus akun a.n. {$nama} (kode: {$e->getCode()}){$this->describeConstraintError($e->getMessage())}");
        }

        Cache::forget('karyawan_list_dropdown');
        Cache::forget('karyawan_list_dropdown_v2');
        Cache::forget('admin_list_dropdown');
        Cache::forget('approvers_list_dropdown');

        return back()->with('success', "Akun a.n. {$nama} berhasil dihapus permanen.");
    }

    /**
     * Set NULL semua kolom FK nullable yang merujuk ke user tertentu.
     * Menggabungkan daftar statis dengan penemuan otomatis dari INFORMATION_SCHEMA
     * agar FK hasil drift production (nama custom) ikut dibersihkan.
     */
    protected function nullifyUserReferences(int $userId): void
    {
        $map = [
            // [tabel => [kolom...]]
            'users' => [
                'approver_dana_1_id', 'approver_dana_2_id', 'approver_dana_3_id', 'approver_dana_4_id',
                'approver_barang_1_id', 'approver_barang_2_id', 'approver_barang_3_id', 'approver_barang_4_id',
                'approver_cuti_1_id', 'approver_cuti_2_id', 'approver_cuti_3_id', 'approver_cuti_4_id',
                'approver_1_id', 'approver_2_id', 'manager_keuangan_id',
                'kpi_evaluator_id', 'kpi_approver_1_id', 'kpi_approver_2_id',
                'atasan_id',
            ],
            'cutis' => [
                'approver_id',
                'approver_cuti_1_id', 'approver_cuti_2_id', 'approver_cuti_3_id', 'approver_cuti_4_id',
            ],
            'pengajuan_dana' => [
                'finance_id', 'approver_1_id', 'approver_2_id',
                'approver_dana_1_id', 'approver_dana_2_id', 'approver_dana_3_id', 'approver_dana_4_id',
                'atasan_id', 'direktur_id',
            ],
            'pengajuan_barang' => [
                'approver_barang_1_id', 'approver_barang_2_id', 'approver_barang_3_id', 'approver_barang_4_id',
            ],
            'kpi_evaluations' => ['evaluator_id'],
            'sph_quotations' => ['user_id', 'created_by'],
            'stock_logs' => ['user_id'],
            'sales_forecast_orders' => ['user_id'],
            'interactions' => ['user_id'],
            'client_interactions' => ['user_id'],
        ];

        // Tambahan otomatis: FK apa pun (termasuk nama custom hasil edit manual)
        // yang menunjuk ke users.id dan kolomnya nullable.
        foreach ($this->discoverNullableUserReferences() as $table => $columns) {
            $map[$table] = array_values(array_unique(array_merge($map[$table] ?? [], $columns)));
        }

        foreach ($map as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }
            foreach (array_unique($columns) as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }
                DB::table($table)->where($column, $userId)->update([$column => null]);
            }
        }
    }

    /**
     * Menemukan semua kolom nullable yang punya FK ke users.id,
     * langsung dari INFORMATION_SCHEMA database yang sedang dipakai.
     * Mengembalikan [tabel => [kolom...]]. Aman di non-MySQL (return []).
     *
     * @return array<string, array<int, string>>
     */
    protected function discoverNullableUserReferences(): array
    {
        try {
            $rows = DB::select("
                SELECT kcu.TABLE_NAME AS t, kcu.COLUMN_NAME AS c
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
                JOIN INFORMATION_SCHEMA.COLUMNS col
                  ON col.TABLE_SCHEMA = kcu.TABLE_SCHEMA
                 AND col.TABLE_NAME = kcu.TABLE_NAME
                 AND col.COLUMN_NAME = kcu.COLUMN_NAME
                WHERE kcu.TABLE_SCHEMA = DATABASE()
                  AND kcu.REFERENCED_TABLE_NAME = 'users'
                  AND kcu.REFERENCED_COLUMN_NAME = 'id'
                  AND col.IS_NULLABLE = 'YES'
            ");
        } catch (\Throwable $e) {
            return [];
        }

        $map = [];
        foreach ($rows as $row) {
            $map[$row->t][] = $row->c;
        }

        return $map;
    }

    /**
     * Mengubah pesan QueryException menjadi petunjuk tabel.kolom + nama constraint,
     * supaya kegagalan hapus bisa didiagnosis dalam sekali lihat.
     */
    protected function describeConstraintError(string $message): string
    {
        if (! preg_match('/CONSTRAINT `([^`]+)`/', $message, $m)) {
            return '.';
        }

        $constraint = $m[1];
        $column = '';
        if (preg_match('/FOREIGN KEY \(`([^`]+)`\)/', $message, $mc)) {
            $column = $mc[1];
        }

        $table = '';
        try {
            $row = DB::selectOne(
                'SELECT TABLE_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_NAME = ? LIMIT 1',
                [$constraint]
            );
            if ($row) {
                $table = $row->TABLE_NAME;
            }
        } catch (\Throwable $e) {
            // abaikan: tetap kembalikan nama constraint saja
        }

        $location = $table !== '' && $column !== ''
            ? "{$table}.{$column}"
            : ($table !== '' ? $table : $column);
        if ($location !== '') {
            return " — terkendala di {$location} (constraint {$constraint}).";
        }

        return " — constraint {$constraint}.";
    }

    /**
     * Mengatur seorang karyawan menjadi Kepala Divisi (menggantikan kepala divisi lama di divisi yang sama).
     */
    public function setAsDivisionHead(User $user): RedirectResponse
    {
        DB::transaction(function () use ($user) {
            User::query()
                ->where('divisi', $user->divisi)
                ->where('id', '!=', $user->id)
                ->update(['is_kepala_divisi' => false]);

            $user->update(['is_kepala_divisi' => true]);
        });

        return redirect()->route('admin.employees.index')->with('success', "{$user->name} telah diatur sebagai Kepala Divisi {$user->divisi}.");
    }

    /**
     * Menghasilkan dan mengunduh PDF profil karyawan spesifik.
     */
    public function downloadProfilePdf(User $user): Response
    {
        $user->load(['riwayatPendidikan', 'riwayatPekerjaan']);

        $admin = Auth::user();

        $data = [
            'user' => $user,
            'pencetak' => $admin ? $admin->name : 'Admin',
            'tanggal_cetak' => now()->translatedFormat('d F Y H:i')
        ];

        $pdf = Pdf::loadView('pdf.documents.profile', $data)->setPaper('a4', 'portrait');

        $filename = 'CV_Profil_' . str_replace(' ', '_', $user->name) . '.pdf';
        $filename = str_replace(['/', '\\'], '-', $filename);
        return $pdf->download($filename);
    }

    /**
     * Menyediakan data JSON profil karyawan untuk modal detail AJAX.
     */
    public function ajaxDetail(User $user)
    {
        $user->load(['riwayatPendidikan', 'riwayatPekerjaan']);
        return response()->json($user);
    }
}