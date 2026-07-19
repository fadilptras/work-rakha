<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_approvers_and_clears_cache()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'user']);
        
        $approver1 = User::factory()->create(['role' => 'user']);
        $approver2 = User::factory()->create(['role' => 'user']);
        $approver3 = User::factory()->create(['role' => 'user']);
        $approver4 = User::factory()->create(['role' => 'admin']);

        // Set fake cache to verify it gets deleted
        Cache::put('karyawan_list_dropdown', 'fake_data_1', 3600);
        Cache::put('approvers_list_dropdown', 'fake_data_2', 3600);
        Cache::put('admins_list_dropdown', 'fake_data_3', 3600);

        $this->actingAs($admin);

        $response = $this->post('/admin/pengajuan-dana/set-approvers', [
            'approver_1' => [$employee->id => $approver1->id],
            'approver_2' => [$employee->id => $approver2->id],
            'approver_3' => [$employee->id => $approver3->id],
            'approver_4' => [$employee->id => $approver4->id],
        ]);

        $response->assertRedirect('/admin/pengajuan-dana/set-approvers');
        $response->assertSessionHas('success');

        // Assert database updated
        $employee->refresh();
        $this->assertEquals($approver1->id, $employee->approver_1_id);
        $this->assertEquals($approver2->id, $employee->approver_2_id);
        $this->assertEquals($approver3->id, $employee->approver_dana_3_id);
        $this->assertEquals($approver4->id, $employee->approver_dana_4_id);

        // Assert caches are cleared
        $this->assertFalse(Cache::has('karyawan_list_dropdown'));
        $this->assertFalse(Cache::has('approvers_list_dropdown'));
        $this->assertFalse(Cache::has('admins_list_dropdown'));
    }
}
