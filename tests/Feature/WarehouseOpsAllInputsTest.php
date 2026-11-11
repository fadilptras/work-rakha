<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMove;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WsMutation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WarehouseOpsAllInputsTest extends TestCase
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
    }

    private function manager(): User
    {
        return User::create([
            'name' => 'Admin Ops Gudang',
            'email' => 'ops-'.uniqid().'@example.test',
            'password' => bcrypt('secret'),
            'role' => 'user',
            'jabatan' => 'Admin Gudang',
            'divisi' => 'Finance dan Gudang',
        ]);
    }

    private function product(string $code = 'PROD-01', string $name = 'Alkohol 70% 1 Liter', float $stock = 50): Product
    {
        return Product::create([
            'product_code' => $code,
            'product_name' => $name,
            'unit' => 'Botol',
            'pcs_per_unit' => 1,
            'fill_unit' => 'Botol',
            'stock' => $stock,
        ]);
    }

    /** 1. Test render all 5 tabs in Warehouse Ops */
    public function test_warehouse_ops_renders_all_tabs_correctly()
    {
        $this->actingAs($this->manager());
        $p = $this->product('PROD-TAB', 'Produk Tab Test', 10);

        // Tab Data Product
        $res0 = $this->get(route('warehouse.index', ['tab' => 'data-product']));
        $res0->assertOk()->assertSee('Product List');

        // Tab Monitoring Product
        $res1 = $this->get(route('warehouse.index', ['tab' => 'produk']));
        $res1->assertOk();

        // Tab Mutasi
        $res2 = $this->get(route('warehouse.index', ['tab' => 'mutasi']));
        $res2->assertOk()->assertSee('Data Mutation');

        // Tab Keluar
        $res3 = $this->get(route('warehouse.index', ['tab' => 'keluar']));
        $res3->assertOk();

        // Tab Masuk
        $res4 = $this->get(route('warehouse.index', ['tab' => 'masuk']));
        $res4->assertOk();
    }

    /** 2. Test Input Barang Masuk dari Supplier Baru dengan Batch dan Exp Date */
    public function test_input_barang_masuk_creates_supplier_batch_and_increases_stock()
    {
        $this->actingAs($this->manager());
        $product = $this->product('P-IN-01', 'Infus RL 500ml', 20);

        $response = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-SUPP-123',
            'purpose' => 'Pembelian',
            'partner_name' => 'PT Sumber Medika Farma',
            'ship_address' => 'Kawasan Industri Cikarang Blok B2',
            'arah' => ['masuk'],
            'product_id' => [$product->id],
            'qty' => [30],
            'batch_no' => ['BATCH-SMF-2026'],
            'expiry_date' => ['2028-06-30'],
            'keterangan' => ['Penerimaan PO-2026-001'],
        ]);

        $response->assertRedirect(route('warehouse.index', ['tab' => 'masuk']));
        $response->assertSessionHas('success');

        // Verify Product stock updated 20 + 30 = 50
        $this->assertEquals(50.0, (float) $product->fresh()->stock);

        // Verify Supplier auto-created
        $supplier = Supplier::where('supplier_name', 'PT Sumber Medika Farma')->first();
        $this->assertNotNull($supplier);

        // Verify WsMutation
        $doc = WsMutation::where('doc_no', 'SJ-SUPP-123')->first();
        $this->assertNotNull($doc);
        $this->assertEquals(WsMutation::DIRECTION_IN, $doc->direction);
        $this->assertEquals($supplier->id, $doc->supplier_id);

        // Verify StockMove
        $move = StockMove::where('ref_id', $doc->id)->first();
        $this->assertNotNull($move);
        $this->assertEquals(StockMove::TYPE_RECEIPT, $move->type);
        $this->assertEquals(30.0, (float) $move->qty);
        $this->assertEquals('BATCH-SMF-2026', $move->batch_no);

        // Verify ProductBatch created
        $batch = ProductBatch::where('product_id', $product->id)->where('batch_no', 'BATCH-SMF-2026')->first();
        $this->assertNotNull($batch);
        $this->assertEquals(30.0, (float) $batch->qty_current);
    }

    /** 3. Test Input Barang Keluar Penjualan ke Customer Baru dengan Ekspedisi & Alamat */
    public function test_input_barang_keluar_penjualan_reduces_stock_creates_customer_and_moves()
    {
        $this->actingAs($this->manager());
        $product = $this->product('P-OUT-01', 'Spuit 3cc Terumo', 100);

        $response = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-OUT-888',
            'po_no' => 'PO-CUST-999',
            'purpose' => 'Penjualan',
            'partner_name' => 'RS Permata Kasih',
            'ship_address' => 'Jl. Jenderal Sudirman No. 45',
            'expedition' => 'Kurir Internal',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [15],
            'batch_no' => ['B-TERUMO-01'],
            'expiry_date' => ['2027-12-31'],
            'keterangan' => ['Pesanan Poli Bedah'],
        ]);

        $response->assertRedirect(route('warehouse.index', ['tab' => 'keluar']));
        $response->assertSessionHas('success');

        // Stock decreased: 100 - 15 = 85
        $this->assertEquals(85.0, (float) $product->fresh()->stock);

        // Customer auto-saved
        $cust = Customer::where('customer_name', 'RS Permata Kasih')->first();
        $this->assertNotNull($cust);

        // WsMutation record
        $doc = WsMutation::where('doc_no', 'SJ-OUT-888')->first();
        $this->assertNotNull($doc);
        $this->assertEquals(WsMutation::DIRECTION_OUT, $doc->direction);
        $this->assertEquals($cust->id, $doc->customer_id);
        $this->assertEquals('Kurir Internal', $doc->expedition);

        // Stock move
        $move = StockMove::where('ref_id', $doc->id)->first();
        $this->assertNotNull($move);
        $this->assertEquals(StockMove::TYPE_DELIVERY, $move->type);
        $this->assertEquals(15.0, (float) $move->qty);
    }

    /** 4. Test Input Barang Keluar Sample Wajib Mengisi Nama PS */
    public function test_input_barang_keluar_sample_requires_ps_name()
    {
        $this->actingAs($this->manager());
        $product = $this->product('P-SAMPLE-01', 'Sampel Jarum Suntik', 50);

        // Attempt without PS Name and without customer ID -> should fail
        $response = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-SAMPLE-FAIL',
            'purpose' => 'Sample',
            'ps_name' => '',
            'partner_name' => '',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [5],
        ]);

        $response->assertSessionHas('error', 'Sample wajib pilih nama PS.');
        $this->assertEquals(50.0, (float) $product->fresh()->stock);

        // Attempt with PS Name -> should succeed
        $responseOk = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-SAMPLE-OK',
            'purpose' => 'Sample',
            'ps_name' => 'Budi Santoso (PS)',
            'partner_name' => '',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [5],
        ]);

        $responseOk->assertSessionHas('success');
        $this->assertEquals(45.0, (float) $product->fresh()->stock);
    }

    /** 5. Test Input Barang Keluar Retur Mengarah ke Lokasi Vendor */
    public function test_input_barang_keluar_retur_routes_to_vendor()
    {
        $this->actingAs($this->manager());
        $product = $this->product('P-RET-01', 'Masker Medis Rusak', 20);

        $response = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'RET-SUPP-01',
            'purpose' => 'Retur Barang',
            'partner_name' => 'PT Vendor Utama',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [10],
            'keterangan' => ['Barang cacat pabrik'],
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(10.0, (float) $product->fresh()->stock);

        $move = StockMove::where('doc_no', 'RET-SUPP-01')->first();
        $this->assertNotNull($move);
        $this->assertEquals('Vendor', $move->to_location);
    }

    /** 6. Test Validasi: Kuantitas 0 atau Negatif Harus Ditolak */
    public function test_input_mutation_rejects_zero_or_negative_qty()
    {
        $this->actingAs($this->manager());
        $product = $this->product('P-VAL-01', 'Produk Validasi Qty', 10);

        $responseZero = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-ZERO',
            'purpose' => 'Penjualan',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [0],
        ]);

        $responseZero->assertSessionHasErrors('qty.0');
        $this->assertEquals(10.0, (float) $product->fresh()->stock);

        $responseNeg = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-NEG',
            'purpose' => 'Penjualan',
            'arah' => ['keluar'],
            'product_id' => [$product->id],
            'qty' => [-5],
        ]);

        $responseNeg->assertSessionHasErrors('qty.0');
        $this->assertEquals(10.0, (float) $product->fresh()->stock);
    }

    /** 7. Test Validasi: Form Tanpa Baris Barang Harus Ditolak */
    public function test_input_mutation_rejects_empty_products()
    {
        $this->actingAs($this->manager());

        $response = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-EMPTY',
            'purpose' => 'Penjualan',
            'arah' => ['keluar'],
            'product_id' => [],
            'qty' => [],
        ]);

        $response->assertSessionHasErrors('product_id');
    }

    /** 8. Test Multi-Row Items Input (Multi Barang Sekaligus) */
    public function test_input_mutation_multiple_items_in_one_document()
    {
        $this->actingAs($this->manager());
        $p1 = $this->product('P-MULTI-1', 'Item 1', 100);
        $p2 = $this->product('P-MULTI-2', 'Item 2', 50);
        $p3 = $this->product('P-MULTI-3', 'Item 3', 30);

        $response = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-MULTI-01',
            'purpose' => 'Penjualan',
            'partner_name' => 'Klinik Sehat Bersama',
            'arah' => ['keluar'],
            'product_id' => [$p1->id, $p2->id, $p3->id],
            'qty' => [10, 20, 5],
            'batch_no' => ['B1', 'B2', 'B3'],
        ]);

        $response->assertSessionHas('success');

        $this->assertEquals(90.0, (float) $p1->fresh()->stock);
        $this->assertEquals(30.0, (float) $p2->fresh()->stock);
        $this->assertEquals(25.0, (float) $p3->fresh()->stock);

        $doc = WsMutation::where('doc_no', 'SJ-MULTI-01')->first();
        $this->assertNotNull($doc);
        $this->assertEquals(3, $doc->activeMoves()->count());
    }

    /** 9. Test CRUD Master Produk dari Warehouse Ops Tab Data-Product */
    public function test_product_store_update_and_delete()
    {
        $this->actingAs($this->manager());

        // Create
        $response = $this->post(route('warehouse.product.store'), [
            'product_code' => 'NEW-001',
            'product_name' => 'Paracetamol 500mg Baru',
            'unit' => 'Strip',
            'pcs_per_unit' => 10,
            'fill_unit' => 'Tablet',
            'stock' => 100,
        ]);

        $response->assertRedirect();
        $product = Product::where('product_code', 'NEW-001')->first();
        $this->assertNotNull($product);
        $this->assertEquals(100.0, (float) $product->stock);

        // Update
        $responseUpd = $this->put(route('warehouse.product.update', $product->id), [
            'product_code' => 'NEW-001-REV',
            'product_name' => 'Paracetamol 500mg Forte',
            'unit' => 'Box',
            'pcs_per_unit' => 100,
            'fill_unit' => 'Tablet',
            'stock' => 100,
        ]);

        $responseUpd->assertRedirect();
        $product->refresh();
        $this->assertEquals('NEW-001-REV', $product->product_code);
        $this->assertEquals('Paracetamol 500mg Forte', $product->product_name);
        $this->assertEquals('Box', $product->unit);

        // Soft Delete
        $responseDel = $this->delete(route('warehouse.product.destroy', $product->id));
        $responseDel->assertRedirect();
        $this->assertEquals(1, $product->fresh()->is_deleted);
    }

    /** 10. Test Edit Baris Mutasi Tunggal (Update Move) */
    public function test_update_single_mutation_move()
    {
        $this->actingAs($this->manager());
        $p = $this->product('P-SINGLE-EDIT', 'Produk Edit Move', 50);

        // Create mutasi keluar 10 -> sisa 40
        $this->post(route('warehouse.mutation.store'), [
            'tanggal' => today()->toDateString(),
            'asal' => 'ops',
            'doc_no' => 'SJ-EDIT-ROW',
            'purpose' => 'Penjualan',
            'arah' => ['keluar'],
            'product_id' => [$p->id],
            'qty' => [10],
        ]);

        $move = StockMove::where('doc_no', 'SJ-EDIT-ROW')->firstOrFail();
        $this->assertEquals(40.0, (float) $p->fresh()->stock);

        // Update move qty from 10 to 6 -> sisa stok harusnya 50 - 6 = 44
        $resEdit = $this->put(route('warehouse.mutation.update', $move->id), [
            'tanggal' => today()->toDateString(),
            'qty' => 6,
            'batch_no' => 'BATCH-REVISED',
            'keterangan' => 'Koreksi nota',
        ]);

        $resEdit->assertRedirect();
        $this->assertEquals(StockMove::STATUS_CANCELLED, $move->fresh()->status);
        $newMove = StockMove::where('product_id', $p->id)->where('status', StockMove::STATUS_DONE)->latest('id')->first();
        $this->assertNotNull($newMove);
        $this->assertEquals(6.0, (float) $newMove->qty);
        $this->assertEquals('BATCH-REVISED', $newMove->batch_no);
        $this->assertEquals(44.0, (float) $p->fresh()->stock);
    }
}
