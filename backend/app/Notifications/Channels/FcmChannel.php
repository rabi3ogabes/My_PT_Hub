<?php

namespace App\Notifications\Channels;

use App\Domain\Identity\Models\Device;
use App\Domain\Identity\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging HTTP v1 channel (Android + iOS via APNs).
 * Uses a service account (FIREBASE_CREDENTIALS json path) for OAuth.
 * Invalid tokens are removed automatically.
 */
class FcmChannel
{
    public function send(object $notifiable, Notification $notification): void
    {
        if (! $notifiable instanceof User || ! method_exists($notification, 'toFcm')) {
            return;
        }
        $credentials = $this->credentials();
        if ($credentials === null) {
            return; // push not configured in this environment
        }
        /** @var FcmMessage $message */
        $message = $notification->toFcm($notifiable);
        $token = $this->accessToken($credentials);
        $url = "https://fcm.googleapis.com/v1/projects/{$credentials['project_id']}/messages:send";

        $notifiable->devices()->whereNotNull('push_token')->each(function (Device $device) use ($message, $token, $url) {
            $response = Http::withToken($token)->timeout(10)->post($url, ['message' => [
                'token' => $device->push_token,
                'notification' => array_filter(['title' => $message->title, 'body' => $message->body, 'image' => $message->imageUrl]),
                'data' => array_map('strval', $message->data),
                'android' => ['priority' => 'HIGH', 'notification' => ['tag' => $message->threadId, 'channel_id' => $message->data['category'] ?? 'default']],
                'apns' => ['payload' => ['aps' => ['thread-id' => $message->threadId, 'sound' => 'default', 'mutable-content' => 1]]],
            ]]);
            if ($response->status() === 404 || $response->json('error.details.0.errorCode') === 'UNREGISTERED') {
                $device->update(['push_token' => null]);
            } elseif ($response->failed()) {
                Log::warning('fcm.send_failed', ['device' => $device->id, 'status' => $response->status()]);
            }
        });
    }

    /** @return array<string, string>|null */
    private function credentials(): ?array
    {
        $path = config('services.fcm.credentials');
        if (! $path || ! is_file($path)) {
            return null;
        }

        return json_decode((string) file_get_contents($path), true) ?: null;
    }

    /** @param array<string, string> $c */
    private function accessToken(array $c): string
    {
        return Cache::remember('fcm:token:'.$c['client_email'], 3300, function () use ($c) {
            $now = time();
            $jwt = JWT::encode([
                'iss' => $c['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ], $c['private_key'], 'RS256');

            return (string) Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ])->throw()->json('access_token');
        });
    }
}
