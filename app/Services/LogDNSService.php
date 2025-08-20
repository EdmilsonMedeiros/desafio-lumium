<?php

namespace App\Services;

use App\Models\LogDNS;
use App\Models\LogDNSFiles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Jobs\WhoisJob;

class LogDNSService
{
    public function processFile($file)
    {
        $file_name = $file->getClientOriginalName();
        $logDNSFile = null;
        $logDNSs = null;

        try{
            $path = $this->saveFile($file);
            $logDNSFile = $this->saveLogDNS($file_name, $path);
            $logDNSs = $this->processDataFile($logDNSFile);
            $this->classifyLogDNS($logDNSs);
            dispatch(new WhoisJob($logDNSs));
        }catch(\Exception $e){
            // Rollback all operations
            if($path){
                Storage::delete($path);
            }

            if($logDNSFile){
                $logDNSFile->delete();
            }

            if($logDNSs){
                foreach($logDNSs as $logDNS){
                    $logDNS->delete();
                }
            }

            throw new \Exception('Erro ao processar o arquivo: ' . $e);
        }

        return $logDNSFile;
    }

    private function saveFile($file)
    {
        /**
         * - Salva o arquivo no storage
         * - Retorna o caminho do arquivo
         */
        $directory = 'dns_files/user_' . auth()->user()->id . '/' . date('Y-m-d_H-i-s');
        $path = $file->storeAs($directory, $file->getClientOriginalName());
        return $path;
    }

    private function saveLogDNS($file_name, $path)
    {
        /**
         * - Salva o arquivo no banco de dados
         * - Retorna o log de DNS
         */
        $logDNSFile = new LogDNSFiles();
        $logDNSFile->user_id = auth()->user()->id;
        $logDNSFile->file_name = $file_name;
        $logDNSFile->path = $path;
        $logDNSFile->save();
        return $logDNSFile;
    }

    private function processDataFile($logDNSFile){
        /**
         * - Lê o arquivo e salva os dados no banco de dados
         * - Retorna os logs de DNS
         */
        $file = Storage::get($logDNSFile->path);
        $file = explode("\n", $file);
        $logDNSs = [];
        foreach($file as $key => $line){

            if($key == 0 || $line == '' || $line == null){
                continue;
            }

            $line = explode(",", $line);
            $logDNS = new LogDNS();
            $logDNS->user_id = auth()->user()->id;
            $logDNS->log_dns_file_id = $logDNSFile->id;
            $logDNS->dns = $line[1] ?? null;
            $logDNS->ip_address = $line[2] ?? null;
            $logDNS->timestamp = $line[0] ?? null;
            $logDNS->save();
            $logDNSs[] = $logDNS;
        }

        return $logDNSs;
    }

    public function getLogDNSs($request)
    {
        /**
         * - Busca os logs de DNS
         * - Filtra pelo nome do arquivo, IP, DNS, timestamp e classificação
         * - Ordena por data de criação em ordem decrescente
         * - Retorna os logs paginados
         */
        $logDNSs = LogDNS::where('file_name', 'like', '%' . $request->search . '%')
        ->orWhere('ip_address', 'like', '%' . $request->search . '%')
        ->orWhere('dns', 'like', '%' . $request->search . '%')
        ->orWhere('timestamp', 'like', '%' . $request->search . '%')
        ->orWhere('classification', 'like', '%' . $request->search . '%')
        ->where('user_id', auth()->user()->id)
        ->orderBy('created_at', 'desc')
        ->paginate(5, ['*'], 'page', $request->page ?? 1);

        return $logDNSs;
    }

