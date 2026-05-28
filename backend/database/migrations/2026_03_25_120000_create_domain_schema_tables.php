<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('source');
            $table->text('issued_key')->nullable();
            $table->text('received_key')->nullable();
            $table->enum('type', ['remote', 'local', 'scraped']);
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->index('owner_user_id');
            $table->foreign('owner_user_id')->references('id')->on('users');
        });

        Schema::create('events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('name');
            $table->text('organizer');
            $table->date('start_date')->nullable();
            $table->text('description');
            $table->text('location')->nullable();
            $table->text('url');
            $table->uuid('app_id');
            $table->text('img')->nullable();
            $table->boolean('is_cancelled')->default(false);
            $table->timestamps();
            $table->foreign('app_id')->references('id')->on('clubs');
            $table->index('app_id');
        });

        Schema::create('ext_int_ids', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_id');
            $table->uuid('app_id');
            $table->text('external_id');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('app_id')->references('id')->on('clubs');
            $table->index('event_id');
            $table->index('app_id');
        });

        Schema::create('staging', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('raw_data');
            $table->uuid('app_id');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('app_id')->references('id')->on('clubs');
            $table->index('app_id');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->text('slug');
        });

        Schema::create('event_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->uuid('event_id');
            $table->unsignedBigInteger('tag_id');
            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('tag_id')->references('id')->on('tags');
            $table->index('event_id');
            $table->index('tag_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_tag');
        Schema::dropIfExists('ext_int_ids');
        Schema::dropIfExists('staging');
        Schema::dropIfExists('events');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('clubs');
    }
};
