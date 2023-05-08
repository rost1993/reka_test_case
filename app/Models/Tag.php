<?php

namespace App\Models;

use App\Models\Todo;
use App\Models\TodoTag;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $table = 'tags';

    protected $fillable = ['name'];

    public function todos() {
        return $this->belongsToMany(Todo::class, app(TodoTag::class)->getTable(), 'tag_id', 'todo_id');
    }
}
