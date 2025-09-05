<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required','string'],
            'password' => ['required','string'],
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->input('email'))->first();
        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
        }

        if (class_exists(\Tymon\JWTAuth\Facades\JWTAuth::class)) {
            $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
            return response()->json(['status' => 'ok', 'token' => $token]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'JWT not installed. Please install tymon/jwt-auth to enable token issuance.'
        ], 501);
    }
}
