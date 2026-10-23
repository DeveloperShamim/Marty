<?php

namespace App\Jobs;

use App\Services\ServerTracking;
use Illuminate\Foundation\Bus\Dispatchable;

/** Runs right after the browser gets its reply (no queue worker needed): one shop event to Meta's Conversions API. */
class SendServerMetaEvent
{
    use Dispatchable;

    public function __construct(public array $event) {}

    public function handle(ServerTracking $tracking): void
    {
        $tracking->sendMeta([$this->event]);
    }
}
