<?php

namespace App\Listeners;

use App\Events\SimpleLogEvent;
use App\Mail\SimpleMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SimpleLogListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SimpleLogEvent $event): void
    {
        // (new SimpleMail())->send("jeff@mail.net", "");
    }
}
