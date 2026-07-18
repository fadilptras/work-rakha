<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

/**
 * Custom Channel WhatsApp
 * 
 * Bertugas sebagai perantara (Driver) untuk mengirim pesan WA melalui Fonnte API.
 * Mendukung pengiriman Broadcast Grup maupun Direct Message Personal, 
 * lengkap dengan fitur proteksi Anti-Spam dan validasi/cleansing Nomor HP.
 */
class WhatsAppChannel
{
    /**
     * Mengeksekusi pengiriman pesan WhatsApp via Fonnte
     * 
     * @param object $notifiable Entitas User/Sistem yang dikirim pesan
     * @param \Illuminate\Notifications\Notification $notification Class notifikasi
     */
    public function send(object $notifiable, Notification $notification): void
    {
        // 1. Cek apakah notifikasi mendukung pengiriman via WhatsApp
        if (!method_exists($notification, 'toWhatsApp')) {
            return;
        }

        // 2. Ambil data pesan dari method toWhatsApp di kelas notifikasi
        $data = $notification->toWhatsApp($notifiable);
        
        // Jika return data kosong (misal dibatalkan karena Group ID kosong di env), hentikan eksekusi
        if (empty($data) || empty($data['message'])) {
            return;
        }

        // 3. Menentukan Target Penerima (Grup atau Personal)
        if (isset($data['target']) && !empty($data['target'])) {
            // Skenario Broadcast: Gunakan target spesifik (misal: ID Grup) yang di-set dari file Notifikasi
            $target = $data['target'];
        } else {
            // Skenario Japri/Transaksional: Fallback otomatis ke nomor telepon user personal
            $rawPhone = $notifiable->nomor_telepon; 

            if (empty($rawPhone)) {
                Log::warning("WA Skip: User {$notifiable->name} tidak memiliki nomor telepon.");
                return;
            }

            // Bersihkan Format Nomor HP (hapus spasi, strip, tanda +, dll)
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            
            // Standarisasi Format ke 62 (Kode Negara Indonesia)
            if (str_starts_with($cleanPhone, '08')) {
                $target = '628' . substr($cleanPhone, 2);
            } elseif (str_starts_with($cleanPhone, '62')) {
                $target = $cleanPhone;
            } else {
                // Jika tidak diawali 08 atau 62 (kemungkinan format aneh/salah input), beri warning namun tetap dicoba
                Log::warning("WA Format Aneh: User {$notifiable->name} (ID: {$notifiable->id}) memiliki format nomor HP tidak standar: {$rawPhone}");
                $target = $cleanPhone; 
            }
        }

        // 4. ANTI-SPAM GRUP (Penting!)
        // Mencegah looping notifikasi yang sama (misal dari Command Cron) untuk dikirim berkali-kali ke grup yang sama.
        static $sentGroupMessages = [];
        
        // Asumsi target adalah ID Grup (biasanya berakhiran @g.us atau format khas API Fonnte)
        if (str_ends_with($target, '@g.us') || str_contains($target, '-')) {
            $messageHash = md5($target . $data['message']); // Hash unik dari target + pesan
            
            // Jika hash ini sudah pernah dikirim di sesi script yang sama, skip pengiriman
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
                CURLOPT_TIMEOUT => 0, // No timeout
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'target' => $target,
                    'message' => $data['message']
                    // countryCode dimatikan karena target bisa berupa ID Grup
                ),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: ' . config('services.fonnte.token')
                ),
            ));

            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            // 6. Logging Status Response Fonnte
            $logContext = "Target: {$target} | User: " . ($notifiable->name ?? 'System/Group') . " (ID: " . ($notifiable->id ?? '-') . ")";
            
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
