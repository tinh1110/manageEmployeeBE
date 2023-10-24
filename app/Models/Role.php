<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $fillable = [
        'role_name',
        'description',
        'created_by_id',
        'update_by_id',
        'deleted_by_id',
        'role_permissions',
    ];

    public array $sortable = ['created_at'];

    protected $casts = [
        'role_permissions' => 'array',
    ];
}
