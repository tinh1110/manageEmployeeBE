<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentIssue extends Model
{

    use HasFactory;
    protected $table = 'comment_issues';
    /**
     * @var string[]
     */
    protected $fillable = ['user_id','issue_id','body'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }
}
