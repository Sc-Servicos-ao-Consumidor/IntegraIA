<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\PrismService;
use Illuminate\Http\Request;

class SpeechController extends Controller
{
    public function textToSpeech(Request $request)
    {
        $request->validate([
            'text' => 'required|string|min:1',
            'voice' => 'nullable|string',
            'tenant_id' => 'integer|exists:tenants,id',
        ]);

        $text = $request->input('text');
        $voice = $request->input('voice', 'default');

        try {
            $prism = new PrismService;
            $response = $prism->textToSpeech($text, $voice);

            if (isset($response['audio_content'])) {
                return response()->json([
                    'response' => $response['audio_content'],
                ]);
            } else {
                return response()->json(['error' => 'Failed to generate speech audio.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Text-to-Speech failed: '.$e->getMessage()], 500);
        }
    }

    public function speechToText(Request $request)
    {
        $request->validate([
            'audio_url' => 'required|url',
            'tenant_id' => 'integer|exists:tenants,id',
        ]);

        try {
            $prism = new PrismService;
            $text = $prism->speechToText($request->input('audio_url'));

            if ($text !== null) {
                return response()->json([
                    'response' => $text,
                ]);
            } else {
                return response()->json(['error' => 'Failed to transcribe audio.'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Speech-to-Text failed: '.$e->getMessage()], 500);
        }
    }
}
