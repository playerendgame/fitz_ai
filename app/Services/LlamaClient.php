<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechResponse;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;


class LlamaClient
{
    private $apiKey;
    private $apiUrl;

    public function __construct()
    {
        $this->apiKey = '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';
        $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    private function elevenLabsTextToSpeech($text)
    {
        $voiceId = '29vD33N1CtxCmqQRPOHJ';
        $apiUrl = "https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}";
        $apiKey = 'sk_0893a6377d0c7dfca375e52bfac3c0c6980cc4fd499f4feb';

        $response = Http::withHeaders([
            'xi-api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'audio/mpeg'
        ])->post($apiUrl, [
            'text' => $text,
            'model_id' => 'eleven_monolingual_v1',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.5
            ]
        ]);

        if ($response->successful()) {
            return $response->body();
        } else {
            Log::error("ElevenLabs API failed with status code " . $response->status() . ". Response body: " . $response->body());
            throw new \Exception('Failed to make API request to ElevenLabs');
        }
    }


    public function predict($prompt)
    {
        try {
            $specificQuestions = [
                'who made you' => 'Clyde Timothy R. Sumabat',
                'who created you' => 'Clyde Timothy R. Sumabat',
                'Who is your creator' => 'Clyde Timothy R. Sumabat'
            ];

            $lowerCasePrompt = strtolower($prompt);
            foreach ($specificQuestions as $question => $answer) {
                if (strpos($lowerCasePrompt, $question) !== false) {
                    return $answer;
                }
            }

            $response = Http::withToken($this->apiKey)
                ->post($this->apiUrl, [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 2048,
                    'temperature' => 0.7,
                ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $assistantMessage = $responseData['choices'][0]['message']['content'];

                try {

                    $audioContent = $this->elevenLabsTextToSpeech($assistantMessage);
        
                    return response($audioContent)
                    ->header('Content-Type', 'audio/mpeg')
                    ->header('Content-Disposition', 'inline; filename = "response.mp3');

                } catch (\Exception $e) {
                    Log::error("Failed to generate audio: " . $e->getMessage());
                    throw new \Exception("Failed to generate audio");
                }

            } else {
                $statusCode = $response->status();
                $reason = $response->reason();
                $responseBody = $response->body();

                Log::error("API request failed with status code $statusCode: $reason. Response body: $responseBody");

                throw new \Exception("Failed to make API request: $statusCode $reason");
            }
        } catch (\Exception $e) {
            Log::error("Error making API request: " . $e->getMessage());
            throw new \Exception("Error making API request: " . $e->getMessage());
        }
    }

    
}
