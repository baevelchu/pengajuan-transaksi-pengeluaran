<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['role'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roleRef()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Accessor: mengembalikan nama role (string, mis. "staff", "spv") dari
     * relasi roleRef, supaya kode & view yang sudah ada (mis. $user->role,
     * middleware role:staff) tetap berfungsi seperti sebelum tabel `roles`
     * dinormalisasi.
     */
    public function getRoleAttribute(): ?string
    {
        return $this->roleRef?->name;
    }

    public function pengajuans()
    {
        return $this->hasMany(Pengajuan::class);
    }

    public function approvalLogs()
    {
        return $this->hasMany(Approval::class);
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isSpv(): bool
    {
        return $this->role === 'spv';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isDirektur(): bool
    {
        return $this->role === 'direktur';
    }

    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }
}
