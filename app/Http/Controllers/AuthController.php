<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // ✅ Validation
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // confirm field must be 'password_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ Create user
        $user = User::create([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // ✅ Generate token
        //$token = $user->createToken('auth_token')->plainTextToken;

        // return response()->json([
        //     'status' => true,
        //     'message' => 'User registered successfully',
        //     'user' => $user,
        //     'token' => $token,
        // ], 201);

        return redirect()->route('login')->with('success', 'Registration successful! Please login.');
    }

    public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:8',
    ]);

    $user = \App\Models\User::where('email', $request->email)->first();

    if (!$user || ! Hash::check($request->password, $user->password)) {
        return response()->json([
            'status' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    // return response()->json([
    //     'status' => true,
    //     'message' => 'Login successful',
    //     'user' => $user,
    //     'token' => $token
    // ]);

    return redirect()->route('dashboard')->with('success', 'Login Successful !, Welcome to Dashboard !');
}

public function me(Request $request)
{
    return response()->json([
        'status' => true,
        'user' => $request->user(),
    ]);
}

public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'status' => true,
        'message' => 'Logged out successfully'
    ]);
}

}
