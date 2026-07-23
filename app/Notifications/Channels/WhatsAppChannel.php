<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Eksekusi pengiriman pesan WhatsApp via Fonnte API.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toWhatsApp')) {
            return;
        }

        $data = $notification->toWhatsApp($notifiable);
        
        if (empty($data) || empty($data['message'])) {
            return;
        }

        // Konfigurasi Fonnte
        $fonnteToken = 'MP8iwGyRDCKJVgNs5ejZ';
        $fonnteGroupId = '120363242834102956@g.us';

        // Tentukan Target Penerima
        $target = $data['target'] ?? null;

        if ($target === 'group') {
            $target = $fonnteGroupId;
        } elseif (empty($target)) {
            // Fallback ke nomor personal
            $rawPhone = $notifiable->nomor_telepon ?? ''; 

            if (empty($rawPhone)) {
                Log::warning("WA Skip: User {$notifiable->name} tidak memiliki nomor telepon.");
                return;
            }

            // Standarisasi Format (62)
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (str_starts_with($cleanPhone, '08')) {
                $target = '628' . substr($cleanPhone, 2);
            } else {
                $target = $cleanPhone; 
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

        // Kirim Request via cURL
        try {
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => [
                    'target' => $target,
                    'message' => $data['message']
                ],
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . $fonnteToken
                ],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            // Logging Status
            $logContext = "Target: {$target} | User: " . ($notifiable->name ?? 'System/Group');
            
            if ($error) {
                Log::error("Fonnte cURL Error [{$logContext}]: " . $error);
            } else {
                $responseData = json_decode($response, true);
                if (isset($responseData['status']) && $responseData['status'] === true) {
                    Log::info("WA Terkirim [{$logContext}] | Response: " . $response);
                } else {
                    Log::error("Fonnte API Error [{$logContext}]: " . $response);
                }
            }
        } catch (\Exception $e) {
            Log::error("Fonnte Exception: " . $e->getMessage());
        }
    }
}
