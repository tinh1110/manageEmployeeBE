<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imported_users extends Model
{

    use HasFactory;
    protected  const isFail = 1;
    protected  const isSuccess = 0;
    public function created_by()
    {
        return $this->hasOne(User::class, 'id', 'created_by_id');
    }
    public function getUser()
    {
        return $this->belongsTo('App\Models\User', 'created_by_id', 'id');
    }
    public function status($status){
        if ($status == self::isFail) return'Import Lỗi';
        return 'Import thành công';
    }
    protected $fillable = [
        'file_name', 'user_id','status','fail_amount','success_amount','error','created_by_id'
    ];
    public $sortable = [
        'created_at'
    ];
}