    public function getLogDNSStatistics()
    {
        /**
         * - Total de logs analisados
         * - Percentual por categoria
         * - Últimos 10 domínios classificados como maliciosos
         */
        $logDNSStatistics = LogDNS::where('user_id', auth()->user()->id)
        ->select('classification', DB::raw('COUNT(*) as total'))
        ->groupBy('classification')
        ->get();
        
        $totalLogs              = LogDNS::where('user_id', auth()->user()->id)->count();
        $percentualSeguro       = ($logDNSStatistics->where('classification', 'Seguro')->sum('total') / $totalLogs) * 100;
        $percentualMalicioso    = ($logDNSStatistics->where('classification', 'Malicioso')->sum('total') / $totalLogs) * 100;
        $percentualSuspeito     = ($logDNSStatistics->where('classification', 'Suspeito')->sum('total') / $totalLogs) * 100;
        $last10Maliciosos       = LogDNS::where('user_id', auth()->user()->id)->where('classification', 'Malicioso')->limit(10)->get();
        $totalMaliciosos        = LogDNS::where('user_id', auth()->user()->id)->where('classification', 'Malicioso')->count();

        $logDNSStatistics = [
            'totalLogs' => $totalLogs,
            'percentualSeguro' => $percentualSeguro,
            'percentualMalicioso' => $percentualMalicioso,
            'percentualSuspeito' => $percentualSuspeito,
            'ultimos10Maliciosos' => $last10Maliciosos,
            'totalMaliciosos' => $totalMaliciosos
        ];

        return $logDNSStatistics;
    }

    private function classifyLogDNS($logDNSs){
        /**
         * - Classificação de logs de DNS por Seguro, Malicioso ou Suspeito
         * - Utiliza a API Gemini do Google para classificar os logs
         * - Salva a classificação no banco de dados
         * - Pequena pausa para evitar rate limiting
         */
        foreach($logDNSs as $logDNS) {
            try{
                // Requisição para Google Gemini para classificar o DNS's
                $apiKey = env('GEMINI_API_KEY');
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => 'Você é um especialista em segurança de rede. Analise o seguinte log DNS e classifique como uma das três categorias: "Seguro", "Malicioso" ou "Suspeito".
                                    Considere os seguintes critérios:
                                    - Domínios conhecidamente maliciosos
                                    - IPs suspeitos ou de países de risco
                                    - Padrões típicos de malware/phishing
                                    - Domínios com caracteres estranhos ou muito longos
                                    - Domínios pouco conhecidos
                                    - Reputação da extensão do domínio
                                    Log DNS para análise:
                                    Domínio: ' . ($logDNS->dns ?? 'N/A') . '
                                    IP: ' . ($logDNS->ip_address ?? 'N/A') . '
                                    Timestamp: ' . ($logDNS->timestamp ?? 'N/A') . '
                                    Responda APENAS com uma das três palavras: Seguro, Malicioso ou Suspeito.'
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 10,
                        'topP' => 0.8,
                        'topK' => 10
                    ]
                ]);

                if($response->successful()) {
                    $responseData = $response->json();
                    
                    // Extrai a classificação da resposta
                    $classification = 'Não classificado';
                    if(isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
                        $content = trim($responseData['candidates'][0]['content']['parts'][0]['text']);
                        // Verifica se a resposta contém uma das classificações válidas
                        if(stripos($content, 'Seguro') !== false) {
                            $classification = 'Seguro';
                        } elseif(stripos($content, 'Malicioso') !== false) {
                            $classification = 'Malicioso';
                        } elseif(stripos($content, 'Suspeito') !== false) {
                            $classification = 'Suspeito';
                        }
                    }

                    // Salva a classificação no banco de dados
                    $logDNS->classification = $classification;
                    $logDNS->save();
                } else {
                    Log::error('Erro na resposta da API Gemini: ' . $response->body());
                    $logDNS->classification = 'Erro na classificação';
                    $logDNS->save();
                }

            }catch(\Exception $e){
                Log::error('Erro ao classificar o log de DNS ID ' . $logDNS->id . ': ' . $e->getMessage());
                $logDNS->classification = 'Erro na classificação';
                $logDNS->save();
            }

            // Pequena pausa para evitar rate limiting
            usleep(100000); // 0.1 segundo
        }
    }
}
