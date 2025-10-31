<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        '_id',
        'userId',
        'name',
        'description',
        'price',
        'offerPrice',
        'images',
        'category',
        'timestamp',
        '__v',
    ];

    // Cast JSON fields properly
    protected $casts = [
        'images' => 'array',
    ];
}
