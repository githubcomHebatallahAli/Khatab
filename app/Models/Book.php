<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
     use HasFactory, SoftDeletes;
    const storageFolder= 'Book';
    protected $fillable = [
        'grade_id',
        'img',
        'nameOfBook',
        'price',
        'description',
        'creationDate',
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    // public function orders()
    // {
    //     return $this->hasMany(Order::class);
    // }
}
