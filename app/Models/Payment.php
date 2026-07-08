<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'submission_id', 'processed_by', 'amount', 'saldo_before', 'saldo_after',
        'status', 'catatan', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'saldo_before' => 'decimal:2',
        'saldo_after' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function submission()
    {
        return $this->belongsTo(Pengajuan::class, 'submission_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
