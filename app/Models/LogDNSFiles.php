<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogDNSFiles extends Model
{
    protected $fillable = [
        'user_id' => 'required',
        'file_name',
        'path',
    ];

    public function logDNS()
    {
        return $this->hasMany(LogDNS::class);
    }
}
