<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'submission_id', 'user_id', 'role_id', 'action', 'catatan',
    ];

    public function submission()
    {
        return $this->belongsTo(Pengajuan::class, 'submission_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roleRef()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Accessor agar $approval->role tetap bisa dipakai seperti string nama role
     * (kompatibel dengan tampilan yang sudah ada), meskipun disimpan sebagai role_id.
     */
    public function getRoleAttribute(): ?string
    {
        return $this->roleRef?->name;
    }
}
