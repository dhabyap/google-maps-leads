<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('google_map_clients', function (Blueprint $table) {
            $table->id();
            $table->string('google_place_id')->unique();
            $table->string('business_name');
            $table->string('category')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('website_url')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->float('rating')->default(0.0);
            $table->integer('review_count')->default(0);
            $table->string('search_keyword')->index();
            $table->string('status')->default('new'); // enum: new, contacted, interested, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_map_clients');
    }
};
