<?php

/**
 * fix_production.php
 * ===================
 * File ini khusus diletakkan di folder PUBLIC agar bisa diakses via browser di server production.
 * 
 * CARA PENGGUNAAN:
 * 1. Upload file ini ke folder PUBLIC di hosting (misal: public_html/)
 * 2. Akses via browser: https://work.rakhanusantaramedika.com/fix_production.php
 * 3. HAPUS file ini setelah berhasil dijalankan!
 *
 * FUNGSI: Mereset semua password yang tidak menggunakan Bcrypt 
 *         menjadi password default: #rakhA2022!
 */

// Bootstrap Laravel dari folder rakha_project (satu level di atas public_html)
$basePath = dirname(__DIR__); // Naik satu level dari public_html ke root hosting
$projectPath = $basePath . '/rakha_project'; // Folder core Laravel Anda

require $projectPath . '/vendor/autoload.php';
$app = require_once $projectPath . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// Password default untuk user yang passwordnya bermasalah
$defaultPassword = '#rakhA2022!';

// Ambil semua user dari database
$users = DB::table('users')->select('id', 'name', 'email', 'password', 'role')->get();

$fixed = [];
$ok    = [];

foreach ($users as $u) {
    $prefix = substr($u->password ?? '', 0, 4);
    // Bcrypt hash selalu diawali $2y$ atau $2b$
    if ($prefix !== '$2y$' && $prefix !== '$2b$') {
        DB::table('users')->where('id', $u->id)->update([
            'password' => Hash::make($defaultPassword),
        ]);
        $fixed[] = [
            'id'    => $u->id,
            'name'  => $u->name,
            'email' => $u->email,
            'role'  => $u->role,
            'old_prefix' => $prefix ?: '(kosong)',
        ];
    } else {
        $ok[] = $u->email;
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix Passwords - Rakha App</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            padding: 30px;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        h1 {
            font-size: 1.8rem;
            color: #f87171;
            margin-bottom: 8px;
        }

        h2 {
            font-size: 1.2rem;
            color: #94a3b8;
            margin: 20px 0 10px;
        }

        .card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .badge-warning {
            background: #92400e;
            color: #fef3c7;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .badge-success {
            background: #14532d;
            color: #bbf7d0;
            padding: 12px 16px;
            border-radius: 8px;
        }

        .badge-danger {
            background: #7f1d1d;
            color: #fecaca;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
        }

        .stat {
            display: inline-block;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 6px;
            padding: 8px 16px;
            margin: 4px;
            font-size: 0.9rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 0.9rem;
        }

        th {
            background: #0f172a;
            color: #7dd3fc;
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #334155;
        }

        td {
            padding: 9px 12px;
            border: 1px solid #334155;
        }

        tr:nth-child(even) td {
            background: #1e293b;
        }

        tr:nth-child(odd) td {
            background: #172032;
        }

        code {
            background: #0f172a;
            border: 1px solid #475569;
            border-radius: 4px;
            padding: 2px 8px;
            color: #4ade80;
            font-size: 1rem;
        }

        .step {
            background: #0f172a;
            border-left: 4px solid #38bdf8;
            padding: 12px 16px;
            border-radius: 0 8px 8px 0;
            margin-bottom: 12px;
        }

        .step strong {
            color: #7dd3fc;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="card">
            <h1>🔧 Fix Password Tool</h1>
            <p style="color:#94a3b8; margin-bottom:16px;">Rakha Nusantaramedika App — Production Database</p>

            <div class="badge-warning">
                <span style="font-size:1.3rem;">⚠️</span>
                <div>
                    <strong>PERINGATAN KEAMANAN:</strong><br>
                    Segera hapus file <code>fix_production.php</code> dari folder <code>public_html/</code>
                    setelah script ini berhasil dijalankan!
                </div>
            </div>

            <h2>📊 Statistik</h2>
            <div>
                <span class="stat">Total user diperiksa: <strong><?= count($users) ?></strong></span>
                <span class="stat" style="color:#4ade80">Password Bcrypt (OK): <strong><?= count($ok) ?></strong></span>
                <span class="stat" style="color:#f87171">Password bermasalah (di-reset): <strong><?= count($fixed) ?></strong></span>
            </div>
        </div>

        <?php if (count($fixed) > 0): ?>
            <div class="card">
                <div class="badge-danger">
                    ✅ <strong><?= count($fixed) ?> user berhasil di-reset</strong> passwordnya ke password default:
                    <code><?= htmlspecialchars($defaultPassword) ?></code>
                </div>
                <p style="margin-bottom:12px; color:#94a3b8;">Daftar akun yang password-nya sudah di-reset:</p>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Hash Lama (Prefix)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fixed as $f): ?>
                            <tr>
                                <td><?= $f['id'] ?></td>
                                <td><?= htmlspecialchars($f['name']) ?></td>
                                <td><?= htmlspecialchars($f['email']) ?></td>
                                <td><code style="color:#f59e0b"><?= htmlspecialchars($f['role']) ?></code></td>
                                <td><code style="color:#f87171"><?= htmlspecialchars($f['old_prefix']) ?></code></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="badge-success">
                    ✅ Semua <strong><?= count($ok) ?></strong> user sudah menggunakan Bcrypt. Tidak ada yang perlu diperbaiki.
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>📋 Langkah Selanjutnya</h2>
            <?php if (count($fixed) > 0): ?>
                <div class="step">
                    <strong>1. Password default semua akun yang di-reset:</strong><br>
                    <code><?= htmlspecialchars($defaultPassword) ?></code>
                </div>
                <div class="step">
                    <strong>2. Minta setiap karyawan untuk login dan segera ganti password mereka.</strong>
                </div>
            <?php endif; ?>
            <div class="step" style="border-left-color: #f87171;">
                <strong style="color:#f87171">WAJIB: Hapus file <code>fix_production.php</code> dari folder public_html sekarang!</strong><br>
                Akses ke script ini berbahaya jika dibiarkan terbuka di internet.
            </div>
        </div>
    </div>
</body>

</html>