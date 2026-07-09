<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Client;
use App\Notifications\Channels\WhatsAppChannel;

class ClientBirthdayNotification extends Notification
{
    use Queueable;

    public $client;

    /**
     * Menerima object Client yang sedang ulang tahun
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function via($notifiable)
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        // Ambil data dari model Client
        $namaClient = $this->client->nama_user;
        $perusahaan = $this->client->nama_perusahaan;
        
        // Ambil nama sales internal jika ada
        $salesName = $this->client->user ? $this->client->user->name : '-';

        return [
            'message' => "*CLIENT BIRTHDAY REMINDER*\n\n" .
                         "Hari ini adalah ulang tahun client/mitra kita: *{$namaClient} - {$perusahaan}*\n" .
                         "PIC Internal: {$salesName}\n\n"
        ];
    }

    public function toArray($notifiable)
    {
        $namaClient = $this->client->nama_user;
        $perusahaan = $this->client->nama_perusahaan;

        return [
            'title'   => 'Client Ulang Tahun!',
            'message' => "Client {$namaClient} ({$perusahaan}) ulang tahun hari ini.",
            // Arahkan ke halaman detail CRM (sesuai route di CrmController)
            'url'     => route('admin.crm.show', $this->client->id), 
            'icon'    => 'fas fa-user-tie',
            'color'   => 'text-blue-500',
        ];
    }
}