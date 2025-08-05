<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PremBook extends Model
{
     use HasFactory, SoftDeletes;

    protected $fillable = [
        'book_id'
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
