<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type')->comment('out, income');
            $table->decimal('value', 10, 2)->comment('valor');
            $table->string('name')->comment('descrição curta');
            $table->string('description')->nullable()->comment('descrição');
            $table->string('status')->default('published')->comment('status');
            $table->date('expense_date')->comment('data');


            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('category_id')->constrained()->onDelete('set null');
            $table->foreignUuid('document_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamps();
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
