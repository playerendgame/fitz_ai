<?php

namespace App\Http\Controllers\Ajax\Prompt;

use Illuminate\Http\Request;
use App\Services\LlamaClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PromptController extends Controller
{
    public function predict(Request $request){
        $prompt = $request->input('prompt');
        $auth = Auth::guard('users')->user();
        $llamaClient = new LlamaClient();
        $response = $llamaClient->predict($prompt, $auth);

        return response()->json(['response' => $response]);
    }
}
