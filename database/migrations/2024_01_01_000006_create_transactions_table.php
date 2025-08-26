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
    Schema::create('transactions', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
      $table->foreignUuid('company_id')->nullable()->constrained('companies')->onDelete('cascade');
      $table->enum('type', ['income', 'expense']);
      $table->string('category')->nullable();
      $table->text('description')->nullable();
      $table->decimal('amount', 12, 2);
      $table->date('date');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('transactions');
  }
};
