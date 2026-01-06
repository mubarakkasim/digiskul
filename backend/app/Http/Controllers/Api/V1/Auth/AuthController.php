<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);

            Log::info('Login attempt', ['email' => $request->email]);
            
            $user = User::where('email', $request->email)
                ->orWhere('phone', $request->email)
                ->first();

            if (!$user) {
                Log::warning('User not found', ['email' => $request->email]);
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            if (!Hash::check($request->password, $user->password)) {
                Log::warning('Invalid password', ['email' => $request->email]);
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            if (!$user->active) {
                Log::warning('Inactive user login attempt', ['email' => $request->email]);
                throw ValidationException::withMessages([
                    'email' => ['Your account is inactive. Please contact administrator.'],
                ]);
            }

            // Update last login
            $user->update(['last_login' => now()]);

            $token = $user->createToken('auth-token')->plainTextToken;

            // Load relationships
            $user->load(['roles', 'school']);

            Log::info('Login successful', ['user_id' => $user->id, 'role' => $user->role]);

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'user' => $user,
                ],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'message' => 'Logged out'
            ]);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user()->load(['roles', 'school']);
            
            return response()->json([
                'success' => true,
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            Log::error('Get user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get user data',
            ], 500);
        }
    }
}

