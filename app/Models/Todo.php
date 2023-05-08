<?php

namespace App\Models;

use App\Models\User;
use App\Models\Tag;
use App\Models\TodoTag;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $table = 'todos';

    protected $fillable = ['header', 'body'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function tags() {
        return $this->belongsToMany(Tag::class, app(TodoTag::class)->getTable(), 'todo_id', 'tag_id')
            ->withPivot('id');
    }
}
