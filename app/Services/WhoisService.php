<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhoisService
{
    public function whois($logDNSs){
        /**
         * - Whois dos DNS's
         * - Salva o Whois no banco de dados
         * - Pequena pausa para evitar rate limiting
         */

        foreach($logDNSs as $logDNS){
            try{
                if($logDNS->dns){
                    $whois = whois_domain($logDNS->dns);
                    Log::info('Whois: ' . json_encode($whois, JSON_PRETTY_PRINT));
                }
            }catch(\Exception $e){
                Log::error('Erro ao buscar o Whois do domínio: ' . $e->getMessage());
            }

            $logDNS->create_date = $whois['create_date'] ?? null;
            $logDNS->update_date = $whois['update_date'] ?? null;
            $logDNS->expiry_date = $whois['expiry_date'] ?? null;
            $logDNS->country_name = $whois['registrant_contact']['country_name'] ?? null;
            $logDNS->state = $whois['registrant_contact']['state'] ?? null;
            $logDNS->city = $whois['registrant_contact']['city'] ?? null;
            $logDNS->company = $whois['registrant_contact']['company'] ?? null;
            $logDNS->status = $whois['status'] ?? null;
            $logDNS->save();

            usleep(100000); // 0.1 segundo
        }
    }
}
