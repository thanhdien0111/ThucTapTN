<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'checkin_time', 'checkout_time', 'distance', 'user_name', 'latitude', 'longitude'];
    protected $table = 'sessions';
    protected $dates = ['checkin_time', 'checkout_time'];
}
