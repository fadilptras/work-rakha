<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RiwayatPendidikan;
use App\Models\RiwayatPekerjaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf; 
use Carbon\Carbon;
use Exception;

class ProfileController extends Controller
{
    public function editProfile()
    {
        $title = 'Edit Profil';
        $user = Auth::user()->load('riwayatPendidikan', 'riwayatPekerjaan');
        return view('users.profile.profile-edit', compact('title', 'user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi Request dengan max 10240 (10 MB)
        $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'tanggal_bergabung' => ['nullable', 'date'],
            'password'          => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_picture'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'cropped_image'     => 'nullable|string',
            'file_ktp'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'file_npwp'         => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'file_bpjs_kesehatan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'file_bpjs_ketenagakerjaan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'pendidikan.*.file_ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ], [
            // Kustomisasi pesan error agar lebih informatif
            'max' => 'Ukuran file :attribute maksimal adalah 10MB.',
            'mimes' => 'Format file :attribute harus berupa pdf, jpg, jpeg, atau png.'
        ]);

        try {
            DB::transaction(function () use ($request, $user) {
                // 1. Whitelist field yang BOLEH diupdate oleh karyawan sendiri
                $allowedFields = [
                    'name', 'email', 'tanggal_bergabung',
                    'nomor_telepon', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
                    'agama', 'golongan_darah', 'status_pernikahan',
                    'alamat_ktp', 'alamat_domisili',
                    'kontak_darurat_nama', 'kontak_darurat_nomor', 'kontak_darurat_hubungan',
                    'npwp', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan',
                    'nama_bank', 'nomor_rekening', 'pemilik_rekening',
                    'nip', 'nik',
                ];
                $userData = $request->only($allowedFields);

                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }

                // 2. Handle Foto Profil
                if ($request->filled('cropped_image')) {
                    if ($user->profile_picture) {
                        Storage::disk('public')->delete($user->profile_picture);
                    }
                    $image_parts = explode(";base64,", $request->cropped_image);
                    $image_base64 = base64_decode($image_parts[1]);
                    $fileName = 'profile_pictures/' . uniqid() . '.png';
                    Storage::disk('public')->put($fileName, $image_base64);
                    $userData['profile_picture'] = $fileName;
                } elseif ($request->hasFile('profile_picture')) {
                    if ($user->profile_picture) {
                        Storage::disk('public')->delete($user->profile_picture);
                    }
                    $userData['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
                }

                // 3. Handle Dokumen Pribadi (Looping agar efisien)
                $dokumenFields = ['file_ktp', 'file_npwp', 'file_bpjs_kesehatan', 'file_bpjs_ketenagakerjaan'];
                foreach ($dokumenFields as $field) {
                    if ($request->hasFile($field)) {
                        if ($user->$field) {
                            Storage::disk('public')->delete($user->$field);
                        }
                        $userData[$field] = $request->file($field)->store('dokumen_karyawan', 'public');
                    }
                }

                $user->update($userData);

                // 4. Handle Riwayat Pendidikan (Termasuk Ijazah)
                $submittedPendidikanIds = [];
                if ($request->has('pendidikan')) {
                    foreach ($request->pendidikan as $index => $pnd) {
                        $dataPnd = [
                            'jenjang'         => $pnd['jenjang'],
                            'nama_institusi'  => $pnd['nama_institusi'],
                            'jurusan'         => $pnd['jurusan'],
                            'tahun_lulus'     => $pnd['tahun_lulus'],
                        ];

                        // Cek upload ijazah menggunakan dot notation untuk array
                        if ($request->hasFile("pendidikan.$index.file_ijazah")) {
                            $file = $request->file("pendidikan.$index.file_ijazah");
                            
                            // Hapus file lama jika sedang mengedit (ada ID)
                            if (isset($pnd['id'])) {
                                $oldPnd = RiwayatPendidikan::find($pnd['id']);
                                if ($oldPnd && $oldPnd->file_ijazah) {
                                    Storage::disk('public')->delete($oldPnd->file_ijazah);
                                }
                            }
                            $dataPnd['file_ijazah'] = $file->store('dokumen_karyawan/ijazah', 'public');
                        }

                        if (isset($pnd['id'])) {
                            $pendidikan = RiwayatPendidikan::findOrFail($pnd['id']);
                            $pendidikan->update($dataPnd);
                            $submittedPendidikanIds[] = $pendidikan->id;
                        } else {
                            $newPnd = $user->riwayatPendidikan()->create($dataPnd);
                            $submittedPendidikanIds[] = $newPnd->id;
                        }
                    }
                }
                $user->riwayatPendidikan()->whereNotIn('id', $submittedPendidikanIds)->delete();

                // 5. Handle Riwayat Pekerjaan
                $submittedPekerjaanIds = [];
                if ($request->has('pekerjaan')) {
                    foreach ($request->pekerjaan as $pkj) {
                        if (isset($pkj['id'])) {
                            $pekerjaan = RiwayatPekerjaan::findOrFail($pkj['id']);
                            $pekerjaan->update($pkj);
                            $submittedPekerjaanIds[] = $pekerjaan->id;
                        } else {
                            $newPkj = $user->riwayatPekerjaan()->create($pkj);
                            $submittedPekerjaanIds[] = $newPkj->id;
                        }
                    }
                }
                $user->riwayatPekerjaan()->whereNotIn('id', $submittedPekerjaanIds)->delete();
            });

            // Berikan response sukses
            return back()->with('success', 'Profil dan seluruh dokumen berhasil diperbarui!');

        } catch (Exception $e) {
            // Berikan response error jika terjadi masalah di server/database
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }

    // METHOD BARU UNTUK DOWNLOAD PDF
    public function downloadPdf()
    {
        // Ambil data user yang sedang login beserta relasinya
        $user = Auth::user()->load(['riwayatPendidikan', 'riwayatPekerjaan']);
        
        // Buat format tanggal cetak saat ini untuk ditampilkan di footer PDF
        $tanggal_cetak = Carbon::now()->translatedFormat('d F Y H:i');

        // Muat halaman view resources/views/pdf/profile.blade.php
        $pdf = Pdf::loadView('pdf.documents.profile', compact('user', 'tanggal_cetak'));
        
        // Atur ukuran kertas ke A4 dengan orientasi Portrait
        $pdf->setPaper('a4', 'portrait');

        // Render & Download PDF dengan format nama file otomatis: CV_Profil_NamaKaryawan.pdf
        return $pdf->download('CV_Profil_' . str_replace(' ', '_', $user->name) . '.pdf');
    }
}