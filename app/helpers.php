<?php

use Illuminate\Support\Facades\Http;


if (!function_exists('whois_domain')) {
    /**
     * Consulta whois de um domínio
     *
     * @param string $domain
     * @return array
     */
    function whois_domain(string $domain): array
    {
        $response = Http::get('https://api.whoisfreaks.com/v1.0/whois?whois=live&domainName=' . $domain . '&apiKey=' . env('WHOIS_FREAKS_API_KEY'));
        return $response->json();
    }
}