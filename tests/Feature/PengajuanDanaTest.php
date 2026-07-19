<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PengajuanDana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengajuanDanaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_user_can_create_pengajuan_dana()
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user);

        $response = $this->post('/users/pengajuan-dana/simpan', [
            'judul_pengajuan' => 'Pembelian Laptop',
            'deskripsi' => 'Laptop untuk tim dev',
            'nama_bank' => 'BCA',
            'no_rekening' => '1234567890',
            'nama_rek' => 'Budi',
            'total_dana' => 15000000,
            'rincian' => [
                ['nama_item' => 'Laptop', 'jumlah' => 15000000]
            ],
            // Simulate missing file uploads for simplicity
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pengajuan_dana', [
            'judul_pengajuan' => 'Pembelian Laptop',
            'user_id' => $user->id,
            'status' => 'diajukan'
        ]);
    }

    public function test_approver_1_can_approve_pengajuan()
    {
        $approver1 = User::factory()->create(['role' => 'user']);
        $user = User::factory()->create([
            'role' => 'user', 
            'approver_1_id' => $approver1->id
        ]);
        
        $pengajuan = PengajuanDana::create([
            'user_id' => $user->id,
            'judul_pengajuan' => 'Test',
            'total_dana' => 1000,
            'status' => 'diajukan',
            'approver_1_id' => $approver1->id,
            'approver_1_status' => 'menunggu'
        ]);

        $this->actingAs($approver1);

        $response = $this->post("/users/pengajuan-dana/{$pengajuan->id}/approve", [
            'catatan_persetujuan' => 'Ok disetujui'
        ]);

        $response->assertRedirect();
        
        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->approver_1_status);
        // Should move to next status based on controller logic
    }

    public function test_approver_3_cannot_approve_normally_but_can_upload_bukti()
    {
        $approver3 = User::factory()->create(['role' => 'user']);
        $user = User::factory()->create([
            'role' => 'user', 
            'approver_dana_3_id' => $approver3->id
        ]);
        
        $pengajuan = PengajuanDana::create([
            'user_id' => $user->id,
            'judul_pengajuan' => 'Test',
            'total_dana' => 1000,
            'status' => 'proses_pembayaran',
            'approver_1_id' => null,
            'approver_2_id' => null,
            'approver_3_id' => $approver3->id,
            'approver_3_status' => 'menunggu'
        ]);

        $this->actingAs($approver3);

        // 1. Coba approve normal (Harus dilarang oleh policy baru)
        $response = $this->post("/users/pengajuan-dana/{$pengajuan->id}/approve", [
            'catatan_persetujuan' => 'Ini tidak boleh'
        ]);
        $response->assertStatus(403); // Forbidden

        // 2. Upload bukti transfer (Diizinkan)
        $file = UploadedFile::fake()->image('bukti.jpg');
        $response = $this->post("/users/pengajuan-dana/{$pengajuan->id}/upload-bukti-transfer", [
            'bukti_transfer' => $file
        ]);

        $response->assertRedirect();
        
        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->approver_3_status);
        $this->assertNotNull($pengajuan->bukti_transfer);
    }
}
