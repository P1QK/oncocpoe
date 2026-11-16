<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'onc_users';

    protected $fillable = [
        'username',
        'name',
        'email',
        'role',
        'license_no',
        'department',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin'], true);
    }

    public function canAccessDoctor(): bool
    {
        return in_array($this->role, ['superadmin', 'admin', 'doctor'], true);
    }

    public function canAccessPharmacist(): bool
    {
        return in_array($this->role, ['superadmin', 'admin', 'pharmacist'], true);
    }

    public function canAccessNurse(): bool
    {
        return in_array($this->role, ['superadmin', 'admin', 'nurse'], true);
    }

    public function roleTitle(): string
    {
        return match ($this->role) {
            'superadmin' => 'Super Admin (ผู้ดูแลระบบสูงสุด)',
            'admin'      => 'Admin (ผู้ดูแลระบบ)',
            'doctor'     => 'Doctor (แพทย์)',
            'pharmacist' => 'Pharmacist (เภสัชกร)',
            'nurse'      => 'Nurse (พยาบาล)',
            default      => $this->role,
        };
    }

    public function roleBadgeClass(): string
    {
        return match ($this->role) {
            'superadmin' => 'bg-danger text-white',
            'admin'      => 'bg-dark text-white',
            'doctor'     => 'bg-primary text-white',
            'pharmacist' => 'bg-success text-white',
            'nurse'      => 'bg-info text-dark',
            default      => 'bg-secondary text-white',
        };
    }
}
