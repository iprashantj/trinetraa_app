<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Full Trinetraa Optician schema — ported 1:1 from the Next.js Prisma schema.
 * Column names match the Prisma @map() snake_case names.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password_hash');
            $table->string('face_shape')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('eyewears', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('discount_percentage')->default(0);
            $table->string('image')->nullable();
            $table->string('brand')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('amazon_url', 500)->nullable();
            $table->string('flipkart_url', 500)->nullable();
            $table->boolean('amazon_enabled')->default(false);
            $table->boolean('flipkart_enabled')->default(false);
            $table->string('availability')->default('in_store')->nullable();
            $table->string('marketplace_sku')->nullable();
            $table->string('try_on_image')->nullable();
            $table->float('try_on_offset_x')->default(0)->nullable();
            $table->float('try_on_offset_y')->default(0)->nullable();
            $table->float('try_on_scale')->default(1)->nullable();
            $table->string('face_shape_tags')->nullable();
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
        });

        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->timestamps();
        });

        Schema::create('recently_viewed', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('eyewear_id');
            $table->timestamp('viewed_at')->useCurrent();
            $table->unique(['customer_id', 'eyewear_id']);
            $table->foreign('customer_id')->references('id')->on('customer_users')->cascadeOnDelete();
            $table->foreign('eyewear_id')->references('id')->on('eyewears')->cascadeOnDelete();
        });

        Schema::create('wishlist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->unsignedBigInteger('eyewear_id');
            $table->timestamp('added_at')->useCurrent();
            $table->unique(['customer_id', 'eyewear_id']);
            $table->foreign('customer_id')->references('id')->on('customer_users')->cascadeOnDelete();
            $table->foreign('eyewear_id')->references('id')->on('eyewears')->cascadeOnDelete();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('user_name');
            $table->string('user_email')->unique();
            $table->integer('rating');
            $table->text('review_text');
            $table->string('image')->nullable();
            $table->boolean('is_approved')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('contact_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('embed_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('stock_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model_number')->nullable()->index();
            $table->string('sku')->nullable()->unique();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->integer('current_stock')->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('type');
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->date('order_date')->index();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->index();
            $table->unsignedBigInteger('stock_item_id')->index();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('stock_item_id')->references('id')->on('stock_items')->cascadeOnDelete();
        });

        Schema::create('frame_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_contact')->nullable();
            $table->date('bill_date')->index();
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });

        Schema::create('frame_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('frame_bill_id')->index();
            $table->string('brand_name');
            $table->string('model_number')->nullable();
            $table->decimal('price', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
            $table->foreign('frame_bill_id')->references('id')->on('frame_bills')->cascadeOnDelete();
        });

        Schema::create('eye_checkup_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_contact')->nullable();
            $table->date('bill_date')->index();
            $table->string('left_eye')->nullable();
            $table->string('right_eye')->nullable();
            $table->string('addition')->nullable();
            $table->decimal('frame_amount', 12, 2)->default(0);
            $table->decimal('glass_amount', 12, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->decimal('other_amount', 12, 2)->default(0);
            $table->boolean('with_gst')->default(false);
            $table->decimal('gst_rate', 5, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('service');
            $table->date('appt_date')->index();
            $table->string('time_slot');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->text('story')->nullable();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
            $table->index(['is_published', 'published_at']);
        });

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('badge')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('price')->nullable();
            $table->string('duration')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('product_enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('eyewear_id')->index();
            $table->string('eyewear_name');
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('notify_me_requests', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('eyewear_id')->index();
            $table->boolean('is_notified')->default(false);
            $table->timestamps();
            $table->foreign('eyewear_id')->references('id')->on('eyewears')->cascadeOnDelete();
        });

        Schema::create('serviceable_pincodes', function (Blueprint $table) {
            $table->id();
            $table->string('pincode')->unique();
            $table->string('area')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('loyalty_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->unique();
            $table->integer('points')->default(0);
            $table->string('tier')->default('Bronze');
            $table->integer('total_earned')->default(0);
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customer_users')->cascadeOnDelete();
        });

        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->index();
            $table->string('type');
            $table->integer('points');
            $table->text('description')->nullable();
            $table->string('reference_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('account_id')->references('id')->on('loyalty_accounts')->cascadeOnDelete();
        });

        Schema::create('lens_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('lens_name');
            $table->string('brand')->nullable();
            $table->string('power')->nullable();
            $table->integer('interval_days');
            $table->date('next_due_date');
            $table->string('status')->default('active');
            $table->string('address_line')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign('customer_id')->references('id')->on('customer_users')->cascadeOnDelete();
        });

        Schema::create('subscription_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id')->index();
            $table->timestamp('ordered_at')->useCurrent();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->foreign('subscription_id')->references('id')->on('lens_subscriptions')->cascadeOnDelete();
        });

        Schema::create('crm_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('lead');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('contact_id')->index();
            $table->string('type');
            $table->text('description')->nullable();
            $table->timestamp('done_at')->useCurrent();
            $table->foreign('contact_id')->references('id')->on('crm_contacts')->cascadeOnDelete();
        });

        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('email')->nullable();
            $table->string('subject')->nullable();
            $table->string('status')->default('sent');
            $table->timestamp('sent_at')->useCurrent();
            $table->text('error')->nullable();
        });

        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->string('endpoint')->unique();
            $table->string('p256dh');
            $table->string('auth');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('customer_id')->references('id')->on('customer_users')->cascadeOnDelete();
        });

        Schema::create('vision_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('answers');
            $table->text('result')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('eyewear_tags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('eyewear_id');
            $table->string('tag')->index();
            $table->unique(['eyewear_id', 'tag']);
            $table->foreign('eyewear_id')->references('id')->on('eyewears')->cascadeOnDelete();
        });

        Schema::create('erp_export_logs', function (Blueprint $table) {
            $table->id();
            $table->string('export_type');
            $table->string('format');
            $table->integer('row_count')->default(0);
            $table->string('filename')->nullable();
            $table->string('status')->default('success');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        foreach ([
            'erp_export_logs', 'eyewear_tags', 'vision_assessments', 'push_subscriptions',
            'reminder_logs', 'crm_activities', 'crm_contacts', 'subscription_orders',
            'lens_subscriptions', 'loyalty_transactions', 'loyalty_accounts', 'serviceable_pincodes',
            'notify_me_requests', 'product_enquiries', 'newsletter_subscribers', 'services',
            'offers', 'blog_posts', 'brands', 'appointments', 'eye_checkup_bills',
            'frame_bill_items', 'frame_bills', 'order_items', 'orders', 'customers',
            'stock_items', 'gallery_items', 'contact_enquiries', 'reviews', 'wishlist_items',
            'recently_viewed', 'site_settings', 'eyewears', 'categories', 'customer_users',
        ] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
