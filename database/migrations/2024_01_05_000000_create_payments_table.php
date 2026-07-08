<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('submissions')->cascadeOnDelete();
            $table->foreignId('processed_by')->constrained('users'); // user Finance yang memproses
            $table->decimal('amount', 18, 2); // nilai yang dibayarkan (= nilai submission)
            $table->decimal('saldo_before', 18, 2); // saldo perusahaan sebelum transaksi
            $table->decimal('saldo_after', 18, 2); // saldo perusahaan setelah transaksi
            $table->enum('status', ['Success', 'Failed'])->default('Success');
            $table->text('catatan')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
