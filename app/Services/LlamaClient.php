<?php

namespace App\Services;

use Carbon\Carbon;
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

     public function predict($prompt, $auth)
    {
        try {

            //Integrated laravel Carbon to access current date and time

            $currentDate = Carbon::now()->format('l, F d, Y');
            $currentTime = Carbon::now()->format('h: i A');


            //Define date and time sentences
            $dateTemplates = [
                "The current date is {date}, {auth}.",
                "Today's date is {date}, {auth}.",
                "The date today is {date}, {auth}.",
            ];

            $timeTemplates = [
                "The current time is {time}, {auth}.",
                "It's {time} right now, {auth}.",
                "The time is {time}, {auth}.",
            ];

            //Triggers the function getConversationHistory
            $conversationHistory = $this->getConversationHistory($auth);

            //If conversationHistory is empty, it will just a new conversation
            if (empty($conversationHistory)) {
                $conversation = Conversation::create(['user_id' => $auth->id]);
                $conversationHistory = [];
            } else {
                $conversation = Conversation::where('user_id', $auth->id)->first();
            }

            //Foreach loop to specify the messages if its user or the ai itself via boolean.
            $messages = [];
            foreach ($conversationHistory as $message) {
                if ($message->is_ai_response) {
                    $messages[] = ['role' => 'assistant', 'content' => $message->message];
                } else {
                    $messages[] = ['role' => 'user', 'content' => $message->message];
                }
            }

            $messages[] = ['role' => 'user', 'content' => $prompt];


            //INtegrating specific model and token to the project
            $response = Http::post($this->localUrl, [
                'model' => 'llama3.2:latest',
                'messages' => $messages,
                'max_tokens' => 2048,
                'temperature' => 0.7,
            ]);


            if ($response->successful()) {
                $responseData = $response->json();
                $aiResponse = $responseData['choices'][0]['message']['content'];
                
                //Conditional statements for time, date prompts
                if (stripos($prompt, 'date') !== false || stripos($prompt, 'today') !== false) {
                    $template = $dateTemplates[array_rand($dateTemplates)];
                    $aiResponse = str_replace("{date}", $currentDate, $template);
                    $aiResponse = str_replace("{auth}", $auth->name, $aiResponse);

                } elseif (stripos($prompt, 'time') !== false) {
                   $template = $timeTemplates[array_rand($timeTemplates)];
                    $aiResponse = str_replace("{time}", $currentTime, $template);
                    $aiResponse = str_replace("{auth}", $auth->name, $aiResponse);
                }


                $conversation = Conversation::where('user_id', $auth->id)->first();
                if(!$conversation){
                    $conversation = Conversation::create(['user_id' => $auth->id]);
                }

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
        $conversation = Conversation::where('user_id', $auth->id)->first();
        if (!$conversation) {
            return [];
        }

        $conversationMessages = ConversationMessage::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();

        return $conversationMessages;
    }
    
}
