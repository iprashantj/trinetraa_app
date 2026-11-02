<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2: purchase orders, stock takes, work orders (repair/delivery tracking), GST invoice numbering,
 * coupons/loyalty redemption at POS, and store-pickup reservations. Additive only.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Purchase orders ───────────────────────────────────────────────
        Schema::create('purchase_orders', function (Blueprint $t) {
            $t->id();
            $t->string('po_number', 40)->unique();
            $t->unsignedBigInteger('supplier_id')->nullable()->index();
            $t->unsignedBigInteger('branch_id')->nullable()->index();
            $t->string('status', 20)->default('draft')->index(); // draft|ordered|partial|received|cancelled
            $t->date('order_date')->nullable();
            $t->date('expected_date')->nullable();
            $t->decimal('total', 12, 2)->default(0);
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });
        Schema::create('purchase_order_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('purchase_order_id')->index();
            $t->unsignedBigInteger('stock_item_id')->index();
            $t->unsignedInteger('quantity_ordered');
            $t->unsignedInteger('quantity_received')->default(0);
            $t->decimal('unit_cost', 12, 2)->default(0);
            $t->timestamps();
        });
        if (Schema::hasTable('stock_inwards') && ! Schema::hasColumn('stock_inwards', 'purchase_order_id')) {
            Schema::table('stock_inwards', fn (Blueprint $t) => $t->unsignedBigInteger('purchase_order_id')->nullable()->index());
        }

        // ── Stock take (physical count vs system) ─────────────────────────
        Schema::create('stock_takes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('branch_id')->index();
            $t->string('status', 20)->default('open')->index(); // open|completed|cancelled
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
        });
        Schema::create('stock_take_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('stock_take_id')->index();
            $t->unsignedBigInteger('stock_item_id')->index();
            $t->integer('system_qty')->default(0);
            $t->integer('counted_qty')->nullable();
            $t->string('note', 255)->nullable();
            $t->timestamps();
        });

        // ── Work orders: repair / lens order / delivery tracking ──────────
        Schema::create('work_orders', function (Blueprint $t) {
            $t->id();
            $t->string('order_number', 40)->unique();
            $t->unsignedBigInteger('branch_id')->nullable()->index();
            $t->unsignedBigInteger('customer_id')->nullable()->index();
            $t->unsignedBigInteger('frame_bill_id')->nullable()->index();
            $t->string('customer_name')->nullable();
            $t->string('customer_contact', 40)->nullable();
            $t->string('description', 500)->nullable();
            $t->string('status', 20)->default('ordered')->index(); // ordered|in_fitting|ready|delivered|cancelled
            $t->date('expected_date')->nullable();
            $t->timestamp('ready_at')->nullable();
            $t->timestamp('delivered_at')->nullable();
            $t->timestamp('notified_at')->nullable();
            $t->text('notes')->nullable();
            $t->unsignedBigInteger('created_by')->nullable();
            $t->timestamps();
        });

        // ── GST invoices, discounts, loyalty redemption, review requests ──
        Schema::table('branches', function (Blueprint $t) {
            if (! Schema::hasColumn('branches', 'gstin')) {
                $t->string('gstin', 20)->nullable();
            }
            if (! Schema::hasColumn('branches', 'invoice_prefix')) {
                $t->string('invoice_prefix', 10)->nullable();
            }
            if (! Schema::hasColumn('branches', 'review_url')) {
                $t->string('review_url', 500)->nullable(); // Google review link for this branch
            }
        });
        Schema::table('stock_items', function (Blueprint $t) {
            if (! Schema::hasColumn('stock_items', 'hsn_code')) {
                $t->string('hsn_code', 12)->nullable();
            }
            if (! Schema::hasColumn('stock_items', 'gst_rate')) {
                $t->decimal('gst_rate', 5, 2)->nullable();
            }
        });
        Schema::table('frame_bill_items', function (Blueprint $t) {
            if (! Schema::hasColumn('frame_bill_items', 'hsn_code')) {
                $t->string('hsn_code', 12)->nullable();
            }
            if (! Schema::hasColumn('frame_bill_items', 'cost_price')) {
                $t->decimal('cost_price', 12, 2)->nullable(); // snapshot at sale time, for margin reports
            }
        });
        Schema::table('frame_bills', function (Blueprint $t) {
            foreach (['invoice_no' => fn () => $t->string('invoice_no', 40)->nullable()->unique(),
                      'customer_gstin' => fn () => $t->string('customer_gstin', 20)->nullable(),
                      'cgst' => fn () => $t->decimal('cgst', 12, 2)->default(0),
                      'sgst' => fn () => $t->decimal('sgst', 12, 2)->default(0),
                      'coupon_code' => fn () => $t->string('coupon_code', 40)->nullable(),
                      'coupon_discount' => fn () => $t->decimal('coupon_discount', 12, 2)->default(0),
                      'loyalty_points_used' => fn () => $t->unsignedInteger('loyalty_points_used')->default(0),
                      'loyalty_discount' => fn () => $t->decimal('loyalty_discount', 12, 2)->default(0),
                      'review_requested_at' => fn () => $t->timestamp('review_requested_at')->nullable()] as $col => $add) {
                if (! Schema::hasColumn('frame_bills', $col)) {
                    $add();
                }
            }
        });
        Schema::table('eye_checkup_bills', function (Blueprint $t) {
            if (! Schema::hasColumn('eye_checkup_bills', 'invoice_no')) {
                $t->string('invoice_no', 40)->nullable()->unique();
            }
        });
        Schema::table('customers', function (Blueprint $t) {
            if (! Schema::hasColumn('customers', 'dob')) {
                $t->date('dob')->nullable();
            }
        });

        Schema::create('invoice_sequences', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('branch_id');
            $t->string('fy', 9);               // e.g. 2026-27
            $t->string('kind', 10)->default('frame'); // frame|eye
            $t->unsignedInteger('last_no')->default(0);
            $t->unique(['branch_id', 'fy', 'kind']);
        });

        Schema::create('coupons', function (Blueprint $t) {
            $t->id();
            $t->string('code', 40)->unique();
            $t->string('description')->nullable();
            $t->string('type', 10)->default('percent'); // percent|flat
            $t->decimal('value', 12, 2);
            $t->decimal('min_amount', 12, 2)->default(0);
            $t->decimal('max_discount', 12, 2)->nullable();
            $t->string('brand')->nullable();            // limit to one brand
            $t->date('valid_from')->nullable();
            $t->date('valid_to')->nullable();
            $t->unsignedInteger('usage_limit')->nullable();
            $t->unsignedInteger('used_count')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        // ── Store-pickup reservations from the public website ─────────────
        Schema::create('reservations', function (Blueprint $t) {
            $t->id();
            $t->string('code', 20)->unique();
            $t->unsignedBigInteger('stock_item_id')->nullable()->index();
            $t->unsignedBigInteger('eyewear_id')->nullable()->index();
            $t->unsignedBigInteger('branch_id')->index();
            $t->unsignedInteger('quantity')->default(1);
            $t->string('name');
            $t->string('phone', 40);
            $t->string('email')->nullable();
            $t->string('status', 20)->default('pending')->index(); // pending|confirmed|collected|cancelled|expired
            $t->timestamp('expires_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['reservations', 'coupons', 'invoice_sequences', 'work_orders', 'stock_take_items', 'stock_takes', 'purchase_order_items', 'purchase_orders'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
