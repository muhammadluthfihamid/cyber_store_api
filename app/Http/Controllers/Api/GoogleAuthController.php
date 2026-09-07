<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleAuthController extends Controller
{
    /**
     * Authenticate or register a customer using Google Sign-In.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function loginWithGoogle(Request $request): JsonResponse
    {
        $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        $idToken = $request->input('id_token');
        $email = null;
        $name = null;
        $googleId = null;

        // Mock bypass is restricted to the 'testing' environment ONLY (PHPUnit automated tests).
        // Do NOT include 'local' here — a staging/production server misconfigured with
        // APP_ENV=local would expose this bypass to attackers, allowing account takeover
        // without any Google verification.
        if ($idToken === 'mock-google-token' && app()->environment('testing')) {
            $email    = $request->input('email', 'mockuser@ubsistore.test');
            $name     = $request->input('name', 'Mock Google User');
            $googleId = $request->input('google_id', 'mock-google-id-123456');
        } else {
            try {
                // Call Google's tokeninfo API to verify the integrity and decode the ID token with timeout
                $response = Http::withoutVerifying()
                    ->withOptions([
                        'curl' => [
                            CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                        ],
                    ])
                    ->timeout(10)
                    ->get('https://oauth2.googleapis.com/tokeninfo', [
                        'id_token' => $idToken,
                    ]);
            } catch (\Exception $e) {
                Log::error('Google Token Verification Connection Timeout/Error', [
                    'message' => $e->getMessage(),
                ]);
                throw ValidationException::withMessages([
                    'id_token' => ['Gagal memverifikasi token Google karena masalah koneksi. Silakan coba beberapa saat lagi.'],
                ]);
            }

            if (!$response->successful()) {
                Log::error('Google Token Verification Failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw ValidationException::withMessages([
                    'id_token' => ['Token Google tidak valid atau telah kedaluwarsa.'],
                ]);
            }

            $payload = $response->json();

            // Verifikasi audience (client ID) dari token
            // PENTING: Android menghasilkan token dengan Android Client ID atau Web Client ID sebagai audience.
            // Kita perlu menerima KEDUANYA.
            $webClientId     = config('services.google.client_id')     ?: env('GOOGLE_CLIENT_ID');
            $androidClientId = config('services.google.android_client_id') ?: env('GOOGLE_ANDROID_CLIENT_ID');

            $allowedAudiences = array_filter([$webClientId, $androidClientId]);

            if (!empty($allowedAudiences) && isset($payload['aud'])) {
                if (!in_array($payload['aud'], $allowedAudiences, true)) {
                    Log::error('Google Token Client ID Mismatch', [
                        'expected_any_of' => $allowedAudiences,
                        'actual'          => $payload['aud'],
                    ]);
                    throw ValidationException::withMessages([
                        'id_token' => ['Client ID tidak sesuai.'],
                    ]);
                }
            }

            $email    = $payload['email']  ?? null;
            $name     = $payload['name']   ?? null;
            $googleId = $payload['sub']    ?? null; // 'sub' is Google's unique identifier for the user
        }

        if (empty($email) || empty($googleId)) {
            throw ValidationException::withMessages([
                'id_token' => ['Data email atau Google ID tidak ditemukan di dalam token.'],
            ]);
        }

        // 1. Search for user by google_id
        $user = User::query()->where('google_id', '=', $googleId)->first();

        if (!$user) {
            // 2. Search for user by email to link the Google ID if they registered with email previously
            $user = User::query()->where('email', '=', $email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleId,
                ]);
            } else {
                // 3. Create a new user with Customer role
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'role' => User::ROLE_CUSTOMER,
                    'is_active' => true,
                    'push_notifications_enabled' => true,
                    'email_notifications_enabled' => true,
                    'biometric_login_enabled' => false,
                    'password' => Hash::make(Str::random(16)), // Random strong password since they login via Google
                ]);
            }
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Akun tidak aktif.'], 403);
        }

        // Ensure the user has an associated shopping cart
        Cart::firstOrCreate(['user_id' => $user->id]);

        // Generate Sanctum access token
        $token = $user->createToken('flutter-token')->plainTextToken;

        return response()->json([
            'message' => 'Login Google berhasil.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    /**
     * Handle callback from Google OAuth authorization code.
     * Exchanges code for ID token and logs the user in.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleCallback(Request $request): JsonResponse
    {
        $code = $request->input('code');

        if (!$code) {
            return response()->json(['message' => 'Authorization code tidak ditemukan.'], 400);
        }

        $clientId     = config('services.google.client_id')     ?: env('GOOGLE_CLIENT_ID');
        $clientSecret = config('services.google.client_secret') ?: env('GOOGLE_CLIENT_SECRET');
        $redirectUri  = config('services.google.redirect_uri')  ?: env('GOOGLE_REDIRECT_URI', 'http://localhost:3000/auth/google/callback');

        try {
            $response = Http::asForm()
                ->withoutVerifying()
                ->withOptions([
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    ],
                ])
                ->timeout(15)
                ->post('https://oauth2.googleapis.com/token', [
                'code'          => $code,
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri'  => $redirectUri,
                'grant_type'    => 'authorization_code',
            ]);
        } catch (\Exception $e) {
            Log::error('Google OAuth Code Exchange Connection Error', [
                'message' => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Gagal menghubungi server Google.'], 500);
        }

        if (!$response->successful()) {
            Log::error('Google OAuth Code Exchange Failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            $errorMsg = $response->json('error_description') ?? 'Kode otorisasi Google tidak valid atau telah kedaluwarsa.';
            return response()->json(['message' => $errorMsg], 400);
        }

        $idToken = $response->json('id_token');

        if (!$idToken) {
            return response()->json(['message' => 'ID Token tidak ditemukan dari respon Google.'], 400);
        }

        $request->merge(['id_token' => $idToken]);

        return $this->loginWithGoogle($request);
    }
}
