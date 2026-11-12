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
    protected $description = 'Kirim notifikasi ultah client HANYA ke Internal Sales (PIC)';

    public function handle()
    {
        $today = now();
        $this->info("Cek Ultah Client {$today->toDateString()}");

        $birthCol = \Illuminate\Support\Facades\Schema::hasColumn('clients', 'birthdate')
            ? 'birthdate'
            : (\Illuminate\Support\Facades\Schema::hasColumn('clients', 'contact_birth_date') ? 'contact_birth_date' : 'tanggal_lahir');

        $birthdayClients = Client::active()
                                 ->whereMonth($birthCol, $today->month)
                                 ->whereDay($birthCol, $today->day)
                                 ->with('user') // Load relasi user (Sales Person)
                                 ->get();

        if ($birthdayClients->isEmpty()) {
            $this->info('Tidak ada client ulang tahun hari ini.');
            return;
        }

        // Ambil Direktur dan Kepala Divisi Marketing & Operasional
        // Berdasarkan data, Direktur memiliki jabatan 'Direktur'
        $direkturs = User::where('jabatan', 'like', '%Direktur%')->get();
        
        // Kepala Divisi Marketing & Operasional (contoh: Kepala Operasional)
        $kepalaDivisi = User::where('divisi', 'Marketing dan Operasional')
                            ->where('jabatan', 'like', '%Kepala%')
                            ->get();

        // 3. Loop Client dan Kirim Notifikasi
        foreach ($birthdayClients as $client) {
            $namaClient = $client->client_name;
            $perusahaan = $client->customer?->customer_name ?? $client->customer_name ?? '-';
            
            $this->info("Memproses Client: {$namaClient} ({$perusahaan})");

            $pic = $client->user;

            if ($pic) {
                $this->info(" - PIC (Sales) ditemukan: " . $pic->name);
                Notification::send($pic, new ClientBirthdayNotification($client));
                $this->info(" - Notifikasi dikirim ke PIC.");
            } else {
                $this->info(" - Client ini tidak memiliki Sales/User internal yang terhubung.");
            }

            // Kirim ke Direktur
            if ($direkturs->isNotEmpty()) {
                Notification::send($direkturs, new ClientBirthdayNotification($client));
                $this->info(" - Notifikasi dikirim ke Direktur.");
            }

            // Kirim ke Kepala Divisi Marketing dan Operasional
            if ($kepalaDivisi->isNotEmpty()) {
                Notification::send($kepalaDivisi, new ClientBirthdayNotification($client));
                $this->info(" - Notifikasi dikirim ke Kepala Divisi Marketing & Operasional.");
            }
        }
        
        $this->info("Selesai.");
    }
}