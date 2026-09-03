<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\EncryptionHelper;

class ApiEncryptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Decrypt request if X-Encrypted header is present.
        // Skip decryption for multipart/form-data requests (e.g. photo uploads),
        // because the body is form-data, not an encrypted JSON payload.
        $isMultipart = str_contains($request->header('Content-Type', ''), 'multipart/form-data');

        if ($request->header('X-Encrypted') === 'true'
            && !$request->isMethod('GET')
            && !$request->isMethod('DELETE')
            && !$isMultipart
        ) {
            $payload = $request->input('payload');
            if ($payload) {
                $decrypted = EncryptionHelper::decrypt($payload);
                if (is_array($decrypted)) {
                    // Replace request input with decrypted data
                    $request->replace($decrypted);
                } else {
                    return response()->json(['message' => 'Gagal mendekripsi payload request.'], 400);
                }
            }
        }

        // 2. Process request
        $response = $next($request);

        // 3. Encrypt response if client requested encryption (X-Encrypted header)
        if ($response instanceof \Illuminate\Http\JsonResponse && $request->header('X-Encrypted') === 'true') {
            $data = $response->getData(true);
            
            // Skip encryption for midtrans callback or if no encryption key configured
            if ($request->is('api/v1/payments/midtrans-callback') || !config('app.api_encryption_key')) {
                return $response;
            }

            $encryptedData = EncryptionHelper::encrypt($data);
            
            $response->setData([
                'payload' => $encryptedData
            ]);
            $response->headers->set('X-Encrypted', 'true');
        }

        return $response;
    }
}
