<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportAttendances extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'created_by_id',
        'file_name',
        'status',
        'success_amount',
        'fail_amount',
        'error',
    ];

    public function users()
    {
        return $this->hasOne('App\Models\User', 'id', 'created_by_id');
    }

    public array $sortable = ['created_at'];


    protected $table = 'imported_attendances';
}
