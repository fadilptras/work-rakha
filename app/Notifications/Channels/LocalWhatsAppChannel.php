<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocalWhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toWhatsApp')) {
            Log::warning('Class notifikasi ' . get_class($notification) . ' tidak memiliki method toWhatsApp.');
            return;
        }

        // Mendapatkan data pesan dari fungsi toWhatsApp di Notification class
        $data = $notification->toWhatsApp($notifiable);
        
        $target = $data['target'] ?? null;
        
        if ($target === 'group') {
            $target = env('WHATSAPP_GROUP_ID', '120363242834102956@g.us');
        } elseif (empty($target)) {
            $rawPhone = $notifiable->nomor_telepon ?? ''; 

            if (empty($rawPhone)) {
                Log::warning("LocalWhatsAppChannel: User {$notifiable->name} tidak memiliki nomor telepon.");
                return;
            }

            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (str_starts_with($cleanPhone, '08')) {
                $target = '628' . substr($cleanPhone, 2);
            } elseif (str_starts_with($cleanPhone, '8')) {
                $target = '628' . substr($cleanPhone, 1);
            } elseif (str_starts_with($cleanPhone, '6208')) {
                $target = '628' . substr($cleanPhone, 4);
            } elseif (str_starts_with($cleanPhone, '628')) {
                $target = $cleanPhone;
            } else {
                $target = $cleanPhone; 
            }
        } else {
            // Jika ada target khusus tapi belum diformat dan bukan grup
            if (!str_ends_with($target, '@g.us') && !str_contains($target, '-')) {
                if (substr($target, 0, 1) === '0') {
                    $target = '62' . substr($target, 1);
                }
            }
        }

        // Anti-Spam Grup: Mencegah notifikasi berulang di satu eksekusi
        static $sentGroupMessages = [];
        
        if (str_ends_with($target, '@g.us') || str_contains($target, '-')) {
            $messageHash = md5($target . $data['message']);
            
            if (in_array($messageHash, $sentGroupMessages)) {
                return; 
            }
            $sentGroupMessages[] = $messageHash;
        }

        // Endpoint dari self-hosted WhatsApp API lokal (Node.js)
        $waApiUrl = env('WA_API_URL', 'http://localhost:3000/send');

        try {
            $response = Http::post($waApiUrl, [
                'target' => $target,
                'message' => $data['message']
            ]);

            if ($response->successful()) {
                Log::info('LocalWhatsAppChannel: Pesan berhasil dikirim ke ' . $target);
            } else {
                Log::error('LocalWhatsAppChannel Error (' . $response->status() . '): ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('LocalWhatsAppChannel Exception: ' . $e->getMessage());
        }
    }
}
