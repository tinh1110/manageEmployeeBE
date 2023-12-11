<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Issue extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function created_by()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
    public function assignee()
    {
        return $this->hasOne(User::class, 'id', 'assignee_id');
    }

    public function parent()
    {
        return $this->belongsTo(Issue::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Issue::class, 'parent_id');
    }

    protected $fillable = [
        'assignee_id',
        'project_id',
        'subject',
        'parent_id',
        'description',
        'start_date',
        'end_date',
        'priority',
        'status',
        'comment',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected array $sortable = ['created_at'];
    protected $casts = [
        'image' => 'array',
    ];
}
