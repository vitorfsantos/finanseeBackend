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
    Schema::create('company_user', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('company_id')->constrained('companies')->onDelete('cascade');
      $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
      $table->enum('role', ['owner', 'manager', 'employee'])->default('employee');
      $table->string('position')->nullable();
      $table->timestamps();
      $table->softDeletes();

      $table->unique(['company_id', 'user_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('company_user');
  }
};
