#!/bin/bash
echo "Memulai konfigurasi Rakha Migrasi Production..."

# 1. Menghapus folder storage yang nyangkut di public_html
echo "Membersihkan symlink lama untuk foto..."
rm -rf /home/rakhan743/public_html/workflow/storage
rm -rf /home/rakhan743/public_html/workflow/public/storage

# 2. Membuat symlink baru yang menjamin foto profil & lampiran muncul
echo "Membuat symlink baru..."
ln -s /home/rakhan743/workflow/storage/app/public /home/rakhan743/public_html/workflow/storage
ln -s /home/rakhan743/workflow/storage/app/public /home/rakhan743/public_html/workflow/public/storage

# 3. Mereset sistem Laravel (Sangat penting agar .env baru terbaca)
echo "Mereset cache dan config aplikasi..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan migrate --force

echo "Selesai! Silakan cek web Anda, foto seharusnya sudah ter-load dengan baik!"
