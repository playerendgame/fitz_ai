<?php

namespace App\Http\Controllers\Ajax\Prompt;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\LlamaClient;

class PromptController extends Controller
{
    public function predict(Request $request){
        $prompt = $request->input('prompt');
        $llamaClient = new LlamaClient();
        $audioContent = $llamaClient->predict($prompt);

        $response = response()->make($audioContent, 200);
        $response->header('Content-Type', 'audio/mpeg');

        return $response;
    }
}
