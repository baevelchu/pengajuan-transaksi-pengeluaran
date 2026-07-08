<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan')->unique(); // Auto Generate: PGJ-YYYYMMDD-0001
            $table->date('tanggal_pengajuan');
            $table->foreignId('user_id')->constrained('users')->comment('Nama Pengaju');
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->decimal('nilai', 18, 2);
            $table->text('deskripsi')->nullable();
            $table->string('lampiran_path')->nullable();
            $table->string('lampiran_original_name')->nullable();

            $table->string('status')->default('Draft');
            // Draft, Submitted, Waiting SPV Approval, Waiting Manager Approval,
            // Waiting Director Approval, Waiting Finance, Paid, Rejected

            $table->boolean('is_po_produk')->default(false);
            $table->boolean('requires_direktur')->default(false); // nilai > 10jt after manager, or PO produk langsung ke direktur

            $table->string('rejected_reason')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
