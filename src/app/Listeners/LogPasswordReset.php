<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogPasswordReset
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
    public function handle(PasswordReset $event): void
    {
        Log::info('security.password_reset.completed', [
            'user_id' => $event->user->id ?? null,
            'email'   => $event->user->email ?? null,
            'ip'      => request()->ip(),
            'ua'      => substr((string) request()->userAgent(), 0, 300),
        ]);
    }
}
