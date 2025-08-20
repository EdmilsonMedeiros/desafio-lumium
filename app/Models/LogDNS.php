<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogDNS extends Model
{
    protected $fillable = [
        'user_id',
        'log_dns_file_id',
        'dns',
        'ip_address',
        'classification',
        'status',
        'timestamp',
        'ai_response',
        'create_date',
        'update_date',
        'expiry_date',
        'country_name',
        'state',
        'city',
        'company',
        'status',
    ];

    protected $with = ['user'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logDNSFile()
    {
        return $this->belongsTo(LogDNSFile::class);
    }
}
