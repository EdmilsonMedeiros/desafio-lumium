<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LogDNSService;
use App\Http\Requests\LogDNSRequest;

class LogDNSController extends Controller
{
    public function __construct(private LogDNSService $logDNSService)
    {
        $this->logDNSService = $logDNSService;
    }

    public function submit(LogDNSRequest $request)
    {
        $file = $request->file('dns_file');
        try{
            $this->logDNSService->processFile($file);
            return redirect()->route('dashboard')->with('success', 'Arquivo processado com sucesso.');
        }catch(\Exception $e){
            return redirect()->route('dashboard')->with('error', $e->getMessage());
        }
    }

    public function getLogDNSs(Request $request)
    {
        $logDNSFiles = $this->logDNSService->getLogDNSs($request);
        return response()->json($logDNSFiles);
    }

    public function getLogDNSStatistics()
    {
        $logDNSStatistics = $this->logDNSService->getLogDNSStatistics();
        return response()->json($logDNSStatistics);
    }
}
