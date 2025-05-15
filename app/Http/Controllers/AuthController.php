<?php

namespace App\Http\Controllers;

use App\Http\Resources\ApiResource;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function registration(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'password' => ['required', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/']
            ],
            [
                'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, simbol, dan minimal 8 karakter.'
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            DB::commit();
            return new ApiResource(true, 'User berhasil registrasi', $user);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|string|email',
                'password' => ['required', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/']
            ],
            [
                'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, simbol, dan minimal 8 karakter.'
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $credential = $request->only('email', 'password');

        if (!Auth::attempt($credential)) {
            return response()->json([
                'success' => false,
                'message' => 'Email & password tidak sesuai',
                'data' => null,
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return new ApiResource(true, 'User berhasil login', [
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return new ApiResource(true, 'User berhasil logout', null);
    }
}
