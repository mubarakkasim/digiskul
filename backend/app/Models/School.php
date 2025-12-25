<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class School extends BaseTenant implements TenantWithDatabase
{
    use HasFactory, HasDatabase, HasDomains;

    protected $table = 'schools';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'logo',
        'subdomain',
        'domain',
        'license_valid_until',
        'subscription_plan',
        'active',
        'meta',
    ];

    protected $casts = [
        'license_valid_until' => 'datetime',
        'active' => 'boolean',
        'meta' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function classes()
    {
        return $this->hasMany(ClassModel::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'subdomain',
            'domain',
            'license_valid_until',
            'subscription_plan',
            'active',
        ];
    }
}

