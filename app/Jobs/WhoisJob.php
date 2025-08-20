<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\WhoisService;
use Illuminate\Support\Facades\Log;

class WhoisJob implements ShouldQueue
{
    use Queueable;

    private $logDNSs;
    private $whoisService;
    /**
     * Create a new job instance.
     */
    public function __construct($logDNSs)
    {
        $this->logDNSs = $logDNSs;
        $this->whoisService = new WhoisService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->whoisService->whois($this->logDNSs);
    }
}
