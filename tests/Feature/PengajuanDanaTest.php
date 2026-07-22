<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PengajuanDana;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PengajuanDanaNotification;
use Tests\TestCase;

class PengajuanDanaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Notification::fake();
    }

    private function createUserWithApprovers($app1, $app2, $app3, $app4)
    {
        return User::factory()->create([
            'role' => 'user',
            'approver_dana_1_id' => $app1 ? $app1->id : null,
            'approver_dana_2_id' => $app2 ? $app2->id : null,
            'approver_dana_3_id' => $app3 ? $app3->id : null,
            'approver_dana_4_id' => $app4 ? $app4->id : null,
        ]);
    }

    public function test_user_can_create_pengajuan_dana_with_correct_initial_status_and_approvers()
    {
        $app1 = User::factory()->create(['role' => 'user']);
        $app2 = User::factory()->create(['role' => 'user']);
        $user = $this->createUserWithApprovers($app1, $app2, null, null);
        
        $this->actingAs($user);

        $response = $this->post('/pengajuan-dana', [
            'judul_pengajuan' => 'Pembelian Laptop',
            'deskripsi' => 'Laptop untuk tim dev',
            'divisi' => 'IT',
            'nama_bank' => 'BCA',
            'no_rekening' => '1234567890',
            'nama_rek' => 'Budi',
            'total_dana' => 15000000,
            'rincian' => [
                ['nama_item' => 'Laptop', 'jumlah' => 15000000]
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pengajuan_dana', [
            'judul_pengajuan' => 'Pembelian Laptop',
            'user_id' => $user->id,
            'status' => 'diajukan',
            'approver_dana_1_id' => $app1->id,
            'approver_1_status' => 'menunggu',
            'approver_dana_2_id' => $app2->id,
            'approver_2_status' => 'menunggu',
            'approver_dana_3_id' => null,
            'approver_3_status' => 'skipped',
            'approver_dana_4_id' => null,
            'approver_4_status' => 'skipped',
        ]);

        // Assert notification sent to approver 1
        Notification::assertSentTo(
            [$app1], PengajuanDanaNotification::class
        );
    }

    public function test_approver_1_can_approve_and_status_changes_to_diproses()
    {
        $app1 = User::factory()->create(['role' => 'user']);
        $app2 = User::factory()->create(['role' => 'user']);
        $user = $this->createUserWithApprovers($app1, $app2, null, null);
        
        $pengajuan = PengajuanDana::create([
            'user_id' => $user->id,
            'judul_pengajuan' => 'Test',
            'divisi' => 'IT',
            'nama_bank' => 'BCA',
            'no_rekening' => '123',
            'nama_rek' => 'Test',
            'total_dana' => 1000,
            'status' => 'diajukan',
            'approver_dana_1_id' => $app1->id,
            'approver_1_status' => 'menunggu',
            'approver_dana_2_id' => $app2->id,
            'approver_2_status' => 'menunggu',
            'approver_dana_3_id' => null,
            'approver_3_status' => 'skipped',
            'approver_dana_4_id' => null,
            'approver_4_status' => 'skipped',
        ]);

        $this->actingAs($app1);

        $response = $this->post("/pengajuan-dana/{$pengajuan->id}/approve", [
            'catatan_persetujuan' => 'Ok disetujui'
        ]);

        $response->assertRedirect();
        
        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->approver_1_status);
        $this->assertEquals('diproses', $pengajuan->status);

        // Assert notification sent to approver 2
        Notification::assertSentTo(
            [$app2], PengajuanDanaNotification::class
        );
    }

    public function test_admin_override_mark_as_paid_completes_if_no_approver_4()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        
        $pengajuan = PengajuanDana::create([
            'user_id' => $user->id,
            'judul_pengajuan' => 'Test Admin Override',
            'divisi' => 'IT',
            'nama_bank' => 'BCA',
            'no_rekening' => '123',
            'nama_rek' => 'Test',
            'total_dana' => 1000,
            'status' => 'proses_pembayaran',
            'approver_dana_1_id' => null,
            'approver_1_status' => 'skipped',
            'approver_dana_2_id' => null,
            'approver_2_status' => 'skipped',
            'approver_dana_3_id' => null,
            'approver_3_status' => 'menunggu', // waiting for finance
            'approver_dana_4_id' => null,
            'approver_4_status' => 'skipped', // no approver 4
        ]);

        $this->actingAs($admin);

        $response = $this->post("/admin/pengajuan-dana/{$pengajuan->id}/mark-as-paid", [
            'catatan_admin' => 'Telah ditransfer'
        ]);

        $response->assertRedirect();
        
        $pengajuan->refresh();
        $this->assertEquals('disetujui', $pengajuan->approver_3_status);
        $this->assertEquals($admin->id, $pengajuan->approver_dana_3_id);
        $this->assertEquals('selesai', $pengajuan->status);

        // Assert notification sent to user for completion
        Notification::assertSentTo(
            [$user], PengajuanDanaNotification::class
        );
    }

    public function test_any_approver_can_reject_and_status_becomes_ditolak()
    {
        $app1 = User::factory()->create(['role' => 'user']);
        $user = $this->createUserWithApprovers($app1, null, null, null);
        
        $pengajuan = PengajuanDana::create([
            'user_id' => $user->id,
            'judul_pengajuan' => 'Test Reject',
            'divisi' => 'IT',
            'nama_bank' => 'BCA',
            'no_rekening' => '123',
            'nama_rek' => 'Test',
            'total_dana' => 1000,
            'status' => 'diajukan',
            'approver_dana_1_id' => $app1->id,
            'approver_1_status' => 'menunggu',
            'approver_dana_2_id' => null,
            'approver_2_status' => 'skipped',
            'approver_dana_3_id' => null,
            'approver_3_status' => 'skipped',
            'approver_dana_4_id' => null,
            'approver_4_status' => 'skipped',
        ]);

        $this->actingAs($app1);

        $response = $this->post("/pengajuan-dana/{$pengajuan->id}/reject", [
            'catatan_penolakan' => 'Tidak valid'
        ]);

        $response->assertRedirect();
        
        $pengajuan->refresh();
        $this->assertEquals('ditolak', $pengajuan->approver_1_status);
        $this->assertEquals('ditolak', $pengajuan->status);

        // Assert notification sent to user for rejection
        Notification::assertSentTo(
            [$user], PengajuanDanaNotification::class
        );
    }
}
