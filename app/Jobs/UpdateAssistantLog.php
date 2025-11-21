<?php

namespace App\Jobs;

use App\Models\AssistantLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAssistantLog implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $interactionId;

    protected string $sessionId;

    protected ?string $query;

    protected ?string $response;

    protected ?string $rating; // 'up' | 'down' | null

    protected ?string $expectedResponse;

    /**
     * Create a new job instance.
     */
    public function __construct(
        ?int $interactionId,
        string $sessionId,
        ?string $query,
        ?string $response,
        ?string $rating,
        ?string $expectedResponse
    ) {
        $this->interactionId = $interactionId;
        $this->sessionId = $sessionId;
        $this->query = $query;
        $this->response = $response;
        $this->rating = $rating;
        $this->expectedResponse = $expectedResponse;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $interaction = null;

        if ($this->interactionId) {
            $interaction = AssistantLog::where('id', $this->interactionId)
                ->where('session_id', $this->sessionId)
                ->first();
        }

        if (! $interaction) {
            $interaction = AssistantLog::where('session_id', $this->sessionId)
                ->latest('id')
                ->first();
        }

        if (! $interaction) {
            $interaction = AssistantLog::create([
                'session_id' => $this->sessionId,
                'query' => (string) ($this->query ?? ''),
                'response' => (string) ($this->response ?? ''),
                'meta' => ['created_from_feedback' => true],
            ]);
        }

        if ($this->rating !== null) {
            $interaction->rating = $this->rating;
        }
        if ($this->expectedResponse !== null && $this->expectedResponse !== '') {
            $interaction->expected_response = $this->expectedResponse;
        }

        $interaction->save();
    }
}
