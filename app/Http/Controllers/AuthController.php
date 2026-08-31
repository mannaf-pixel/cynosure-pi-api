<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Email ya password galat hai.',
            ], 401);
        }

        $user = auth('api')->user();

        if (! $user->is_active) {
            auth('api')->logout();
            return response()->json([
                'success' => false,
                'message' => 'Aapka account deactivate hai. Admin se contact karein.',
            ], 403);
        }

        // Load company
        $user->load('company');

        return response()->json([
            'success' => true,
            'token'   => $token,
            'user'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'role'           => $user->role,
                'is_super_admin' => $user->is_super_admin,
                'company_id'     => $user->company_id,
                'company'        => $user->company ? [
                    'id'           => $user->company->id,
                    'name'         => $user->company->name,
                    'slug'         => $user->company->slug,
                    'pricing_mode' => $user->company->pricing_mode,
                ] : null,
            ],
        ]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me()
    {
        $user = auth('api')->user();
        $user->load('company');

        return response()->json([
            'success' => true,
            'user'    => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'role'           => $user->role,
                'is_super_admin' => $user->is_super_admin,
                'company_id'     => $user->company_id,
                'company'        => $user->company ? [
                    'id'           => $user->company->id,
                    'name'         => $user->company->name,
                    'slug'         => $user->company->slug,
                    'pricing_mode' => $user->company->pricing_mode,
                ] : null,
            ],
        ]);
    }
}