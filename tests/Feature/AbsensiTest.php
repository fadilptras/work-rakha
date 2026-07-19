<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Absensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clock_in()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        // Simulasi koordinat valid (dekat dengan kantor jika ada validasi jarak)
        $response = $this->post('/users/absensi/store', [
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
        ]);

        $response->assertRedirect('/users/absensi');
        
        // Assert masuk ke database
        $this->assertDatabaseHas('absensi', [
            'user_id' => $user->id,
        ]);
        
        // Assert tidak bisa absen masuk 2 kali di hari yang sama
        $response2 = $this->post('/users/absensi/store', [
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
        ]);
        
        $response2->assertSessionHas('error'); // Asumsi controller mengembalikan error jika sudah absen
    }

    public function test_user_can_clock_out()
    {
        $user = User::factory()->create(['role' => 'user']);
        
        // Buat record absen masuk hari ini
        $absensi = Absensi::create([
            'user_id' => $user->id,
            'jam_masuk' => now()->subHours(8), // Masuk 8 jam yang lalu
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
            'latitude_masuk' => '-6.200000',
            'longitude_masuk' => '106.816666',
        ]);

        $this->actingAs($user);

        $response = $this->post('/users/absensi/checkout', [
            'latitude' => '-6.200000',
            'longitude' => '106.816666',
        ]);

        $response->assertRedirect('/users/absensi');
        
        $absensi->refresh();
        $this->assertNotNull($absensi->jam_keluar);
    }
}
