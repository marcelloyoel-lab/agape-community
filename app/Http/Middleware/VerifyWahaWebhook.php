<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class VerifyWahaWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.waha.webhook_key');

        if ($secret === '') {
            Log::critical('WAHA webhook secret is not configured.');

            return response()->json([
                'message' => 'Webhook authentication unavailable.',
            ], 500);
        }

        $signature = $request->header('X-Webhook-Hmac');
        $algorithm = strtolower((string) $request->header('X-Webhook-Hmac-Algorithm'));

        if (! is_string($signature) || $signature === '' || $algorithm !== 'sha512') {
            Log::warning('WAHA webhook authentication failed.', [
                'request_id' => $request->header('X-Webhook-Request-Id'),
                'reason' => 'Missing signature or unsupported algorithm.',
            ]);

            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $expectedSignature = hash_hmac(
            'sha512',
            $request->getContent(),
            $secret
        );

        if (! hash_equals($expectedSignature, $signature)) {
            Log::warning('WAHA webhook authentication failed.', [
                'request_id' => $request->header('X-Webhook-Request-Id'),
                'reason' => 'Invalid signature.',
            ]);

            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        return $next($request);
    }
}