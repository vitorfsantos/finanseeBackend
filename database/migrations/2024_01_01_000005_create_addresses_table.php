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
    Schema::create('addresses', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->uuid('addressable_id');
      $table->string('addressable_type'); // 'user' ou 'company'
      $table->string('street');
      $table->string('number')->nullable();
      $table->string('complement')->nullable();
      $table->string('neighborhood')->nullable();
      $table->string('city');
      $table->string('state', 2);
      $table->string('zipcode');
      $table->string('country')->default('Brasil');
      $table->timestamps();
      $table->softDeletes();

      $table->index(['addressable_type', 'addressable_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('addresses');
  }
};
