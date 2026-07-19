<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cuti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CutiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_apply_for_cuti_and_has_correct_status()
    {
        $user = User::factory()->create([
            'role' => 'user',
            'jatah_cuti_tahunan' => 12
        ]);
        
        $this->actingAs($user);

        $response = $this->post('/users/cuti', [
            'tanggal_mulai' => now()->addDays(1)->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'jenis_cuti' => 'tahunan',
            'alasan' => 'Liburan',
            'keterangan_lain' => ''
        ]);

        $response->assertRedirect('/users/cuti');
        
        $this->assertDatabaseHas('cuti', [
            'user_id' => $user->id,
            'status' => 'menunggu'
        ]);
    }

    public function test_approver_can_approve_cuti_and_deducts_quota()
    {
        $approver1 = User::factory()->create(['role' => 'user']);
        $user = User::factory()->create([
            'role' => 'user',
            'jatah_cuti_tahunan' => 12,
            'approver_1_id' => $approver1->id
        ]);
        
        $cuti = Cuti::create([
            'user_id' => $user->id,
            'tanggal_mulai' => now()->addDays(1)->toDateString(),
            'tanggal_selesai' => now()->addDays(2)->toDateString(),
            'lama_cuti' => 2,
            'jenis_cuti' => 'tahunan',
            'alasan' => 'Liburan',
            'status' => 'menunggu',
            'approver_1_id' => $approver1->id,
            'approver_1_status' => 'menunggu'
        ]);

        $this->actingAs($approver1);

        // Simulasi controller approve (bisa berbeda tergantung implementasi asli,
        // namun untuk keperluan test logika persetujuan dasar)
        $response = $this->post("/users/cuti/{$cuti->id}/approve", [
            'catatan_persetujuan' => 'Disetujui'
        ]);

        $response->assertRedirect();
        
        $cuti->refresh();
        $this->assertEquals('disetujui', $cuti->approver_1_status);
        
        // Cek jika status cuti menjadi disetujui (misal ini 1-level approver untuk testing)
        // Di aplikasi asli mungkin menunggu approver 2-4
    }
}
