<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class FcmNotificationService
{
    protected static ?string $accessToken = null;
    protected static ?array $serviceAccount = null;

    protected static function getAccessToken(): ?string
    {
        if (self::$accessToken !== null) {
            return self::$accessToken;
        }

        $path = storage_path('app/brandy-bb646-firebase-adminsdk-fbsvc-c95660894c.json');
        if (!file_exists($path)) {
            return null;
        }

        self::$serviceAccount = json_decode(file_get_contents($path), true);
        if (self::$serviceAccount === null) {
            return null;
        }

        $jwt = \Firebase\JWT\JWT::encode([
            'iss' => self::$serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => time(),
            'exp' => time() + 3600,
        ], self::$serviceAccount['private_key'], 'RS256');

        $tokenResponse = Http::asForm()->withOptions(['verify' => false])
            ->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

        if (!$tokenResponse->successful()) {
            return null;
        }

        self::$accessToken = $tokenResponse->json()['access_token'];
        return self::$accessToken;
    }

    /**
     * إرسال إشعار FCM لـ tokens معينة
     * @param array $tokens قائمة device tokens
     * @param string $title عنوان الإشعار
     * @param string $body نص الإشعار
     * @param array $data بيانات مخصصة (ad_id, user_id, type, ...)
     */
    public static function sendToTokens(array $tokens, string $title, string $body, array $data = []): int
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken || empty($tokens)) {
            return 0;
        }

        $baseData = [
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ];
        $mergedData = array_merge($baseData, array_map(fn ($v) => (string) $v, $data));

        $successCount = 0;
        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $mergedData,
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->withOptions(['verify' => false])
                ->post(
                    'https://fcm.googleapis.com/v1/projects/' . self::$serviceAccount['project_id'] . '/messages:send',
                    $payload
                );

            if ($response->successful()) {
                $successCount++;
            }
        }

        return $successCount;
    }

    /**
     * إرسال إشعار لمجموعة مستخدمين
     */
    public static function sendToUserIds(array $userIds, string $title, string $body, array $data = []): int
    {
        $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('device_token')->unique()->values()->toArray();
        return self::sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * إشعار متعلق بإعلان (تعليق جديد، فولو، إلخ)
     */
    public static function sendAdNotification(array $userIds, int $adId, int $adOwnerUserId, string $title, string $body, ?int $excludeUserId = null): int
    {
        $userIds = array_unique(array_filter($userIds));
        if ($excludeUserId !== null) {
            $userIds = array_values(array_diff($userIds, [$excludeUserId]));
        }
        if (empty($userIds)) {
            return 0;
        }

        return self::sendToUserIds($userIds, $title, $body, [
            'type' => 'ad',
            'ad_id' => $adId,
            'user_id' => $adOwnerUserId,
        ]);
    }

    /**
     * إشعار من اللوحة (panel)
     */
    public static function sendPanelNotification(array $userIds, string $title, string $body): int
    {
        return self::sendToUserIds($userIds, $title, $body, [
            'type' => 'panel',
        ]);
    }
}
