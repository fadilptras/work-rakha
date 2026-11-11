<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMove;
use App\Models\User;
use App\Models\WsMutation;
use App\Services\StockMoveService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WarehouseMutationFlexibilityTest extends TestCase
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

        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('customer_name')->unique();
            $t->string('customer_type')->nullable();
            $t->string('sales_customer_name')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->json('customer_address')->nullable();
            $t->string('area')->nullable();
            $t->boolean('is_deleted')->default(false);
            $t->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('supplier_name')->unique();
            $t->string('supplier_type')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->json('supplier_address')->nullable();
            $t->string('pic_name')->nullable();
            $t->string('pic_phone')->nullable();
            $t->boolean('is_deleted')->default(false);
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

        Schema::create('document_sequences', function (Blueprint $t) {
            $t->id();
            $t->string('type');
            $t->integer('year')->nullable();
            $t->integer('last_no')->default(0);
            $t->timestamps();
            $t->unique(['type', 'year']);
        });

        Schema::create('ws_mutation', function (Blueprint $t) {
            $t->id();
            $t->string('doc_no')->nullable();
            $t->string('direction');
            $t->date('mutation_date');
            $t->unsignedBigInteger('customer_id')->nullable();
            $t->unsignedBigInteger('supplier_id')->nullable();
            $t->text('ship_address')->nullable();
            $t->string('expedition')->nullable();
            $t->string('po_no')->nullable();
            $t->string('purpose')->nullable();
            $t->string('source')->default('manual');
            $t->string('source_no')->nullable();
            $t->string('status')->default('posted');
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });
    }

    private function manager(): User
    {
        return User::create([
            'name' => 'Admin Gudang',
            'email' => 'gudang-'.uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'role' => 'user',
            'jabatan' => 'Admin Gudang',
            'divisi' => 'Finance dan Gudang',
        ]);
    }

    private function product(float $stock = 10): Product
    {
        return Product::create([
            'product_code' => 'TST-'.uniqid(),
            'product_name' => 'Produk Uji',
            'unit' => 'Box',
            'pcs_per_unit' => 10,
            'fill_unit' => 'Pcs',
            'stock' => $stock,
        ]);
    }

    public function test_typed_customer_is_saved_with_address_note_and_stock_reduced()
    {
        $this->actingAs($this->manager());
        $product = $this->product(10);

        $response = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-TEST-001',
            'purpose' => 'Penjualan',
            'partner_name' => 'RS Pasien Baru',
            'ship_address' => 'Jl. Kesehatan No. 1',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [2],
            'batch_no' => [''],
            'expiry_date' => [''],
            'keterangan' => ['Untuk pasien A'],
        ]);

        $response->assertRedirect();
        $customer = Customer::where('customer_name', 'RS Pasien Baru')->first();
        $this->assertNotNull($customer);
        $this->assertSame('Jl. Kesehatan No. 1', $customer->customer_address[0]['address']);
        $this->assertSame(8.0, (float) $product->fresh()->stock);

        $doc = WsMutation::where('doc_no', 'SJ-TEST-001')->first();
        $this->assertNotNull($doc);
        $this->assertSame($customer->id, $doc->customer_id);
        $this->assertStringContainsString('Untuk pasien A', $doc->activeMoves()->first()->reason);
    }

    public function test_customer_and_supplier_are_optional_for_in_and_out_mutations()
    {
        $this->actingAs($this->manager());
        $inProduct = $this->product(0);
        $outProduct = $this->product(10);

        $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-TEST-NOPARTY-IN',
            'purpose' => 'Pembelian',
            'partner_name' => '',
            'arah' => ['masuk'],
            'product_id' => [$inProduct->id],
            'qty' => [3],
            'batch_no' => [''],
            'expiry_date' => [''],
            'keterangan' => [''],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $inDoc = WsMutation::where('doc_no', 'SJ-TEST-NOPARTY-IN')->firstOrFail();
        $this->assertNull($inDoc->supplier_id);
        $this->assertNull($inDoc->customer_id);
        $this->assertSame(3.0, (float) $inProduct->fresh()->stock);

        $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-TEST-NOPARTY-OUT',
            'purpose' => 'Penjualan',
            'partner_name' => '',
            'arah' => ['keluar'],
            'product_id' => [$outProduct->id],
            'qty' => [2],
            'batch_no' => [''],
            'expiry_date' => [''],
            'keterangan' => [''],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $outDoc = WsMutation::where('doc_no', 'SJ-TEST-NOPARTY-OUT')->firstOrFail();
        $this->assertNull($outDoc->supplier_id);
        $this->assertNull($outDoc->customer_id);
        $this->assertSame(8.0, (float) $outProduct->fresh()->stock);

        // Tidak ada master customer/supplier baru yang dibuat saat field dikosongkan.
        $this->assertSame(0, \App\Models\Customer::count());
        $this->assertSame(0, \App\Models\Supplier::count());
    }

    public function test_delete_hides_document_and_restores_stock_without_hard_delete()
    {
        $this->actingAs($this->manager());
        $product = $this->product(10);
        $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-TEST-002',
            'purpose' => 'Penjualan',
            'partner_name' => 'RS Hapus',
            'ship_address' => 'Jl. Hapus',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [4],
            'batch_no' => [''],
            'expiry_date' => [''],
            'keterangan' => [''],
        ])->assertRedirect();

        $doc = WsMutation::where('doc_no', 'SJ-TEST-002')->firstOrFail();
        $move = $doc->activeMoves()->firstOrFail();

        $this->delete(route('warehouse.mutation.destroy', $move))->assertRedirect();

        $this->assertSame(10.0, (float) $product->fresh()->stock);
        $this->assertSame(StockMove::STATUS_CANCELLED, $move->fresh()->status);
        $this->assertSame(WsMutation::STATUS_VOID, $doc->fresh()->status);
        $this->assertDatabaseHas('stock_moves', [
            'ref_type' => 'stock_move_reversal',
            'ref_id' => $move->id,
        ]);
        $this->assertSame(0, StockMove::visible()->where('ref_id', $doc->id)->count());
    }

    public function test_document_edit_replaces_all_lines_and_updates_header()
    {
        $this->actingAs($this->manager());
        $product = $this->product(10);
        $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-TEST-003',
            'purpose' => 'Penjualan',
            'partner_name' => 'RS Edit',
            'ship_address' => 'Jl. Lama',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [2],
            'batch_no' => [''],
            'expiry_date' => [''],
            'keterangan' => ['Lama'],
        ])->assertRedirect();

        $doc = WsMutation::where('doc_no', 'SJ-TEST-003')->firstOrFail();
        $oldMove = $doc->activeMoves()->firstOrFail();

        $this->put(route('warehouse.mutation.doc.update', $doc), [
            'tanggal' => today()->toDateString(),
            'doc_no' => 'SJ-TEST-003-EDIT',
            'purpose' => 'Penjualan',
            'partner_name' => 'RS Edit',
            'ship_address' => 'Jl. Baru',
            'expedition' => 'JNE',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [3],
            'batch_no' => [''],
            'expiry_date' => [''],
            'keterangan' => ['Baru'],
        ])->assertRedirect();

        $doc->refresh();
        $this->assertSame('SJ-TEST-003-EDIT', $doc->doc_no);
        $this->assertSame('Jl. Baru', $doc->ship_address);
        $this->assertSame('JNE', $doc->expedition);
        $this->assertSame(StockMove::STATUS_CANCELLED, $oldMove->fresh()->status);
        $this->assertSame(1, $doc->activeMoves()->count());
        $newMove = $doc->activeMoves()->first();
        $this->assertSame(3.0, (float) $newMove->qty);
        $this->assertSame(7.0, (float) $product->fresh()->stock);
        $this->assertStringContainsString('Baru', $newMove->reason);
    }

    public function test_sj_pages_render_and_edit_form_loads_saved_document()
    {
        $this->actingAs($this->manager());
        $product = $this->product(10);
        $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-TEST-RENDER',
            'purpose' => 'Penjualan',
            'partner_name' => 'RS Render',
            'ship_address' => 'Jl. Render',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [2],
            'batch_no' => [''],
            'expiry_date' => [''],
            'keterangan' => ['Catatan render'],
        ])->assertRedirect();

        $doc = WsMutation::where('doc_no', 'SJ-TEST-RENDER')->firstOrFail();

        $this->get(route('warehouse.sj.index', ['tab' => 'keluar']))->assertOk();
        $this->get(route('warehouse.index', ['tab' => 'keluar']))->assertOk();
        $this->get(route('warehouse.mutation.index', ['tab' => 'masuk']))
            ->assertRedirect(route('warehouse.index', ['tab' => 'masuk']));

        $this->get(route('warehouse.index', ['tab' => 'masuk']))
            ->assertOk()
            ->assertSee('Ketik Nama Supplier (Opsional / Lain-lain)', false);

        $edit = $this->get(route('warehouse.mutation.doc.edit', $doc))->assertOk();
        $edit->assertSee('SJ-TEST-RENDER', false);
        $edit->assertSee('Catatan render', false);
    }

    public function test_same_batch_receipt_is_aggregated()
    {
        $product = $this->product(0);
        $svc = app(StockMoveService::class);

        foreach ([5, 7] as $qty) {
            $move = $svc->createMove([
                'product_id' => $product->id,
                'qty' => $qty,
                'type' => StockMove::TYPE_RECEIPT,
                'batch_no' => 'B-SAMA',
                'expiry_date' => '2027-12-31',
                'reason' => 'Uji batch',
            ]);
            $svc->validateMove($move);
        }

        $this->assertSame(1, ProductBatch::where('product_id', $product->id)->where('batch_no', 'B-SAMA')->count());
        $batch = ProductBatch::where('product_id', $product->id)->where('batch_no', 'B-SAMA')->first();
        $this->assertSame(12.0, (float) $batch->qty_current);
        $this->assertSame(12.0, (float) $product->fresh()->stock);
    }
}
