<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Models\User;
use App\Services\StockMoveService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    /**
     * Skema minimal SQLite. Migrasi produksi memakai banyak sintaks MySQL
     * (MODIFY/ENUM) sehingga tidak bisa dijalankan pada :memory:.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('users')) {
            return;
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
            $t->string('year')->nullable();
            $t->unsignedInteger('last_no')->default(0);
            $t->timestamps();
        });

        Schema::create('stock_opnames', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('warehouse_id')->nullable();
            $t->unsignedBigInteger('product_id');
            $t->string('period', 7);
            $t->date('opname_date');
            $t->dateTime('opname_at')->nullable();
            $t->string('status', 10)->default('draft');
            $t->decimal('recorded_qty', 15, 2)->default(0);
            $t->decimal('counted_qty', 15, 2)->default(0);
            $t->decimal('diff_qty', 15, 2)->default(0);
            $t->text('note')->nullable();
            $t->string('ba_creator', 100)->nullable();
            $t->string('ba_checker', 100)->nullable();
            $t->unsignedBigInteger('stock_move_id')->nullable();
            $t->unsignedInteger('revision')->default(1);
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('validated_by')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->timestamps();
        });

        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('customer_name')->nullable();
            $t->text('address')->nullable();
            $t->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('supplier_name')->nullable();
            $t->text('address')->nullable();
            $t->timestamps();
        });

        Schema::create('ws_mutation', function (Blueprint $t) {
            $t->id();
            $t->string('doc_no')->nullable();
            $t->string('direction')->nullable();
            $t->date('mutation_date')->nullable();
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

    private function stockManager(): User
    {
        return User::factory()->create([
            'role' => 'user',
            'divisi' => 'Finance dan Gudang',
            'jabatan' => 'Admin Gudang',
        ]);
    }

    private function makeProduct(int $stock = 10): Product
    {
        return Product::create([
            'product_code' => 'OPN-' . fake()->unique()->numberBetween(1000, 9999),
            'product_name' => 'Produk Uji Opname',
            'unit' => 'Box',
            'pcs_per_unit' => 10,
            'fill_unit' => 'Pcs',
            'stock' => $stock,
        ]);
    }

    private function saveOpname(Product $product, string $tanggal, float $counted, ?string $reason = null, ?float $expected = null, bool $recount = false, ?float $recorded = null)
    {
        return $this->postJson(route('warehouse.stockopname.store'), [
            'product_id' => $product->id,
            'counted_qty' => $counted,
            'recorded_qty' => $recorded,
            'expected_qty' => $expected ?? (float) $product->fresh()->stock,
            'reason' => $reason,
            'recount' => $recount ? 1 : null,
            'tanggal' => $tanggal,
        ]);
    }

    private function finish(string $bulan)
    {
        return $this->postJson(route('warehouse.stockopname.finish'), [
            'bulan' => $bulan,
            'pembuat_ba' => 'Pembuat Uji',
            'pemeriksa_ba' => 'Pemeriksa Uji',
        ]);
    }

    public function test_manual_save_creates_draft_without_changing_stock_or_issuing_ba()
    {
        $this->travelTo('2026-10-20 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $this->saveOpname($product, '2026-10-12', 8, 'Rusak saat penyimpanan')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('draft', true)
            ->assertJsonPath('done', false)
            ->assertJsonPath('status', StockOpname::STATUS_DRAFT);

        $row = StockOpname::where('product_id', $product->id)->firstOrFail();
        $this->assertSame(StockOpname::STATUS_DRAFT, $row->status);
        $this->assertEquals(10, (float) $row->recorded_qty);
        $this->assertEquals(8, (float) $row->counted_qty);
        $this->assertEquals(-2, (float) $row->diff_qty);
        $this->assertEquals('2026-10', $row->period);

        $this->assertEquals(10, (float) $product->fresh()->stock);
        $this->assertEquals(0, StockMove::count());

        $this->get(route('warehouse.stockopname.print-bulan', ['bulan' => '2026-10']))
            ->assertForbidden();
        $this->get(route('warehouse.stockopname.print', $row))
            ->assertForbidden();
    }

    public function test_manual_recorded_qty_changes_difference_but_stock_follows_physical_count()
    {
        $this->travelTo('2026-10-20 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $this->saveOpname($product, '2026-10-12', 8, 'Koreksi tercatat', null, false, 7)
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('recorded', 7)
            ->assertJsonPath('diff', 1);

        $row = StockOpname::where('product_id', $product->id)->firstOrFail();
        $this->assertEquals(7, (float) $row->recorded_qty);
        $this->assertEquals(8, (float) $row->counted_qty);
        $this->assertEquals(1, (float) $row->diff_qty);
        $this->assertEquals(10, (float) $product->fresh()->stock);
        $this->assertEquals(0, StockMove::count());

        $this->travelTo('2026-10-25 10:00:00');
        $this->finish('2026-10')->assertOk()->assertJsonPath('done', 1);

        $this->assertEquals(8, (float) $product->fresh()->stock);
        $row->refresh();
        $this->assertSame(StockOpname::STATUS_DONE, $row->status);
        $this->assertEquals(7, (float) $row->recorded_qty);
    }

    public function test_existing_draft_can_update_manual_recorded_qty()
    {
        $this->travelTo('2026-10-20 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $this->saveOpname($product, '2026-10-12', 8, 'Koreksi tercatat', null, false, 7)->assertOk();
        $this->saveOpname($product, '2026-10-12', 8, 'Koreksi tercatat', null, false, 9)
            ->assertOk()
            ->assertJsonPath('recorded', 9)
            ->assertJsonPath('diff', -1);

        $row = StockOpname::where('product_id', $product->id)->firstOrFail();
        $this->assertEquals(9, (float) $row->recorded_qty);
        $this->assertEquals(-1, (float) $row->diff_qty);
        $this->assertEquals(10, (float) $product->fresh()->stock);
    }

    public function test_negative_physical_count_is_saved_and_applied_on_finish()
    {
        $this->travelTo('2026-10-12 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $this->saveOpname($product, '2026-10-12', -2, 'Stok fisik minus', null, true)
            ->assertOk()
            ->assertJsonPath('draft', true)
            ->assertJsonPath('status', StockOpname::STATUS_DRAFT);

        $row = StockOpname::where('product_id', $product->id)->firstOrFail();
        $this->assertEquals(-2, (float) $row->counted_qty);
        $this->assertEquals(-12, (float) $row->diff_qty);
        $this->assertEquals(10, (float) $product->fresh()->stock);
        $this->assertEquals(0, StockMove::count());

        $this->travelTo('2026-10-15 10:00:00');
        $this->finish('2026-10')->assertOk()->assertJsonPath('done', 1);

        $this->assertEquals(-2, (float) $product->fresh()->stock);
        $row->refresh();
        $this->assertSame(StockOpname::STATUS_DONE, $row->status);
        $move = StockMove::findOrFail($row->stock_move_id);
        $this->assertEquals(StockMove::TYPE_ADJUSTMENT, $move->type);
        $this->assertEquals(StockMove::LOC_STOCK, $move->from_location);
        $this->assertEquals(StockMove::LOC_ADJUSTMENT, $move->to_location);
        $this->assertEquals(12, (float) $move->qty);
    }

    public function test_finish_applies_stock_at_opname_date_and_keeps_moves_after_it()
    {
        $this->travelTo('2026-10-12 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $this->saveOpname($product, '2026-10-12', 8, 'Selisih hitung')->assertOk();

        // Mutasi setelah tanggal opname tetap harus dipertahankan.
        $this->travelTo('2026-10-18 09:00:00');
        $moves = app(StockMoveService::class);
        $receipt = $moves->createMove([
            'product_id' => $product->id,
            'qty' => 5,
            'type' => StockMove::TYPE_RECEIPT,
            'from_location' => 'Supplier',
            'to_location' => StockMove::LOC_STOCK,
            'reason' => 'Penerimaan setelah opname',
        ], $this->stockManager());
        $moves->validateMove($receipt, $this->stockManager());

        $this->assertEquals(15, (float) $product->fresh()->stock);

        $this->travelTo('2026-10-20 10:00:00');
        $this->finish('2026-10')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('done', 1);

        // Opname 8 pada tgl 12, lalu +5 setelahnya => stok akhir 13.
        $this->assertEquals(13, (float) $product->fresh()->stock);

        $row = StockOpname::where('product_id', $product->id)->firstOrFail();
        $this->assertSame(StockOpname::STATUS_DONE, $row->status);
        $this->assertSame('Pembuat Uji', $row->ba_creator);
        $this->assertSame('Pemeriksa Uji', $row->ba_checker);
        $this->assertNotNull($row->stock_move_id);

        $adjustment = StockMove::findOrFail($row->stock_move_id);
        $this->assertEquals(StockMove::TYPE_ADJUSTMENT, $adjustment->type);
        $this->assertEquals(StockMove::LOC_STOCK, $adjustment->from_location);
        $this->assertEquals(StockMove::LOC_ADJUSTMENT, $adjustment->to_location);
        $this->assertEquals(2, (float) $adjustment->qty);
        $this->assertStringStartsWith('Opname:', (string) $adjustment->reason);
        $this->assertSame('2026-10-12', $adjustment->created_at->format('Y-m-d'));
        $this->assertSame('2026-10-12', $adjustment->updated_at->format('Y-m-d'));

        $this->get(route('warehouse.stockopname.print-bulan', ['bulan' => '2026-10']))
            ->assertOk();
        $this->get(route('warehouse.stockopname.print', $row))->assertOk();
    }

    public function test_edit_after_finish_directly_stays_done_updates_stock_and_keeps_ba_available()
    {
        $this->travelTo('2026-10-12 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $this->saveOpname($product, '2026-10-12', 8, 'Selisih awal')->assertOk();
        $this->travelTo('2026-10-15 10:00:00');
        $this->finish('2026-10')->assertOk();

        $this->assertEquals(8, (float) $product->fresh()->stock);
        $row = StockOpname::where('product_id', $product->id)->firstOrFail();
        $this->assertSame(StockOpname::STATUS_DONE, $row->status);

        // Edit setelah selesai: simpan hasil baru langsung berstatus DONE dan stok langsung disesuaikan!
        $this->saveOpname($product, '2026-10-12', 9, 'Koreksi hitung', 8)
            ->assertOk()
            ->assertJsonPath('done', true)
            ->assertJsonPath('status', StockOpname::STATUS_DONE);

        $row->refresh();
        $this->assertSame(StockOpname::STATUS_DONE, $row->status);
        $this->assertSame('Pembuat Uji', $row->ba_creator);
        $this->assertSame('Pemeriksa Uji', $row->ba_checker);
        $this->assertEquals(2, (int) $row->revision);
        $this->assertEquals(9, (float) $row->counted_qty);
        $this->assertEquals(-1, (float) $row->diff_qty);
        // Stok produk langsung disesuaikan ke 9 seketika tanpa perlu klik "Selesaikan" lagi!
        $this->assertEquals(9, (float) $product->fresh()->stock);

        // BA tetap aktif dan langsung dapat diunduh tanpa perlu klik Selesaikan lagi!
        $this->get(route('warehouse.stockopname.print-bulan', ['bulan' => '2026-10']))
            ->assertOk();
        $this->get(route('warehouse.stockopname.print', $row))
            ->assertOk();
        $this->assertEquals(2, StockMove::where('type', StockMove::TYPE_ADJUSTMENT)->count());
    }

    public function test_first_finish_still_requires_ba_names()
    {
        $this->travelTo('2026-10-12 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $this->saveOpname($product, '2026-10-12', 8, 'Selisih awal')->assertOk();
        $this->travelTo('2026-10-15 10:00:00');

        $this->postJson(route('warehouse.stockopname.finish'), ['bulan' => '2026-10'])
            ->assertStatus(422)
            ->assertJsonPath('ok', false);

        $row = StockOpname::where('product_id', $product->id)->firstOrFail();
        $this->assertSame(StockOpname::STATUS_DRAFT, $row->status);
        $this->assertEquals(10, (float) $product->fresh()->stock);
    }

    public function test_finish_zero_difference_marks_done_without_stock_move_and_issues_ba()
    {
        $this->travelTo('2026-10-12 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $this->saveOpname($product, '2026-10-12', 10, 'Sesuai hitung')->assertOk();
        $this->travelTo('2026-10-15 10:00:00');
        $this->finish('2026-10')->assertOk()->assertJsonPath('done', 1);

        $row = StockOpname::where('product_id', $product->id)->firstOrFail();
        $this->assertSame(StockOpname::STATUS_DONE, $row->status);
        $this->assertNull($row->stock_move_id);
        $this->assertEquals(10, (float) $product->fresh()->stock);
        $this->assertEquals(0, StockMove::count());

        $this->get(route('warehouse.stockopname.print-bulan', ['bulan' => '2026-10']))
            ->assertOk();
        $this->get(route('warehouse.stockopname.print', $row))->assertOk();
    }

    public function test_catat_page_remembers_last_opname_date_and_respects_explicit_month()
    {
        $this->travelTo('2026-10-20 10:00:00');
        $user = $this->stockManager();
        $this->actingAs($user);
        $this->makeProduct(10);
        $cookieKey = 'opname_last_date_' . $user->id;

        $first = $this->get(route('warehouse.stockopname.catat', ['tanggal' => '2026-10-12']));
        $first->assertOk()->assertCookie($cookieKey, '2026-10-12');
        $first->assertViewHas('tanggal', '2026-10-12');

        // Tanpa parameter: pakai tanggal terakhir dari cookie.
        $this->withCookie($cookieKey, '2026-10-12')
            ->get(route('warehouse.stockopname.catat'))
            ->assertOk()
            ->assertViewHas('tanggal', '2026-10-12');

        // Bulan yang sama: tanggal terakhir tetap dipakai.
        $this->withCookie($cookieKey, '2026-10-12')
            ->get(route('warehouse.stockopname.catat', ['bulan' => '2026-10']))
            ->assertOk()
            ->assertViewHas('tanggal', '2026-10-12');

        // Bulan berbeda: ikuti bulan yang diklik, bukan cookie bulan lain.
        $this->withCookie($cookieKey, '2026-10-12')
            ->get(route('warehouse.stockopname.catat', ['bulan' => '2026-09']))
            ->assertOk()
            ->assertViewHas('tanggal', '2026-09-30');

        // Tanggal eksplisit selalu menang dan memperbarui cookie.
        $this->withCookie($cookieKey, '2026-10-12')
            ->get(route('warehouse.stockopname.catat', ['tanggal' => '2026-09-05']))
            ->assertOk()
            ->assertCookie($cookieKey, '2026-09-05')
            ->assertViewHas('tanggal', '2026-09-05');
    }

    public function test_finish_modal_lists_only_drafts_with_difference()
    {
        $this->travelTo('2026-10-12 10:00:00');
        $this->actingAs($this->stockManager());
        $different = $this->makeProduct(10);
        $same = $this->makeProduct(10);

        $this->saveOpname($different, '2026-10-12', 8, 'Selisih hitung')->assertOk();
        $this->saveOpname($same, '2026-10-12', 10, 'Sesuai hitung')->assertOk();

        $response = $this->getJson(route('warehouse.stockopname.drafts', ['bulan' => '2026-10']));
        $response->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('total_drafts', 2)
            ->assertJsonPath('same_count', 1)
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.produk', $different->product_code . ' — ' . $different->product_name);
    }

    public function test_catat_counted_input_is_plain_decimal_text_without_native_spinner()
    {
        $this->travelTo('2026-10-20 10:00:00');
        $this->actingAs($this->stockManager());
        $this->makeProduct(10);

        $response = $this->get(route('warehouse.stockopname.catat', ['tanggal' => '2026-10-12']));
        $response->assertOk();
        $response->assertSee('id="catatCounted"', false);
        $response->assertSee('inputmode="decimal"', false);
        $response->assertSee('autocomplete="off"', false);
        $response->assertDontSee('type="number" name="counted_qty"', false);
        $response->assertSee(route('warehouse.stockopname.export-rekap', ['bulan' => '2026-10', 'format' => 'excel']), false);
        $response->assertSee(route('warehouse.stockopname.export-rekap', ['bulan' => '2026-10', 'format' => 'pdf']), false);
    }

    public function test_card_opening_balance_uses_month_opname_and_skips_moves_before_it()
    {
        $this->travelTo('2026-10-12 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $this->saveOpname($product, '2026-10-12', 8, 'Selisih hitung')->assertOk();
        $this->travelTo('2026-10-18 09:00:00');
        $moves = app(StockMoveService::class);
        $receipt = $moves->createMove([
            'product_id' => $product->id,
            'qty' => 5,
            'type' => StockMove::TYPE_RECEIPT,
            'from_location' => 'Supplier',
            'to_location' => StockMove::LOC_STOCK,
            'reason' => 'Penerimaan setelah opname',
        ], $this->stockManager());
        $moves->validateMove($receipt, $this->stockManager());

        $this->travelTo('2026-10-20 10:00:00');
        $this->finish('2026-10')->assertOk();

        $response = $this->get(route('warehouse.stockcard.show', [
            'product' => $product->id,
            'from' => '2026-10-01',
            'to' => '2026-10-31',
            'embed' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Saldo fisik opname', false);
        $response->assertSee('Penerimaan setelah opname', false);
        $response->assertViewHas('saldoAwal', 8.0);
        $response->assertViewHas('rows', function ($rows) {
            return collect($rows)->filter(fn ($r) => $r['move']->reason === 'Penerimaan setelah opname')->count() === 1
                && collect($rows)->filter(fn ($r) => str_starts_with((string) $r['move']->reason, 'Opname:'))->isEmpty();
        });
    }

    public function test_card_delete_opname_row_reverses_stock_and_removes_opname_record()
    {
        $this->travelTo('2026-10-12 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);

        $this->saveOpname($product, '2026-10-12', 8, 'Selisih hitung')->assertOk();
        $this->travelTo('2026-10-15 10:00:00');
        $this->finish('2026-10')->assertOk();

        $row = StockOpname::where('product_id', $product->id)->firstOrFail();
        $moveId = $row->stock_move_id;
        $this->assertNotNull($moveId);
        $this->assertEquals(8, (float) $product->fresh()->stock);

        // Hapus baris opname langsung dari Kartu Stock.
        $this->travelTo('2026-10-20 10:00:00');
        $this->deleteJson(route('warehouse.stockcard.moves.destroy', $moveId))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('stock_opnames', ['id' => $row->id]);
        $this->assertSame(StockMove::STATUS_CANCELLED, StockMove::findOrFail($moveId)->status);
        // Stok kembali seperti sebelum opname diterapkan.
        $this->assertEquals(10, (float) $product->fresh()->stock);
    }

    public function test_opname_index_hides_placeholder_exports_and_uses_compact_action_buttons()
    {
        $this->travelTo('2026-10-20 10:00:00');
        $this->actingAs($this->stockManager());
        $this->makeProduct(10);

        $response = $this->get(route('warehouse.stockopname.index', ['tahun' => 2026]));

        $response->assertOk()
            ->assertSee('Rekap Opname')
            ->assertSee('Catat Opname')
            ->assertSee('Barang Rejected')
            ->assertSee('h-[30px] px-3 rounded-lg bg-indigo-600', false)
            ->assertSee('h-[30px] px-3 rounded-lg bg-white border border-rose-200', false)
            ->assertSee('id="rbEdit" href="#" class="flex items-center justify-center', false)
            ->assertSee('id="rbBa" href="#" class="hidden flex items-center justify-center', false)
            ->assertSee('id="rbFinish" class="inline-flex items-center justify-center', false)
            ->assertDontSee('Export Excel (segera hadir)')
            ->assertDontSee('Export PDF (segera hadir)')
            ->assertDontSee('modal-opname-detail')
            ->assertSee('id="rbExportExcel"', false)
            ->assertSee('id="rbExportPdf"', false);
    }

    public function test_opname_rekap_can_be_exported_to_excel_and_pdf()
    {
        $this->travelTo('2026-10-20 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(10);
        $this->saveOpname($product, '2026-10-12', 8, 'Selisih hitung')->assertOk();

        $this->get(route('warehouse.stockopname.export-rekap', [
            'bulan' => '2026-10',
            'format' => 'excel',
        ]))->assertOk()->assertDownload('Rekap_Opname_2026-10.xlsx');

        $this->get(route('warehouse.stockopname.export-rekap', [
            'bulan' => '2026-10',
            'format' => 'pdf',
        ]))->assertOk()->assertDownload('Rekap_Opname_2026-10.pdf');
    }

    public function test_future_date_and_cross_month_opname_can_be_set_and_inputted()
    {
        $this->travelTo('2026-10-30 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(25);

        // Akses halaman catat untuk periode November dengan tanggal besok (31 Oktober 2026)
        $response = $this->get(route('warehouse.stockopname.catat', [
            'bulan' => '2026-11',
            'tanggal' => '2026-10-31',
        ]));

        $response->assertOk();
        $response->assertViewHas('bulan', '2026-11');
        $response->assertViewHas('tanggal', '2026-10-31');
        $response->assertSee('Catat Opname — 31 Oktober 2026');
        $response->assertSee('November 2026');
        $response->assertDontSee('max="2026-10-30"', false);

        // Simpan opname tanggal 31 Oktober untuk periode November dengan fisik & tercatat kustom
        $post = $this->postJson(route('warehouse.stockopname.store'), [
            'product_id' => $product->id,
            'counted_qty' => 30,
            'recorded_qty' => 25,
            'tanggal' => '2026-10-31',
            'bulan' => '2026-11',
            'reason' => 'Opname November dijalankan 31 Oktober',
        ]);

        $post->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('counted', 30)
            ->assertJsonPath('recorded', 25)
            ->assertJsonPath('diff', 5);

        $row = StockOpname::where('product_id', $product->id)->where('period', '2026-11')->firstOrFail();
        $this->assertSame('2026-11', $row->period);
        $this->assertSame('2026-10-31', $row->opname_date->format('Y-m-d'));
        $this->assertEquals(30, (float) $row->counted_qty);
        $this->assertEquals(25, (float) $row->recorded_qty);
        $this->assertEquals(5, (float) $row->diff_qty);
    }

    public function test_new_products_after_finished_opname_only_appear_in_next_month()
    {
        $this->travelTo('2026-10-05 09:00:00');
        $this->actingAs($this->stockManager());

        $before = $this->makeProduct(10);
        $this->saveOpname($before, '2026-10-12', 8, 'Selisih hitung')->assertOk();

        $this->travelTo('2026-10-20 10:00:00');
        $this->finish('2026-10')->assertOk();

        // Produk didaftarkan sesudah opname Oktober selesai (cutoff 20 Okt).
        $this->travelTo('2026-10-22 10:00:00');
        $after = $this->makeProduct(10);

        $response = $this->get(route('warehouse.stockopname.index', [
            'bulan' => '2026-10',
            'tahun' => 2026,
        ]));
        $response->assertOk();
        $response->assertViewHas('rekapPerBulan', function ($rows) {
            $oktober = collect($rows)->firstWhere('key', '2026-10');

            return $oktober !== null && (int) $oktober['total'] === 1;
        });

        $catatOktober = $this->get(route('warehouse.stockopname.catat', [
            'bulan' => '2026-10',
            'tanggal' => '2026-10-12',
        ]));
        $catatOktober->assertOk();
        $catatOktober->assertViewHas('productOptions', function ($options) use ($before, $after) {
            $ids = $options->pluck('id');

            return $ids->contains($before->id) && ! $ids->contains($after->id);
        });

        $catatNovember = $this->get(route('warehouse.stockopname.catat', [
            'bulan' => '2026-11',
            'tanggal' => '2026-11-01',
        ]));
        $catatNovember->assertOk();
        $catatNovember->assertViewHas('productOptions', fn ($options) => $options->pluck('id')->contains($after->id));
    }

    public function test_opname_month_total_uses_products_existing_in_that_month_not_current_total()
    {
        $this->travelTo('2026-10-05 09:00:00');
        $this->actingAs($this->stockManager());

        $octoberProduct = $this->makeProduct(10);
        $this->saveOpname($octoberProduct, '2026-10-12', 8, 'Selisih hitung')->assertOk();

        // Produk Desember tidak boleh ikut menambah denominator opname Oktober.
        $this->travelTo('2026-12-01 09:00:00');
        $decemberProduct = $this->makeProduct(10);

        // Opname Oktober baru divalidasi pada Desember.
        $this->finish('2026-10')->assertOk();

        $response = $this->get(route('warehouse.stockopname.index', [
            'bulan' => '2026-10',
            'tahun' => 2026,
        ]));
        $response->assertOk();
        $response->assertViewHas('rekapPerBulan', function ($rows) {
            $oktober = collect($rows)->firstWhere('key', '2026-10');

            return $oktober !== null && (int) $oktober['total'] === 1;
        });

        $catat = $this->get(route('warehouse.stockopname.catat', [
            'bulan' => '2026-10',
            'tanggal' => '2026-10-12',
        ]));
        $catat->assertOk();
        $catat->assertViewHas('totalSku', 1);
        $catat->assertViewHas('productOptions', function ($options) use ($octoberProduct, $decemberProduct) {
            $ids = $options->pluck('id');

            return $ids->contains($octoberProduct->id) && ! $ids->contains($decemberProduct->id);
        });
    }

    public function test_opname_note_is_optional_and_defaults_to_selisih_dots()
    {
        $this->travelTo('2026-10-31 10:00:00');
        $this->actingAs($this->stockManager());
        $product = $this->makeProduct(20);

        // 1. Simpan opname dengan selisih tapi tanpa mengisi keterangan (null / kosong)
        $post = $this->postJson(route('warehouse.stockopname.store'), [
            'product_id' => $product->id,
            'counted_qty' => 18,
            'recorded_qty' => 20,
            'tanggal' => '2026-10-31',
            'reason' => '',
        ]);

        $post->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('diff', -2);

        $row = StockOpname::where('product_id', $product->id)->where('period', '2026-10')->firstOrFail();
        $this->assertEquals(-2, (float) $row->diff_qty);
        $this->assertSame('Selisih -2', $row->note);

        // 2. Jika keterangan diisi secara spesifik, gunakan keterangan tersebut
        $postCustom = $this->postJson(route('warehouse.stockopname.store'), [
            'product_id' => $product->id,
            'counted_qty' => 17,
            'recorded_qty' => 20,
            'tanggal' => '2026-10-31',
            'reason' => 'Barang sample promosi',
        ]);

        $postCustom->assertOk()
            ->assertJsonPath('ok', true);

        $row->refresh();
        $this->assertSame('Barang sample promosi', $row->note);

        // 3. Tanpa selisih (sesuai) dan tanpa keterangan -> note kosong/null
        $product2 = $this->makeProduct(15);
        $postSesuai = $this->postJson(route('warehouse.stockopname.store'), [
            'product_id' => $product2->id,
            'counted_qty' => 15,
            'recorded_qty' => 15,
            'tanggal' => '2026-10-31',
            'reason' => '',
        ]);

        $postSesuai->assertOk()
            ->assertJsonPath('ok', true);

        $row2 = StockOpname::where('product_id', $product2->id)->where('period', '2026-10')->firstOrFail();
        $this->assertNull($row2->note);
    }
}
