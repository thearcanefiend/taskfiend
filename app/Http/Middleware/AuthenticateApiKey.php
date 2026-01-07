<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'API key required. Provide as Bearer token.',
            ], 401);
        }

        $apiKeys = ApiKey::whereNull('invalidated_at')->with('user')->get();

        $validKey = null;
        foreach ($apiKeys as $apiKey) {
            if (Hash::check($token, $apiKey->key_hash)) {
                $validKey = $apiKey;
                break;
            }
        }

        if (!$validKey) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired API key.',
            ], 401);
        }

        if (!$validKey->user->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'User account is disabled.',
            ], 403);
        }

        $request->setUserResolver(function () use ($validKey) {
            return $validKey->user;
        });

        return $next($request);
    }
}
