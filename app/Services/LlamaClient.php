<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Memory;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechResponse;


class LlamaClient
{
    private $localUrl;

    public function __construct()
    {
        $this->localUrl = 'http://localhost:11434/v1/chat/completions';
    }

    public function generate($prompt, $auth)
    {
        try {
        
            //Fetch conversation history
            $conversationHistory = $this->getConversationHistory($auth);

            //Create conversation if none exists
            $conversation = Conversation::firstOrCreate(['user_id' => $auth->id]);

            //Fetch memories for this user and prepare system message
            $memories = Memory::where('user_id', $auth->id)->get();
            $memoryText = "";

            if ($memories->isNotEmpty()) {
                $memoryText = "Here are things you should remember about the user:\n";
                foreach ($memories as $memory) {
                    $memoryText .= "- " . $memory->summary . "\n";
                }
            }

            //Prepare messages array including memories as system message
            $messages = [];

            if (!empty($memoryText)) {
                $messages[] = [
                    'role' => 'system',
                    'content' => $memoryText,
                ];
            }

            //Add conversation history to messages
            foreach ($conversationHistory as $message) {
                $messages[] = [
                    'role' => $message->is_ai_response ? 'assistant' : 'user',
                    'content' => $message->message,
                ];
            }

            //Add current prompt as user message
            $messages[] = ['role' => 'user', 'content' => $prompt];

            //Call AI API
            $response = Http::post($this->localUrl, [
                'model' => 'llama3.2:latest',
                'messages' => $messages,
                'max_tokens' => 2048,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $aiResponse = $responseData['choices'][0]['message']['content'];


                //Save messages in conversation
                ConversationMessage::create([
                    'conversation_id' => $conversation->id,
                    'message' => $prompt,
                    'is_ai_response' => false,
                ]);

                ConversationMessage::create([
                    'conversation_id' => $conversation->id,
                    'message' => $aiResponse,
                    'is_ai_response' => true,
                ]);

                //Check if user wants to save this to memories
                if (preg_match('/remember this|save this|keep this/i', $prompt)) {
                    Memory::create([
                        'user_id' => $auth->id,
                        'summary' => $prompt . " → " . $aiResponse,
                    ]);
                }

                return $aiResponse;

            } else {
                $statusCode = $response->status();
                $reason = $response->reason();
                $responseBody = $response->body();

                Log::error("API request failed with status code $statusCode: $reason. Response body: $responseBody");
                throw new \Exception("API request failed with status code $statusCode: $reason");
            }

        } catch (\Exception $e) {
            Log::error("Error making API request: " . $e->getMessage());
            throw new \Exception("Error making API request: " . $e->getMessage());
        }
    }


    //Public function to fetch conversation History
    public function getConversationHistory($auth)
    {
        $conversationIds = Conversation::where('user_id', $auth->id)->pluck('id');

        $conversationMessages = ConversationMessage::whereIn('conversation_id', $conversationIds)
            ->orderBy('created_at', 'asc')
            ->get();

        return $conversationMessages;
    }
    
}
