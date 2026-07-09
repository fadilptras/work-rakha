<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\User;
use App\Notifications\ClientBirthdayNotification;
use Illuminate\Support\Facades\Notification;

class SendClientBirthdayNotifications extends Command
{
    /**
     * Nama command: php artisan app:send-client-birthday
     */
    protected $signature = 'app:send-client-birthday';
    protected $description = 'Kirim notifikasi ultah client ke Internal Sales (PIC), Direktur, & Kadiv terkait';

    public function handle()
    {
        $today = now();
        $this->info("Cek Ultah Client {$today->toDateString()}");

        $birthdayClients = Client::whereMonth('tanggal_lahir', $today->month)
                                 ->whereDay('tanggal_lahir', $today->day)
                                 ->with('user') // Load relasi user (Sales Person)
                                 ->get();

        if ($birthdayClients->isEmpty()) {
            $this->info('Tidak ada client ulang tahun hari ini.');
            return;
        }

        // kita
        $managementUsers = User::where('jabatan', 'Direktur')
                                ->orWhere(function($query) {
                                    $query->where('is_kepala_divisi', 1)
                                          ->whereIn('divisi', ['Marketing', 'Operasional', 'Marketing dan Operasional']);
                                })
                                ->get();

        $this->info("Management users found: " . $managementUsers->count());

        // 3. Loop Client dan Kirim Notifikasi
        foreach ($birthdayClients as $client) {
            $namaClient = $client->nama_user;
            $perusahaan = $client->nama_perusahaan;
            
            $this->info("Memproses Client: {$namaClient} ({$perusahaan})");

            // A. Mulai dengan daftar Management
            // Gunakan 'unique' untuk memastikan tidak ada duplikat ID
            $recipients = $managementUsers->unique('id'); 

            // B. Tambahkan PIC Internal (Sales) dari relasi 'user'
            if ($client->user) {
                // Method 'push' menambahkan item ke collection
                $recipients->push($client->user);
                $this->info(" - PIC (Sales) ditemukan: " . $client->user->name);
            } else {
                $this->info(" - Client ini tidak memiliki Sales/User internal yang terhubung.");
            }

            // C. Filter Duplikat Terakhir (Jaga-jaga jika PIC juga seorang Direktur/Kadiv)
            $finalRecipients = $recipients->unique('id');

            if ($finalRecipients->isNotEmpty()) {
                // Kirim Notifikasi
                Notification::send($finalRecipients, new ClientBirthdayNotification($client));
                $this->info(" - Notifikasi dikirim ke " . $finalRecipients->count() . " orang.");
            }
        }
        
        $this->info("Selesai.");
    }
}