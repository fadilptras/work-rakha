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

class BulkMutationAndBatchTest extends TestCase
{
    protected User $user;
    protected Warehouse $warehouse;

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

        $this->warehouse = Warehouse::create([
            'code' => 'GUD-UTAMA',
            'name' => 'Gudang Utama',
            'is_default' => true,
        ]);

        $this->user = User::create([
            'name' => 'Admin Gudang',
            'email' => 'gudang-test@example.test',
            'password' => bcrypt('secret'),
            'role' => 'user',
            'jabatan' => 'Admin Gudang',
            'divisi' => 'Finance dan Gudang',
        ]);
        $this->actingAs($this->user);
    }

    /**
     * Test 1: Pastikan TIDAK ADA auto-generate nomor batch acak jika kosong.
     * Jika kosong, di database bernilai NULL dan saat ditampilkan (documentDetail / view) tampil '-'.
     */
    public function test_empty_batch_is_stored_as_null_and_displays_dash(): void
    {
        $prod = Product::create([
            'product_code' => 'PROD-NO-BATCH',
            'product_name' => 'Produk Tanpa Batch',
            'stock' => 0,
        ]);

        // Input barang masuk tanpa batch
        $responseIn = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => now()->toDateString(),
            'asal' => 'ops',
            'arah' => ['masuk'],
            'product_id' => [$prod->id],
            'qty' => [20],
            'batch_no' => [''], // sengaja kosong
            'expiry_date' => [''],
            'keterangan' => ['Barang masuk tanpa batch'],
        ]);

        $responseIn->assertSessionHasNoErrors();
        $this->assertEquals(20, (float) $prod->fresh()->stock);

        $docIn = WsMutation::where('direction', WsMutation::DIRECTION_IN)->latest('id')->first();
        $this->assertNotNull($docIn);

        $moveIn = $docIn->activeMoves()->first();
        $this->assertNotNull($moveIn);
        $this->assertNull($moveIn->batch_no, 'Batch pada stock_moves harus null jika tidak diisi.');

        // Periksa API documentDetail
        $detailResIn = $this->getJson(route('warehouse.document', $docIn->id));
        $detailResIn->assertOk();
        $linesIn = $detailResIn->json('data.lines');
        $this->assertEquals('-', $linesIn[0]['batch_no'], 'Document detail harus menampilkan "-" untuk batch kosong.');

        // Sekarang lakukan delivery (barang keluar) dari produk tersebut tanpa isi batch
        $responseOut = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => now()->toDateString(),
            'asal' => 'ops',
            'arah' => ['keluar'],
            'purpose' => 'Penjualan',
            'partner_name' => 'RSUD Uji Coba',
            'product_id' => [$prod->id],
            'qty' => [5],
            'batch_no' => [''], // kosong
            'expiry_date' => [''],
            'keterangan' => ['Kirim ke RSUD'],
        ]);

        $responseOut->assertSessionHasNoErrors();
        $this->assertEquals(15, (float) $prod->fresh()->stock);

        $docOut = WsMutation::where('direction', WsMutation::DIRECTION_OUT)->latest('id')->first();
        $this->assertNotNull($docOut);

        $moveOut = $docOut->activeMoves()->first();
        $this->assertNotNull($moveOut);
        $this->assertNull($moveOut->batch_no, 'Batch keluar TIDAK BOLEH auto-generate atau mengambil batch lain jika dikosongkan.');

        $detailResOut = $this->getJson(route('warehouse.document', $docOut->id));
        $detailResOut->assertOk();
        $linesOut = $detailResOut->json('data.lines');
        $this->assertEquals('-', $linesOut[0]['batch_no'], 'Batch keluar harus tampil "-" di rincian dokumen.');
    }

    /**
     * Test 2: Input barang keluar & masuk lebih dari 50 produk sekaligus tetap aman dan presisi,
     * tidak ada QTY ganda/double!
     */
    public function test_bulk_input_over_50_products_saves_accurately_without_duplicate_qty(): void
    {
        $count = 60; // Lebih dari 50 produk
        $productIds = [];
        $qtysOut = [];
        $batches = [];
        $expiries = [];
        $notes = [];
        $arahs = [];

        for ($i = 1; $i <= $count; $i++) {
            $p = Product::create([
                'product_code' => 'PRD-BULK-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'product_name' => 'Produk Bulk ' . $i,
                'stock' => 100, // Stok awal masing-masing 100
            ]);
            $productIds[] = $p->id;
            $qtysOut[] = 5; // Masing-masing keluar 5
            $batches[] = ''; // Kosong (tanpa batch)
            $expiries[] = '';
            $notes[] = 'Rincian item ' . $i;
            $arahs[] = 'keluar';
        }

        // Submit form keluar untuk 60 produk sekaligus
        $response = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => now()->toDateString(),
            'asal' => 'ops',
            'arah' => $arahs,
            'purpose' => 'Penjualan',
            'partner_name' => 'Distributor Utama Farmasi',
            'product_id' => $productIds,
            'qty' => $qtysOut,
            'batch_no' => $batches,
            'expiry_date' => $expiries,
            'keterangan' => $notes,
        ]);

        $response->assertSessionHasNoErrors();

        $doc = WsMutation::where('direction', WsMutation::DIRECTION_OUT)->latest('id')->first();
        $this->assertNotNull($doc);

        $savedMoves = $doc->activeMoves()->get();
        $this->assertCount($count, $savedMoves, 'Harus tersimpan tepat 60 baris pergerakan stok.');

        // Verifikasi setiap produk: stok berkurang tepat 5 (100 - 5 = 95), TIDAK DOUBLE
        foreach ($productIds as $pid) {
            $prod = Product::find($pid);
            $this->assertEquals(95, (int) $prod->stock, "Produk {$prod->product_code} harus bersisa 95, tidak boleh terpotong double.");
        }

        // Test 3: Anti Double Submission
        // Jika request identik dikirim ulang seketika (rapid click / retry), request kedua harus dicegah!
        $responseDuplicate = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => now()->toDateString(),
            'asal' => 'ops',
            'arah' => $arahs,
            'purpose' => 'Penjualan',
            'partner_name' => 'Distributor Utama Farmasi',
            'product_id' => $productIds,
            'qty' => $qtysOut,
            'batch_no' => $batches,
            'expiry_date' => $expiries,
            'keterangan' => $notes,
        ]);

        // Verifikasi tidak ada dokumen ganda yang terbentuk
        $totalDocs = WsMutation::where('direction', WsMutation::DIRECTION_OUT)->count();
        $this->assertEquals(1, $totalDocs, 'Tidak boleh ada dokumen duplikat yang tercipta dari submission ganda.');

        // Verifikasi stok tetap 95, sama sekali TIDAK berkurang lagi (tidak double deduction)
        foreach ($productIds as $pid) {
            $prod = Product::find($pid);
            $this->assertEquals(95, (int) $prod->stock, "Stok produk {$prod->product_code} tidak boleh terpotong dua kali.");
        }
    }

    /**
     * Test 4: Bulk input masuk lebih dari 50 produk sekaligus.
     */
    public function test_bulk_input_inbound_over_50_products(): void
    {
        $count = 55;
        $productIds = [];
        $qtysIn = [];
        $batches = [];
        $expiries = [];
        $notes = [];
        $arahs = [];

        for ($i = 1; $i <= $count; $i++) {
            $p = Product::create([
                'product_code' => 'PRD-IN-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'product_name' => 'Produk Inbound ' . $i,
                'stock' => 10,
            ]);
            $productIds[] = $p->id;
            $qtysIn[] = 15;
            $batches[] = '';
            $expiries[] = '';
            $notes[] = 'Penerimaan bulk ' . $i;
            $arahs[] = 'masuk';
        }

        $response = $this->post(route('warehouse.mutation.store'), [
            'tanggal' => now()->toDateString(),
            'asal' => 'ops',
            'arah' => $arahs,
            'purpose' => 'Pembelian',
            'partner_name' => 'Supplier PT Kimia',
            'product_id' => $productIds,
            'qty' => $qtysIn,
            'batch_no' => $batches,
            'expiry_date' => $expiries,
            'keterangan' => $notes,
        ]);

        $response->assertSessionHasNoErrors();

        // Cek bahwa stok masing-masing produk bertambah tepat 15 (10 + 15 = 25), tidak double
        foreach ($productIds as $pid) {
            $prod = Product::find($pid);
            $this->assertEquals(25, (float) $prod->stock, "Produk {$prod->product_code} harus bertambah presisi menjadi 25.");
        }

        $doc = WsMutation::where('direction', WsMutation::DIRECTION_IN)->latest('id')->first();
        $this->assertNotNull($doc);
        $this->assertCount(55, $doc->activeMoves()->get());

        // Verifikasi semua baris batch_no adalah null
        foreach ($doc->activeMoves()->get() as $move) {
            $this->assertNull($move->batch_no, 'Batch produk tanpa batch harus tetap null.');
        }
    }
}
