<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'codiceFiscale' => ['nullable', 'string', 'max:255'],
            'birthDate' => ['nullable', 'date'],
            'birthPlace' => ['nullable', 'string', 'max:255'],
            'residence' => ['nullable', 'string', 'max:255'],

        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'firstName' => $validated['firstName'],
            'lastName' => $validated['lastName'],
            'codiceFiscale' => $validated['codiceFiscale'],
            'birthDate' => $validated['birthDate'],
            'birthPlace' => $validated['birthPlace'],
            'residence' => $validated['residence'],
        ]);

        $tokenResult = $user->createToken('auth_token', ['*'], now()->addMinutes((int) config('sanctum.access_token_expiration', 60)));

        return response()->json([
            'access_token'  => $tokenResult->plainTextToken,
            'token_type'    => 'Bearer',
            'expires_at'    => $tokenResult->accessToken->expires_at
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'username'    => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            throw ValidationException::withMessages([
                'username' => ['Credenziali non valide.'],
            ]);
        }
        /** @var User $user */
        $user = Auth::user();

        // Revoca tutti i token precedenti (opzionale)
        $user->tokens()->delete();

        // Access token (breve durata)
        $accessToken = $user->createToken(
            'access_token',
            ['*'],
            now()->addMinutes((int) config('sanctum.access_token_expiration', 60))
        );

        // Refresh token (lunga durata)
        $refreshToken = $user->createToken(
            'refresh_token',
            ['refresh'],
            now()->addDays((int) config('sanctum.refresh_token_expiration', 30))
        );

        return response()->json([
            'access_token'  => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type'    => 'Bearer',
            'expires_at'    => $accessToken->accessToken->expires_at,
        ]);
    }

    public function refresh(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        // Verifica che il token corrente sia un refresh token
        if (!$request->user()->currentAccessToken()->can('refresh')) {
            return response()->json(['message' => 'Token non autorizzato per il refresh.'], 403);
        }

        // Revoca il refresh token usato
        $request->user()->currentAccessToken()->delete();

        // Crea un nuovo access token
        $accessToken = $user->createToken(
            'access_token',
            ['*'],
            now()->addMinutes((int) config('sanctum.access_token_expiration', 60))
        );

        // Crea un nuovo refresh token (rotation)
        $refreshToken = $user->createToken(
            'refresh_token',
            ['refresh'],
            now()->addDays((int) config('sanctum.refresh_token_expiration', 30))
        );

        return response()->json([
            'access_token'  => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken,
            'token_type'    => 'Bearer',
            'expires_at'    => $accessToken->accessToken->expires_at,
        ]);
    }

    public function logout(Request $request)
    {
        // Revoca solo il token corrente
        /** @var User $user */
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout effettuato con successo.']);
    }

    public function logoutAll(Request $request)
    {
        // Revoca tutti i token (utile per "esci da tutti i dispositivi")
        /** @var User $user */
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout da tutti i dispositivi effettuato.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
