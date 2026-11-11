<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMove;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SetOpeningStockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::getConnection()->getPdo()->sqliteCreateFunction('YEAR', fn ($date) => (int) date('Y', strtotime((string) $date)), 1);
        }

        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->rememberToken();
            $t->string('role')->nullable();
            $t->string('jabatan')->nullable();
            $t->string('divisi')->nullable();
            $t->boolean('is_kepala_divisi')->default(false);
            $t->timestamps();
        });

        Schema::create('warehouses', function (Blueprint $t) {
            $t->id();
            $t->string('code')->nullable();
            $t->string('name')->nullable();
            $t->text('address')->nullable();
            $t->boolean('is_default')->default(false);
            $t->timestamps();
        });

        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->string('product_code')->nullable();
            $t->string('product_code_clean')->nullable();
            $t->string('product_name')->nullable();
            $t->string('product_name_clean')->nullable();
            $t->string('match_key')->nullable();
            $t->string('unit')->nullable();
            $t->integer('pcs_per_unit')->nullable();
            $t->string('fill_unit')->nullable();
            $t->decimal('stock', 15, 2)->default(0);
            $t->decimal('stock_po', 15, 2)->default(0);
            $t->decimal('min_stock', 15, 2)->nullable();
            $t->decimal('reorder_qty', 15, 2)->nullable();
            $t->boolean('is_deleted')->default(false);
            $t->timestamps();
        });

        Schema::create('stock_moves', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->unsignedBigInteger('product_id');
            $t->string('batch_no')->nullable();
            $t->string('doc_no')->nullable();
            $t->string('partner')->nullable();
            $t->string('po_no')->nullable();
            $t->string('purpose')->nullable();
            $t->text('ship_address')->nullable();
            $t->string('expedition')->nullable();
            $t->decimal('qty', 15, 2);
            $t->string('uom')->nullable();
            $t->string('from_location')->nullable();
            $t->string('to_location')->nullable();
            $t->string('type');
            $t->string('status');
            $t->string('ref_type')->nullable();
            $t->unsignedBigInteger('ref_id')->nullable();
            $t->date('expiry_date')->nullable();
            $t->text('reason')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->unsignedBigInteger('validated_by')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->timestamps();
        });

        Schema::create('stock_opnames', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_id')->nullable();
            $t->date('opname_date')->nullable();
            $t->decimal('system_qty', 15, 2)->default(0);
            $t->decimal('counted_qty', 15, 2)->default(0);
            $t->decimal('difference_qty', 15, 2)->default(0);
            $t->string('status')->default('draft');
            $t->integer('revision')->default(1);
            $t->text('note')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        Schema::create('document_sequences', function (Blueprint $t) {
            $t->id();
            $t->string('type');
            $t->integer('year')->nullable();
            $t->integer('last_no')->default(0);
            $t->timestamps();
            $t->unique(['type', 'year']);
        });

        Schema::create('product_batches', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('product_id');
            $t->string('batch_no')->nullable();
            $t->dateTime('receipt_date')->nullable();
            $t->date('expiry_date')->nullable();
            $t->decimal('qty_initial', 15, 2)->default(0);
            $t->decimal('qty_current', 15, 2)->default(0);
            $t->timestamps();
        });
    }

    private function stockManager(): User
    {
        return User::create([
            'name' => 'Admin Gudang',
            'email' => 'gudang-'.uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'role' => 'user',
            'divisi' => 'Finance dan Gudang',
            'jabatan' => 'Admin Gudang',
        ]);
    }

    private function makeProduct(int $stock = 10): Product
    {
        return Product::create([
            'product_code' => 'TST-' . fake()->unique()->numberBetween(1000, 9999),
            'product_name' => 'Produk Uji Stok',
            'unit' => 'Box',
            'pcs_per_unit' => 10,
            'fill_unit' => 'Pcs',
            'stock' => $stock,
        ]);
    }

    public function test_manager_can_set_opening_stock_upwards_and_move_is_auto_validated()
    {
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $response = $this->post(route('warehouse.product.set-stock'), [
            'product_id' => $product->id,
            'counted_qty' => 25,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(25, (int) $product->fresh()->stock);

        $move = StockMove::where('product_id', $product->id)->latest('id')->first();
        $this->assertNotNull($move);
        $this->assertEquals(StockMove::TYPE_ADJUSTMENT, $move->type);
        $this->assertEquals(StockMove::STATUS_DONE, $move->status);
        $this->assertEquals(15, (float) $move->qty);
        $this->assertEquals(StockMove::LOC_ADJUSTMENT, $move->from_location);
        $this->assertEquals(StockMove::LOC_STOCK, $move->to_location);
        // Reason terpisah dari riwayat Opname ("Opname: ...").
        $this->assertStringStartsWith('Stok awal:', (string) $move->reason);
        $this->assertDatabaseMissing('stock_moves', [
            'product_id' => $product->id,
            'reason' => 'Opname: %',
        ]);
    }

    public function test_manager_can_set_opening_stock_downwards()
    {
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $response = $this->post(route('warehouse.product.set-stock'), [
            'product_id' => $product->id,
            'counted_qty' => 4,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(4, (int) $product->fresh()->stock);

        $move = StockMove::where('product_id', $product->id)->latest('id')->first();
        $this->assertEquals(6, (float) $move->qty);
        $this->assertEquals(StockMove::LOC_STOCK, $move->from_location);
        $this->assertEquals(StockMove::LOC_ADJUSTMENT, $move->to_location);
    }

    public function test_same_stock_reports_no_adjustment_needed()
    {
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $response = $this->post(route('warehouse.product.set-stock'), [
            'product_id' => $product->id,
            'counted_qty' => 10,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals(0, StockMove::where('product_id', $product->id)->count());
    }

    public function test_user_without_manage_access_gets_403()
    {
        // Divisi marketing boleh baca gudang, tapi tidak boleh mengatur stok.
        $user = User::factory()->create([
            'role' => 'user',
            'divisi' => 'Marketing dan Operasional',
            'jabatan' => 'Sales',
        ]);
        $this->actingAs($user);
        $product = $this->makeProduct(10);

        $response = $this->post(route('warehouse.product.set-stock'), [
            'product_id' => $product->id,
            'counted_qty' => 25,
        ]);

        $response->assertForbidden();
        $this->assertEquals(10, (int) $product->fresh()->stock);
    }

    public function test_guest_is_redirected_to_login()
    {
        $response = $this->post(route('warehouse.product.set-stock'), [
            'product_id' => 1,
            'counted_qty' => 5,
        ]);

        $response->assertRedirect('/login');
    }

    public function test_validation_requires_existing_product_and_non_negative_qty()
    {
        $this->actingAs($this->stockManager());

        $response = $this->post(route('warehouse.product.set-stock'), [
            'product_id' => 999999,
            'counted_qty' => -3,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['product_id', 'counted_qty']);
        $this->assertEquals(0, StockMove::count());
    }
}
