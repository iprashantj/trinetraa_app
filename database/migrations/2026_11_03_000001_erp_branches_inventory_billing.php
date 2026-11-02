<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SOW: multi-branch ERP — branches, per-branch stock, inward, transfers, POS billing
 * links, returns, structured eye tests, permissions/MFA/lockout and audit detail.
 * Purely additive: existing tables/columns are untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('code', 20)->unique();
            $t->string('city')->nullable();
            $t->text('address')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('hours')->nullable();
            $t->string('map_url', 500)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('gstin', 20)->nullable();
            $t->text('address')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::table('stock_items', function (Blueprint $t) {
            $t->string('brand')->nullable()->index()->after('model_number');
            $t->string('barcode', 100)->nullable()->unique()->after('sku');
            $t->unsignedInteger('pack_quantity')->default(1)->after('barcode');
            $t->unsignedBigInteger('supplier_id')->nullable()->index()->after('category_id');
        });

        // Quantity per item per branch (stock_items.current_stock stays as the all-branch total).
        Schema::create('stock_levels', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('stock_item_id');
            $t->unsignedBigInteger('branch_id');
            $t->integer('quantity')->default(0);
            $t->timestamps();
            $t->unique(['stock_item_id', 'branch_id']);
            $t->foreign('stock_item_id')->references('id')->on('stock_items')->cascadeOnDelete();
            $t->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
        });

        Schema::create('stock_inwards', function (Blueprint $t) {
            $t->id();
            $t->date('inward_date')->index();
            $t->unsignedBigInteger('branch_id')->index();
            $t->unsignedBigInteger('supplier_id')->nullable();
            $t->unsignedBigInteger('stock_item_id')->index();
            $t->unsignedInteger('units_per_pack')->default(1);
            $t->unsignedInteger('packs')->default(1);
            $t->unsignedInteger('quantity');
            $t->string('invoice_ref')->nullable();
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamps();
            $t->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $t->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $t->foreign('stock_item_id')->references('id')->on('stock_items')->cascadeOnDelete();
        });

        // One row per physical unit. unique_code is never reused, even after the unit is sold.
        Schema::create('stock_units', function (Blueprint $t) {
            $t->id();
            $t->string('unique_code', 40)->unique();
            $t->unsignedBigInteger('stock_item_id')->index();
            $t->unsignedBigInteger('branch_id')->index();
            $t->string('status', 20)->default('in_stock')->index(); // in_stock | in_transit | sold | returned
            $t->unsignedBigInteger('inward_id')->nullable();
            $t->timestamps();
            $t->foreign('stock_item_id')->references('id')->on('stock_items')->cascadeOnDelete();
            $t->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
        });

        Schema::create('stock_transfers', function (Blueprint $t) {
            $t->id();
            $t->string('transfer_number')->unique();
            $t->unsignedBigInteger('from_branch_id')->index();
            $t->unsignedBigInteger('to_branch_id')->index();
            $t->string('status', 20)->default('draft')->index(); // draft | in_transit | received | cancelled
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->unsignedBigInteger('dispatched_by')->nullable();
            $t->unsignedBigInteger('received_by')->nullable();
            $t->timestamp('dispatched_at')->nullable();
            $t->timestamp('received_at')->nullable();
            $t->timestamps();
            $t->foreign('from_branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $t->foreign('to_branch_id')->references('id')->on('branches')->cascadeOnDelete();
        });

        Schema::create('stock_transfer_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('stock_transfer_id')->index();
            $t->unsignedBigInteger('stock_item_id')->index();
            $t->unsignedInteger('quantity');
            $t->timestamps();
            $t->foreign('stock_transfer_id')->references('id')->on('stock_transfers')->cascadeOnDelete();
            $t->foreign('stock_item_id')->references('id')->on('stock_items')->cascadeOnDelete();
        });

        // Append-only ledger of every stock change (who / what / when / why).
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('stock_item_id')->index();
            $t->unsignedBigInteger('branch_id')->index();
            $t->string('type', 30)->index(); // inward|transfer_out|transfer_in|sale|return|adjustment|void
            $t->integer('quantity'); // signed
            $t->string('reference_type', 40)->nullable();
            $t->unsignedBigInteger('reference_id')->nullable();
            $t->string('reason')->nullable();
            $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->foreign('stock_item_id')->references('id')->on('stock_items')->cascadeOnDelete();
            $t->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
        });

        // Low-stock alert level per brand or category.
        Schema::create('stock_alert_rules', function (Blueprint $t) {
            $t->id();
            $t->string('scope', 10); // brand | category
            $t->string('brand')->nullable();
            $t->unsignedBigInteger('category_id')->nullable();
            $t->unsignedInteger('threshold');
            $t->timestamps();
            $t->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });

        Schema::table('frame_bills', function (Blueprint $t) {
            $t->unsignedBigInteger('branch_id')->nullable()->index()->after('customer_id');
            $t->string('payment_method', 20)->nullable()->after('total'); // cash|upi|card|online
            $t->string('payment_status', 20)->default('paid')->after('payment_method'); // paid|pending|refunded|partial
            $t->decimal('paid_amount', 12, 2)->nullable()->after('payment_status');
            $t->string('share_token', 64)->nullable()->unique()->after('notes');
            $t->string('payment_link_id')->nullable()->after('share_token');
            $t->string('payment_link_url', 500)->nullable()->after('payment_link_id');
            $t->unsignedBigInteger('created_by')->nullable()->after('payment_link_url');
        });
        Schema::table('frame_bill_items', function (Blueprint $t) {
            $t->unsignedBigInteger('stock_item_id')->nullable()->index()->after('frame_bill_id');
        });
        Schema::table('eye_checkup_bills', function (Blueprint $t) {
            $t->unsignedBigInteger('branch_id')->nullable()->index();
            $t->string('payment_method', 20)->nullable();
            $t->string('share_token', 64)->nullable()->unique();
            $t->unsignedBigInteger('created_by')->nullable();
        });

        Schema::create('bill_returns', function (Blueprint $t) {
            $t->id();
            $t->string('return_number')->unique();
            $t->unsignedBigInteger('frame_bill_id')->index();
            $t->unsignedBigInteger('branch_id')->nullable()->index();
            $t->string('type', 12)->default('return'); // return | exchange
            $t->string('reason');
            $t->decimal('refund_amount', 12, 2)->default(0);
            $t->string('refund_method', 20)->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->foreign('frame_bill_id')->references('id')->on('frame_bills')->cascadeOnDelete();
        });
        Schema::create('bill_return_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('bill_return_id')->index();
            $t->unsignedBigInteger('frame_bill_item_id')->index();
            $t->unsignedInteger('quantity');
            $t->boolean('restock')->default(true);
            $t->timestamps();
            $t->foreign('bill_return_id')->references('id')->on('bill_returns')->cascadeOnDelete();
            $t->foreign('frame_bill_item_id')->references('id')->on('frame_bill_items')->cascadeOnDelete();
        });

        Schema::table('customers', function (Blueprint $t) {
            $t->boolean('marketing_consent')->default(false);
            $t->timestamp('consent_at')->nullable();
            $t->boolean('whatsapp_opt_in')->default(false);
            $t->unsignedBigInteger('branch_id')->nullable()->index();
        });

        Schema::create('eye_tests', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('customer_id')->index();
            $t->unsignedBigInteger('branch_id')->nullable()->index();
            $t->date('test_date')->index();
            foreach (['right', 'left'] as $eye) {
                $t->string("{$eye}_sph", 12)->nullable();
                $t->string("{$eye}_cyl", 12)->nullable();
                $t->string("{$eye}_axis", 12)->nullable();
                $t->string("{$eye}_add", 12)->nullable();
                $t->string("{$eye}_va", 12)->nullable();
            }
            $t->string('pd', 12)->nullable();
            $t->string('optometrist')->nullable();
            $t->text('notes')->nullable();
            $t->string('report_path', 500)->nullable();
            $t->string('source', 12)->default('manual'); // manual | upload
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
            $t->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });

        Schema::table('users', function (Blueprint $t) {
            $t->unsignedBigInteger('branch_id')->nullable()->index();
            $t->json('permissions')->nullable();
            $t->string('totp_secret')->nullable();
            $t->boolean('totp_enabled')->default(false);
            $t->unsignedInteger('failed_logins')->default(0);
            $t->timestamp('locked_until')->nullable();
        });

        Schema::table('eyewears', function (Blueprint $t) {
            $t->unsignedBigInteger('stock_item_id')->nullable()->index();
        });

        Schema::table('admin_activity_logs', function (Blueprint $t) {
            $t->text('details')->nullable();
        });

        // Seed the three branches from the SOW. Pune/Mumbai details are filled in later from the admin.
        $now = now();
        DB::table('branches')->insert([
            ['name' => 'Nashik', 'code' => 'NSK', 'city' => 'Nashik', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Mumbai', 'code' => 'MUM', 'city' => 'Mumbai', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pune', 'code' => 'PUN', 'city' => 'Pune', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $nashik = DB::table('branches')->where('code', 'NSK')->value('id');

        // Existing single-store data belongs to Nashik: carry current stock into its level table.
        foreach (DB::table('stock_items')->get(['id', 'current_stock']) as $item) {
            if ((int) $item->current_stock > 0) {
                DB::table('stock_levels')->insert([
                    'stock_item_id' => $item->id, 'branch_id' => $nashik,
                    'quantity' => (int) $item->current_stock, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
        DB::table('frame_bills')->update(['branch_id' => $nashik]);
        DB::table('eye_checkup_bills')->update(['branch_id' => $nashik]);
    }

    public function down(): void
    {
        Schema::table('admin_activity_logs', fn (Blueprint $t) => $t->dropColumn('details'));
        Schema::table('eyewears', fn (Blueprint $t) => $t->dropColumn('stock_item_id'));
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['branch_id', 'permissions', 'totp_secret', 'totp_enabled', 'failed_logins', 'locked_until']));
        Schema::dropIfExists('eye_tests');
        Schema::table('customers', fn (Blueprint $t) => $t->dropColumn(['marketing_consent', 'consent_at', 'whatsapp_opt_in', 'branch_id']));
        Schema::dropIfExists('bill_return_items');
        Schema::dropIfExists('bill_returns');
        Schema::table('eye_checkup_bills', fn (Blueprint $t) => $t->dropColumn(['branch_id', 'payment_method', 'share_token', 'created_by']));
        Schema::table('frame_bill_items', fn (Blueprint $t) => $t->dropColumn('stock_item_id'));
        Schema::table('frame_bills', fn (Blueprint $t) => $t->dropColumn(['branch_id', 'payment_method', 'payment_status', 'paid_amount', 'share_token', 'payment_link_id', 'payment_link_url', 'created_by']));
        Schema::dropIfExists('stock_alert_rules');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('stock_units');
        Schema::dropIfExists('stock_inwards');
        Schema::dropIfExists('stock_levels');
        Schema::table('stock_items', fn (Blueprint $t) => $t->dropColumn(['brand', 'barcode', 'pack_quantity', 'supplier_id']));
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('branches');
    }
};
