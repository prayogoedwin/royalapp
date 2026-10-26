<?php

namespace App\Jobs;

use App\Services\Firebase\FcmClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendCrewPushNotification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  list<string>  $tokens
     * @param  array<string, string>  $data
     */
    public function __construct(
        public array $tokens,
        public string $title,
        public string $body,
        public array $data = [],
    ) {}

    public function handle(FcmClient $client): void
    {
        try {
            $client->sendToTokens($this->tokens, $this->title, $this->body, $this->data);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
