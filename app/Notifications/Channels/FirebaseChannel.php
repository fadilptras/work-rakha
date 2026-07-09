<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use Illuminate\Support\Facades\Log;

class FirebaseChannel
{
    protected $messaging;

    public function __construct()
    {
        // Menggunakan app() untuk memanggil service Firebase
        $this->messaging = app('firebase.messaging');
    }

    public function send($notifiable, Notification $notification)
    {
        // 1. Ambil Token FCM User
        // Pastikan model User punya kolom 'fcm_token'
        $token = $notifiable->fcm_token;

        if (empty($token)) {
            return; // Stop jika user belum mengizinkan notifikasi (token kosong)
        }

        // 2. Ambil Data Pesan dari Notification Class
        // Menggunakan method 'toFirebase' yang ada di CutiNotification.php
        $data = method_exists($notification, 'toFirebase')
                ? $notification->toFirebase($notifiable)
                : null;

        if (empty($data)) {
            return;
        }

        // 3. Susun Format Pesan Firebase
        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(FirebaseNotification::create($data['title'], $data['body']))
                ->withData([
                    // 'click_action' penting agar notifikasi bisa diklik & redirect
                    'click_action' => $data['url'] ?? url('/'),
                    'type' => 'notification_alert'
                ]);

            // 4. Kirim Pesan
            $this->messaging->send($message);

        } catch (\Kreait\Firebase\Exception\Messaging\NotFound $e) {
            // Error ini muncul jika Token HP User sudah expired/tidak valid
            Log::warning("Token FCM hangus untuk User ID: {$notifiable->id}. Menghapus token.");
            $notifiable->update(['fcm_token' => null]);
        } catch (\Throwable $e) {
            // Error lain (koneksi putus, config salah, dll)
            Log::error("Gagal mengirim FCM: " . $e->getMessage());
        }
    }
}