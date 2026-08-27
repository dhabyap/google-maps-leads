<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleMapClient extends Model
{
    protected $fillable = [
        'google_place_id',
        'business_name',
        'category',
        'phone_number',
        'website_url',
        'address',
        'latitude',
        'longitude',
        'rating',
        'review_count',
        'search_keyword',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'rating' => 'float',
        'review_count' => 'integer',
    ];
}
