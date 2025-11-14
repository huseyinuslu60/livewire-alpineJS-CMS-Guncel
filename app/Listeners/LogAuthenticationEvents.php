<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Request;
use Modules\Logs\Models\UserLog;

class LogAuthenticationEvents
{
    /**
     * Handle the Login event.
     */
    public function handleLogin(Login $event): void
    {
        UserLog::log(
            action: 'login',
            description: 'Kullanıcı giriş yaptı: '.($event->user instanceof User ? $event->user->name : 'Bilinmeyen'),
            ipAddress: Request::ip(),
            userAgent: Request::userAgent(),
            url: Request::url(),
            method: Request::method(),
            metadata: [
                'user_id' => $event->user instanceof User ? $event->user->id : null,
                'user_email' => $event->user instanceof User ? $event->user->email : null,
                'login_time' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle the Logout event.
     */
    public function handleLogout(Logout $event): void
    {
        UserLog::log(
            action: 'logout',
            description: 'Kullanıcı çıkış yaptı: '.($event->user instanceof User ? $event->user->name : 'Bilinmeyen'),
            ipAddress: Request::ip(),
            userAgent: Request::userAgent(),
            url: Request::url(),
            method: Request::method(),
            metadata: [
                'user_id' => $event->user instanceof User ? $event->user->id : null,
                'user_email' => $event->user instanceof User ? $event->user->email : null,
                'logout_time' => now()->toDateTimeString(),
            ]
        );
    }

    /**
     * Handle the Failed login event.
     */
    public function handleFailed(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'Bilinmeyen email';

        UserLog::log(
            action: 'login_failed',
            description: "Başarısız giriş denemesi: {$email}",
            ipAddress: Request::ip(),
            userAgent: Request::userAgent(),
            url: Request::url(),
            method: Request::method(),
            metadata: [
                'email' => $event->credentials['email'] ?? null,
                'attempt_time' => now()->toDateTimeString(),
            ]
        );
    }
}
