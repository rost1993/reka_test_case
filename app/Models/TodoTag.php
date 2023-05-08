<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TodoTag extends Model
{
    use HasFactory;

    protected $table = 'todo_tag';

    protected $fillable = ['todo_id', 'tag_id'];
}
