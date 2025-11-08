<?php

namespace App\Helpers;
class JWTHelper
{
    /**
     * Generate a JSON Web Token (JWT) with the given payload and secret key.
     *
     * @param array $payload        The payload data to include in the JWT  The secret key used to sign the JWT
     * @param int $expirySeconds    The expiration time in seconds from now
     * @return string               The generated JWT
     */
    public static function generateJWT(array $payload, int $expirySeconds): string
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload['exp'] = time() + $expirySeconds;
        $payload = json_encode($payload);

        $secretKey = env('JWT_SECRET');

        // Base64 URL encode
        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payload);

        // Create signature
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secretKey, true);
        $base64Signature = self::base64UrlEncode($signature);

        // Return JWT
        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    // Helper
    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function decodeJWT(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            throw new \Exception('Invalid token format');
        }

        [$base64Header, $base64Payload, $base64Signature] = $parts;

        $header = json_decode(base64_decode($base64Header), true);
        $payload = json_decode(base64_decode($base64Payload), true);

        $secretKey = env('JWT_SECRET');
        $expectedSignature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secretKey, true);
        $expectedBase64Signature = rtrim(strtr(base64_encode($expectedSignature), '+/', '-_'), '=');

        if (!hash_equals($expectedBase64Signature, $base64Signature)) {
            throw new \Exception('Invalid signature');
        }

        if (isset($payload['exp']) && time() > $payload['exp']) {
            throw new \Exception('Token expired');
        }

        return $payload;
    }

}