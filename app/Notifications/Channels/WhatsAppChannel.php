<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Mengirim pesan WhatsApp via Fonnte
     */
    public function send($notifiable, $notification)
    {
        // 1. Cek apakah notifikasi punya method 'toWhatsApp'
        if (!method_exists($notification, 'toWhatsApp')) {
            return;
        }

        // 2. Ambil data pesan dari notifikasi
        $data = $notification->toWhatsApp($notifiable);
        
        // Jika return data kosong, batalkan pengiriman
        if (empty($data) || empty($data['message'])) {
            return;
        }

        // 3. Menentukan Target Penerima (Grup atau Personal)
        if (isset($data['target']) && !empty($data['target'])) {
            // Gunakan target spesifik (misal: ID Grup) yang di-set dari file Notifikasi
            $target = $data['target'];
        } else {
            // Fallback ke nomor telepon user personal
            $rawPhone = $notifiable->nomor_telepon; 

            if (empty($rawPhone)) {
                Log::warning("WA Skip: User {$notifiable->name} tidak memiliki nomor telepon.");
                return;
            }

            // Bersihkan Format Nomor HP
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (substr($cleanPhone, 0, 2) === '08') {
                $target = '62' . substr($cleanPhone, 1);
            } elseif (substr($cleanPhone, 0, 2) === '62') {
                $target = $cleanPhone;
            } else {
                $target = $cleanPhone; 
            }
        }

        // 4. ANTI-SPAM GRUP (Penting!)
        // Jika command mengirim ke 50 user, kita tidak ingin grup menerima 50 pesan yang sama.
        static $sentGroupMessages = [];
        // Cek apakah target adalah ID Grup (biasanya berakhiran @g.us atau mengandung tanda strip)
        if (str_ends_with($target, '@g.us') || str_contains($target, '-')) {
            $messageHash = md5($target . $data['message']);
            // Jika pesan yang sama sudah pernah dikirim ke grup ini di proses yang sama, batalkan.
            if (in_array($messageHash, $sentGroupMessages)) {
                return; 
            }
            $sentGroupMessages[] = $messageHash;
        }

        // 5. Kirim Request ke API Fonnte menggunakan cURL Native
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'target' => $target,
                    'message' => $data['message']
                    // countryCode dimatikan karena target bisa berupa ID Grup
                ),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: MP8iwGyRDCKJVgNs5ejZ'
                ),
            ));

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            // Cek status response
            if ($error) {
                Log::error("Fonnte cURL Error: " . $error);
            } else {
                $responseData = json_decode($response, true);
                if (isset($responseData['status']) && $responseData['status'] === true) {
                    Log::info("WA Terkirim ke: {$target} | Response: " . $response);
                } else {
                    Log::error("Fonnte API Error: " . $response);
                }
            }
        } catch (\Exception $e) {
            Log::error("Fonnte Exception: " . $e->getMessage());
        }
    }
}